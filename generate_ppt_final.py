from pptx import Presentation
from pptx.util import Inches, Pt, Emu
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.util import Inches, Pt

SLIDE_W = Inches(13.33)
SLIDE_H = Inches(7.5)

C_DARK   = RGBColor(10,  14,  26)
C_CYAN   = RGBColor(0,   212, 255)
C_PURPLE = RGBColor(124, 58,  237)
C_WHITE  = RGBColor(255, 255, 255)
C_GRAY   = RGBColor(148, 163, 184)

prs = Presentation()
prs.slide_width  = SLIDE_W
prs.slide_height = SLIDE_H

def blank(prs):
    return prs.slides.add_slide(prs.slide_layouts[6])

def bg(slide, color=None):
    if color is None:
        color = C_DARK
    fill = slide.background.fill
    fill.solid()
    fill.fore_color.rgb = color

def box(slide, text, x, y, w, h,
        size=22, bold=False, color=None, align=PP_ALIGN.LEFT,
        bg_color=None, italic=False):
    if color is None:
        color = C_WHITE
    shape = slide.shapes.add_textbox(x, y, w, h)
    tf = shape.text_frame
    tf.word_wrap = True
    if bg_color:
        shape.fill.solid()
        shape.fill.fore_color.rgb = bg_color
    p = tf.paragraphs[0]
    p.alignment = align
    run = p.add_run()
    run.text = text
    run.font.name   = 'Arial'
    run.font.size   = Pt(size)
    run.font.bold   = bold
    run.font.italic = italic
    run.font.color.rgb = color
    return shape

def divider(slide, y, color=None):
    if color is None:
        color = C_CYAN
    line = slide.shapes.add_shape(1,
        Inches(0.5), y, Inches(12.33), Pt(2))
    line.fill.solid()
    line.fill.fore_color.rgb = color
    line.line.fill.background()

def bullet_block(slide, items, x, y, w, h, size=17, color=None):
    if color is None:
        color = C_WHITE
    shape = slide.shapes.add_textbox(x, y, w, h)
    tf = shape.text_frame
    tf.word_wrap = True
    for i, item in enumerate(items):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        run = p.add_run()
        run.text = item
        run.font.name  = 'Arial'
        run.font.size  = Pt(size)
        run.font.color.rgb = color
        p.space_after  = Pt(8)

# ─────────────────────────────────────────────
# SLIDE 1 – Cover
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "RedSim",
    Inches(0.6), Inches(1.6), Inches(9), Inches(1.5),
    size=60, bold=True, color=C_CYAN)
box(s, "Sistem Penilaian, Penguji & Simulasi Keamanan Berbasis AI",
    Inches(0.6), Inches(3.0), Inches(9), Inches(1.0),
    size=22, color=C_GRAY)
divider(s, Inches(4.2))
box(s, "Mata Kuliah: Keamanan Basis Data\nAkbar Maulana — 6404240019\nPoliteknik Negeri Bengkalis  |  2025/2026",
    Inches(0.6), Inches(4.4), Inches(8), Inches(1.8),
    size=16, color=C_GRAY)

# ─────────────────────────────────────────────
# SLIDE 2 – Arsitektur Sistem
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Arsitektur Sistem RedSim",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.7),
    size=34, bold=True, color=C_CYAN)
divider(s, Inches(1.15))

layers = [
    ("🌐  Infrastruktur & Keamanan Web",
     "Cloudflare Tunnel — menyembunyikan IP asli, enkripsi TLS end-to-end, anti-DDoS"),
    ("🖥️  Backend Framework",
     "Laravel 11 (PHP) — routing, middleware, queue worker, Blade template engine"),
    ("🗄️  Database Relasional",
     "PostgreSQL via Supabase — menyimpan user, histori scan, audit log, konfigurasi AI"),
    ("🧠  AI / LLM Engine",
     "Groq API — model LLaMA 3.3 70B Versatile, respons <20 detik"),
    ("📦  Vector Database (RAG)",
     "Pinecone Serverless — menyimpan embedding dataset keamanan global"),
    ("📲  Notifikasi & Verifikasi",
     "Fonnte API — kirim OTP & notifikasi login via WhatsApp"),
]

