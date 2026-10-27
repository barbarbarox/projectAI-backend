{{--
    Komponen: Feature Help Modal (Popup Bantuan Fitur)
    Penggunaan:
        <x-feature-help :feature="'pemindai-url'" />
    
    Atau inline tanpa komponen:
        <button x-data @click="$dispatch('open-help', { feature: 'pemindai-url' })"> ? </button>
--}}

{{-- Global Help Modal — letakkan sekali di layouts/app.blade.php --}}
<div x-data="featureHelp()"
     x-show="open"
     x-cloak
     @open-help.window="openModal($event.detail.feature)"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-[999] flex items-center justify-center p-4"
     style="display:none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="close()"></div>

    {{-- Panel --}}
    <div class="relative z-10 w-full max-w-lg bg-[#0d1117] border border-slate-700/60 rounded-2xl shadow-2xl shadow-black/60 overflow-hidden"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-800">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                 :style="'background-color:' + (current?.color || '#1e293b') + '20'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     :style="'color:' + (current?.color || '#94a3b8')">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-sm font-bold text-white" x-text="current?.title || 'Bantuan Fitur'"></h2>
                <p class="text-[10px] text-slate-500" x-text="current?.subtitle || ''"></p>
            </div>
            <button @click="close()"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-5 py-4 max-h-[65vh] overflow-y-auto space-y-4 text-sm">

            {{-- Deskripsi --}}
            <div x-show="current?.description">
                <p class="text-slate-300 text-xs leading-relaxed" x-text="current?.description"></p>
            </div>

            {{-- Cara Menggunakan --}}
            <div x-show="current?.steps?.length">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Cara Menggunakan
                </h3>
                <div class="space-y-2">
                    <template x-for="(step, i) in current?.steps" :key="i">
                        <div class="flex gap-3 p-2.5 rounded-lg bg-slate-800/40">
                            <div class="w-5 h-5 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-300 shrink-0 mt-0.5"
                                 x-text="i + 1"></div>
                            <p class="text-xs text-slate-300 leading-relaxed" x-text="step"></p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Kepentingan / Manfaat --}}
            <div x-show="current?.benefits?.length">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Kepentingan Fitur
                </h3>
                <div class="space-y-1.5">
                    <template x-for="(b, i) in current?.benefits" :key="i">
                        <div class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-slate-300" x-text="b"></p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Tips Tambahan --}}
            <div x-show="current?.tips">
                <div class="p-3 rounded-lg bg-blue-500/8 border border-blue-500/20">
                    <div class="flex items-start gap-2">
                        <svg class="w-3.5 h-3.5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-blue-300 leading-relaxed" x-text="current?.tips"></p>
                    </div>
                </div>
            </div>

            {{-- Link Eksternal --}}
            <div x-show="current?.links?.length">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Referensi Terkait
                </h3>
                <div class="flex flex-wrap gap-2">
                    <template x-for="link in current?.links" :key="link.url">
                        <a :href="link.url" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 hover:text-white transition-colors border border-slate-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span x-text="link.label"></span>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-slate-800 flex justify-end">
            <button @click="close()"
                    class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 hover:text-white transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('featureHelp', () => ({
        open: false,
        current: null,

        // Database semua fitur
        features: {
            'pemindai-url': {
                title: 'Pemindai URL',
                subtitle: 'Analisis keamanan website secara mendalam',
                color: '#ef4444',
                description: 'Fitur ini memindai sebuah URL untuk mendeteksi potensi ancaman keamanan seperti malware, phishing, konfigurasi SSL yang buruk, dan header keamanan yang hilang.',
                steps: [
                    'Masukkan URL target yang ingin dianalisis (format lengkap, contoh: https://contoh.com).',
                    'Pilih mode pemindaian: "Scan Biasa" untuk cek cepat, atau "Scan Intens" untuk analisis mendalam (butuh verifikasi domain).',
                    'Atur model AI yang ingin digunakan di bagian Opsi AI jika tersedia.',
                    'Klik tombol "Mulai Pemindaian" dan tunggu hasilnya.',
                ],
                benefits: [
                    'Mendeteksi website phishing atau berbahaya sebelum dikunjungi oleh pengguna.',
                    'Memeriksa konfigurasi keamanan SSL/TLS yang salah atau kedaluwarsa.',
                    'Mengidentifikasi Security Headers yang hilang (CSP, HSTS, X-Frame-Options).',
                    'Analisis reputasi domain melalui VirusTotal dan URLScan.io.',
                ],
                tips: 'Gunakan Scan Intens untuk laporan keamanan yang lebih komprehensif, namun pastikan Anda memiliki hak akses terhadap domain yang dipindai.',
                links: [
                    { label: 'VirusTotal', url: 'https://www.virustotal.com' },
                    { label: 'URLScan.io', url: 'https://urlscan.io' },
                    { label: 'Security Headers', url: 'https://securityheaders.com' },
                ]
            },
            'analisis-log': {
                title: 'Analisis Log',
                subtitle: 'Deteksi anomali dan serangan dari file log',
                color: '#22d3ee',
                description: 'Fitur ini menganalisis file log server (Apache, Nginx, Laravel, dll.) menggunakan AI untuk menemukan pola serangan, akses mencurigakan, dan anomali keamanan.',
                steps: [
                    'Pilih tab "Upload File Log" untuk mengunggah file, atau "Paste Teks Log" untuk menempel isi log langsung.',
                    'Pilih tipe log (Apache, Nginx, Laravel, dll.) atau biarkan "Deteksi Otomatis".',
                    'Klik tombol "Mulai Analisis Log".',
                    'Sistem AI akan mengidentifikasi pola serangan, IP mencurigakan, dan memberikan rekomendasi.',
                ],
                benefits: [
                    'Mendeteksi percobaan brute force, SQL injection, dan XSS dari log secara otomatis.',
                    'Mengidentifikasi IP berbahaya dan pola akses yang tidak wajar.',
                    'Menghemat waktu analisis log secara manual yang memakan waktu berjam-jam.',
                    'Mendukung berbagai format log populer (Apache, Nginx, Laravel, auth.log).',
                ],
                tips: 'Untuk privasi, pastikan log yang diunggah sudah di-anonimkan atau tidak mengandung data sensitif pengguna.',
                links: []
            },
            'analisis-kode': {
                title: 'Analisis Kode',
                subtitle: 'Review keamanan kode sumber secara otomatis',
                color: '#a78bfa',
                description: 'Fitur ini melakukan review keamanan statis (SAST) pada kode sumber program untuk menemukan kerentanan seperti SQL Injection, XSS, command injection, dan lainnya.',
                steps: [
                    'Tempelkan cuplikan kode yang ingin dianalisis ke dalam kotak kode.',
                    'Pilih bahasa pemrograman yang sesuai.',
                    'Klik "Analisis Kode Sekarang".',
                    'Tinjau temuan kerentanan dan rekomendasi perbaikannya.',
                ],
                benefits: [
                    'Menemukan celah keamanan di kode sebelum masuk ke lingkungan produksi.',
                    'Mendukung berbagai bahasa: PHP, Python, JavaScript, SQL, dan lainnya.',
                    'Memberikan saran perbaikan kode yang konkret dan aman.',
                ],
                tips: 'Analisis kode paling efektif dilakukan sebelum melakukan deployment atau code review.',
                links: [
                    { label: 'OWASP Top 10', url: 'https://owasp.org/www-project-top-ten/' },
                ]
            },
            'cve-intel': {
                title: 'CVE Intelligence Dashboard',
                subtitle: 'Analisis ancaman dengan Ensemble AI (RF + XGBoost)',
                color: '#ef4444',
                description: 'Fitur unggulan RedSim yang menggunakan dua model Machine Learning (Random Forest & XGBoost) secara bersamaan untuk memprediksi tingkat keparahan kerentanan secara instan — bahkan sebelum skor NIST resmi tersedia.',
                steps: [
                    'Mode "CVE ID": Masukkan ID kerentanan dari NVD (contoh: CVE-2024-12345). Sistem akan otomatis mengambil data dari database NIST NVD.',
                    'Mode "Manual": Isi jenis kerentanan, tingkat keparahan, dan CWE ID secara manual untuk analisis cepat.',
                    'Klik "Analisis dengan AI Ensemble" dan tunggu prediksi dari dua model ML.',
                    'Tinjau hasilnya: Tingkat bahaya, suara per model, distribusi probabilitas, dan rekomendasi tindakan.',
                ],
                benefits: [
                    'Prediksi severity kerentanan secara instan tanpa menunggu penilaian NIST yang bisa berhari-hari.',
                    'Ensemble dua model ML (RF + XGBoost) memberikan akurasi lebih tinggi (Macro F1 > 0.95).',
                    'XGBoost dengan bobot 60% sangat tajam mendeteksi kerentanan KRITIS (recall 92.57%).',
                    'Distribusi probabilitas menunjukkan kepercayaan diri AI untuk setiap kelas severity.',
                    'Batas waktu mitigasi otomatis membantu tim keamanan memprioritaskan respons.',
                ],
                tips: 'Jika kedua model berbeda pendapat (tidak sepakat), sistem akan menggunakan bobot Soft Voting untuk mengambil keputusan akhir yang paling optimal.',
                links: [
                    { label: 'NVD Database', url: 'https://nvd.nist.gov/' },
                    { label: 'CVSSv3.1 Calculator', url: 'https://www.first.org/cvss/calculator/3.1' },
                    { label: 'MITRE ATT&CK', url: 'https://attack.mitre.org' },
                ]
            },
            'cvss-calculator': {
                title: 'CVSSv3.1 Calculator',
                subtitle: 'Penghitung skor kerentanan standar industri',
                color: '#f59e0b',
                description: 'CVSS (Common Vulnerability Scoring System) versi 3.1 adalah standar industri untuk menilai keparahan sebuah kerentanan keamanan secara numerik dari 0.0 hingga 10.0.',
                steps: [
                    'Akses kalkulator di FIRST.org melalui link "CVSSv3.1 Calculator".',
                    'Isi parameter vektor serangan: attack vector, complexity, privileges required, dan user interaction.',
                    'Isi parameter dampak: confidentiality, integrity, dan availability impact.',
                    'Skor akan dihitung otomatis beserta vektor string (contoh: CVSS:3.1/AV:N/AC:L/...).',
                ],
                benefits: [
                    'Standar global yang digunakan oleh NVD, vendor keamanan, dan tim SOC seluruh dunia.',
                    'Membantu membandingkan keparahan antar kerentanan secara objektif.',
                    'Diperlukan untuk membuat laporan keamanan formal yang terstandar.',
                ],
                tips: 'Skor CVSS saja tidak cukup — selalu pertimbangkan konteks lingkungan (Environmental Score) dan status exploit yang tersedia.',
                links: [
                    { label: 'CVSS v3.1 Calculator', url: 'https://www.first.org/cvss/calculator/3.1' },
                    { label: 'Panduan CVSS', url: 'https://www.first.org/cvss/v3.1/specification-document' },
                ]
            },
            'mitre-attack': {
                title: 'MITRE ATT&CK Matrix',
                subtitle: 'Kerangka taktik dan teknik serangan siber',
                color: '#f87171',
                description: 'MITRE ATT&CK adalah basis pengetahuan global berisi taktik, teknik, dan prosedur (TTP) yang digunakan oleh aktor ancaman nyata di seluruh dunia, diorganisir dalam bentuk matriks yang mudah dipahami.',
                steps: [
                    'Buka MITRE ATT&CK Matrix melalui link di bawah.',
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
                tips: 'Gunakan ATT&CK Navigator (tools.attack.mitre.org) untuk memetakan coverage deteksi tim keamanan Anda secara visual.',
                links: [
                    { label: 'MITRE ATT&CK', url: 'https://attack.mitre.org' },
                    { label: 'ATT&CK Navigator', url: 'https://mitre-attack.github.io/attack-navigator/' },
                ]
            },
        },

        openModal(featureKey) {
            this.current = this.features[featureKey] || {
                title: 'Bantuan Fitur',
                subtitle: '',
                color: '#94a3b8',
                description: 'Informasi bantuan untuk fitur ini belum tersedia.',
                steps: [],
                benefits: [],
                tips: null,
                links: []
            };
            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.open = false;
            document.body.style.overflow = '';
        }
    }));
});
</script>
