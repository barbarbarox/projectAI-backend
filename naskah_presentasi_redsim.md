# Naskah Presentasi — RedSim

### Integrasi Keamanan AI dan Basis Data

**Akbar Maulana | 6404240019 | Keamanan Basis Data**

---

## SLIDE 1 — Cover

> *(Berdiri, senyum, tatap audiens)*

"Assalamu'alaikum warahmatullahi wabarakatuh. Selamat pagi / siang Bapak/Ibu dosen dan rekan-rekan sekalian.

Perkenalkan, saya **Akbar Maulana**, dengan NIM **6404240019**. Pada kesempatan hari ini, saya akan mempresentasikan proyek tugas akhir saya yang berjudul:

**RedSim — Sistem Penilaian, Penguji, dan Simulasi Keamanan Berbasis Kecerdasan Buatan.**

Proyek ini saya kembangkan sebagai implementasi nyata dari mata kuliah Keamanan Basis Data, di mana saya menerapkan berbagai konsep keamanan — mulai dari level infrastruktur, layer aplikasi, hingga level database itu sendiri.

Baik, mari kita mulai."

---

## SLIDE 2 — Arsitektur Sistem

> *(Tunjuk layer-layer pada slide)*

"Sebelum membahas fitur dan cara kerja, saya ingin menjelaskan terlebih dahulu **arsitektur sistem RedSim** secara keseluruhan, karena arsitektur ini menjadi fondasi dari seluruh keamanan yang ada.

RedSim dibangun menggunakan **6 lapisan utama:**

Pertama, di lapisan paling atas — **Cloudflare Tunnel**. Ini adalah gerbang utama yang melindungi server fisik saya. IP asli server disembunyikan sepenuhnya, dan seluruh komunikasi dienkripsi dengan TLS.

Kedua, **Backend Laravel 11** — framework PHP yang menangani seluruh logika bisnis, routing, autentikasi, dan queue worker untuk proses AI yang berjalan di latar belakang.

Ketiga, **Supabase PostgreSQL** — database relasional tempat menyimpan seluruh data pengguna, histori scan, konfigurasi AI, dan audit log.

Keempat, **Groq API dengan model LLaMA 3.3 70B** — mesin AI utama yang menghasilkan analisis keamanan. Model ini dipilih karena kecepatan responsnya yang luar biasa, rata-rata di bawah 20 detik.

Kelima, **Pinecone Serverless** — ini adalah Vector Database yang menyimpan seluruh pengetahuan keamanan dalam bentuk embedding. Komponen inilah yang membuat AI RedSim 'cerdas' dan tidak berhalusinasi.

Dan keenam, **Fonnte API** — gateway WhatsApp untuk mengirim OTP dan notifikasi keamanan ke pengguna secara real-time."

---

## SLIDE 3 — Layanan yang Digunakan

> *(Tunjuk card masing-masing layanan)*

"Berikut adalah rincian layanan eksternal yang saya integrasikan ke dalam RedSim.

**Groq API** digunakan sebagai mesin inferensi LLM. Saya memilih Groq karena layanan ini menawarkan kecepatan komputasi yang sangat tinggi dibanding provider AI lainnya.

**Pinecone** bertindak sebagai otak memori AI — menyimpan ribuan chunk pengetahuan keamanan dalam bentuk vektor, sehingga AI bisa menemukan informasi yang relevan dalam hitungan milidetik.

**Supabase** menyediakan PostgreSQL dengan fitur autentikasi managed dan Row Level Security yang sangat memudahkan penerapan keamanan di level database.

**Cloudflare** memberikan perlindungan di lapisan jaringan — tunneling, CDN, anti-DDoS, dan enkripsi TLS — semuanya tanpa biaya tambahan untuk skala proyek ini.

Di lapisan autentikasi, saya menggunakan **Fonnte** untuk WhatsApp OTP, **Google OAuth** untuk kemudahan login, **reCAPTCHA v3** untuk memblokir bot, dan tentunya **Laravel** sebagai kerangka kerja utama yang menyatukan semuanya."