y = Inches(1.3)
for title, desc in layers:
    box(s, title, Inches(0.5), y, Inches(12), Inches(0.35), size=16, bold=True, color=C_CYAN)
    box(s, desc,  Inches(0.5), y+Inches(0.32), Inches(12), Inches(0.3), size=14, color=C_GRAY)
    y += Inches(0.75)

# ─────────────────────────────────────────────
# SLIDE 3 – Layanan yang Digunakan
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Layanan & Teknologi yang Digunakan",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.7),
    size=32, bold=True, color=C_CYAN)
divider(s, Inches(1.15))

services = [
    ("Groq API", "LLM inference super cepat (70B model)", "https://groq.com", Inches(0.5)),
    ("Pinecone", "Serverless Vector Database untuk RAG", "https://pinecone.io", Inches(3.4)),
    ("Supabase", "PostgreSQL cloud + autentikasi managed", "https://supabase.com", Inches(6.3)),
    ("Cloudflare", "Tunnel, CDN, DDoS protection, TLS", "https://cloudflare.com", Inches(9.2)),
]

y_card = Inches(1.3)
for name, desc, url, x_pos in services:
    sh = s.shapes.add_shape(1, x_pos, y_card, Inches(2.7), Inches(1.4))
    sh.fill.solid()
    sh.fill.fore_color.rgb = RGBColor(15, 22, 41)
    sh.line.color.rgb = C_CYAN
    box(s, name, x_pos+Inches(0.1), y_card+Inches(0.05), Inches(2.5), Inches(0.4), size=17, bold=True, color=C_CYAN)
    box(s, desc, x_pos+Inches(0.1), y_card+Inches(0.45), Inches(2.5), Inches(0.45), size=12, color=C_WHITE)
    box(s, url,  x_pos+Inches(0.1), y_card+Inches(0.95), Inches(2.5), Inches(0.3), size=10, color=C_GRAY, italic=True)

services2 = [
    ("Fonnte API", "WhatsApp Gateway untuk OTP", "https://fonnte.com", Inches(0.5)),
    ("Laravel 11", "PHP Framework backend utama", "https://laravel.com", Inches(3.4)),
    ("Google OAuth", "Login dengan akun Google (Socialite)", "https://console.cloud.google.com", Inches(6.3)),
    ("reCAPTCHA v3", "Bot protection pada form auth", "https://google.com/recaptcha", Inches(9.2)),
]

y_card2 = Inches(2.9)
for name, desc, url, x_pos in services2:
    sh = s.shapes.add_shape(1, x_pos, y_card2, Inches(2.7), Inches(1.4))
    sh.fill.solid()
    sh.fill.fore_color.rgb = RGBColor(15, 22, 41)
    sh.line.color.rgb = C_PURPLE
    box(s, name, x_pos+Inches(0.1), y_card2+Inches(0.05), Inches(2.5), Inches(0.4), size=17, bold=True, color=C_PURPLE)
    box(s, desc, x_pos+Inches(0.1), y_card2+Inches(0.45), Inches(2.5), Inches(0.45), size=12, color=C_WHITE)
    box(s, url,  x_pos+Inches(0.1), y_card2+Inches(0.95), Inches(2.5), Inches(0.3), size=10, color=C_GRAY, italic=True)

box(s, "Vite + TailwindCSS → Build frontend | Pinecone Text-Embedding-3-Large → embedding dataset keamanan",
    Inches(0.5), Inches(4.5), Inches(12), Inches(0.4), size=13, color=C_GRAY)

# ─────────────────────────────────────────────
# SLIDE 4 – Cara Kerja Sistem (Alur Umum)
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Cara Kerja Sistem RedSim",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.7),
    size=32, bold=True, color=C_CYAN)
divider(s, Inches(1.15))

steps = [
    ("1", "INPUT", "Pengguna mengunggah target (URL / Kode / File ZIP / Log Server) melalui antarmuka web RedSim."),
    ("2", "EKSTRAKSI", "Backend Laravel memproses file: parsing header HTTP, ekstrak isi ZIP, tokenisasi kode sumber, atau parsing entri log."),
    ("3", "RAG RETRIEVAL", "Teks target diubah menjadi vektor embedding oleh Pinecone. Sistem mengambil top-K chunk yang paling relevan dari dataset keamanan."),
    ("4", "AI GENERATION", "Teks target + konteks kerentanan dikirim ke Groq (LLaMA 3.3). AI menghasilkan analisis komprehensif dengan referensi CVE/MITRE."),
    ("5", "OUTPUT & SIMPAN", "Hasil disimpan ke Supabase, ditampilkan sebagai laporan interaktif, dan bisa diekspor ke PDF."),
]

