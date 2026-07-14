#!/bin/bash
# ============================================================
# RedSim - Ubuntu Server Setup Script
# ============================================================
# Laravel 13 + Nginx + PHP 8.3-FPM + Node.js + Cloudflare Tunnel
#
# CARA PAKAI:
# 1. Upload semua file backend ke Ubuntu Server (scp/rsync)
# 2. Upload script ini ke Ubuntu Server
# 3. chmod +x setup-ubuntu-server.sh
# 4. sudo ./setup-ubuntu-server.sh
#
# Script ini akan:
# - Install semua dependencies (PHP 8.3, Nginx, Node.js, Composer)
# - Memindahkan file project ke /var/www/redsim
# - Konfigurasi Nginx sebagai web server
# - Build asset frontend (Vite + Tailwind)
# - Setup Cloudflare Tunnel dengan login interaktif
# - Menampilkan link login Cloudflare agar mudah
# ============================================================

set -e

# ============================================================
# KONFIGURASI — SESUAIKAN SEBELUM MENJALANKAN
# ============================================================
DOMAIN="redsim.my.id"                        # Ganti dengan domain kamu
TUNNEL_NAME="redsim-tunnel"                  # Nama tunnel Cloudflare
PROJECT_DIR="/var/www/redsim"                # Direktori project di server
UPLOAD_DIR="$(pwd)"                          # Direktori file yang di-upload (jalankan script dari sini)
PHP_VERSION="8.3"                            # Versi PHP
NODE_VERSION="20"                            # Versi Node.js LTS

# Warna output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# ============================================================
# FUNGSI HELPER
# ============================================================
print_header() {
    echo ""
    echo -e "${CYAN}╔══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║${NC} ${BOLD}$1${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_step() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

print_warn() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Script ini harus dijalankan sebagai root (sudo)!"
        echo "Gunakan: sudo ./setup-ubuntu-server.sh"
        exit 1
    fi
}

# ============================================================
# CEK ROOT
# ============================================================
check_root

print_header "RedSim - Ubuntu Server Auto Setup"
echo -e "${MAGENTA}Script ini akan menginstall dan mengkonfigurasi:${NC}"
echo -e "  • PHP ${PHP_VERSION} + Extensions"
echo -e "  • Nginx Web Server"
echo -e "  • Node.js ${NODE_VERSION} + npm"
echo -e "  • Composer (PHP Package Manager)"
echo -e "  • Cloudflare Tunnel (cloudflared)"
echo -e "  • Domain: ${BOLD}${DOMAIN}${NC}"
echo ""

# Konfirmasi domain
read -p "$(echo -e ${YELLOW}Domain yang akan digunakan [${DOMAIN}]: ${NC})" INPUT_DOMAIN
if [ -n "$INPUT_DOMAIN" ]; then
    DOMAIN="$INPUT_DOMAIN"
fi
print_info "Domain yang digunakan: ${BOLD}${DOMAIN}${NC}"
echo ""

# ============================================================
# STEP 1: UPDATE SYSTEM
# ============================================================
print_header "STEP 1/10 — Update & Upgrade System"
apt update -y && apt upgrade -y
apt install -y software-properties-common curl wget gnupg2 unzip git lsb-release ca-certificates apt-transport-https
print_step "System berhasil di-update"

# ============================================================
# STEP 2: INSTALL PHP 8.3 + EXTENSIONS
# ============================================================
print_header "STEP 2/10 — Install PHP ${PHP_VERSION} + Extensions"

# Tambah PPA Ondrej untuk PHP terbaru
add-apt-repository -y ppa:ondrej/php
apt update -y

apt install -y \
    php${PHP_VERSION} \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-readline \
    php${PHP_VERSION}-tokenizer \
    php${PHP_VERSION}-dom \
    php${PHP_VERSION}-fileinfo \
    php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-soap

