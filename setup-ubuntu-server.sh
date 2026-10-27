#!/bin/bash
# ============================================================
# RedSim - Ubuntu Server Setup Script (VirtualBox Edition)
# ============================================================
# Stack: Laravel 13 + Nginx + PHP 8.3-FPM + Node.js 20 +
#        OpenWA + Supervisor + Cloudflare Tunnel
#
# Database  : Supabase (PostgreSQL cloud) — sudah dikonfigurasi
# Env       : .env.deploy sebagai basis konfigurasi
# Target OS : Ubuntu 24.04 LTS (server-redsim, 192.168.1.10)
# RAM       : 2GB — Script ini akan setup SWAP 4GB otomatis
#
# CARA PAKAI (dari server Ubuntu via SSH):
# 1. Transfer file project dulu (lihat PANDUAN_DEPLOY.md)
# 2. Jalankan dari folder yang berisi file backend:
#       chmod +x setup-ubuntu-server.sh
#       sudo ./setup-ubuntu-server.sh
# ============================================================

set -e

# ============================================================
# KONFIGURASI — SESUAIKAN SEBELUM MENJALANKAN
# ============================================================
DOMAIN="redsim.my.id"               # Ganti dengan domain kamu di Cloudflare
TUNNEL_NAME="redsim-tunnel"         # Nama tunnel Cloudflare
PROJECT_DIR="/var/www/redsim"       # Direktori project Laravel
OPENWA_DIR="/var/www/openwa"        # Direktori OpenWA
UPLOAD_DIR="$(pwd)"                 # Jalankan script dari folder backend
PHP_VERSION="8.3"
NODE_VERSION="20"
SWAP_SIZE="4G"                      # Swap 4GB untuk kompensasi RAM 2GB
OPENWA_PORT="2785"                  # Port OpenWA API
OPENWA_SESSION="default"            # Session ID OpenWA

# ============================================================
# WARNA OUTPUT
# ============================================================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'
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

print_step()  { echo -e "${GREEN}[✓]${NC} $1"; }
print_info()  { echo -e "${BLUE}[i]${NC} $1"; }
print_warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
print_error() { echo -e "${RED}[✗]${NC} $1"; }

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Script ini harus dijalankan sebagai root (sudo)!"
        echo "Gunakan: sudo ./setup-ubuntu-server.sh"
        exit 1
    fi
}

check_root

# ============================================================
# TAMPILKAN INTRO
# ============================================================
print_header "RedSim - Ubuntu Server Auto Setup (VirtualBox Edition)"
echo -e "${MAGENTA}Script ini akan menginstall dan mengkonfigurasi:${NC}"
echo -e "  • Swap Memory ${SWAP_SIZE} (penting untuk RAM 2GB)"
echo -e "  • PHP ${PHP_VERSION}-FPM + Extensions lengkap"
echo -e "  • Nginx Web Server + Security Headers"
echo -e "  • Node.js ${NODE_VERSION} + npm"
echo -e "  • Composer 2.x"
echo -e "  • OpenWA WhatsApp API Gateway (Node.js)"
echo -e "  • Supervisor (Process Manager untuk Queue Worker & OpenWA)"
echo -e "  • Cloudflare Tunnel (Expose ke internet tanpa port forwarding)"
echo -e "  • Chromium (Headless browser untuk OpenWA/Puppeteer)"
echo ""

# Konfirmasi domain
read -p "$(echo -e ${YELLOW}Domain Cloudflare yang akan digunakan [${DOMAIN}]: ${NC})" INPUT_DOMAIN
if [ -n "$INPUT_DOMAIN" ]; then DOMAIN="$INPUT_DOMAIN"; fi
print_info "Domain: ${BOLD}${DOMAIN}${NC}"
echo ""

# ============================================================
# STEP 1: UPDATE SYSTEM
# ============================================================
print_header "STEP 1/12 — Update & Upgrade System"
apt update -y && apt upgrade -y
apt install -y software-properties-common curl wget gnupg2 unzip git \
    lsb-release ca-certificates apt-transport-https build-essential \
    htop nano net-tools ufw
