<div align="center">

# 🔴 RedSim

### Platform Penilaian Keamanan Sistem Berbasis AI

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-3.x-38BDF8?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

**RedSim** adalah platform web berbasis AI yang menyediakan analisis keamanan siber secara otomatis dan komprehensif. Didukung knowledge base dari MITRE ATT&CK, NVD, OWASP, CISA KEV, CWE, dan CAPEC — analisis kerentanan kode, URL, dan proyek dalam hitungan detik.

</div>

---

## ✨ Fitur Unggulan

### 🛡️ Analisis Keamanan (AI-Powered)

| Fitur | Deskripsi |
|-------|-----------|
| **Analisis Kode** | Scan source code untuk kerentanan OWASP Top 10, CWE, dan remediasi actionable |
| **Analisis URL** | Pemindaian URL/domain untuk phishing, malware, miskonfigurasi. Termasuk verifikasi domain aktif |
| **Analisis ZIP** | Upload file ZIP proyek untuk analisis keamanan komprehensif seluruh codebase sekaligus |
| **Analisis Log** | Parsing log file (Nginx, Apache, sistem) untuk deteksi anomali dan intrusi |
| **Scan Intensif** | Pemindaian mendalam: OSINT, enumerasi port, fingerprinting, analisis kerentanan spesifik |
| **Simulasi Serangan** | Visualisasi skenario serangan berbasis framework MITRE ATT&CK sesuai hasil temuan |
| **Ekspor Laporan PDF** | Generate laporan profesional siap audit dalam format PDF |
| **Skor Keamanan** | Penilaian skor keamanan 0–100 untuk setiap hasil scan |

### 🤖 Integrasi AI & Knowledge Base

| Komponen | Detail |
|----------|--------|
| **Multi AI Provider** | Google Gemini, NVIDIA NIM (Llama, Mistral, dll) via `AIProviderService` |
| **RAG System** | Retrieval-Augmented Generation dengan Pinecone sebagai vector database |
| **AI Chatbot** | Asisten AI terintegrasi untuk tanya jawab seputar keamanan siber |
| **RAG Debugging** | Panel debugging RAG khusus admin untuk troubleshoot konteks retrieval |
| **Knowledge Chunking** | Ingestion otomatis knowledge base ke vector store |
| **Knowledge Base** | MITRE ATT&CK, NVD (200K+ CVE), OWASP, CISA KEV, CWE (1000+), CAPEC |

### 🎓 Edukasi & Gamifikasi

| Fitur | Deskripsi |
|-------|-----------|
| **Tantangan (CTF-Style)** | Kuis dan tantangan interaktif keamanan siber dengan sistem poin |
| **Ensiklopedia** | Knowledge base konsep keamanan siber yang dapat dicari |
| **Leaderboard** | Papan peringkat real-time berdasarkan poin tantangan |
| **Sistem Poin** | Reward poin untuk setiap tantangan yang diselesaikan |

### 🔐 Autentikasi & Keamanan Akun

| Fitur | Deskripsi |
|-------|-----------|
| **WhatsApp OTP** | Login dan reset password via OTP WhatsApp (OpenWA atau Fonnte) |
| **Google OAuth** | Single Sign-On (SSO) via akun Google menggunakan Laravel Socialite |
| **Notifikasi Login WA** | Notifikasi WhatsApp otomatis saat akun berhasil login |
| **Reset Password via WA** | Link reset password dikirim langsung ke nomor WhatsApp pengguna |
| **User Approval System** | Verifikasi manual oleh admin untuk pengguna baru |

### 📊 Admin Dashboard

| Fitur | Deskripsi |
|-------|-----------|
| **System Health Monitor** | Pemantauan status layanan: database, queue, AI provider, OpenWA |
| **Audit Logs** | Catatan aktivitas pengguna dan event sistem secara lengkap |
| **AI Configuration** | Kelola API key, model, dan parameter AI dari panel admin |
| **Manajemen User** | Approve/reject pendaftaran, kelola role dan data pengguna |
| **Manajemen Konten** | CRUD tantangan, ensiklopedia, dan knowledge base |
| **WhatsApp Gateway Config** | Konfigurasi OpenWA/Fonnte langsung dari panel admin |

