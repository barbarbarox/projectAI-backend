{{--
    Komponen: Feature Help Modal (Popup Panduan Fitur)
    Penggunaan dispatch:
        $dispatch('open-help', { feature: 'pemindai-url' })

    Auto-popup dari layout (via blade section):
        @section('page_feature', 'pemindai-url')

    Welcome popup untuk dashboard:
        @section('page_feature', 'welcome')
--}}

{{-- ══════════════════════════════════════════════
     UNIFIED HELP MODAL (Feature + Welcome)
     ══════════════════════════════════════════════ --}}
<div x-data="featureHelp()"
     x-show="open"
     x-cloak
     @open-help.window="openModal($event.detail.feature)"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-[999] flex items-center justify-center p-4"
     style="display:none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="close()"></div>

    {{-- Panel --}}
    <div class="relative z-10 w-full max-w-3xl rounded-lg overflow-hidden shadow-2xl"
         style="background:#161616; border:1px solid #2a2a2a;"
         x-transition:enter="transition ease-out duration-220"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-160"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4">

        {{-- Top accent gradient bar --}}
        <div style="height:3px; background:linear-gradient(90deg,#ef4444 0%,#dc2626 50%,#991b1b 100%);"></div>

        {{-- ── HEADER BAR ── --}}
        <div class="flex items-center justify-between px-6 py-3" style="border-bottom:1px solid #242424;">
            <div class="flex items-center gap-2.5">
                <span style="color:#ef4444; font-weight:800; font-size:0.72rem; letter-spacing:0.12em;">REDSIM</span>
                <span style="color:#3a3a3a; font-size:0.85rem; line-height:1;">|</span>
                <span style="color:#555; font-size:0.65rem; letter-spacing:0.18em; text-transform:uppercase;"
                      x-text="currentFeatureKey === 'welcome' ? 'SELAMAT DATANG' : 'PANDUAN FITUR'"></span>
            </div>
            <button @click="close()"
                    style="color:#555; width:26px; height:26px; border:1px solid #2e2e2e; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:1rem; line-height:1; background:transparent; cursor:pointer;"
                    onmouseover="this.style.color='#fff'; this.style.borderColor='#444';"
                    onmouseout="this.style.color='#555'; this.style.borderColor='#2e2e2e';">
                &#215;
            </button>
        </div>

        {{-- ── TITLE SECTION ── --}}
        <div class="px-6 py-5" style="border-bottom:1px solid #242424;">
            <h2 class="font-bold text-white" style="font-size:1.6rem; line-height:1.2; margin-bottom:0.3rem;"
                x-text="current?.title || 'Panduan Fitur'"></h2>
            <p style="color:#666; font-size:0.82rem;" x-text="current?.subtitle || ''"></p>
        </div>

        {{-- ══ BODY ══ --}}
        <div class="px-6 py-5" style="max-height:calc(90vh - 220px); overflow-y:auto;">

            {{-- Description paragraph --}}
            <p x-show="current?.description"
               style="color:#888; font-size:0.82rem; line-height:1.75; margin-bottom:1.4rem;"
               x-text="current?.description"></p>

            {{-- ── WELCOME: Feature grid ── --}}
            <template x-if="currentFeatureKey === 'welcome' && current?.featureGrid">
                <div>
                    <p style="color:#444; font-size:0.62rem; letter-spacing:0.18em; text-transform:uppercase; margin-bottom:0.9rem;">FITUR UNGGULAN</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1px; background:#242424; border:1px solid #242424; border-radius:6px; overflow:hidden; margin-bottom:1.25rem;">
                        <template x-for="(item, i) in current.featureGrid" :key="i">
                            <div style="background:#1c1c1c; padding:1rem 1.1rem; display:flex; align-items:center; gap:0.75rem;">
                                {{-- SVG Icon slot: rendered via icon key --}}
                                <div style="width:30px; height:30px; border:1.5px solid #2e2e2e; border-radius:5px; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#ef4444;"
                                     x-html="getFeatureIcon(item.iconKey)"></div>
                                <div>
                                    <p style="color:#e5e5e5; font-size:0.82rem; font-weight:600; line-height:1.2;" x-text="item.title"></p>
                                    <p style="color:#555; font-size:0.72rem; margin-top:2px;" x-text="item.desc"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- ── FEATURE: Two-column (Steps | Benefits) ── --}}
            <template x-if="currentFeatureKey !== 'welcome' && current?.steps?.length">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:2.5rem; margin-bottom:1.25rem;">

                    {{-- Left: CARA MENGGUNAKAN --}}
                    <div>
                        <p style="color:#444; font-size:0.62rem; letter-spacing:0.18em; text-transform:uppercase; margin-bottom:1rem;">CARA MENGGUNAKAN</p>
                        <template x-for="(step, i) in current.steps" :key="i">
                            <div>
                                <div style="display:flex; gap:0.75rem; padding:0.65rem 0;">
                                    <span style="color:#ef4444; font-weight:700; font-size:0.72rem; min-width:22px; padding-top:1px; flex-shrink:0;"
                                          x-text="String(i+1).padStart(2,'0')"></span>
                                    <div>
                                        <p style="color:#e0e0e0; font-size:0.8rem; font-weight:600; margin-bottom:0.2rem;"
                                           x-text="current.stepTitles?.[i] || ''"></p>
                                        <p style="color:#777; font-size:0.76rem; line-height:1.6;"
                                           x-text="step"></p>
                                    </div>
                                </div>
                                <div x-show="i < current.steps.length - 1"
                                     style="border-bottom:1px solid #252525;"></div>
                            </div>
                        </template>
                    </div>

                    {{-- Right: MANFAAT FITUR --}}
                    <div>
                        <p style="color:#444; font-size:0.62rem; letter-spacing:0.18em; text-transform:uppercase; margin-bottom:1rem;">MANFAAT FITUR</p>
                        <div style="display:flex; flex-direction:column; gap:0.75rem;">
                            <template x-for="(b, i) in current.benefits" :key="i">
                                <div style="display:flex; gap:0.6rem; align-items:flex-start;">
                                    <svg style="width:13px; height:13px; color:#ef4444; flex-shrink:0; margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <p style="color:#888; font-size:0.76rem; line-height:1.6;" x-text="b"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </template>

            {{-- ── INFO / TIPS BOX ── --}}
            <div x-show="current?.tips"
                 style="display:flex; gap:0.75rem; align-items:flex-start; padding:0.9rem 1rem; border-radius:5px; background:#1b1212; border:1px solid #3a1f1f; margin-top:0.25rem;">
                <div style="width:18px; height:18px; border-radius:50%; border:1.5px solid #ef4444; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;">
                    <span style="color:#ef4444; font-size:0.58rem; font-weight:800; line-height:1;">!</span>
                </div>
                <div>
                    <p style="color:#ef4444; font-size:0.6rem; letter-spacing:0.14em; text-transform:uppercase; font-weight:700; margin-bottom:0.3rem;"
                       x-text="current?.tipsLabel || 'CATATAN PENTING'"></p>
                    <p style="color:#888; font-size:0.76rem; line-height:1.65;" x-text="current?.tips"></p>
                </div>
            </div>

        </div>

        {{-- ── FOOTER ── --}}
        <div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid #242424;">

            {{-- Checkbox (non-welcome) --}}
            <label x-show="currentFeatureKey && currentFeatureKey !== 'welcome'"
                   style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; user-select:none;">
                <input type="checkbox"
                       x-model="dontShowAgain"
                       @change="saveDontShow()"
                       style="width:13px; height:13px; cursor:pointer; accent-color:#ef4444;">
                <span style="color:#555; font-size:0.73rem;">Jangan tampilkan otomatis lagi</span>
            </label>

            {{-- Welcome: checkbox "Jangan tampilkan lagi" --}}
            <label x-show="currentFeatureKey === 'welcome'"
                   style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; user-select:none;">
                <input type="checkbox"
                       x-model="dontShowAgain"
                       @change="saveDontShow()"
                       style="width:13px; height:13px; cursor:pointer; accent-color:#ef4444;">
                <span style="color:#555; font-size:0.73rem;">Jangan tampilkan lagi</span>
            </label>

            {{-- Close / CTA button --}}
            <button @click="close()"
                    style="background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; padding:0.5rem 1.3rem; border-radius:5px; font-size:0.82rem; font-weight:600; border:none; cursor:pointer; display:flex; align-items:center; gap:0.4rem; letter-spacing:0.01em;"
                    onmouseover="this.style.opacity='0.9'"
                    onmouseout="this.style.opacity='1'">
                <span x-text="currentFeatureKey === 'welcome' ? 'Mulai Eksplorasi' : 'Mengerti, Tutup'"></span>
                <span x-show="currentFeatureKey === 'welcome'" style="font-size:0.9rem;">→</span>
            </button>

        </div>

    </div>