print_step "System berhasil di-update"

# ============================================================
# STEP 2: SETUP SWAP MEMORY (KRITIS untuk RAM 2GB)
# ============================================================
print_header "STEP 2/12 — Setup Swap Memory ${SWAP_SIZE}"

if swapon --show | grep -q '/swapfile'; then
    print_info "Swap sudah ada, melewati..."
    swapon --show
else
    # Cek ketersediaan ruang disk minimum 5GB untuk aman (karena minta 4GB swap)
    FREE_SPACE=$(df -m / | awk 'NR==2 {print $4}')
    if [ "$FREE_SPACE" -lt 5120 ]; then
        print_warn "Ruang disk tersisa kurang dari 5GB (${FREE_SPACE}MB). Melewati pembuatan swap agar tidak kehabisan storage (ENOSPC)."
    else
        print_info "Membuat swap file ${SWAP_SIZE}..."
        fallocate -l $SWAP_SIZE /swapfile
        chmod 600 /swapfile
        mkswap /swapfile
        swapon /swapfile

        # Pastikan swap permanen setelah reboot
        if ! grep -q '/swapfile' /etc/fstab; then
            echo '/swapfile none swap sw 0 0' >> /etc/fstab
        fi

        # Tuning swappiness (60 = balance antara performance & swap usage)
        sysctl vm.swappiness=60
        echo 'vm.swappiness=60' >> /etc/sysctl.conf

        print_step "Swap ${SWAP_SIZE} berhasil dibuat"
    fi
fi

free -h

# ============================================================
# STEP 3: INSTALL PHP 8.3 + EXTENSIONS
# ============================================================
print_header "STEP 3/12 — Install PHP ${PHP_VERSION} + Extensions"

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
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-sockets

# Konfigurasi PHP-FPM
PHP_FPM_CONF="/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf"
sed -i "s/^user = .*/user = www-data/"         "$PHP_FPM_CONF"
sed -i "s/^group = .*/group = www-data/"       "$PHP_FPM_CONF"
sed -i "s/^listen.owner = .*/listen.owner = www-data/" "$PHP_FPM_CONF"
sed -i "s/^listen.group = .*/listen.group = www-data/" "$PHP_FPM_CONF"

# PHP.ini untuk production (disesuaikan RAM 2GB)
PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 64M/"   "$PHP_INI"
sed -i "s/post_max_size = .*/post_max_size = 64M/"               "$PHP_INI"
sed -i "s/memory_limit = .*/memory_limit = 256M/"               "$PHP_INI"
sed -i "s/max_execution_time = .*/max_execution_time = 300/"    "$PHP_INI"
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/"             "$PHP_INI"

# OPcache untuk performa
PHP_CLI_INI="/etc/php/${PHP_VERSION}/cli/php.ini"
sed -i "s/memory_limit = .*/memory_limit = 512M/" "$PHP_CLI_INI"

systemctl restart php${PHP_VERSION}-fpm
systemctl enable  php${PHP_VERSION}-fpm

print_step "PHP ${PHP_VERSION}-FPM berhasil diinstall"
php -v

# ============================================================
# STEP 4: INSTALL NGINX
# ============================================================
print_header "STEP 4/12 — Install Nginx"
apt install -y nginx
systemctl start nginx
systemctl enable nginx
print_step "Nginx berhasil diinstall"
nginx -v

# ============================================================
# STEP 5: INSTALL NODE.JS 20 LTS
# ============================================================
print_header "STEP 5/12 — Install Node.js ${NODE_VERSION}"
curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -
apt install -y nodejs
print_step "Node.js berhasil diinstall"
node -v && npm -v