---

## 🛠️ Tech Stack

### Backend
- **Framework:** [Laravel 13](https://laravel.com/) (PHP 8.3+)
- **Queue:** Laravel Queue (database driver) — untuk pemrosesan scan AI asinkron
- **PDF:** [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) v3.1
- **HTTP Client:** GuzzleHTTP v7.x
- **OAuth:** Laravel Socialite v5.x

### Frontend
- **CSS Framework:** Tailwind CSS via Vite
- **Animasi:** GSAP 3.x, WebGL custom shader (GridScan effect)
- **Font:** Inter, JetBrains Mono, Montserrat (Google Fonts)
- **Build Tool:** Vite

### Database
- **Utama:** MySQL 8.x / PostgreSQL 15+ / SQLite (development)
- **Vector DB:** [Pinecone](https://www.pinecone.io/) (RAG & embedding)

### Integrasi Eksternal

| Layanan | Fungsi | Env Key |
|---------|--------|---------|
| Google Gemini API | Model AI utama | `GEMINI_API_KEY` |
| NVIDIA NIM API | Model AI alternatif | `NVIDIA_API_KEY` |
| Pinecone | Vector database RAG | `PINECONE_API_KEY` |
| VirusTotal | Reputasi URL/IP | `VIRUSTOTAL_API_KEY` |
| URLScan.io | Scan URL lanjutan | `URLSCAN_API_KEY` |
| OpenWA | WhatsApp self-hosted | `OPENWA_BASE_URL`, `OPENWA_API_KEY` |
| Fonnte | WhatsApp cloud API | `FONNTE_TOKEN` |
| Google OAuth | SSO login | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` |

---

## ⚙️ Instalasi Lokal (Development)

### Prasyarat

- PHP 8.3+
- Composer 2.x
- Node.js 20+ & NPM
- MySQL 8+ atau SQLite (untuk dev)
- Git

### Langkah Instalasi

**1. Clone repository**
```bash
git clone https://github.com/barbarbarox/redsim.git
cd redsim/backend
```

**2. Install PHP & Node dependencies**
```bash
composer install
npm install
```

**3. Konfigurasi environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Setup database**
```bash
# Buat file SQLite (development)
touch database/database.sqlite

# Atau untuk MySQL, edit .env terlebih dahulu
# DB_CONNECTION=mysql
# DB_DATABASE=redsim
# DB_USERNAME=root
# DB_PASSWORD=

php artisan migrate
php artisan db:seed   # opsional
```

**5. Jalankan semua service (recommended)**
```bash
composer run dev
```

Perintah ini menjalankan secara paralel:
- `php artisan serve` — web server `http://localhost:8000`
- `php artisan queue:listen` — worker queue untuk proses AI
- `php artisan pail` — log viewer
- `npm run dev` — Vite HMR

> ⚠️ **Queue worker wajib dijalankan.** Semua proses scan AI berjalan secara asinkron melalui queue.

---

## 🔧 Konfigurasi `.env`

```env
# ===== APLIKASI =====
APP_NAME="RedSim"
APP_ENV=production
APP_URL=https://domain-anda.com
APP_DEBUG=false

# ===== DATABASE =====
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=redsim
DB_USERNAME=redsim_user
DB_PASSWORD=your_strong_password

# ===== QUEUE =====
QUEUE_CONNECTION=database

# ===== GOOGLE OAUTH =====
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=https://domain-anda.com/auth/google/callback

# ===== AI PROVIDERS =====
GEMINI_API_KEY=your_gemini_api_key
NVIDIA_API_KEY=your_nvidia_api_key

# ===== VECTOR DATABASE (RAG) =====
PINECONE_API_KEY=your_pinecone_api_key
PINECONE_INDEX=redsim-knowledge
PINECONE_ENVIRONMENT=us-east-1-aws

# ===== WHATSAPP GATEWAY (OpenWA - self-hosted) =====
OPENWA_BASE_URL=http://localhost:2785
OPENWA_API_KEY=your_openwa_api_key
OPENWA_SESSION_ID=default
OPENWA_SENDER_NUMBER=628xxxxxxxxxx

# ===== WHATSAPP GATEWAY (Fonnte - cloud, alternatif) =====
FONNTE_TOKEN=your_fonnte_token

# ===== SECURITY TOOLS =====
VIRUSTOTAL_API_KEY=your_virustotal_api_key
URLSCAN_API_KEY=your_urlscan_api_key
```

---

## 🚀 Deployment ke Ubuntu Server

Project sudah dilengkapi script deployment otomatis untuk Ubuntu 22.04/24.04:

```bash
chmod +x setup-ubuntu-server.sh
sudo ./setup-ubuntu-server.sh
```

Script otomatis menginstall dan mengkonfigurasi:
- PHP 8.3-FPM + Nginx
- Node.js 20 LTS + Composer
- Cloudflare Tunnel (HTTPS publik)
- Semua dependency Laravel

### Stack Deployment Rekomendasi
```
Internet → Cloudflare Tunnel → Nginx → PHP-FPM → Laravel
                                             └→ Queue Worker (systemd)
                                             └→ OpenWA (Node.js, systemd)
```

---

## 📱 Setup OpenWA sebagai WhatsApp API Gateway

RedSim menggunakan **OpenWA** (open-source WhatsApp Web API) untuk fitur OTP WhatsApp, notifikasi login, dan notifikasi hasil scan.

> 📁 Folder OpenWA tersedia di: `../openwa/` (satu level di atas folder `backend/`)

### Prasyarat OpenWA

- Node.js 18+ (direkomendasikan: 20 LTS)
- Google Chrome / Chromium (untuk headless browser Puppeteer)
- RAM minimal **1 GB free**
- **1 nomor WhatsApp aktif** sebagai sender bot

---

### 📦 Langkah 1 — Install Dependencies

```bash
cd openwa
npm install

# Verifikasi
node --version    # v18+ atau v20+
npm --version
```

Jika di Ubuntu server, install Chromium terlebih dahulu:
```bash
sudo apt update
sudo apt install -y chromium-browser

# Verifikasi
chromium-browser --version
```

---

### 🔑 Langkah 2 — Konfigurasi Environment OpenWA

```bash
cp .env.minimal .env
nano .env
```

Isi `.env` OpenWA:
```env
# Server
PORT=2785
NODE_ENV=production

# Database
DATABASE_TYPE=sqlite
DATABASE_NAME=./data/openwa.sqlite
DATABASE_SYNCHRONIZE=true
DATABASE_LOGGING=false

# WhatsApp Engine
ENGINE_TYPE=whatsapp-web.js
SESSION_DATA_PATH=./data/sessions
PUPPETEER_HEADLESS=true
PUPPETEER_ARGS=--no-sandbox,--disable-setuid-sandbox,--disable-dev-shm-usage

# API Security (WAJIB untuk production - min 32 karakter)
API_MASTER_KEY=gunakan_random_string_panjang_minimal_32_karakter_ini

# Auto-start sesi saat server restart
AUTO_START_SESSIONS=true

# Webhook (opsional)
WEBHOOK_TIMEOUT=10000
WEBHOOK_RETRY_DELAY=5000

# Storage
STORAGE_TYPE=local
STORAGE_LOCAL_PATH=./data/media

# Redis & Queue (nonaktif untuk setup sederhana)
REDIS_ENABLED=false
QUEUE_ENABLED=false
CACHE_ENABLED=false
```

Buat folder data:
```bash
mkdir -p data/sessions data/media
```

---

### 📲 Langkah 3 — Jalankan & Scan QR Code

```bash
# Jalankan OpenWA
npm start
```

Output yang diharapkan:
```
[OpenWA] Server running on port 2785
[OpenWA] Dashboard: http://localhost:2785
```

**Buka Dashboard di browser:** `http://localhost:2785`  
*(Jika server remote, gunakan SSH tunneling: `ssh -L 2785:localhost:2785 user@server`)*

1. Masuk dengan `API_MASTER_KEY` yang Anda set
2. Klik **Sessions** → **New Session**
3. Session ID: `default` (harus sama dengan `OPENWA_SESSION_ID` di `.env` Laravel)
4. Klik **Start Session** → **QR Code akan muncul**
5. Buka WhatsApp di HP → **Linked Devices** → **Link a Device**
6. Scan QR Code
7. Tunggu status: `CONNECTED` ✅

---

### 🔐 Langkah 4 — Mendapatkan API Key OpenWA

#### Cara A — Via Dashboard (Direkomendasikan)

1. Login di `http://localhost:2785`
2. Menu **Settings** → **API Keys**
3. Klik **Generate New API Key**
4. Salin API key yang dihasilkan
5. Simpan dengan aman — key hanya tampil sekali

#### Cara B — Gunakan Master Key Langsung

`API_MASTER_KEY` dari `.env` OpenWA bisa digunakan langsung sebagai API key untuk testing:

```bash
# Test kirim pesan
curl -X POST http://localhost:2785/api/sessions/default/messages/send-text \
  -H "X-Api-Key: API_MASTER_KEY_ANDA" \
  -H "Content-Type: application/json" \
  -d '{
    "chatId": "628123456789@c.us",
    "text": "Halo dari RedSim! 🔴"
  }'
```

Response sukses:
```json
{
  "status": true,
  "message": "Message sent",
  "id": "ABCD12345"
}
```

> **Format `chatId`:** `628xxxxxxxxxx@c.us` — awali dengan kode negara `62` (Indonesia), tanpa `+`, tanpa `0` di depan.

---

### ⚙️ Langkah 5 — Hubungkan dengan Laravel (RedSim)

Edit `.env` di folder `backend/`:
```env
OPENWA_BASE_URL=http://localhost:2785
OPENWA_API_KEY=api_key_dari_langkah_4
OPENWA_SESSION_ID=default
OPENWA_SENDER_NUMBER=628xxxxxxxxxx
```

Verifikasi dari Admin Panel RedSim:
1. Login sebagai admin
2. **Admin** → **App Settings** → bagian **WhatsApp Gateway**
3. Klik **Test Koneksi**
4. Status harus: `CONNECTED ✅`

---

### 🔄 Langkah 6 — Jalankan OpenWA sebagai systemd Service

```bash
sudo nano /etc/systemd/system/openwa.service
```

```ini
[Unit]
Description=OpenWA WhatsApp API Gateway
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/openwa
ExecStart=/usr/bin/node src/main.js
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=openwa
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable openwa
sudo systemctl start openwa

# Cek status
sudo systemctl status openwa

# Lihat log real-time
sudo journalctl -u openwa -f
```

---

### 🌐 Langkah 7 — Nginx Reverse Proxy untuk OpenWA (Opsional)

Jika OpenWA perlu diakses dari URL publik:

```nginx
server {
    listen 80;
    server_name wa.domain-anda.com;

    location / {
        proxy_pass http://127.0.0.1:2785;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_cache_bypass $http_upgrade;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/openwa /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Pasang SSL dengan Certbot
sudo certbot --nginx -d wa.domain-anda.com
```

> ⚠️ **Jika OpenWA diakses publik:** Wajib HTTPS + `API_MASTER_KEY` minimal 32 karakter acak. Pertimbangkan membatasi akses hanya ke IP server backend.

---

### 🔄 Alternatif: Fonnte (Cloud WhatsApp Gateway)

Jika tidak ingin self-host OpenWA, gunakan **[Fonnte](https://fonnte.com)**:

**Setup Fonnte:**
1. Daftar di [fonnte.com](https://fonnte.com)
2. Tambahkan device → scan QR Code dengan WhatsApp
3. Salin **Token** dari dashboard
4. Set di `.env` backend:
   ```env
   FONNTE_TOKEN=token_dari_dashboard_fonnte
   ```
5. Admin Panel → App Settings → WhatsApp Provider: pilih `fonnte`

**Perbandingan:**

| | OpenWA (Self-host) | Fonnte (Cloud) |
|--|--|--|
| Biaya | Gratis | ~Rp 50.000/bulan |
| Kontrol | Penuh | Terbatas |
| Setup | Lebih kompleks | Sangat mudah |
| Uptime | Tergantung server | 99.9% |
| Cocok untuk | Dev + Production managed | Production cepat |

---

## 🗄️ Struktur Database

| Tabel | Fungsi |
|-------|--------|
| `users` | Data pengguna, nomor WA, role, status approval |
| `scans` | Hasil scan keamanan (kode, URL, ZIP, log, intensif) |
| `temuan` | Detail temuan/kerentanan dari setiap scan |
| `simulasi_serangan` | Data simulasi serangan MITRE ATT&CK |
| `tantangan` | Data soal/tantangan edukasi |
| `poin_users` | Poin gamifikasi pengguna |
| `ai_configurations` | Konfigurasi AI provider (API key, model, parameter) |
| `app_settings` | Konfigurasi aplikasi (WA gateway, dll) |
| `audit_logs` | Log aktivitas pengguna |
| `knowledge_chunks` | Potongan knowledge base untuk RAG |
| `domain_verifications` | Cache verifikasi domain aktif |

---

## 🧩 Arsitektur Layanan

```
┌─────────────────────────────────────────────────────────────┐
│                       RedSim Platform                        │
├──────────────┬─────────────────────┬────────────────────────┤
│  Web Layer   │   Service Layer     │   External Services    │
│              │                     │                        │
│  Controllers │ AIProviderService   │ Google Gemini API      │
│  Blade Views │ RAGService          │ NVIDIA NIM API         │
│  Vite Assets │ ScanBiasaService    │ Pinecone Vector DB     │
│              │ ScanIntensService   │ VirusTotal API         │
│              │ AnalisisKodeService │ URLScan.io API         │
│              │ AnalisisURLService  │ OpenWA (WhatsApp)      │
│              │ WhatsappService     │ Fonnte (WhatsApp alt)  │
│              │ GeminiService       │ Google OAuth           │
│              │ EmbeddingService    │                        │
│              │ PineconeService     │                        │
│              │ SystemHealthService │                        │
└──────────────┴─────────────────────┴────────────────────────┘
                        │
             ┌──────────▼──────────┐
             │   Laravel Queue     │
             │  (async AI scan)    │
             └─────────────────────┘
```

---

## ❓ Troubleshooting

### OpenWA: QR Code tidak muncul
```bash
# Pastikan Chromium terinstall
which chromium-browser || which google-chrome
sudo apt install -y chromium-browser

# Cek log
sudo journalctl -u openwa -n 100
```

### Scan AI stuck di "pending" / tidak diproses
```bash
# Pastikan queue worker aktif
php artisan queue:work --tries=3

# Cek failed jobs
php artisan queue:failed

# Retry semua
php artisan queue:retry all

# Clear dan restart queue
php artisan queue:clear
php artisan queue:work
```

### OpenWA disconnect setelah beberapa waktu
- WhatsApp membatasi sesi web — re-scan QR Code jika expired
- Set `AUTO_START_SESSIONS=true` di `.env` OpenWA
- Cek stabilitas koneksi internet server

### Error 403 / Unauthorized di OpenWA API
```bash
# Pastikan API key benar
curl -H "X-Api-Key: API_KEY_ANDA" http://localhost:2785/api/sessions
```

### Pinecone connection error
```bash
php artisan tinker
>>> app(\App\Services\PineconeService::class)->testConnection();
```

---

## 📝 Lisensi

Project ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

---

<div align="center">

Dibuat dengan ❤️ untuk keamanan siber Indonesia 🇮🇩

**RedSim** — *Temukan celah sebelum peretas menemukannya.*

</div>