</div>

<script>
(function() {
    function registerFeatureModals() {
        if (!window.Alpine) return;

        Alpine.data('featureHelp', () => ({
            open: false,
            current: null,
            currentFeatureKey: null,
            dontShowAgain: false,

            init() {
                this.$nextTick(() => {
                    const metaFeature = document.querySelector('meta[name="page-feature"]');
                    if (!metaFeature) return;

                    const featureKey = metaFeature.getAttribute('content');
                    if (!featureKey) return;

                    const storageKey = featureKey === 'welcome' ? 'welcome_dismissed' : 'help_dismissed_' + featureKey;
                    if (localStorage.getItem(storageKey) === '1') return;

                    setTimeout(() => {
                        this.openModal(featureKey);
                    }, 450);
                });
            },

            getFeatureIcon(key) {
                const icons = {
                    'scan-url': '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>',
                    'analisis-log': '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                    'cve-intel': '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01"/></svg>',
                    'ai-assistant': '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
                };
                return icons[key] || '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            },

            // ══════════════════════════════════════════
            // DATABASE SEMUA FITUR
            // ══════════════════════════════════════════
            features: {

                // ─── WELCOME ───────────────────────────────────────────
                'welcome': {
                    title: 'Selamat datang di RedSim.',
                    subtitle: 'Platform keamanan siber berbasis AI untuk menganalisis ancaman, mendeteksi kerentanan, dan melindungi aset digital Anda.',
                    description: null,
                    featureGrid: [
                        { iconKey: 'scan-url',     title: 'Scan URL',       desc: 'Cek keamanan website' },
                        { iconKey: 'analisis-log', title: 'Analisis Log',   desc: 'Deteksi serangan' },
                        { iconKey: 'cve-intel',    title: 'CVE Intel',      desc: 'AI prediksi ancaman' },
                        { iconKey: 'ai-assistant', title: 'AI Assistant',   desc: 'Tanya pakar keamanan' },
                    ],
                    tips: 'Setiap halaman menu memiliki tombol Panduan. Klik kapan saja untuk melihat cara penggunaan fitur.',
                    tipsLabel: 'PANDUAN SELALU TERSEDIA',
                },

                // ─── PEMINDAI URL ───────────────────────────────────────
                'pemindai-url': {
                    title: 'Pemindai URL',
                    subtitle: 'Analisis keamanan website secara mendalam',
                    description: 'Pindai URL untuk mendeteksi malware, phishing, konfigurasi SSL yang buruk, dan header keamanan yang hilang. Cek keamanan website sebelum mengunjunginya atau mempercayai layanan online.',
                    stepTitles: [
                        'Masukkan URL target',
                        'Pilih mode pemindaian',
                        'Atur model AI',
                        'Mulai dan tunggu hasilnya',
                    ],
                    steps: [
                        'Gunakan format lengkap, misalnya https://contoh.com.',
                        '"Scan Biasa" untuk cek cepat; "Scan Intens" untuk analisis mendalam. Scan Intens memerlukan verifikasi domain terlebih dahulu.',
                        'Pilih model yang ingin digunakan di bagian Opsi AI, jika tersedia.',
                        'Klik "Mulai Pemindaian". Proses scan memakan waktu 30 detik hingga beberapa menit.',
                    ],
                    benefits: [
                        'Mendeteksi website phishing atau berbahaya sebelum dikunjungi.',
                        'Memeriksa konfigurasi SSL/TLS yang salah atau kedaluwarsa.',
                        'Mengidentifikasi Security Headers yang hilang: CSP, HSTS, dan X-Frame-Options.',
                        'Menganalisis reputasi domain melalui VirusTotal dan URLScan.io.',
                    ],
                    tips: 'Gunakan Scan Intens untuk laporan yang lebih komprehensif. Pastikan Anda memiliki hak akses atas domain yang dipindai. Jangan memindai website tanpa izin pemiliknya.',
                    tipsLabel: 'AKSES DAN IZIN',
                },

                // ─── ANALISIS LOG ────────────────────────────────────────
                'analisis-log': {
                    title: 'Analisis Log',
                    subtitle: 'Deteksi anomali dan serangan dari file log server',
                    description: 'Analisis file log server (Apache, Nginx, Laravel, auth.log) menggunakan AI untuk menemukan pola serangan, akses mencurigakan, dan anomali keamanan secara otomatis dalam hitungan detik.',
                    stepTitles: [
                        'Upload atau tempelkan log',
                        'Pilih tipe log',
                        'Mulai analisis',
                        'Tinjau hasil temuan',
                    ],
                    steps: [
                        'Pilih tab "Upload File Log" untuk mengunggah file (.log/.txt/.csv, maks 10MB), atau tab "Tempel Teks Log" untuk paste langsung.',
                        'Pilih tipe log sesuai server Anda (Apache, Nginx, Laravel, dll.) atau gunakan "Deteksi Otomatis".',
                        'Klik "Mulai Analisis Log" dan tunggu proses AI selesai.',
                        'Tinjau IP mencurigakan, pola serangan yang terdeteksi, dan rekomendasi tindakan.',
                    ],
                    benefits: [
                        'Mendeteksi brute force, SQL injection, dan XSS dari log secara otomatis.',
                        'Mengidentifikasi IP berbahaya dan pola akses yang tidak wajar.',
                        'Menghemat waktu analisis log manual yang bisa memakan berjam-jam.',
                        'Mendukung format log populer: Apache, Nginx, Laravel, auth.log.',
                    ],
                    tips: 'Pastikan log yang diunggah sudah dianonimkan atau tidak mengandung data sensitif pengguna (misalnya email/password) untuk menjaga privasi.',
                    tipsLabel: 'PRIVASI DATA',
                },

                // ─── ANALISIS KODE ───────────────────────────────────────
                'analisis-kode': {
                    title: 'Analisis Kode',
                    subtitle: 'Review keamanan kode sumber secara otomatis (SAST)',
                    description: 'Lakukan review keamanan statis (SAST) pada kode sumber program. AI akan membaca kode Anda dan menemukan celah seperti SQL Injection, XSS, Command Injection, dan kerentanan keamanan umum lainnya.',
                    stepTitles: [
                        'Tempelkan kode',
                        'Pilih bahasa pemrograman',
                        'Mulai analisis',
                        'Tinjau temuan',
                    ],
                    steps: [
                        'Tempelkan cuplikan kode ke dalam kotak teks yang tersedia.',
                        'Pilih bahasa pemrograman dari dropdown (PHP, Python, JavaScript, Java, Go, Ruby).',
                        'Klik "Mulai Analisis" dan tunggu AI memproses kode Anda.',
                        'Tinjau daftar kerentanan beserta penjelasan dan saran cara memperbaikinya.',
                    ],
                    benefits: [
                        'Menemukan celah keamanan sebelum kode masuk ke lingkungan produksi.',
                        'Mendukung berbagai bahasa: PHP, Python, JavaScript, SQL, dan lainnya.',
                        'Memberikan saran perbaikan kode yang konkret dan aman.',
                    ],
                    tips: 'Analisis kode paling efektif dilakukan sebelum deployment atau code review. Fokus terlebih dahulu pada fungsi login dan pemrosesan input dari pengguna.',
                    tipsLabel: 'WAKTU TERBAIK',
                },

                // ─── CVE INTEL ───────────────────────────────────────────
                'cve-intel': {
                    title: 'CVE Intelligence Dashboard',
                    subtitle: 'Analisis ancaman dengan Ensemble AI · Random Forest + XGBoost',
                    description: 'CVE Intelligence menggunakan dua model Machine Learning—Random Forest dan XGBoost—secara bersamaan untuk memprediksi tingkat bahaya kerentanan secara instan, bahkan sebelum penilaian resmi NIST tersedia. CVE (Common Vulnerabilities and Exposures) adalah daftar kerentanan keamanan yang diidentifikasi secara global.',
                    stepTitles: [
                        'Mode "CVE ID": ambil data dari NVD',
                        'Mode "Manual": analisis tanpa ID CVE',
                        'Jalankan analisis ensemble',
                        'Tinjau prediksi dan rekomendasi',
                    ],
                    steps: [
                        'Masukkan ID kerentanan, misalnya CVE-2024-12345. Sistem otomatis mengambil data detail dari NIST NVD.',
                        'Isi jenis kerentanan, tingkat keparahan awal, dan kode CWE secara manual untuk analisis cepat tanpa ID CVE.',
                        'Klik "Analisis dengan AI Ensemble" dan tunggu prediksi dari kedua model ML.',
                        'Lihat tingkat bahaya (Low/Medium/High/Critical), persetujuan antar model, distribusi probabilitas, dan rekomendasi waktu mitigasi.',
                    ],
                    benefits: [
                        'Prediksi instan tanpa menunggu penilaian NIST yang bisa memakan berhari-hari.',
                        'Ensemble RF + XGBoost memberi akurasi lebih tinggi (Macro F1 > 0.95).',
                        'Distribusi probabilitas memperlihatkan keyakinan AI untuk setiap tingkat bahaya.',
                        'Batas waktu mitigasi otomatis membantu memprioritaskan respons tim keamanan.',
                    ],
                    tips: 'Jika kedua model berbeda pendapat, sistem menggunakan Soft Voting dengan bobot XGBoost 60% untuk keputusan akhir. Selalu validasi hasil AI dengan konteks lingkungan sistem Anda.',
                    tipsLabel: 'CARA KERJA AI',
                },

                // ─── THREAT HUNT ─────────────────────────────────────────
                'threat-hunt': {
                    title: 'Threat Hunting by CVE',
                    subtitle: 'Deteksi proaktif paparan kerentanan ke seluruh aset Anda',
                    description: 'Cari secara proaktif apakah aset digital Anda—domain, subdomain, port, dan layanan—terekspos terhadap CVE tertentu atau komponen teknologi yang rentan. Periksa seluruh aset untuk menemukan celah yang masih terbuka.',
                    stepTitles: [
                        'Tentukan kerentanan yang dicari',
                        'Persempit target jika diperlukan',
                        'Mulai pencarian',
                        'Tinjau paparan dan mitigasi',
                    ],
                    steps: [
                        'Masukkan CVE ID spesifik (misalnya CVE-2024-1234) atau nama teknologi/komponen, seperti "Apache 2.4" atau "OpenSSL 1.1".',
                        'Opsional: pilih "Target Scan" dari riwayat scan Anda untuk memfokuskan pencarian pada aset tertentu.',
                        'Klik "Mulai Hunt". Tunggu sistem memeriksa semua aset terdaftar.',
                        'Lihat aset yang terekspos, detail kerentanan yang ditemukan, serta saran mitigasi.',
                    ],
                    benefits: [
                        'Mendeteksi paparan di seluruh aset sebelum diserang: proaktif, bukan reaktif.',
                        'Memetakan teknologi rentan pada setiap subdomain dan layanan Anda.',
                        'Menghubungkan data CVE global dengan inventaris aset nyata.',
                        'Memprioritaskan patch/update berdasarkan aset yang paling terekspos.',
                    ],
                    tips: 'Gunakan fitur ini secara rutin setiap ada CVE baru yang dipublikasikan.',
                    tipsLabel: 'PEMERIKSAAN RUTIN',
                },

                // ─── MONITORING ──────────────────────────────────────────
                'monitoring': {
                    title: 'Website Integrity Monitor',
                    subtitle: 'Pantau perubahan mencurigakan pada website Anda',
                    description: 'Pantau website Anda secara berkala untuk mendeteksi perubahan yang tidak diinginkan — seperti defacement, injeksi konten berbahaya, atau perubahan file penting. Seperti memiliki satpam digital yang mengawasi website Anda 24 jam.',
                    stepTitles: [
                        'Tambahkan monitor',
                        'Atur jadwal pemantauan',
                        'Pemantauan berjalan otomatis',
                        'Quick Check manual',
                    ],
                    steps: [
                        'Klik tombol "Tambah Monitor" untuk mendaftarkan website yang ingin dipantau.',
                        'Masukkan URL website target dan atur jadwal pengecekan (interval waktu pemantauan).',
                        'Sistem akan secara otomatis memotret kondisi website dan membandingkannya secara berkala.',
                        'Gunakan tombol "Quick Check" untuk memeriksa kondisi website saat ini secara manual kapan saja.',
                    ],
                    benefits: [
                        'Deteksi dini defacement website sebelum pengguna melihatnya.',
                        'Pemantauan otomatis 24/7 tanpa perlu manual mengecek.',
                        'Alert instan ketika ada perubahan konten yang mencurigakan.',
                        'Riwayat snapshot perubahan untuk forensik digital.',
                    ],
                    tips: 'Pantau halaman utama dan halaman login sebagai prioritas, karena keduanya paling sering menjadi target. Tersedia maksimal 2 monitor pada paket gratis.',
                    tipsLabel: 'PRIORITAS PEMANTAUAN',
                },

                // ─── INSIDEN ─────────────────────────────────────────────
                'insiden': {
                    title: 'Manajemen Insiden & Remediasi',
                    subtitle: 'Lacak dan selesaikan insiden keamanan secara terstruktur',
                    description: 'Dokumentasikan, lacak, dan selesaikan insiden keamanan dari tahap penemuan hingga verifikasi resolusi. Seperti buku catatan digital yang terorganisir untuk semua masalah keamanan yang perlu ditangani.',
                    stepTitles: [
                        'Laporkan insiden baru',
                        'Isi detail insiden',
                        'Update status penanganan',
                        'Verifikasi resolusi',
                    ],
                    steps: [
                        'Klik "Laporkan Insiden Baru" untuk mencatat temuan keamanan (misalnya dari hasil scan).',
                        'Isi detail: judul, deskripsi, tingkat keparahan, dan aset yang terdampak.',
                        'Update status seiring proses penanganan: Open → Investigating → Patching → Resolved.',
                        'Lakukan scan ulang setelah perbaikan selesai untuk memverifikasi bahwa celah sudah tertutup.',
                    ],
                    benefits: [
                        'Dokumentasi insiden terstruktur yang berguna untuk audit keamanan.',
                        'Pelacakan status dari penemuan hingga penyelesaian secara transparan.',
                        'Memastikan tidak ada celah keamanan yang terlewat atau terlupakan.',
                        'Riwayat insiden sebagai bahan pembelajaran untuk mencegah kejadian serupa.',
                    ],
                    tips: 'Biasakan melaporkan setiap temuan ke sistem insiden ini — meskipun tingkat keparahannya rendah. Catatan yang lengkap sangat penting untuk compliance dan audit.',
                    tipsLabel: 'CATATAN PENTING',
                },

                // ─── LAPORAN ─────────────────────────────────────────────
                'laporan': {
                    title: 'Riwayat & Laporan Scan',
                    subtitle: 'Kelola semua riwayat pemindaian keamanan Anda',
                    description: 'Halaman ini menyimpan semua riwayat pemindaian yang pernah dilakukan di RedSim. Lihat kembali hasil scan, unduh laporan PDF, bandingkan hasil antar waktu, dan hapus data lama yang tidak diperlukan.',
                    stepTitles: [
                        'Temukan scan yang dicari',
                        'Lihat detail hasil scan',
                        'Unduh laporan PDF',
                        'Bandingkan dua scan',
                    ],
                    steps: [
                        'Gunakan tabel riwayat untuk menemukan scan — klik baris untuk melihat detail lengkapnya.',
                        'Klik ikon mata pada baris scan untuk membuka halaman detail dengan semua temuan.',
                        'Gunakan tombol "Unduh PDF" di halaman detail untuk mengekspor laporan keamanan profesional.',
                        'Gunakan fitur "Bandingkan" untuk melihat perubahan antara dua scan pada target yang sama.',
                    ],
                    benefits: [
                        'Semua riwayat scan tersimpan aman dan bisa diakses kapan saja.',
                        'Laporan PDF profesional yang bisa diserahkan ke klien atau manajemen.',
                        'Perbandingan scan membantu mengukur peningkatan keamanan dari waktu ke waktu.',
                        'Arsip temuan keamanan untuk keperluan audit dan compliance.',
                    ],
                    tips: 'Ekspor laporan PDF secara rutin setelah scan selesai. Laporan ini berguna untuk presentasi ke manajemen atau sebagai bukti kepatuhan keamanan (compliance).',
                    tipsLabel: 'TIPS DOKUMENTASI',
                },

                // ─── CHAT AI ─────────────────────────────────────────────
                'chat-ai': {
                    title: 'AI Security Assistant',
                    subtitle: 'Konsultasi keamanan siber dengan AI berbasis RAG',
                    description: 'Asisten kecerdasan buatan yang dilatih dengan pengetahuan keamanan siber. Tanya seputar keamanan, ancaman, kerentanan, atau minta saran — seperti berkonsultasi dengan pakar keamanan berpengalaman kapan saja.',
                    stepTitles: [
                        'Ketik pertanyaan Anda',
                        'Kirim ke AI',
                        'Baca respons AI',
                        'Lanjutkan percakapan',
                    ],
                    steps: [
                        'Ketik pertanyaan atau topik keamanan di kotak teks bagian bawah.',
                        'Tekan Enter atau klik tombol kirim untuk mengajukan pertanyaan.',
                        'AI akan merespons berdasarkan basis pengetahuan keamanan (NVD, MITRE, OWASP).',
                        'Lanjutkan dengan pertanyaan lanjutan — AI mengingat konteks percakapan sebelumnya.',
                    ],
                    benefits: [
                        'Tersedia 24/7 — tanya kapan saja tanpa menunggu pakar keamanan.',
                        'Jawaban berbasis RAG yang mengacu pada database terpercaya (OWASP, NVD, MITRE ATT&CK).',
                        'Menjawab pertanyaan teknis maupun pertanyaan umum seputar keamanan siber.',
                        'Membantu memahami hasil scan dan memberikan saran remediasi yang lebih spesifik.',
                    ],
                    tips: 'Ajukan pertanyaan yang spesifik untuk mendapat jawaban yang lebih akurat. Contoh: "Apa itu SQL Injection dan bagaimana cara mencegahnya di PHP?" lebih baik dari pertanyaan umum.',
                    tipsLabel: 'TIPS BERTANYA',
                },

                // ─── RED TEAM LAB ────────────────────────────────────────
                'red-team-lab': {
                    title: 'Red Team Lab',
                    subtitle: 'Kalkulasi CVSS dan referensi teknik serangan MITRE ATT&CK',
                    description: 'Ruang kerja untuk simulasi dan penilaian keamanan. Tersedia kalkulator CVSS (penghitung skor bahaya kerentanan) secara real-time dan referensi MITRE ATT&CK Matrix — kerangka taktik serangan yang digunakan secara global.',
                    stepTitles: [
                        'Gunakan CVSSv3.1 Calculator',
                        'Baca hasil skor',
                        'Referensi MITRE ATT&CK',
                        'Kombinasikan untuk penilaian risiko',
                    ],
                    steps: [
                        'Pilih nilai untuk setiap parameter serangan (Attack Vector, Complexity, dll.) — skor dihitung otomatis.',
                        'Baca hasil: 0–3.9 (Low), 4–6.9 (Medium), 7–8.9 (High), 9–10 (Critical).',
                        'Gunakan MITRE ATT&CK Matrix sebagai referensi teknik serangan berdasarkan fase serangan.',
                        'Kombinasikan skor CVSS dengan teknik ATT&CK relevan untuk penilaian risiko yang komprehensif.',
                    ],
                    benefits: [
                        'Menghitung skor kerentanan sesuai standar industri global (FIRST.org) secara real-time.',
                        'Memahami teknik serangan hacker nyata melalui MITRE ATT&CK.',
                        'Membantu membuat laporan keamanan formal dengan skor CVSS terstandar.',
                        'Referensi untuk Red Team (simulasi serangan) dan Blue Team (pertahanan).',
                    ],
                    tips: 'Skor CVSS saja tidak cukup untuk menentukan prioritas — selalu pertimbangkan ketersediaan exploit di internet dan seberapa kritis aset yang terdampak.',
                    tipsLabel: 'CATATAN EVALUASI',
                },

                // ─── ASSET GROUPS ────────────────────────────────────────
                'asset-groups': {
                    title: 'Grup Aset & Multi-Target ASM',
                    subtitle: 'Kelola dan pantau postur keamanan seluruh aset digital Anda',
                    description: 'ASM (Attack Surface Management) memungkinkan pengelompokan dan pemantauan keamanan seluruh aset digital secara terpusat — domain, subdomain, API, dan infrastruktur. Seperti memiliki peta lengkap semua "pintu" masuk ke sistem digital Anda.',
                    stepTitles: [
                        'Buat grup aset baru',
                        'Tambahkan target ke grup',
                        'Jalankan scan ASM',
                        'Monitor skor keamanan',
                    ],
                    steps: [
                        'Klik "Buat Grup Aset Baru" dan beri nama kelompok aset (misalnya: "Website Utama", "API Backend").',
                        'Tambahkan URL/domain target ke dalam grup yang telah dibuat.',
                        'Jalankan scan ASM pada grup untuk menganalisis seluruh aset dalam satu grup sekaligus.',
                        'Monitor skor keamanan agregat dan temuan di seluruh aset dalam grup tersebut.',
                    ],
                    benefits: [
                        'Pemantauan postur keamanan terpusat untuk semua aset dalam satu tampilan.',
                        'Pemindaian massal hemat waktu dibanding scan satu per satu.',
                        'Visibilitas lengkap terhadap attack surface (permukaan serangan) organisasi.',
                        'Memudahkan prioritisasi remediasi berdasarkan grup yang paling kritikal.',
                    ],
                    tips: 'Organisasikan grup berdasarkan lingkungan (Production, Staging, Dev). Jangan lupa memindai subdomain yang sering terlupakan — mereka sering menjadi titik masuk serangan.',
                    tipsLabel: 'TIPS ORGANISASI',
                },

                // ─── PROFIL ──────────────────────────────────────────────
                'profil': {
                    title: 'Profil & Pengaturan Akun',
                    subtitle: 'Kelola informasi akun dan konfigurasi keamanan Anda',
                    description: 'Halaman profil memungkinkan Anda mengelola informasi akun pribadi: mengubah nama tampilan, email, nomor WhatsApp untuk notifikasi dan OTP, serta memperbarui password akun secara berkala.',
                    stepTitles: [
                        'Edit informasi profil',
                        'Pastikan nomor WhatsApp valid',
                        'Ubah password secara berkala',
                        'Simpan perubahan',
                    ],
                    steps: [
                        'Edit nama tampilan dan informasi kontak Anda di form "Informasi Profil".',
                        'Pastikan nomor WhatsApp aktif dan valid untuk menerima kode verifikasi dan notifikasi.',
                        'Ubah password akun secara berkala melalui bagian "Ubah Password".',
                        'Klik tombol "Simpan Perubahan" untuk menyimpan semua pembaruan.',
                    ],
                    benefits: [
                        'Menjaga akun RedSim tetap terlindungi dengan kredensial yang selalu diperbarui.',
                        'Memastikan notifikasi dan alert keamanan sampai ke WhatsApp Anda.',
                        'Kemudahan pemulihan akun jika sewaktu-waktu lupa kata sandi.',
                    ],
                    tips: 'Gunakan password yang kuat (minimal 8 karakter: huruf besar, kecil, angka, dan simbol). Jangan gunakan password yang sama dengan akun lain.',
                    tipsLabel: 'KEAMANAN AKUN',
                },

                // ─── MODEL AI ────────────────────────────────────────────
                'model-ai': {
                    title: 'Pengaturan Model AI',
                    subtitle: 'Konfigurasi kecerdasan buatan untuk analisis keamanan',
                    description: 'Pilih dan atur AI yang digunakan RedSim untuk analisis keamanan. Gunakan AI bawaan RedSim atau hubungkan kunci API Anda sendiri (Google Gemini, OpenAI, Groq, atau Ollama lokal).',
                    stepTitles: [
                        'Pilih penyedia AI',
                        'Masukkan API Key',
                        'Pilih model spesifik',
                        'Simpan konfigurasi',
                    ],
                    steps: [
                        'Pilih penyedia AI yang ingin digunakan (Google Gemini, Groq, OpenAI, atau Ollama lokal).',
                        'Masukkan API Key dari akun AI Anda jika menggunakan penyedia eksternal.',
                        'Pilih model spesifik yang diinginkan (misal: gemini-1.5-pro, gemini-1.5-flash, gpt-4o).',
                        'Klik "Simpan Konfigurasi" — model pilihan Anda akan digunakan untuk analisis berikutnya.',
                    ],
                    benefits: [
                        'Fleksibilitas menggunakan AI tercepat atau tercerdas sesuai kebutuhan.',
                        'Dukungan model lokal (Ollama) untuk menjaga privasi data di komputer sendiri.',
                        'Rekomendasi keamanan yang lebih mendalam dan disesuaikan.',
                    ],
                    tips: 'Untuk analisis cepat, gunakan Gemini Flash atau Groq. Untuk review kode rumit, model Pro memberikan ketajaman analisis tertinggi.',
                    tipsLabel: 'REKOMENDASI MODEL',
                },

                // ─── HUB TEMUAN ──────────────────────────────────────────
                'hub-temuan': {
                    title: 'Hub Seluruh Temuan Keamanan',
                    subtitle: 'Daftar semua celah keamanan yang terdeteksi di sistem Anda',
                    description: 'Tempat berkumpulnya seluruh celah keamanan yang terdeteksi dari berbagai aktivitas pemindaian — Pemindai URL, Analisis Kode, hingga Analisis Log. Pantau celah mana yang paling berbahaya dan butuh penanganan segera.',
                    stepTitles: [
                        'Filter berdasarkan keparahan',
                        'Cari temuan spesifik',
                        'Lihat detail temuan',
                        'Eskalasi ke insiden',
                    ],
                    steps: [
                        'Gunakan filter tingkat keparahan (Kritis, Tinggi, Sedang, Rendah) untuk memprioritaskan masalah paling berbahaya.',
                        'Gunakan kotak pencarian untuk mencari nama celah, kode CVE, atau alamat aset tertentu.',
                        'Klik pada temuan untuk melihat penjelasan lengkap, bukti, dan cara memperbaikinya.',
                        'Klik "Eskalasi ke Insiden" jika temuan perlu dipantau proses perbaikannya oleh tim Anda.',
                    ],
                    benefits: [
                        'Semua masalah keamanan terpusat di satu halaman yang rapi dan mudah ditinjau.',
                        'Mempermudah memprioritaskan perbaikan mulai dari yang paling berbahaya.',
                        'Terkoneksi langsung dengan alur kerja penanganan insiden.',
                    ],
                    tips: 'Fokus perbaiki celah berstatus "Kritis" dan "Tinggi" dalam 24–48 jam pertama karena paling sering disasar oleh hacker.',
                    tipsLabel: 'PRIORITAS PERBAIKAN',
                },

                // ─── ALERT INTEGRITAS ────────────────────────────────────
                'alert-integritas': {
                    title: 'Alert Integritas Website',
                    subtitle: 'Daftar peringatan deteksi perubahan mencurigakan',
                    description: 'Halaman ini mencatat semua peringatan (alert) ketika sistem pemantau mendeteksi perubahan mencurigakan pada website Anda — seperti defacement, injeksi skrip jahat, atau hilangnya komponen penting.',
                    stepTitles: [
                        'Tinjau alert baru',
                        'Lihat detail perbandingan',
                        'Konfirmasi jika wajar',
                        'Tanggapi jika mencurigakan',
                    ],
                    steps: [
                        'Tinjau daftar alert yang baru masuk dengan label "belum dibaca".',
                        'Klik salah satu alert untuk melihat detail perbandingan kondisi website sebelum vs sesudah perubahan.',
                        'Jika perubahan wajar (karena Anda baru mengupdate website), klik "Konfirmasi / Acknowledge".',
                        'Jika mencurigakan atau indikasi peretasan, segera pulihkan dari backup dan buat laporan di menu Insiden.',
                    ],
                    benefits: [
                        'Pemberitahuan cepat saat website diretas atau diubah tanpa izin.',
                        'Menyediakan bukti visual dan perbandingan kode untuk memudahkan pelacakan.',
                        'Mengurangi kerugian reputasi akibat tampilan website dirusak hacker.',
                    ],
                    tips: 'Segera periksa website Anda jika menerima alert berkategori "Kritis" atau "Defacement", dan pastikan Anda rutin menyimpan backup website.',
                    tipsLabel: 'TINDAKAN DARURAT',
                },

                // ─── CVSS CALCULATOR ─────────────────────────────────────
                'cvss-calculator': {
                    title: 'CVSSv3.1 Calculator',
                    subtitle: 'Penghitung skor kerentanan standar industri',
                    description: 'CVSS (Common Vulnerability Scoring System) v3.1 adalah standar industri untuk menilai keparahan sebuah kerentanan keamanan secara numerik dari 0.0 hingga 10.0.',
                    stepTitles: [
                        'Isi parameter vektor serangan',
                        'Isi parameter dampak',
                        'Baca skor dan vektor string',
                        'Gunakan untuk laporan',
                    ],
                    steps: [
                        'Isi parameter: Attack Vector, Complexity, Privileges Required, dan User Interaction.',
                        'Isi parameter dampak: Confidentiality, Integrity, dan Availability Impact.',
                        'Skor dihitung otomatis beserta vektor string (contoh: CVSS:3.1/AV:N/AC:L/...).',
                        'Gunakan skor dan vektor string ini dalam laporan keamanan formal Anda.',
                    ],
                    benefits: [
                        'Standar global yang digunakan oleh NVD, vendor keamanan, dan tim SOC seluruh dunia.',
                        'Membantu membandingkan keparahan antar kerentanan secara objektif.',
                        'Diperlukan untuk membuat laporan keamanan formal yang terstandar.',
                    ],
                    tips: 'Skor CVSS saja tidak cukup — selalu pertimbangkan konteks lingkungan (Environmental Score) dan status exploit yang tersedia.',
                    tipsLabel: 'CATATAN EVALUASI',
                },

                // ─── MITRE ATTACK ────────────────────────────────────────
                'mitre-attack': {
                    title: 'MITRE ATT&CK Matrix',
                    subtitle: 'Kerangka taktik dan teknik serangan siber',
                    description: 'MITRE ATT&CK adalah basis pengetahuan global berisi taktik, teknik, dan prosedur (TTP) yang digunakan aktor ancaman nyata di seluruh dunia, diorganisir dalam bentuk matriks yang mudah dipahami.',
                    stepTitles: [
                        'Buka ATT&CK Matrix',
                        'Pilih domain',
                        'Identifikasi taktik dan teknik',
                        'Gunakan untuk penilaian risiko',
                    ],
                    steps: [
                        'Akses MITRE ATT&CK melalui tautan yang tersedia di halaman Red Team Lab.',
                        'Pilih domain: Enterprise (sistem IT), Mobile, atau ICS (sistem industri).',
                        'Identifikasi taktik yang relevan (kolom) dan teknik spesifiknya (baris).',
                        'Gunakan teknik yang ditemukan untuk menilai postur keamanan sistem Anda.',
                    ],
                    benefits: [
                        'Memahami cara kerja teknik serangan nyata yang digunakan oleh hacker.',
                        'Membantu red team merancang skenario simulasi serangan yang realistis.',
                        'Digunakan oleh blue team untuk memperkuat deteksi dan respons insiden.',
                        'Menjadi referensi utama dalam membuat laporan ancaman profesional.',
                    ],
                    tips: 'Gunakan ATT&CK Navigator untuk memetakan coverage deteksi tim keamanan Anda secara visual.',
                    tipsLabel: 'TOOLS PENDUKUNG',
                },
            },

            openModal(featureKey) {
                this.currentFeatureKey = featureKey;
                this.current = this.features[featureKey] || {
                    title: 'Panduan Fitur',
                    subtitle: '',
                    description: 'Informasi panduan untuk fitur ini belum tersedia.',
                    steps: [],
                    stepTitles: [],
                    benefits: [],
                    tips: null,
                    tipsLabel: 'CATATAN',
                };
                // Sync checkbox state
                const storageKey = featureKey === 'welcome' ? 'welcome_dismissed' : 'help_dismissed_' + featureKey;
                this.dontShowAgain = localStorage.getItem(storageKey) === '1';
                this.open = true;
                document.body.style.overflow = 'hidden';
            },

            saveDontShow() {
                const storageKey = this.currentFeatureKey === 'welcome'
                    ? 'welcome_dismissed'
                    : 'help_dismissed_' + this.currentFeatureKey;
                localStorage.setItem(storageKey, this.dontShowAgain ? '1' : '0');
            },

            close() {
                this.open = false;
                document.body.style.overflow = '';
            }
        }));
    }

    if (window.Alpine) {
        registerFeatureModals();
    } else {
        document.addEventListener('alpine:init', registerFeatureModals);
    }
})();
</script>