---

## SLIDE 4 — Cara Kerja Sistem (Alur Umum)

> *(Jelaskan alur 5 langkah secara berurutan)*

"Sekarang, bagaimana RedSim sebenarnya bekerja? Saya akan jelaskan alurnya dalam **5 tahapan.**

**Tahap 1 — Input.** Pengguna mengakses antarmuka web RedSim, kemudian memasukkan target analisis. Target ini bisa berupa URL sebuah website, file kode sumber, arsip ZIP sebuah proyek, atau file log server.

**Tahap 2 — Ekstraksi.** Backend Laravel memproses input tersebut sesuai jenisnya. Untuk URL, sistem melakukan pemindaian header HTTP dan sertifikat TLS. Untuk kode, sistem melakukan parsing dan tokenisasi. Untuk ZIP, sistem membongkar isi arsip file per file.

**Tahap 3 — RAG Retrieval.** Ini adalah inti dari sistem. Teks hasil ekstraksi diubah menjadi vektor embedding. Sistem kemudian melakukan pencarian di Pinecone untuk menemukan konteks kerentanan yang paling relevan dari dataset keamanan global.

**Tahap 4 — AI Generation.** Data hasil scan digabungkan dengan konteks yang ditemukan dari Pinecone, lalu dikirim ke model LLaMA 3.3 melalui Groq API. AI kemudian menghasilkan analisis komprehensif — lengkap dengan tingkat risiko, referensi CVE, dan rekomendasi mitigasi.

**Tahap 5 — Output.** Hasil analisis disimpan permanen ke database Supabase, ditampilkan sebagai laporan interaktif di halaman web, dan tersedia untuk diekspor ke PDF."

---

## SLIDE 5 — Alur RAG: Pemindai URL

> *(Ini slide teknis, jelaskan dengan percaya diri)*

"Saya akan zoom-in ke salah satu fitur paling kompleks: **Pemindai URL**. Fitur ini menggunakan pendekatan tiga fase.

**Fase Pertama — Pemindaian Aktif.** Sebelum menganalisis sebuah URL, sistem mengharuskan pengguna membuktikan kepemilikan domain terlebih dahulu — dengan mengunggah file TXT atau menambahkan meta tag di situsnya. Ini mencegah RedSim disalahgunakan untuk memindai website milik orang lain. Setelah terverifikasi, sistem melakukan pemindaian cURL multi-step: memeriksa header HTTP, kode status, rantai redirect, dan informasi sertifikat TLS.

**Fase Kedua — Retrieval dari Pinecone.** Hasil pemindaian diubah menjadi format teks terstruktur, lalu dikonversi menjadi vektor embedding. Sistem kemudian melakukan query ke Pinecone untuk mengambil **top-5 chunk** yang paling relevan dari dataset OWASP dan CVE. Hanya chunk dengan skor kemiripan di atas 0.75 yang digunakan.

**Fase Ketiga — Generasi AI.** Prompt akhir yang dikirim ke LLaMA 3.3 terdiri dari tiga bagian: system prompt yang mendefinisikan peran AI sebagai analis keamanan, data hasil scan, dan konteks kerentanan dari Pinecone. Output-nya adalah laporan yang terstruktur, berisi daftar kerentanan, skor risiko CVSS, dan langkah mitigasi konkret."

---

## SLIDE 6 — Alur RAG: Fitur Lainnya

> *(Jelaskan keempat fitur dengan singkat dan jelas)*

"RedSim memiliki empat fitur analisis lainnya yang menggunakan pendekatan RAG yang sama namun disesuaikan konteksnya masing-masing.

**Analisis Kode Sumber** — Kode di-chunk per 500 token, masing-masing chunk dianalisis secara independen, lalu laporan digabungkan. Ini memastikan bahkan untuk proyek dengan ribuan baris kode, tidak ada bagian yang terlewat.