# ============================================================
# STEP 6: INSTALL CHROMIUM (untuk OpenWA/Puppeteer)
# ============================================================
print_header "STEP 6/12 — Install Chromium (Headless Browser untuk OpenWA)"
apt install -y chromium-browser fonts-liberation \
    libasound2t64 libatk-bridge2.0-0 libatk1.0-0 libatspi2.0-0 \
    libcups2 libdbus-1-3 libdrm2 libgbm1 libgtk-3-0 \
    libnspr4 libnss3 libpango-1.0-0 libx11-6 libx11-xcb1 \
    libxcb1 libxcomposite1 libxdamage1 libxext6 libxfixes3 \
    libxkbcommon0 libxrandr2 libxshmfence1 xdg-utils

CHROMIUM_PATH=$(which chromium-browser 2>/dev/null || which chromium 2>/dev/null || echo "")
if [ -z "$CHROMIUM_PATH" ]; then
    print_warn "chromium-browser tidak ditemukan di PATH, coba cek manual: which chromium"
else
    print_step "Chromium ditemukan di: $CHROMIUM_PATH"
    $CHROMIUM_PATH --version 2>/dev/null || true
fi

# ============================================================
# STEP 7: INSTALL COMPOSER
# ============================================================
print_header "STEP 7/12 — Install Composer"
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
print_step "Composer berhasil diinstall"
composer --version

# ============================================================
# STEP 8: PINDAHKAN FILE PROJECT
# ============================================================
print_header "STEP 8/12 — Setup File Project Laravel"

mkdir -p "${PROJECT_DIR}"

print_info "Memindahkan file dari ${UPLOAD_DIR} ke ${PROJECT_DIR}..."
if [ -f "${UPLOAD_DIR}/artisan" ]; then
    print_info "File Laravel ditemukan di ${UPLOAD_DIR}"
    rsync -a --exclude='.git' --exclude='node_modules' \
        "${UPLOAD_DIR}/" "${PROJECT_DIR}/"
elif [ -d "${UPLOAD_DIR}/backend" ]; then
    print_info "Subfolder backend ditemukan"
    rsync -a --exclude='.git' --exclude='node_modules' \
        "${UPLOAD_DIR}/backend/" "${PROJECT_DIR}/"
else
    print_warn "File Laravel tidak ditemukan di direktori ini."
    print_warn "Pastikan kamu jalankan script dari folder backend/ yang sudah di-upload."
fi

# Setup .env — gunakan .env.deploy sebagai basis
cd "${PROJECT_DIR}"
if [ -f ".env.deploy" ] && [ ! -f ".env" ]; then
    cp ".env.deploy" ".env"
    print_info "Menggunakan .env.deploy sebagai .env"
elif [ -f ".env.deploy" ]; then
    cp ".env.deploy" ".env"
fi

# Update nilai .env untuk server ini
if [ -f ".env" ]; then
    sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|"                                                    ".env"
    sed -i "s|ASSET_URL=.*|ASSET_URL=https://${DOMAIN}|"                                                ".env"
    sed -i "s|APP_ENV=.*|APP_ENV=production|"                                                           ".env"
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|"                                                            ".env"
    sed -i "s|LOG_CHANNEL=.*|LOG_CHANNEL=stack|"                                                        ".env"
    sed -i "s|LOG_LEVEL=.*|LOG_LEVEL=warning|"                                                          ".env"
    sed -i "s|SESSION_DOMAIN=.*|SESSION_DOMAIN=${DOMAIN}|"                                              ".env"
    sed -i "s|GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=https://${DOMAIN}/oauth/google/callback|"      ".env"
    sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|"                                           ".env"
    sed -i "s|CACHE_STORE=.*|CACHE_STORE=file|"                                                         ".env"
    # OpenWA settings
    if ! grep -q "OPENWA_BASE_URL" ".env"; then
        echo ""                                                   >> ".env"
        echo "# ===== OPENWA (WhatsApp Gateway) ====="           >> ".env"
        echo "OPENWA_BASE_URL=http://localhost:${OPENWA_PORT}"   >> ".env"
        echo "OPENWA_SESSION_ID=${OPENWA_SESSION}"               >> ".env"
        echo "OPENWA_API_KEY=ganti_dengan_api_key_openwa"        >> ".env"
        echo "OPENWA_SENDER_NUMBER=628xxxxxxxxxx"                >> ".env"
    else
        sed -i "s|OPENWA_BASE_URL=.*|OPENWA_BASE_URL=http://localhost:${OPENWA_PORT}|" ".env"
    fi
    print_step ".env berhasil dikonfigurasi"