# Konfigurasi PHP-FPM untuk production
PHP_FPM_CONF="/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf"
sed -i "s/^user = .*/user = www-data/" "$PHP_FPM_CONF"
sed -i "s/^group = .*/group = www-data/" "$PHP_FPM_CONF"
sed -i "s/^listen.owner = .*/listen.owner = www-data/" "$PHP_FPM_CONF"
sed -i "s/^listen.group = .*/listen.group = www-data/" "$PHP_FPM_CONF"

# PHP.ini untuk production
PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 64M/" "$PHP_INI"
sed -i "s/post_max_size = .*/post_max_size = 64M/" "$PHP_INI"
sed -i "s/memory_limit = .*/memory_limit = 256M/" "$PHP_INI"
sed -i "s/max_execution_time = .*/max_execution_time = 120/" "$PHP_INI"
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" "$PHP_INI"

systemctl restart php${PHP_VERSION}-fpm
systemctl enable php${PHP_VERSION}-fpm

print_step "PHP ${PHP_VERSION}-FPM berhasil diinstall"
php -v

# ============================================================
# STEP 3: INSTALL NGINX
# ============================================================
print_header "STEP 3/10 — Install Nginx"
apt install -y nginx
systemctl start nginx
systemctl enable nginx
print_step "Nginx berhasil diinstall"
nginx -v

# ============================================================
# STEP 4: INSTALL NODE.JS
# ============================================================
print_header "STEP 4/10 — Install Node.js ${NODE_VERSION}"

# Install Node.js dari NodeSource
curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -
apt install -y nodejs

print_step "Node.js berhasil diinstall"
node -v
npm -v

# ============================================================
# STEP 5: INSTALL COMPOSER
# ============================================================
print_header "STEP 5/10 — Install Composer"

cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

print_step "Composer berhasil diinstall"
composer --version

# ============================================================
# STEP 6: PINDAHKAN FILE PROJECT KE /var/www/
# ============================================================
print_header "STEP 6/10 — Memindahkan File Project"

# Buat direktori project
mkdir -p "${PROJECT_DIR}"

# Pindahkan semua file dari direktori upload
print_info "Memindahkan file dari ${UPLOAD_DIR} ke ${PROJECT_DIR}..."

# Cek apakah ada file di direktori saat ini
if [ -f "${UPLOAD_DIR}/artisan" ]; then
    # File Laravel ditemukan di direktori saat ini
    print_info "File Laravel ditemukan di ${UPLOAD_DIR}"
    cp -rf "${UPLOAD_DIR}/"* "${PROJECT_DIR}/" 2>/dev/null || true
    cp -rf "${UPLOAD_DIR}/".* "${PROJECT_DIR}/" 2>/dev/null || true
elif [ -d "${UPLOAD_DIR}/backend" ]; then
    # Ada subfolder backend
    print_info "Subfolder backend ditemukan"
    cp -rf "${UPLOAD_DIR}/backend/"* "${PROJECT_DIR}/" 2>/dev/null || true
    cp -rf "${UPLOAD_DIR}/backend/".* "${PROJECT_DIR}/" 2>/dev/null || true
else
    print_warn "Tidak menemukan file Laravel. Pastikan kamu menjalankan script dari direktori yang berisi file backend."
    print_info "Atau pindahkan file secara manual ke ${PROJECT_DIR}"
fi

# Pastikan .env ada (gunakan .env.deploy jika .env belum ada)
cd "${PROJECT_DIR}"

if [ -f "${PROJECT_DIR}/.env.deploy" ] && [ ! -f "${PROJECT_DIR}/.env" ]; then
    cp "${PROJECT_DIR}/.env.deploy" "${PROJECT_DIR}/.env"
    print_info "Menggunakan .env.deploy sebagai .env"