**Pemindai File ZIP** — Setelah arsip dibongkar, sistem membaca setiap file kode dan konfigurasi. AI secara khusus mencari pola berbahaya seperti API key yang di-hardcode, konfigurasi server yang salah, atau dependency dengan kerentanan yang diketahui.

**Analisis Log Server** — Log Nginx atau Apache diparsing baris demi baris. Sistem mendeteksi anomali statistik seperti satu IP yang mengirim ratusan request dalam satu menit, lonjakan error 4xx, atau pola yang mengindikasikan port scanning.

**Chat AI** — Ini adalah fitur paling interaktif. Pengguna bisa bertanya bebas tentang konsep keamanan. Pertanyaan diubah menjadi vektor, kemudian dicari jawabannya di Pinecone terlebih dahulu sebelum dikirim ke AI. Hasilnya jauh lebih akurat dibanding chatbot biasa karena didukung data faktual."

---

## SLIDE 7 — Keamanan Sistem

> *(Ini slide favorit — jelaskan dengan antusias)*

"Inilah inti dari topik kita hari ini: **bagaimana RedSim dirancang agar aman** dari berbagai sudut pandang.

Dari sisi **infrastruktur**, server fisik RedSim berjalan di VirtualBox lokal dan sama sekali tidak memiliki IP publik yang terekspos. Semua lalu lintas masuk harus melewati **Cloudflare Tunnel** — yang menyediakan enkripsi TLS end-to-end dan secara otomatis memblokir traffic yang mencurigakan sebelum mencapai server.

Dari sisi **autentikasi**, saya menerapkan sistem berlapis. Password di-hash menggunakan Bcrypt dengan cost factor tinggi. Setiap form login dilindungi reCAPTCHA v3 untuk memblokir bot. Pengguna bisa login menggunakan OTP WhatsApp atau Google OAuth. Dan yang terpenting, setiap akun baru harus mendapatkan persetujuan manual dari Admin sebelum bisa menggunakan sistem — ini adalah implementasi nyata dari Role-Based Access Control.

Dari sisi **database dan AI**, seluruh query database menggunakan Eloquent ORM dengan PDO binding — tidak ada raw query yang bisa dieksploitasi untuk SQL Injection. Supabase menerapkan Row Level Security di level PostgreSQL, memastikan seorang pengguna tidak bisa mengakses data milik pengguna lain meskipun mereka berhasil bypass layer aplikasi. Dan untuk keamanan AI, setiap input pengguna disanitasi untuk mencegah serangan Prompt Injection."

---

## SLIDE 8 — Dataset Knowledge Base

> *(Tunjuk tiap dataset dan linknya)*

"Keandalan RedSim sangat bergantung pada kualitas dataset yang digunakan. Saya mengintegrasikan **empat sumber dataset resmi dan terpercaya.**

**NVD CVE dari NIST** — ini adalah repositori paling komprehensif untuk kerentanan perangkat lunak yang ada secara global. Setiap entri CVE memiliki skor CVSS, deskripsi teknis detail, dan referensi patch resmi. Data ini menjadi landasan saat AI mengidentifikasi kerentanan spesifik.

**MITRE ATT&CK** — framework ini mendokumentasikan taktik dan teknik yang digunakan oleh peretas nyata dalam serangan siber. AI menggunakannya untuk mengenali pola serangan berdasarkan anomali yang ditemukan dalam log atau konfigurasi.

**OWASP Top 10** — ini adalah standar industri yang mendefinisikan 10 kerentanan web paling kritis. Setiap developer web wajib mengetahui ini. Saya menggunakannya sebagai referensi utama untuk pemindaian URL dan analisis kode.

**CISA KEV** — Known Exploited Vulnerabilities dari Cybersecurity Agency pemerintah Amerika Serikat. Daftar ini berisi kerentanan yang **sudah terbukti sedang dieksploitasi** di dunia nyata — bukan sekadar teoritis. Ini memberikan prioritas yang tepat saat sistem memberikan peringatan risiko."

---

## SLIDE 9 — Keamanan Basis Data (Fokus)