fi

# Permissions awal
chown -R www-data:www-data "${PROJECT_DIR}"
chmod -R 755 "${PROJECT_DIR}"
chmod -R 775 "${PROJECT_DIR}/storage"        2>/dev/null || true
chmod -R 775 "${PROJECT_DIR}/bootstrap/cache" 2>/dev/null || true

# ============================================================
# INSTALL COMPOSER DEPENDENCIES & BUILD ASSETS
# ============================================================
print_info "Menginstall Composer dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction 2>&1 || \
    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction 2>&1

print_info "Menginstall NPM dependencies..."
npm ci --ignore-scripts 2>/dev/null || npm install --ignore-scripts 2>/dev/null || true

print_info "Building frontend assets (Vite + Tailwind)..."
npm run build 2>&1 || {
    print_warn "npm run build gagal, mencoba ulang dengan npm install..."
    npm install
    npm run build 2>&1
}

print_info "Menjalankan Laravel setup..."
php artisan key:generate --no-interaction 2>&1 || true
php artisan storage:link 2>&1 || true
php artisan migrate --force 2>&1 || {
    print_warn "Migration gagal — periksa koneksi database Supabase di .env"
}
php artisan config:cache  2>&1 || true
php artisan route:cache   2>&1 || true
php artisan view:cache    2>&1 || true
php artisan event:cache   2>&1 || true
php artisan optimize      2>&1 || true

# Set ulang permissions setelah install
chown -R www-data:www-data "${PROJECT_DIR}"
chmod -R 755 "${PROJECT_DIR}"
chmod -R 775 "${PROJECT_DIR}/storage"
chmod -R 775 "${PROJECT_DIR}/bootstrap/cache"

print_step "Project Laravel berhasil di-setup"

# ============================================================
# STEP 9: SETUP OPENWA (WhatsApp API Gateway)
# ============================================================
print_header "STEP 9/12 — Setup OpenWA WhatsApp Gateway"

mkdir -p "${OPENWA_DIR}"

# Pindah file OpenWA jika ada di direktori upload
if [ -d "${UPLOAD_DIR}/../openwa" ]; then
    print_info "Folder openwa ditemukan, memindahkan..."
    rsync -a --exclude='.git' --exclude='node_modules' --exclude='data' \
        "${UPLOAD_DIR}/../openwa/" "${OPENWA_DIR}/"
elif [ -d "${UPLOAD_DIR}/openwa" ]; then
    rsync -a --exclude='.git' --exclude='node_modules' --exclude='data' \
        "${UPLOAD_DIR}/openwa/" "${OPENWA_DIR}/"
else
    print_warn "Folder openwa tidak ditemukan di direktori upload."
    print_warn "Upload folder openwa secara manual ke ${OPENWA_DIR} dan jalankan:"
    print_warn "  cd ${OPENWA_DIR} && npm install"
fi

# Setup .env OpenWA jika belum ada
cd "${OPENWA_DIR}"
if [ ! -f ".env" ]; then
    if [ -f ".env.minimal" ]; then
        cp ".env.minimal" ".env"
        print_info "Menggunakan .env.minimal sebagai .env OpenWA"
    else
        # Buat .env OpenWA dari scratch
        cat > "${OPENWA_DIR}/.env" << OPENWA_ENV
# OpenWA Environment - server-redsim
PORT=${OPENWA_PORT}
NODE_ENV=production

DATABASE_TYPE=sqlite
DATABASE_NAME=./data/openwa.sqlite
DATABASE_SYNCHRONIZE=true
DATABASE_LOGGING=false

