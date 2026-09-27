#!/bin/bash
# ============================================================
# RedSim - Prepare Deploy untuk Shared Hosting cPanel
# Jalankan di komputer lokal sebelum upload ke server
# ============================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'
BOLD='\033[1m'

echo -e "${CYAN}${BOLD}"
echo "╔══════════════════════════════════════════╗"
echo "║   RedSim - Prepare Shared Hosting Deploy ║"
echo "╚══════════════════════════════════════════╝"
echo -e "${NC}"

# Step 1: Build Vite assets
echo -e "${GREEN}[1/5]${NC} Build Vite assets (npm run build)..."
npm run build
echo -e "  ✅ Assets berhasil di-build: public/build/"

# Step 2: Composer production
echo -e "${GREEN}[2/5]${NC} Install Composer dependencies (production mode)..."
composer install --optimize-autoloader --no-dev --quiet
echo -e "  ✅ Vendor berhasil dioptimasi"

# Step 3: Salin .env.deploy ke .env.production (jangan sampai .env lokal ikut ke-zip)
echo -e "${GREEN}[3/5]${NC} Menyiapkan template .env production..."
if [ -f ".env.deploy" ]; then
    cp .env.deploy .env.production.template
    echo -e "  ✅ .env.production.template siap — edit dulu sebelum upload!"
else
    echo -e "  ${YELLOW}⚠️  .env.deploy tidak ditemukan. Buat manual .env untuk server.${NC}"
fi

# Step 4: Buat ZIP
echo -e "${GREEN}[4/5]${NC} Membuat file ZIP untuk upload..."
cd ..
zip -r redsim_sharedhosting.zip backend/ \
    --exclude "backend/.git/*" \
    --exclude "backend/.gitignore" \
    --exclude "backend/node_modules/*" \
    --exclude "backend/storage/logs/*.log" \
    --exclude "backend/storage/framework/cache/*" \
    --exclude "backend/storage/framework/sessions/*" \
    --exclude "backend/storage/framework/views/*" \
    --exclude "backend/venv/*" \
    --exclude "backend/*.log" \
    --exclude "backend/serve.log" \
    --exclude "backend/serve.pid" \
    --exclude "backend/.env" \
    --exclude "backend/.env.production.template" \
    --quiet

SIZE=$(du -sh redsim_sharedhosting.zip | cut -f1)
echo -e "  ✅ ZIP berhasil: redsim_sharedhosting.zip (${SIZE})"

# Step 5: Kembalikan composer ke mode dev
cd backend
echo -e "${GREEN}[5/5]${NC} Memulihkan Composer dev dependencies..."
composer install --quiet
echo -e "  ✅ Dev dependencies dipulihkan"

echo ""
echo -e "${CYAN}${BOLD}═══════════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ Persiapan selesai!${NC}"
echo ""
echo -e "  📦 File untuk upload: ${BOLD}../redsim_sharedhosting.zip${NC}"
echo -e "  📝 Edit dulu .env sebelum upload ke server"
echo ""
echo -e "  Langkah selanjutnya:"
echo -e "  1. Upload ${BOLD}redsim_sharedhosting.zip${NC} ke cPanel File Manager"
echo -e "  2. Buat database MySQL di cPanel"
echo -e "  3. Extract ZIP → pindah ke ~/redsim/"
echo -e "  4. Upload & konfigurasi .env di server"
echo -e "  5. Jalankan: php artisan migrate --force"
echo -e "${CYAN}${BOLD}═══════════════════════════════════════════════${NC}"