y = Inches(1.35)
for num, label, desc in steps:
    # Circle number
    sh = s.shapes.add_shape(9, Inches(0.5), y, Inches(0.5), Inches(0.5))
    sh.fill.solid()
    sh.fill.fore_color.rgb = C_CYAN
    sh.line.fill.background()
    box(s, num, Inches(0.5), y+Inches(0.05), Inches(0.5), Inches(0.4), size=16, bold=True, color=C_DARK, align=PP_ALIGN.CENTER)
    box(s, label, Inches(1.15), y+Inches(0.02), Inches(2.0), Inches(0.35), size=14, bold=True, color=C_CYAN)
    box(s, desc,  Inches(3.2), y+Inches(0.02), Inches(9.6), Inches(0.45), size=13, color=C_WHITE)
    y += Inches(1.0)

# ─────────────────────────────────────────────
# SLIDE 5 – Alur RAG: Pemindai URL
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Alur RAG — Pemindai URL",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.6),
    size=30, bold=True, color=C_CYAN)
divider(s, Inches(1.0))

box(s, "FASE 1 — PEMINDAIAN AKTIF",
    Inches(0.5), Inches(1.1), Inches(12), Inches(0.4), size=16, bold=True, color=C_PURPLE)
bullet_block(s, [
    "• Validasi kepemilikan domain (unggah file TXT atau meta tag ke server target)",
    "• cURL multi-step: ambil HTTP header, status code, TLS certificate info, redirect chain",
    "• Deteksi teknologi (Server, X-Powered-By, Set-Cookie pattern)",
], Inches(0.5), Inches(1.55), Inches(12), Inches(0.9), size=14)

box(s, "FASE 2 — RETRIEVAL (Pinecone)",
    Inches(0.5), Inches(2.6), Inches(12), Inches(0.4), size=16, bold=True, color=C_PURPLE)
bullet_block(s, [
    "• Hasil pemindaian diubah ke teks terstruktur, lalu di-embed oleh model text-embedding-3-large",
    "• Query ke Pinecone: ambil top-5 chunk paling relevan dari dataset OWASP & CVE",
    "• Filter berdasarkan skor cosine similarity (threshold ≥ 0.75)",
], Inches(0.5), Inches(3.05), Inches(12), Inches(0.9), size=14)

box(s, "FASE 3 — GENERASI (Groq LLaMA 3.3)",
    Inches(0.5), Inches(4.1), Inches(12), Inches(0.4), size=16, bold=True, color=C_PURPLE)
bullet_block(s, [
    "• Prompt: [System Prompt Keamanan] + [Data Scan] + [Konteks RAG dari Pinecone]",
    "• Output: Daftar kerentanan, tingkat risiko (CVSS), rekomendasi mitigasi, referensi CVE-ID",
    "• Hasil disimpan di Supabase dan ditampilkan dengan render Markdown di frontend",
], Inches(0.5), Inches(4.55), Inches(12), Inches(1.0), size=14)

# ─────────────────────────────────────────────
# SLIDE 6 – Alur RAG: Fitur Lainnya
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Alur RAG — Fitur Analisis Lainnya",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.6),
    size=30, bold=True, color=C_CYAN)
divider(s, Inches(1.0))

features = [
    ("📝  Analisis Kode Sumber",
     "Input kode → Deteksi bahasa → Chunk per 500 token → Embed & query Pinecone (CWE/OWASP) → AI analisis per chunk → Gabungkan laporan akhir"),
    ("📦  Pemindai File ZIP",
     "Ekstrak ZIP → Pindai semua ekstensi file → Baca isi file code & config → Chunk & embed → Query kerentanan (misconfig, hardcoded secret) → Laporan per file"),
    ("🗒️  Analisis Log Server",
     "Parse log (Nginx/Apache) baris per baris → Deteksi anomali (IP tunggal banyak request, 4xx spike) → RAG query teknik serangan (DDoS, brute-force) → Laporan ancaman"),
    ("💬  Chat AI (RAG Assistant)",
     "Pertanyaan user → Embed pertanyaan → Cari konteks di Pinecone → Kirim ke LLaMA 3.3 dengan konteks dataset keamanan → Jawaban akurat tanpa halusinasi"),
]