elif [ -f "${PROJECT_DIR}/.env.deploy" ]; then
    # Gunakan .env.deploy sebagai basis untuk production
    cp "${PROJECT_DIR}/.env.deploy" "${PROJECT_DIR}/.env"
    print_info "Mengganti .env dengan .env.deploy untuk production"
fi

# Update .env untuk deployment di server ini
if [ -f "${PROJECT_DIR}/.env" ]; then
    # Update APP_URL dan domain terkait
    sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" "${PROJECT_DIR}/.env"
    sed -i "s|ASSET_URL=.*|ASSET_URL=https://${DOMAIN}|" "${PROJECT_DIR}/.env"
    sed -i "s|APP_ENV=.*|APP_ENV=production|" "${PROJECT_DIR}/.env"
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" "${PROJECT_DIR}/.env"
    sed -i "s|LOG_CHANNEL=.*|LOG_CHANNEL=stack|" "${PROJECT_DIR}/.env"
    sed -i "s|LOG_LEVEL=.*|LOG_LEVEL=warning|" "${PROJECT_DIR}/.env"

    # Update SESSION_DOMAIN
    sed -i "s|SESSION_DOMAIN=.*|SESSION_DOMAIN=${DOMAIN}|" "${PROJECT_DIR}/.env"

    # Update Google OAuth redirect
    sed -i "s|GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=https://${DOMAIN}/oauth/google/callback|" "${PROJECT_DIR}/.env"

    # Update QUEUE dan CACHE untuk server (gunakan database, lebih stabil dari sync)
    sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|" "${PROJECT_DIR}/.env"
    sed -i "s|CACHE_STORE=.*|CACHE_STORE=file|" "${PROJECT_DIR}/.env"

    # Hapus DATASET_PATH lama dan set ulang
    sed -i "s|DATASET_PATH=.*|DATASET_PATH=${PROJECT_DIR}/dataset|" "${PROJECT_DIR}/.env"

    print_step ".env berhasil dikonfigurasi untuk domain ${DOMAIN}"
fi

# Set permissions
chown -R www-data:www-data "${PROJECT_DIR}"
chmod -R 755 "${PROJECT_DIR}"
chmod -R 775 "${PROJECT_DIR}/storage" 2>/dev/null || true
chmod -R 775 "${PROJECT_DIR}/bootstrap/cache" 2>/dev/null || true

print_step "File project berhasil dipindahkan ke ${PROJECT_DIR}"

# ============================================================
# STEP 7: INSTALL DEPENDENCIES & BUILD ASSETS
# ============================================================
print_header "STEP 7/10 — Install Dependencies & Build Assets"

cd "${PROJECT_DIR}"

# Install Composer dependencies
print_info "Menginstall Composer dependencies..."
if [ -d "${PROJECT_DIR}/vendor" ]; then
    print_info "Folder vendor sudah ada, menjalankan composer install..."
    composer install --no-dev --optimize-autoloader --no-interaction 2>&1 || {
        print_warn "Composer install gagal, mencoba dump-autoload saja..."
        composer dump-autoload --optimize --no-interaction 2>&1 || true
    }
else
    composer install --no-dev --optimize-autoloader --no-interaction 2>&1
fi

# Install NPM dependencies dan build
print_info "Menginstall NPM dependencies..."
npm ci --ignore-scripts 2>/dev/null || npm install --ignore-scripts 2>/dev/null || true

print_info "Building frontend assets (Vite + Tailwind)..."
npm run build 2>&1 || {
    print_warn "npm run build gagal. Mencoba ulang..."
    npm install
    npm run build 2>&1
}

# Laravel optimizations
print_info "Menjalankan Laravel optimizations..."
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true
php artisan event:cache 2>&1 || true

# Generate storage link
php artisan storage:link 2>&1 || true

# Migrasi database (jika diperlukan)
print_info "Menjalankan database migration..."
php artisan migrate --force 2>&1 || {
    print_warn "Migration gagal (mungkin sudah terupdate atau koneksi DB belum siap)"
}

