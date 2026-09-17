#!/bin/bash
# ============================================================
# RedSim - Ubuntu Server Setup Script (Local MySQL)
# ============================================================
# Laravel 13 + Nginx + PHP 8.3-FPM + Node.js + Cloudflare Tunnel
# Database: MySQL (Local Server)

set -e

DOMAIN="projectai.my.id"
TUNNEL_NAME="projectai-tunnel"
PROJECT_DIR="/var/www/projectai"
UPLOAD_DIR="$(pwd)"
PHP_VERSION="8.3"
NODE_VERSION="20"

# MySQL Settings
DB_NAME="projectai_db"
DB_USER="projectai_user"
# Generate random password for DB
DB_PASS=$(openssl rand -base64 12)

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'
BOLD='\033[1m'

print_header() {
    echo ""
    echo -e "${CYAN}╔══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║${NC} ${BOLD}$1${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════════════╝${NC}"
    echo ""
}
print_step() { echo -e "${GREEN}[✓]${NC} $1"; }
print_info() { echo -e "${BLUE}[i]${NC} $1"; }
print_warn() { echo -e "${YELLOW}[!]${NC} $1"; }
print_error() { echo -e "${RED}[✗]${NC} $1"; }

if [ "$EUID" -ne 0 ]; then
    print_error "Script ini harus dijalankan sebagai root (sudo)!"
    exit 1
fi

print_header "RedSim - Setup Ubuntu Server (MySQL DB Lokal)"
read -p "$(echo -e ${YELLOW}Domain yang akan digunakan [${DOMAIN}]: ${NC})" INPUT_DOMAIN
if [ -n "$INPUT_DOMAIN" ]; then DOMAIN="$INPUT_DOMAIN"; fi

print_header "STEP 1/11 — Update System"
apt update -y && apt upgrade -y
apt install -y software-properties-common curl wget gnupg2 unzip git ca-certificates apt-transport-https

print_header "STEP 2/11 — Install MySQL Server"
apt install -y mysql-server
systemctl start mysql
systemctl enable mysql

print_info "Membuat Database dan User MySQL..."
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
print_step "Database: ${DB_NAME}"
print_step "Username: ${DB_USER}"
print_step "Password: ${DB_PASS}"

print_header "STEP 3/11 — Install PHP ${PHP_VERSION} + MySQL Ext"
add-apt-repository -y ppa:ondrej/php
apt update -y
# Install php-mysql instead of php-pgsql
apt install -y php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-common php${PHP_VERSION}-mysql php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-bcmath php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-intl php${PHP_VERSION}-zip php${PHP_VERSION}-readline php${PHP_VERSION}-tokenizer php${PHP_VERSION}-dom php${PHP_VERSION}-fileinfo

sed -i "s/^user = .*/user = www-data/" /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 64M/" /etc/php/${PHP_VERSION}/fpm/php.ini
systemctl restart php${PHP_VERSION}-fpm

print_header "STEP 4/11 — Install Nginx"
apt install -y nginx

print_header "STEP 5/11 — Install Node.js"
curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -
apt install -y nodejs

print_header "STEP 6/11 — Install Composer"
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

print_header "STEP 7/11 — Memindahkan File Project"
mkdir -p "${PROJECT_DIR}"
if [ -f "${UPLOAD_DIR}/artisan" ]; then
    cp -rf "${UPLOAD_DIR}/"* "${PROJECT_DIR}/" 2>/dev/null || true
    cp -rf "${UPLOAD_DIR}/".* "${PROJECT_DIR}/" 2>/dev/null || true
elif [ -d "${UPLOAD_DIR}/backend" ]; then
    cp -rf "${UPLOAD_DIR}/backend/"* "${PROJECT_DIR}/" 2>/dev/null || true
    cp -rf "${UPLOAD_DIR}/backend/".* "${PROJECT_DIR}/" 2>/dev/null || true
fi