ENGINE_TYPE=whatsapp-web.js
SESSION_DATA_PATH=./data/sessions
PUPPETEER_HEADLESS=true
PUPPETEER_ARGS=--no-sandbox,--disable-setuid-sandbox,--disable-dev-shm-usage,--disable-gpu,--single-process,--no-zygote
PUPPETEER_EXECUTABLE_PATH=$(which chromium-browser 2>/dev/null || which chromium 2>/dev/null || echo "/usr/bin/chromium-browser")

API_MASTER_KEY=$(openssl rand -hex 32)
AUTO_START_SESSIONS=true

WEBHOOK_TIMEOUT=10000
WEBHOOK_RETRY_DELAY=5000

STORAGE_TYPE=local
STORAGE_LOCAL_PATH=./data/media

REDIS_ENABLED=false
QUEUE_ENABLED=false
CACHE_ENABLED=false
OPENWA_ENV
        print_info "File .env OpenWA baru dibuat"
    fi
fi

# Update Puppeteer executable path di .env OpenWA
CHROMIUM_EXEC=$(which chromium-browser 2>/dev/null || which chromium 2>/dev/null || echo "/usr/bin/chromium-browser")
sed -i "s|PUPPETEER_EXECUTABLE_PATH=.*|PUPPETEER_EXECUTABLE_PATH=${CHROMIUM_EXEC}|" "${OPENWA_DIR}/.env" 2>/dev/null || true

# Update Puppeteer args untuk lingkungan tanpa sandbox (VirtualBox)
sed -i "s|PUPPETEER_ARGS=.*|PUPPETEER_ARGS=--no-sandbox,--disable-setuid-sandbox,--disable-dev-shm-usage,--disable-gpu,--single-process,--no-zygote|" "${OPENWA_DIR}/.env" 2>/dev/null || true

# Buat folder data OpenWA
mkdir -p "${OPENWA_DIR}/data/sessions"
mkdir -p "${OPENWA_DIR}/data/media"

# Install npm dependencies OpenWA
if [ -f "${OPENWA_DIR}/package.json" ]; then
    print_info "Menginstall npm dependencies OpenWA..."
    cd "${OPENWA_DIR}"
    npm install --ignore-scripts 2>&1 || npm install 2>&1
    print_step "Dependencies OpenWA berhasil diinstall"
fi

# Set permissions OpenWA
chown -R www-data:www-data "${OPENWA_DIR}"
chmod -R 755 "${OPENWA_DIR}"
chmod -R 775 "${OPENWA_DIR}/data"

print_step "OpenWA berhasil di-setup di ${OPENWA_DIR}"

# ============================================================
# STEP 10: INSTALL SUPERVISOR (Process Manager)
# ============================================================
print_header "STEP 10/12 — Install & Konfigurasi Supervisor"

apt install -y supervisor

# Konfigurasi Supervisor untuk Laravel Queue Worker
cat > /etc/supervisor/conf.d/redsim-queue.conf << SUPERVISOR_QUEUE
[program:redsim-queue]
process_name=%(program_name)s_%(process_num)02d
command=php${PHP_VERSION} ${PROJECT_DIR}/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=128
directory=${PROJECT_DIR}
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=${PROJECT_DIR}/storage/logs/queue-worker.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=3600
SUPERVISOR_QUEUE

# Konfigurasi Supervisor untuk OpenWA
cat > /etc/supervisor/conf.d/openwa.conf << SUPERVISOR_OPENWA
[program:openwa]
process_name=%(program_name)s
command=node ${OPENWA_DIR}/src/main.js
directory=${OPENWA_DIR}
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=${OPENWA_DIR}/data/openwa.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=60
environment=NODE_ENV="production"
SUPERVISOR_OPENWA

# Aktifkan dan reload supervisor
systemctl enable supervisor
systemctl start supervisor
supervisorctl reread
supervisorctl update
supervisorctl start all 2>/dev/null || true

print_step "Supervisor berhasil dikonfigurasi"
print_info "Queue Worker dan OpenWA akan auto-start saat server reboot"