# Set ulang permissions setelah install
chown -R www-data:www-data "${PROJECT_DIR}"
chmod -R 755 "${PROJECT_DIR}"
chmod -R 775 "${PROJECT_DIR}/storage"
chmod -R 775 "${PROJECT_DIR}/bootstrap/cache"

print_step "Dependencies dan assets berhasil diinstall/build"

# ============================================================
# STEP 8: KONFIGURASI NGINX
# ============================================================
print_header "STEP 8/10 — Konfigurasi Nginx"

# Buat konfigurasi Nginx untuk RedSim
cat > /etc/nginx/sites-available/redsim <<NGINX_CONF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};

    root ${PROJECT_DIR}/public;
    index index.php index.html;

    # Charset
    charset utf-8;

    # Logging
    access_log /var/log/nginx/redsim-access.log;
    error_log /var/log/nginx/redsim-error.log;

    # Max upload size (sesuai .env MAX_FILE_SIZE_MB=50)
    client_max_body_size 64M;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip Compression
    gzip on;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_proxied any;
    gzip_vary on;
    gzip_types
        application/javascript
        application/json
        application/xml
        application/rss+xml
        text/css
        text/javascript
        text/plain
        text/xml
        image/svg+xml;

    # Laravel routing
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP-FPM
    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;

        # Timeouts
        fastcgi_connect_timeout 60;
        fastcgi_send_timeout 120;
        fastcgi_read_timeout 120;

        # Buffers
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)\$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files \$uri =404;
    }

    # Vite build assets (hashed, long cache)
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files \$uri =404;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Deny access to sensitive files
    location ~* \.(env|log|sql|bak|old|backup)\$ {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Favicon & robots
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Error pages
    error_page 404 /index.php;
}
NGINX_CONF

# Enable site dan disable default
ln -sf /etc/nginx/sites-available/redsim /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test konfigurasi Nginx
nginx -t

# Restart Nginx
systemctl restart nginx
systemctl enable nginx

print_step "Nginx berhasil dikonfigurasi untuk ${DOMAIN}"

# ============================================================
# STEP 9: BUKA PORT YANG DIBUTUHKAN (UFW Firewall)
# ============================================================
print_header "STEP 9/10 — Konfigurasi Firewall (UFW)"

# Install UFW jika belum ada
apt install -y ufw

# Reset rules
ufw --force reset

# Default policies
ufw default deny incoming
ufw default allow outgoing

# Buka port yang dibutuhkan
ufw allow 22/tcp       # SSH
ufw allow 80/tcp       # HTTP (Nginx)
ufw allow 443/tcp      # HTTPS (Cloudflare Tunnel)
ufw allow 8080/tcp     # Alternate HTTP (jika diperlukan)

# Port untuk Cloudflare Tunnel
ufw allow 7844/tcp     # Cloudflare Tunnel QUIC
ufw allow 7844/udp     # Cloudflare Tunnel QUIC (UDP)

# Enable firewall
ufw --force enable

print_step "Firewall dikonfigurasi. Port yang dibuka:"
ufw status verbose

# ============================================================
# STEP 10: INSTALL & SETUP CLOUDFLARE TUNNEL
# ============================================================
print_header "STEP 10/10 — Setup Cloudflare Tunnel"

# Install cloudflared
print_info "Menginstall cloudflared..."

# Deteksi arsitektur
ARCH=$(dpkg --print-architecture)
if [ "$ARCH" = "amd64" ]; then
    CLOUDFLARED_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb"
elif [ "$ARCH" = "arm64" ]; then
    CLOUDFLARED_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64.deb"
else
    print_error "Arsitektur ${ARCH} tidak didukung"
    exit 1
fi

wget -q "$CLOUDFLARED_URL" -O /tmp/cloudflared.deb
dpkg -i /tmp/cloudflared.deb
rm -f /tmp/cloudflared.deb