cd "${PROJECT_DIR}"
if [ -f ".env.deploy" ]; then
    cp .env.deploy .env
    sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
    sed -i "s|ASSET_URL=.*|ASSET_URL=https://${DOMAIN}|" .env
    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|SESSION_DOMAIN=.*|SESSION_DOMAIN=${DOMAIN}|" .env
    sed -i "s|GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=https://${DOMAIN}/oauth/google/callback|" .env
    
    # Configure MySQL
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
    sed -i "s|DB_HOST=.*|DB_HOST=127.0.0.1|" .env
    sed -i "s|DB_PORT=.*|DB_PORT=3306|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    # We use sed differently for password to avoid special character conflicts
    # Remove existing DB_PASSWORD, DB_SSLMODE line
    sed -i '/^DB_PASSWORD=/d' .env
    sed -i '/^DB_SSLMODE=/d' .env
    # Insert new DB_PASSWORD under DB_USERNAME
    sed -i "/^DB_USERNAME=.*/a DB_PASSWORD=\"${DB_PASS}\"" .env
fi

# Fix permissions sebelum artisan commands
chown -R www-data:www-data "${PROJECT_DIR}"
chmod -R 775 "${PROJECT_DIR}/storage" "${PROJECT_DIR}/bootstrap/cache"

print_header "STEP 8/11 — Install Dependencies"
sudo -u www-data composer install --no-dev --optimize-autoloader || sudo -u www-data composer dump-autoload --optimize
sudo -u www-data npm ci --ignore-scripts 2>/dev/null || sudo -u www-data npm install --ignore-scripts 2>/dev/null || true
sudo -u www-data npm run build

print_info "Clearing and building cache..."
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan storage:link || true
sudo -u www-data php artisan migrate --force || true

print_header "STEP 9/11 — Konfigurasi Nginx"
cat > /etc/nginx/sites-available/projectai <<NGINX_CONF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${PROJECT_DIR}/public;
    index index.php index.html;
    client_max_body_size 64M;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    location /build/ { expires 1y; add_header Cache-Control "public, immutable"; }
}
NGINX_CONF
ln -sf /etc/nginx/sites-available/projectai /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

print_header "STEP 10/11 — Firewall (UFW)"
apt install -y ufw
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 7844/tcp
ufw allow 7844/udp
ufw --force enable

print_header "STEP 11/11 — Setup Cloudflare Tunnel"
ARCH=\$(dpkg --print-architecture)
if [ "\$ARCH" = "amd64" ]; then CLOUDFLARED_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb"; else CLOUDFLARED_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64.deb"; fi
wget -q "\$CLOUDFLARED_URL" -O /tmp/cloudflared.deb && dpkg -i /tmp/cloudflared.deb

echo -e "\n${YELLOW}SIAPKAN BROWSER UNTUK LOGIN CLOUDFLARE${NC}\n"
read -p "Tekan ENTER untuk login..." _
cloudflared tunnel login

cloudflared tunnel delete "${TUNNEL_NAME}" 2>/dev/null || true
cloudflared tunnel create "${TUNNEL_NAME}"
TUNNEL_ID=\$(cloudflared tunnel list | grep "${TUNNEL_NAME}" | awk '{print \$1}')

mkdir -p /root/.cloudflared
cat > /root/.cloudflared/config.yml <<TUNNEL_CONF
tunnel: \${TUNNEL_ID}
credentials-file: /root/.cloudflared/\${TUNNEL_ID}.json
ingress:
  - hostname: ${DOMAIN}
    service: http://localhost:80
  - hostname: www.${DOMAIN}
    service: http://localhost:80
  - service: http_status:404
TUNNEL_CONF

cloudflared tunnel route dns "${TUNNEL_NAME}" "${DOMAIN}" || true
cloudflared service install || true
systemctl daemon-reload && systemctl enable cloudflared && systemctl start cloudflared

print_header "SETUP SELESAI! 🎉"
echo -e "${GREEN}Web kamu bisa diakses di: https://${DOMAIN}${NC}"
echo -e "${MAGENTA}Info MySQL Lokal:${NC}"
echo -e "Database: ${DB_NAME}"
echo -e "User: ${DB_USER}"
echo -e "Password: ${DB_PASS}"
