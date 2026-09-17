from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN

def add_slide(prs, title, content_list):
    slide_layout = prs.slide_layouts[1] # Title and Content
    slide = prs.slides.add_slide(slide_layout)
    
    title_shape = slide.shapes.title
    title_shape.text = title
    
    # Styling title
    title_shape.text_frame.paragraphs[0].font.name = 'Arial'
    title_shape.text_frame.paragraphs[0].font.size = Pt(36)
    title_shape.text_frame.paragraphs[0].font.bold = True
    title_shape.text_frame.paragraphs[0].font.color.rgb = RGBColor(14, 165, 233) # Cyan-blue theme
    
    body_shape = slide.shapes.placeholders[1]
    tf = body_shape.text_frame
    tf.clear()
    
    for i, content in enumerate(content_list):
        p = tf.add_paragraph()
        p.text = content
        p.font.name = 'Arial'
        p.font.size = Pt(20)
        p.space_after = Pt(14)
        p.level = 0

prs = Presentation()

# Slide 1: Title
slide_layout = prs.slide_layouts[0]
slide = prs.slides.add_slide(slide_layout)
title = slide.shapes.title
subtitle = slide.placeholders[1]

title.text = "RedSim\nIntegrasi Keamanan AI dan Basis Data"
title.text_frame.paragraphs[0].font.name = 'Arial'
title.text_frame.paragraphs[0].font.size = Pt(48)
title.text_frame.paragraphs[0].font.bold = True
title.text_frame.paragraphs[0].font.color.rgb = RGBColor(124, 58, 237) # Purple theme

subtitle.text = "Mata Kuliah: Keamanan Basis Data\n\nOleh: Akbar Maulana (6404240019)\nPoliteknik Negeri Bengkalis"

# Slide 2: Latar Belakang Masalah
add_slide(prs, "Latar Belakang Masalah", [
    "• Ancaman keamanan siber dan kebocoran data terus meningkat.",
    "• Keterbatasan SDM yang mampu melakukan analisis kerentanan manual.",
    "• Kebutuhan akan sistem cerdas yang mampu mengotomatisasi deteksi kerentanan kode, URL, log, dan infrastruktur.",
    "• Tantangan keamanan pada penggunaan AI itu sendiri (Prompt Injection, Data Privacy)."
])

# Slide 3: Tujuan & Manfaat
add_slide(prs, "Tujuan & Manfaat", [
    "• Mengembangkan platform otomatisasi analisis keamanan menggunakan AI.",
    "• Meminimalisir waktu identifikasi kerentanan menggunakan teknologi RAG.",
    "• Melatih pengguna terkait keamanan siber melalui Hub Edukasi interaktif (CTF).",
    "• Menyediakan sistem yang aman sejak dari level infrastruktur (Cloudflare) hingga level Basis Data."
])

# Slide 4: Arsitektur Sistem
add_slide(prs, "Arsitektur & Teknologi Sistem", [
    "• Frontend & Backend: Framework Laravel dengan desain Glassmorphism.",
    "• Database Utama: PostgreSQL (dihosting via Supabase) untuk relasi data (ACID).",
    "• Vector Database: Pinecone untuk menyimpan embedding dataset keamanan.",
    "• Mesin AI (LLM): LLaMA 3.3 (via Groq API) untuk respons analisis super cepat.",
    "• Notifikasi & Verifikasi: API Fonnte (WhatsApp Gateway)."
])

# Slide 5: Alur Kerja (Bagaimana Sistem Bekerja?)
add_slide(prs, "Bagaimana RedSim Bekerja?", [
    "1. Input: Pengguna memasukkan target (URL / File ZIP / Kode / Log Server).",
    "2. Ekstraksi: Backend membongkar data (parsing kode, memindai header/port URL).",
    "3. Retrieval (RAG): Backend mencari konteks kerentanan yang relevan di Pinecone DB.",
    "4. Generation: Data + Konteks RAG dikirim ke AI LLaMA 3.3.",
    "5. Output: Laporan akhir dihasilkan dengan referensi CVE/MITRE dan disimpan ke Supabase."
])

# Slide 6: Dataset RAG yang Digunakan
add_slide(prs, "Dataset Keamanan AI (Knowledge Base)", [
    "RedSim menggunakan metode RAG (Retrieval-Augmented Generation) yang didukung dataset global resmi, sehingga AI tidak 'berhalusinasi':",
    "• NVD CVE: Kerentanan publik (Common Vulnerabilities and Exposures).",
    "• MITRE ATT&CK: Taktik, teknik, dan prosedur peretas (TTPs).",
    "• OWASP Top 10: Standar keamanan web (SQLi, XSS, Broken Access Control).",
    "• CISA KEV: Daftar eksploitasi kerentanan yang sedang aktif."
])

# Slide 7: Keamanan Sistem, Web & Database
add_slide(prs, "Keamanan Sistem & Basis Data", [
    "Sistem ini dirancang dengan standar keamanan tinggi:",
    "• Proteksi Infrastruktur: Dihosting menggunakan Cloudflare Tunnel (Menyembunyikan IP server asli, enkripsi HTTPS End-to-End, dan Anti-DDoS).",
    "• Proteksi Database: Menggunakan Eloquent ORM & PDO Binding (Anti SQL Injection).",
    "• Autentikasi Kuat: Password hashing Bcrypt, reCAPTCHA v3 (Anti-Bot).",
    "• Keamanan AI: Filter input untuk mencegah Prompt Injection.",
    "• Otorisasi: Role-Based Access Control (RBAC) dengan fitur persetujuan Admin."
])

# Slide 8: Skema Database (ERD)
add_slide(prs, "Skema Database (ERD)", [
    "Menerapkan relasi basis data yang terstruktur dan aman:",
    "• Tabel Users: Menyimpan data pengguna (Password ter-hash, Status Verifikasi, Role).",
    "• Tabel Scans: Mencatat histori pemindaian (ID berelasi dengan User).",
    "• Tabel Audit Logs: Mencatat seluruh aktivitas sistem (Login, Modifikasi Data).",
    "• Tabel AI Configs: Menyimpan kredensial AI secara tersentralisasi."
])

# Slide 9: Hasil Pengujian
add_slide(prs, "Hasil Pengujian Sistem", [
    "• Fungsionalitas AI: Sistem berhasil menganalisis kerentanan secara dinamis tanpa jawaban halusinasi berkat dukungan Vector DB Pinecone.",
    "• Ketahanan Web: Cloudflare Tunnel sukses memblokir akses direct-IP dan mencegah serangan brute-force konvensional.",
    "• Performa: Penggunaan model Groq LLaMA 3.3 memangkas waktu respons dari ~120 detik (NVIDIA) menjadi rata-rata ~15-20 detik."
])

# Slide 10: Penutup
slide_layout = prs.slide_layouts[0]
slide = prs.slides.add_slide(slide_layout)
title = slide.shapes.title
title.text = "TERIMA KASIH"
title.text_frame.paragraphs[0].font.name = 'Arial'
title.text_frame.paragraphs[0].font.size = Pt(54)
title.text_frame.paragraphs[0].font.bold = True
title.text_frame.paragraphs[0].font.color.rgb = RGBColor(124, 58, 237)
subtitle = slide.placeholders[1]
subtitle.text = "Sesi Tanya Jawab\n\nPlatform RedSim - Keamanan Sistem Cerdas"

prs.save('/home/abarox/Desktop/projectAI/Presentasi_RedSim_Akbar.pptx')
print("PPTX Berhasil Dibuat!")