# ============================================================
# STEP 11: KONFIGURASI NGINX
# ============================================================
print_header "STEP 11/12 — Konfigurasi Nginx"

cat > /etc/nginx/sites-available/redsim << NGINX_CONF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};

    root ${PROJECT_DIR}/public;
    index index.php index.html;

    charset utf-8;

    access_log /var/log/nginx/redsim-access.log;
    error_log  /var/log/nginx/redsim-error.log;

    # Max upload size (sesuai MAX_FILE_SIZE_MB=64)
    client_max_body_size 64M;

    # Security Headers
    add_header X-Frame-Options         "SAMEORIGIN"                always;
    add_header X-Content-Type-Options  "nosniff"                   always;
    add_header X-XSS-Protection        "1; mode=block"             always;
    add_header Referrer-Policy         "strict-origin-when-cross-origin" always;

    # Gzip
    gzip on;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_proxied any;
    gzip_vary on;
    gzip_types application/javascript application/json application/xml
               text/css text/javascript text/plain image/svg+xml;

    # Laravel routing
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP-FPM
    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_connect_timeout 60;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # OpenWA API reverse proxy (akses via /openwa/)
    location /openwa/ {
        proxy_pass http://127.0.0.1:${OPENWA_PORT}/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade           \$http_upgrade;
        proxy_set_header Connection        'upgrade';
        proxy_set_header Host              \$host;
        proxy_set_header X-Real-IP         \$remote_addr;
        proxy_set_header X-Forwarded-For   \$proxy_add_x_forwarded_for;
        proxy_cache_bypass \$http_upgrade;
        proxy_read_timeout 300;
    }

    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)\$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files \$uri =404;
    }

    # Vite build assets (long cache)
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files \$uri =404;
    }

    # Deny hidden files & sensitive files
    location ~ /\. { deny all; access_log off; log_not_found off; }
    location ~* \.(env|log|sql|bak|old|backup)\$ { deny all; }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;
}
NGINX_CONF

ln -sf /etc/nginx/sites-available/redsim /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl restart nginx
systemctl enable nginx

print_step "Nginx berhasil dikonfigurasi"

# Konfigurasi firewall
apt install -y ufw
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp       # SSH
ufw allow 80/tcp       # HTTP
ufw allow 443/tcp      # HTTPS
ufw allow 7844/tcp     # Cloudflare QUIC
ufw allow 7844/udp
ufw --force enable
print_step "Firewall (UFW) dikonfigurasi"

# ============================================================
# STEP 12: INSTALL & SETUP CLOUDFLARE TUNNEL
# ============================================================
print_header "STEP 12/12 — Setup Cloudflare Tunnel"

# Deteksi arsitektur dan install cloudflared
ARCH=$(dpkg --print-architecture)
if [ "$ARCH" = "amd64" ]; then
    CF_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb"
elif [ "$ARCH" = "arm64" ]; then
    CF_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64.deb"
else
    print_error "Arsitektur ${ARCH} tidak didukung"
    exit 1
fi

wget -q "$CF_URL" -O /tmp/cloudflared.deb
dpkg -i /tmp/cloudflared.deb
rm -f /tmp/cloudflared.deb
print_step "cloudflared berhasil diinstall"
cloudflared --version

# ============================================================
# LOGIN CLOUDFLARE (INTERAKTIF)
# ============================================================
echo ""
echo -e "${CYAN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║${NC}  ${BOLD}${YELLOW}CLOUDFLARE TUNNEL LOGIN — BACA DULU!${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}  1. Tekan ENTER → akan muncul URL login${NC}"
echo -e "${YELLOW}  2. Copy URL tersebut → buka di browser PC kamu${NC}"
echo -e "${YELLOW}  3. Login ke akun Cloudflare → pilih domain ${BOLD}${DOMAIN}${NC}"
echo -e "${YELLOW}  4. Setelah berhasil authorize → script lanjut otomatis${NC}"
echo ""
read -p "$(echo -e ${GREEN}Tekan ENTER untuk memulai login Cloudflare...${NC})" _