y = Inches(1.1)
for title, flow in features:
    box(s, title, Inches(0.5), y, Inches(12), Inches(0.35), size=16, bold=True, color=C_CYAN)
    box(s, flow,  Inches(0.5), y+Inches(0.33), Inches(12), Inches(0.45), size=13, color=C_GRAY)
    y += Inches(1.05)

# ─────────────────────────────────────────────
# SLIDE 7 – Keamanan Sistem
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Keamanan Sistem RedSim",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.6),
    size=32, bold=True, color=C_CYAN)
divider(s, Inches(1.0))

cols = [
    ("🌐  Infrastruktur", [
        "Cloudflare Tunnel: IP asli server tersembunyi",
        "TLS/HTTPS end-to-end terenkripsi",
        "Anti-DDoS & Rate Limiting Cloudflare",
        "Server lokal (VirtualBox) tidak terekspos publik",
    ]),
    ("🔐  Autentikasi", [
        "Password: Bcrypt hashing (cost factor 12)",
        "reCAPTCHA v3 di semua form login/daftar",
        "OTP WhatsApp 6 digit via Fonnte (expire 5 menit)",
        "Google OAuth 2.0 via Laravel Socialite",
        "Verifikasi manual oleh Admin (RBAC)",
    ]),
    ("🗄️  Database & AI", [
        "Eloquent ORM + PDO Binding (anti SQL Injection)",
        "Row Level Security (RLS) di Supabase",
        "Audit Log setiap aksi penting",
        "Prompt sanitasi (filter Prompt Injection AI)",
        "API key AI tersimpan terenkripsi di DB",
    ]),
]

x_start = Inches(0.4)
for title, items in cols:
    box(s, title, x_start, Inches(1.1), Inches(3.9), Inches(0.4), size=15, bold=True, color=C_PURPLE)
    bullet_block(s, ["• "+i for i in items], x_start, Inches(1.55), Inches(3.9), Inches(3.5), size=13)
    x_start += Inches(4.3)

# ─────────────────────────────────────────────
# SLIDE 8 – Dataset Knowledge Base
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Dataset & Knowledge Base",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.6),
    size=32, bold=True, color=C_CYAN)
divider(s, Inches(1.0))

datasets = [
    ("NVD CVE",
     "National Vulnerability Database — repositori kerentanan perangkat lunak yang diidentifikasi secara global. Setiap entri memiliki skor CVSS, deskripsi teknis, dan referensi patch.",
     "https://nvd.nist.gov/vuln/data-feeds"),
    ("MITRE ATT&CK",
     "Framework global taktik, teknik, dan prosedur (TTP) peretas nyata. Digunakan AI untuk mengenali pola serangan berdasarkan anomali yang ditemukan.",
     "https://attack.mitre.org"),
    ("OWASP Top 10",
     "Standar industri 10 kerentanan web paling kritis (SQLi, XSS, IDOR, dll). Menjadi referensi utama sistem dalam analisis URL dan kode.",
     "https://owasp.org/www-project-top-ten"),
    ("CISA KEV",
     "Known Exploited Vulnerabilities — daftar kerentanan yang dikonfirmasi sedang aktif dieksploitasi di dunia nyata oleh CISA (Cybersecurity Agency AS).",
     "https://www.cisa.gov/known-exploited-vulnerabilities-catalog"),
]

y = Inches(1.1)
for name, desc, url in datasets:
    sh = s.shapes.add_shape(1, Inches(0.5), y, Inches(12.3), Inches(1.2))
    sh.fill.solid()
    sh.fill.fore_color.rgb = RGBColor(15, 22, 41)
    sh.line.color.rgb = C_CYAN
    box(s, name, Inches(0.65), y+Inches(0.05), Inches(2.2), Inches(0.4), size=16, bold=True, color=C_CYAN)
    box(s, desc, Inches(2.9), y+Inches(0.05), Inches(7.5), Inches(0.6), size=13, color=C_WHITE)
    box(s, "🔗 " + url, Inches(2.9), y+Inches(0.7), Inches(7.5), Inches(0.35), size=11, color=C_GRAY, italic=True)
    y += Inches(1.35)

# ─────────────────────────────────────────────
# SLIDE 9 – Keamanan Basis Data (Fokus)
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Keamanan Basis Data (Supabase/PostgreSQL)",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.6),
    size=28, bold=True, color=C_CYAN)