print_step "cloudflared berhasil diinstall"
cloudflared --version

# ============================================================
# CLOUDFLARE LOGIN (INTERAKTIF — menampilkan link)
# ============================================================
echo ""
echo -e "${CYAN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║${NC}  ${BOLD}${YELLOW}CLOUDFLARE TUNNEL LOGIN${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════${NC}"
echo -e "${BOLD}Langkah Login Cloudflare:${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "  1. Setelah menekan ENTER, akan muncul ${BOLD}link URL login${NC}"
echo -e "  2. ${BOLD}Copy link tersebut${NC} dan buka di browser PC kamu"
echo -e "  3. Login ke akun Cloudflare kamu"
echo -e "  4. Pilih domain ${BOLD}${DOMAIN}${NC}"
echo -e "  5. Setelah berhasil, script akan melanjutkan otomatis"
echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════${NC}"
echo ""
read -p "$(echo -e ${GREEN}Tekan ENTER untuk memulai login Cloudflare...${NC})" _

# Jalankan cloudflared login
# Ini akan menampilkan URL yang harus dibuka di browser
print_info "Menjalankan cloudflared login..."
print_info "Silakan copy URL di bawah ini dan buka di browser:"
echo ""

cloudflared tunnel login

echo ""
print_step "Login Cloudflare berhasil!"

# ============================================================
# BUAT TUNNEL
# ============================================================
print_info "Membuat Cloudflare Tunnel: ${TUNNEL_NAME}..."

# Hapus tunnel lama jika ada (ignore error)
cloudflared tunnel delete "${TUNNEL_NAME}" 2>/dev/null || true

# Buat tunnel baru
cloudflared tunnel create "${TUNNEL_NAME}"

# Dapatkan Tunnel ID
TUNNEL_ID=$(cloudflared tunnel list | grep "${TUNNEL_NAME}" | awk '{print $1}')

if [ -z "$TUNNEL_ID" ]; then
    print_error "Gagal mendapatkan Tunnel ID. Silakan cek manual."
    exit 1
fi

print_step "Tunnel berhasil dibuat. ID: ${TUNNEL_ID}"

# ============================================================
# KONFIGURASI TUNNEL
# ============================================================
print_info "Mengkonfigurasi tunnel..."

# Buat konfigurasi cloudflared
mkdir -p /root/.cloudflared

cat > /root/.cloudflared/config.yml <<TUNNEL_CONF
tunnel: ${TUNNEL_ID}
credentials-file: /root/.cloudflared/${TUNNEL_ID}.json

ingress:
  # Route utama — ke Nginx di port 80
  - hostname: ${DOMAIN}
    service: http://localhost:80
    originRequest:
      noTLSVerify: true

  # Route www subdomain
  - hostname: www.${DOMAIN}
    service: http://localhost:80
    originRequest:
      noTLSVerify: true

  # Catch-all rule (wajib ada di paling bawah)
  - service: http_status:404
TUNNEL_CONF

print_step "Konfigurasi tunnel selesai"

# ============================================================
# SETUP DNS RECORD VIA TUNNEL
# ============================================================
print_info "Menambahkan DNS record untuk ${DOMAIN}..."

cloudflared tunnel route dns "${TUNNEL_NAME}" "${DOMAIN}" 2>&1 || {
    print_warn "DNS route untuk ${DOMAIN} mungkin sudah ada"
}

cloudflared tunnel route dns "${TUNNEL_NAME}" "www.${DOMAIN}" 2>&1 || {
    print_warn "DNS route untuk www.${DOMAIN} mungkin sudah ada"
}

print_step "DNS record berhasil ditambahkan"

# ============================================================
# INSTALL CLOUDFLARED SEBAGAI SERVICE
# ============================================================
print_info "Menginstall cloudflared sebagai systemd service..."