cloudflared tunnel login

print_step "Login Cloudflare berhasil!"

# Buat tunnel
cloudflared tunnel delete "${TUNNEL_NAME}" 2>/dev/null || true
cloudflared tunnel create "${TUNNEL_NAME}"

TUNNEL_ID=$(cloudflared tunnel list | grep "${TUNNEL_NAME}" | awk '{print $1}')
if [ -z "$TUNNEL_ID" ]; then
    print_error "Gagal mendapatkan Tunnel ID. Jalankan manual: cloudflared tunnel list"
    exit 1
fi
print_step "Tunnel dibuat. ID: ${TUNNEL_ID}"

# Buat config tunnel
mkdir -p /root/.cloudflared
cat > /root/.cloudflared/config.yml << TUNNEL_CONF
tunnel: ${TUNNEL_ID}
credentials-file: /root/.cloudflared/${TUNNEL_ID}.json

ingress:
  - hostname: ${DOMAIN}
    service: http://localhost:80
    originRequest:
      noTLSVerify: true
  - hostname: www.${DOMAIN}
    service: http://localhost:80
    originRequest:
      noTLSVerify: true
  - service: http_status:404
TUNNEL_CONF

# Route DNS
cloudflared tunnel route dns "${TUNNEL_NAME}" "${DOMAIN}"     2>&1 || print_warn "DNS ${DOMAIN} mungkin sudah ada"
cloudflared tunnel route dns "${TUNNEL_NAME}" "www.${DOMAIN}" 2>&1 || print_warn "DNS www.${DOMAIN} mungkin sudah ada"

# Install cloudflared sebagai systemd service
cloudflared service install 2>/dev/null || {
    cat > /etc/systemd/system/cloudflared.service << CF_SERVICE
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
CF_SERVICE
}

systemctl daemon-reload
systemctl enable cloudflared
systemctl start cloudflared

print_step "Cloudflare Tunnel service aktif"

# ============================================================
# FINAL STATUS
# ============================================================
print_header "SETUP SELESAI! 🎉"

echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║${NC}  ${BOLD}STATUS SEMUA SERVICES${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  Nginx:           $(systemctl is-active nginx)"
echo -e "  PHP-FPM:         $(systemctl is-active php${PHP_VERSION}-fpm)"
echo -e "  Supervisor:      $(systemctl is-active supervisor)"
echo -e "  Cloudflared:     $(systemctl is-active cloudflared)"
echo ""
echo -e "  Proses Supervisor:"
supervisorctl status 2>/dev/null || echo "  (jalankan: sudo supervisorctl status)"
echo ""
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo -e "  ${BOLD}🌐 Website:${NC}  ${CYAN}https://${DOMAIN}${NC}"
echo -e "  ${BOLD}📱 OpenWA:${NC}   Scan QR di dashboard admin RedSim"
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}LANGKAH SELANJUTNYA:${NC}"
echo -e "  1. Edit .env dan isi OPENWA_API_KEY setelah scan QR WhatsApp"
echo -e "  2. Cek log Laravel:    tail -f ${PROJECT_DIR}/storage/logs/laravel.log"
echo -e "  3. Cek log OpenWA:     tail -f ${OPENWA_DIR}/data/openwa.log"
echo -e "  4. Cek log Nginx:      tail -f /var/log/nginx/redsim-error.log"
echo -e "  5. Cek Supervisor:     sudo supervisorctl status"
echo -e "  6. Cek Tunnel:         cloudflared tunnel info ${TUNNEL_NAME}"
echo -e "  7. Restart semua:      sudo supervisorctl restart all && sudo systemctl restart nginx"
echo ""
echo -e "${MAGENTA}Spesifikasi VirtualBox ini (RAM 2GB) sudah dioptimasi dengan Swap ${SWAP_SIZE}.${NC}"
echo -e "${MAGENTA}Untuk production stabil, gunakan VPS 4GB RAM.${NC}"
echo ""