divider(s, Inches(1.0))

bullet_block(s, [
    "Pencegahan SQL Injection:",
    "  → Laravel Eloquent ORM: semua query menggunakan prepared statement & PDO binding otomatis",
    "  → Tidak ada raw query langsung dari input user",
    "",
    "Row Level Security (RLS) di Supabase:",
    "  → Setiap user hanya bisa mengakses baris data miliknya sendiri pada level database",
    "  → Kebijakan RLS diterapkan secara policy di PostgreSQL, bukan hanya di aplikasi",
    "",
    "Audit Log:",
    "  → Setiap aksi sensitif (login, hapus data, ubah konfigurasi AI) dicatat ke tabel audit_logs",
    "  → Menyimpan: user_id, action, description, IP, timestamp",
    "",
    "Enkripsi Data Sensitif:",
    "  → Password di-hash dengan Bcrypt (tidak pernah disimpan plaintext)",
    "  → API Key AI disimpan terenkripsi di tabel ai_configurations",
], Inches(0.5), Inches(1.1), Inches(12.5), Inches(6.0), size=14)

# ─────────────────────────────────────────────
# SLIDE 10 – Fitur Sistem
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Fitur Utama RedSim",
    Inches(0.5), Inches(0.3), Inches(12), Inches(0.6),
    size=32, bold=True, color=C_CYAN)
divider(s, Inches(1.0))

fitur = [
    ("🔍  Pemindai URL", "Verifikasi domain, analisis header HTTP, TLS, teknologi, dan potensi kerentanan web secara otomatis."),
    ("📝  Analisis Kode Sumber", "Upload kode → AI mendeteksi kerentanan (SQLi hardcoded, RCE, XSS, dll) per chunk kode."),
    ("📦  Pemindai File ZIP", "Analisis seluruh isi project/arsip ZIP: misconfiguration, secret key terekspos, dependency berbahaya."),
    ("🗒️  Analisis Log Server", "Upload log Nginx/Apache → deteksi anomali, pola brute-force, port scan, dan serangan DDoS."),
    ("💬  Chat AI (RAG)", "Tanya jawab keamanan yang didukung knowledge base global (bukan hallucination)."),
    ("🎓  Hub Edukasi", "Modul pembelajaran keamanan siber + soal CTF interaktif dengan leaderboard."),
]

x, y = Inches(0.5), Inches(1.1)
for i, (title, desc) in enumerate(fitur):
    sh = s.shapes.add_shape(1, x, y, Inches(6.0), Inches(1.1))
    sh.fill.solid()
    sh.fill.fore_color.rgb = RGBColor(15, 22, 41)
    sh.line.color.rgb = C_CYAN if i % 2 == 0 else C_PURPLE
    box(s, title, x+Inches(0.1), y+Inches(0.05), Inches(5.8), Inches(0.35), size=15, bold=True,
        color=C_CYAN if i % 2 == 0 else C_PURPLE)
    box(s, desc, x+Inches(0.1), y+Inches(0.42), Inches(5.8), Inches(0.55), size=12, color=C_WHITE)
    if x == Inches(0.5):
        x = Inches(6.8)
    else:
        x = Inches(0.5)
        y += Inches(1.3)

# ─────────────────────────────────────────────
# SLIDE 11 – Penutup
# ─────────────────────────────────────────────
s = blank(prs)
bg(s)
box(s, "Terima Kasih",
    Inches(1), Inches(2.0), Inches(11), Inches(1.5),
    size=60, bold=True, color=C_CYAN, align=PP_ALIGN.CENTER)
divider(s, Inches(3.8))
box(s, "Sesi Tanya Jawab",
    Inches(1), Inches(4.0), Inches(11), Inches(0.7),
    size=24, color=C_GRAY, align=PP_ALIGN.CENTER)
box(s, "RedSim — Platform Keamanan Siber Berbasis AI  |  Akbar Maulana  |  6404240019",
    Inches(1), Inches(5.0), Inches(11), Inches(0.5),
    size=14, color=C_GRAY, align=PP_ALIGN.CENTER)

out = '/home/abarox/Desktop/projectAI/Presentasi_RedSim_FINAL.pptx'
prs.save(out)
print(f"Selesai! File disimpan: {out}")