cloudflared service install 2>/dev/null || {
    # Manual service creation jika otomatis gagal
    cat > /etc/systemd/system/cloudflared.service <<SERVICE_CONF
[Unit]
Description=Cloudflare Tunnel for RedSim
After=network.target nginx.service

[Service]
Type=simple
User=root
ExecStart=/usr/local/bin/cloudflared tunnel --config /root/.cloudflared/config.yml run ${TUNNEL_NAME}
Restart=on-failure
RestartSec=5
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
SERVICE_CONF
}

systemctl daemon-reload
systemctl enable cloudflared
systemctl start cloudflared

print_step "Cloudflare Tunnel service berhasil diinstall dan dijalankan"

# ============================================================
# SETUP LARAVEL QUEUE WORKER (opsional, untuk background jobs)
# ============================================================
print_info "Setup Laravel Queue Worker..."

cat > /etc/systemd/system/redsim-queue.service <<QUEUE_CONF
[Unit]
Description=RedSim Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=${PROJECT_DIR}
ExecStart=/usr/bin/php${PHP_VERSION} ${PROJECT_DIR}/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
QUEUE_CONF

systemctl daemon-reload
systemctl enable redsim-queue
systemctl start redsim-queue

print_step "Queue Worker berhasil disetup"

# ============================================================
# FINAL CHECK & STATUS
# ============================================================
print_header "SETUP SELESAI! 🎉"

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║${NC}  ${BOLD}STATUS DEPLOYMENT${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Cek status services
echo -e "${BOLD}Service Status:${NC}"
echo -e "  Nginx:           $(systemctl is-active nginx)"
echo -e "  PHP-FPM:         $(systemctl is-active php${PHP_VERSION}-fpm)"
echo -e "  Cloudflared:     $(systemctl is-active cloudflared)"
echo -e "  Queue Worker:    $(systemctl is-active redsim-queue)"
echo ""

echo -e "${BOLD}Konfigurasi:${NC}"
echo -e "  Project Dir:     ${PROJECT_DIR}"
echo -e "  Nginx Config:    /etc/nginx/sites-available/redsim"
echo -e "  Tunnel Config:   /root/.cloudflared/config.yml"
echo -e "  PHP Version:     $(php -v | head -1)"
echo -e "  Node Version:    $(node -v)"
echo -e "  Tunnel ID:       ${TUNNEL_ID}"
echo ""

echo -e "${BOLD}Port yang Dibuka:${NC}"
echo -e "  22  (SSH)"
echo -e "  80  (HTTP - Nginx)"
echo -e "  443 (HTTPS - Cloudflare)"
echo ""

echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo -e ""
echo -e "  ${BOLD}🌐 Website kamu sekarang bisa diakses di:${NC}"
echo -e ""
echo -e "       ${CYAN}${BOLD}https://${DOMAIN}${NC}"
echo -e ""
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo ""

echo -e "${YELLOW}CATATAN PENTING:${NC}"
echo -e "  • Pastikan DNS di Cloudflare sudah mengarah ke tunnel"
echo -e "  • Cek status tunnel: ${BOLD}cloudflared tunnel info ${TUNNEL_NAME}${NC}"
echo -e "  • Cek log Nginx: ${BOLD}tail -f /var/log/nginx/redsim-error.log${NC}"
echo -e "  • Cek log Laravel: ${BOLD}tail -f ${PROJECT_DIR}/storage/logs/laravel.log${NC}"
echo -e "  • Cek log tunnel: ${BOLD}journalctl -u cloudflared -f${NC}"
echo -e "  • Restart semua: ${BOLD}systemctl restart nginx php${PHP_VERSION}-fpm cloudflared redsim-queue${NC}"
echo ""

echo -e "${MAGENTA}Jika domain belum bisa diakses, tunggu 1-5 menit untuk propagasi DNS.${NC}"
echo ""

echo -e "${GREEN}Setup selesai! Terima kasih menggunakan RedSim Deployer 🚀${NC}"