> *(Ini sangat relevan dengan mata kuliah, jelaskan dengan detail)*

"Karena mata kuliah kita adalah **Keamanan Basis Data**, saya ingin secara khusus membahas bagaimana RedSim melindungi lapisan datanya.

**Pertama, pencegahan SQL Injection.** Saya menggunakan Laravel Eloquent ORM yang secara internal selalu menggunakan PDO prepared statement. Artinya, setiap input dari pengguna diperlakukan sebagai data mentah — bukan sebagai bagian dari query SQL. Tidak ada satu pun raw query yang ditulis langsung menggunakan string concatenation dari input user.

**Kedua, Row Level Security di Supabase.** Ini adalah fitur PostgreSQL yang saya aktifkan. RLS memastikan bahwa meskipun seseorang berhasil mendapatkan akses ke database secara langsung, mereka tetap tidak bisa membaca data milik pengguna lain. Kebijakan ini diterapkan di tingkat database engine, bukan di level aplikasi — sehingga lebih sulit untuk di-bypass.

**Ketiga, Audit Log.** Setiap tindakan sensitif dicatat: siapa yang login, kapan, dari mana, apa yang diubah. Log ini tidak bisa dihapus oleh pengguna biasa, dan berfungsi sebagai forensik digital jika terjadi insiden keamanan.

**Keempat, enkripsi data sensitif.** Password tidak pernah disimpan dalam bentuk asli — selalu di-hash dengan Bcrypt. API Key AI yang tersimpan di database juga dienkripsi, sehingga bahkan jika database berhasil diakses, kunci-kunci tersebut tidak bisa langsung digunakan."

---

## SLIDE 10 — Fitur Utama

> *(Ringkas, tidak perlu detail karena sudah dijelaskan sebelumnya)*

"Untuk merangkum, RedSim memiliki **enam fitur utama** yang bisa digunakan oleh pengguna:

Satu, **Pemindai URL** — analisis keamanan website secara otomatis dengan verifikasi kepemilikan domain.

Dua, **Analisis Kode Sumber** — deteksi kerentanan langsung dari baris kode yang diunggah.

Tiga, **Pemindai File ZIP** — analisis menyeluruh terhadap seluruh isi proyek dalam satu arsip.

Empat, **Analisis Log Server** — deteksi pola serangan dari log Nginx atau Apache.

Lima, **Chat AI berbasis RAG** — asisten keamanan yang cerdas dan faktual, bukan sekadar chatbot biasa.

Dan keenam, **Hub Edukasi** — modul pembelajaran keamanan siber interaktif dengan soal CTF dan leaderboard, yang memotivasi pengguna untuk terus belajar."

---

## SLIDE 11 — Penutup

> *(Tarik napas, kontak mata dengan audiens)*

"Demikianlah presentasi saya mengenai **RedSim — Sistem Penilaian, Penguji, dan Simulasi Keamanan Berbasis AI.**

Melalui proyek ini, saya telah mengimplementasikan konsep keamanan secara menyeluruh — mulai dari perlindungan infrastruktur menggunakan Cloudflare, keamanan autentikasi berlapis, keamanan basis data melalui ORM dan Row Level Security, hingga penggunaan AI yang bertanggung jawab melalui teknik RAG dengan dataset standar industri global.

Saya berharap RedSim dapat menjadi kontribusi nyata dalam membantu para developer dan administrator sistem untuk menemukan dan memperbaiki kerentanan sebelum dieksploitasi.

Terima kasih telah menyimak presentasi saya. Saya membuka sesi tanya jawab.

Wassalamu'alaikum warahmatullahi wabarakatuh."

---

> **Tips Presentasi:**
>
> - Alokasi waktu per slide: ~2-3 menit
> - Total estimasi waktu: 22–33 menit
> - Bawa catatan kecil hanya berisi kata kunci tiap slide
> - Saat menjelaskan alur RAG (Slide 5-6), gambar diagram di papan tulis jika perlu
