#!/usr/bin/env python3
"""
Generator Dokumen Tugas Dataset Keamanan - Kelompok 8
Mata Kuliah: Kecerdasan Buatan untuk Keamanan
"""

from docx import Document
from docx.shared import Pt, Inches, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
import copy

def set_cell_bg(cell, hex_color):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'), 'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'), hex_color)
    tcPr.append(shd)

def set_cell_border(cell, **kwargs):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    tcBorders = OxmlElement('w:tcBorders')
    for edge in ('top', 'left', 'bottom', 'right'):
        tag = 'w:{}'.format(edge)
        element = OxmlElement(tag)
        element.set(qn('w:val'), kwargs.get(edge, 'single'))
        element.set(qn('w:sz'), kwargs.get('sz', '6'))
        element.set(qn('w:space'), '0')
        element.set(qn('w:color'), kwargs.get('color', 'C0392B'))
        tcBorders.append(element)
    tcPr.append(tcBorders)

def add_heading(doc, text, level=1, color='1A1A2E', bold=True):
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
    run = p.add_run(text)
    run.bold = bold
    run.font.color.rgb = RGBColor.from_string(color)
    if level == 0:
        run.font.size = Pt(20)
    elif level == 1:
        run.font.size = Pt(16)
    elif level == 2:
        run.font.size = Pt(13)
    else:
        run.font.size = Pt(11)
    return p

def add_body(doc, text, bold=False, italic=False, size=11, color='2C3E50', indent=False):
    p = doc.add_paragraph()
    if indent:
        p.paragraph_format.left_indent = Cm(0.5)
    run = p.add_run(text)
    run.bold = bold
    run.italic = italic
    run.font.size = Pt(size)
    run.font.color.rgb = RGBColor.from_string(color)
    return p

def add_bullet(doc, text, bold_prefix=None, color='2C3E50'):
    p = doc.add_paragraph(style='List Bullet')
    if bold_prefix:
        run1 = p.add_run(bold_prefix)
        run1.bold = True
        run1.font.size = Pt(11)
        run1.font.color.rgb = RGBColor.from_string('C0392B')
        run2 = p.add_run(text)
        run2.font.size = Pt(11)
        run2.font.color.rgb = RGBColor.from_string(color)
    else:
        run = p.add_run(text)
        run.font.size = Pt(11)
        run.font.color.rgb = RGBColor.from_string(color)
    return p

def add_divider(doc):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(2)
    run = p.add_run('─' * 80)
    run.font.size = Pt(8)
    run.font.color.rgb = RGBColor.from_string('BDC3C7')
    return p

def main():
    doc = Document()

    # ─── Page Margins ──────────────────────────────────────────────────────────
    section = doc.sections[0]
    section.page_width  = Inches(8.27)   # A4
    section.page_height = Inches(11.69)
    section.left_margin   = Cm(2.5)
    section.right_margin  = Cm(2.5)
    section.top_margin    = Cm(2.5)
    section.bottom_margin = Cm(2.5)

    # ─── Default Font ──────────────────────────────────────────────────────────
    style = doc.styles['Normal']
    style.font.name = 'Calibri'
    style.font.size = Pt(11)
    style.font.color.rgb = RGBColor.from_string('2C3E50')

    # ══════════════════════════════════════════════════════════════════════════
    # HEADER BOX
    # ══════════════════════════════════════════════════════════════════════════
    table_header = doc.add_table(rows=1, cols=1)
    table_header.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = table_header.cell(0, 0)
    set_cell_bg(cell, '1A1A2E')
    set_cell_border(cell, color='C0392B', sz='12')

    cell.paragraphs[0].clear()
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(10)
    p.paragraph_format.space_after  = Pt(4)

    r = p.add_run('🛡️  REDSIM')
    r.bold = True
    r.font.size = Pt(22)
    r.font.color.rgb = RGBColor.from_string('E74C3C')

    p2 = cell.add_paragraph()
    p2.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r2 = p2.add_run('AI-Powered Cybersecurity Analysis & Education Platform')
    r2.font.size = Pt(11)
    r2.font.color.rgb = RGBColor.from_string('ECF0F1')

    p3 = cell.add_paragraph()
    p3.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p3.paragraph_format.space_after = Pt(10)
    r3 = p3.add_run('Project Brief — Tugas Dataset Keamanan  |  Sesi 2')
    r3.font.size = Pt(10)
    r3.italic = True
    r3.font.color.rgb = RGBColor.from_string('95A5A6')

    doc.add_paragraph()

    # ══════════════════════════════════════════════════════════════════════════
    # INFO KELOMPOK
    # ══════════════════════════════════════════════════════════════════════════
    tbl_info = doc.add_table(rows=2, cols=4)
    tbl_info.alignment = WD_TABLE_ALIGNMENT.CENTER
    labels = ['Mata Kuliah', 'Kelompok', 'Anggota', 'NIM']
    values = ['Kecerdasan Buatan\nuntuk Keamanan', 'Kelompok 8',
              '1. Akbar Maulana\n2. Nur Lela Sabila',
              '6404240019\n6404240011']

    for i, (lbl, val) in enumerate(zip(labels, values)):
        cell_lbl = tbl_info.cell(0, i)
        cell_val = tbl_info.cell(1, i)
        set_cell_bg(cell_lbl, 'C0392B')
        set_cell_bg(cell_val, 'F8F9FA')

        cell_lbl.paragraphs[0].clear()
        pl = cell_lbl.paragraphs[0]
        pl.alignment = WD_ALIGN_PARAGRAPH.CENTER
        rl = pl.add_run(lbl)
        rl.bold = True
        rl.font.size = Pt(9)
        rl.font.color.rgb = RGBColor.from_string('FFFFFF')

        cell_val.paragraphs[0].clear()
        pv = cell_val.paragraphs[0]
        pv.alignment = WD_ALIGN_PARAGRAPH.CENTER
        rv = pv.add_run(val)
        rv.font.size = Pt(9)
        rv.font.color.rgb = RGBColor.from_string('1A1A2E')

    doc.add_paragraph()

    # ══════════════════════════════════════════════════════════════════════════
    # SECTION 1 — PROJECT BRIEF
    # ══════════════════════════════════════════════════════════════════════════
    add_heading(doc, '1.  PROJECT BRIEF', level=1, color='C0392B')
    add_divider(doc)

    # 1.1 Latar Belakang
    add_heading(doc, '1.1  Latar Belakang & Motivasi', level=2, color='1A1A2E')
    add_body(doc,
        'Ancaman siber terus berkembang dengan pesat. Organisasi menghadapi tantangan besar '
        'dalam mendeteksi, memahami, dan merespons kerentanan keamanan secara cepat dan akurat. '
        'Pendekatan konvensional sering kali reaktif, lambat, dan membutuhkan keahlian tinggi. '
        'RedSim hadir sebagai solusi inovatif yang mengintegrasikan Kecerdasan Buatan (AI) '
        'dengan metodologi keamanan berbasis standar internasional untuk mengotomatisasi '
        'analisis ancaman dan memberdayakan profesional keamanan.',
        size=11)

    doc.add_paragraph()
    add_heading(doc, '1.2  Deskripsi Proyek', level=2, color='1A1A2E')
    add_body(doc,
        'RedSim (Red Team Simulation) adalah platform web komprehensif berbasis Laravel (PHP 8.3+) '
        'yang menyediakan serangkaian alat keamanan siber bertenaga AI, analisis ancaman otomatis, '
        'dan fitur edukasi interaktif. Platform ini dirancang untuk membantu tim keamanan, '
        'peneliti, dan pelajar dalam memahami, mendeteksi, dan merespons ancaman siber secara efektif.',
        size=11)

    # 1.3 Tujuan
    doc.add_paragraph()
    add_heading(doc, '1.3  Tujuan (Objectives)', level=2, color='1A1A2E')
    objectives = [
        ('T-1: ', 'Membangun sistem analisis keamanan otomatis berbasis RAG (Retrieval-Augmented Generation) yang mampu mengidentifikasi kerentanan pada URL, kode sumber, file ZIP, dan log sistem.'),
        ('T-2: ', 'Mengintegrasikan database pengetahuan keamanan siber standar internasional (NVD/CVE, CWE, MITRE ATT&CK, CAPEC, CISA KEV) ke dalam pipeline AI untuk mendukung analisis kontekstual.'),
        ('T-3: ', 'Menyediakan laporan keamanan yang actionable dengan skor risiko (0–100), verdict, rantai serangan MITRE ATT&CK, dan rekomendasi remediasi.'),
        ('T-4: ', 'Mengembangkan modul edukasi interaktif berupa CTF (Capture The Flag), ensiklopedia, dan leaderboard untuk meningkatkan kompetensi keamanan siber pengguna.'),
        ('T-5: ', 'Membangun sistem autentikasi berlapis (OTP WhatsApp, Google OAuth, approval admin) untuk memastikan keamanan akses platform itu sendiri.'),
    ]
    for bold_p, text in objectives:
        add_bullet(doc, text, bold_prefix=bold_p)

    # 1.4 Target Model
    doc.add_paragraph()
    add_heading(doc, '1.4  Target Model AI', level=2, color='1A1A2E')
    add_body(doc,
        'Model yang dikembangkan dalam RedSim adalah sistem AI Hybrid Multi-Task yang '
        'mengkombinasikan beberapa komponen:',
        size=11)

    models = [
        ('Model Utama — Klasifikasi & Penilaian Risiko: ',
         'Menggunakan LLM (Large Language Model) — Groq/OpenAI/NVIDIA — melalui pipeline '
         'RAG untuk mengklasifikasikan kerentanan, menghitung skor risiko, dan menghasilkan '
         'laporan naratif. Target output: skor keamanan (0–100), label verdict '
         '(aman/perhatian/berbahaya), identifikasi CVE/CWE, dan rantai serangan MITRE ATT&CK.'),
        ('Model Pendukung — Embedding & Retrieval: ',
         'Menggunakan Pinecone Serverless Vector Database dengan Integrated Inference '
         'untuk menyimpan dan mengambil potongan pengetahuan (chunks) keamanan yang relevan '
         'berdasarkan kemiripan semantik (cosine similarity).'),
        ('Pipeline OSINT: ',
         'Mengintegrasikan API eksternal (urlscan.io, VirusTotal) untuk pengumpulan data '
         'intelijen ancaman secara real-time sebelum dianalisis oleh LLM.'),
    ]
    for bold_m, text_m in models:
        add_bullet(doc, text_m, bold_prefix=bold_m)

    # 1.5 Arsitektur
    doc.add_paragraph()
    add_heading(doc, '1.5  Arsitektur Sistem', level=2, color='1A1A2E')
    add_body(doc,
        'Sistem RedSim terdiri dari komponen-komponen berikut yang saling terintegrasi:',
        size=11)

    arch_items = [
        ('Frontend: ', 'Antarmuka web berbasis Blade/Vite dengan desain responsif.'),
        ('Backend API: ', 'Laravel 11 (PHP 8.3+) sebagai REST API engine dengan rate limiting, middleware autentikasi, dan queue jobs.'),
        ('RAG Pipeline: ', 'Query Embedding → Pinecone Vector Search (Top-K) → LLM Generation.'),
        ('Database: ', 'Supabase PostgreSQL / MySQL untuk persistensi data scan, temuan, dan pengguna.'),
        ('OSINT Layer: ', 'urlscan.io (HTTP/DOM/SSL analysis) + VirusTotal (malware & reputasi).'),
        ('LLM Layer: ', 'Dukungan multi-provider: Groq (Llama/Qwen), OpenAI (GPT-4), NVIDIA NIM — dapat dikonfigurasi secara dinamis dari admin panel.'),
    ]
    for bold_a, text_a in arch_items:
        add_bullet(doc, text_a, bold_prefix=bold_a)

    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════
    # SECTION 2 — DESKRIPSI DATASET
    # ══════════════════════════════════════════════════════════════════════════
    add_heading(doc, '2.  DESKRIPSI DATASET', level=1, color='C0392B')
    add_divider(doc)

    add_body(doc,
        'Dataset yang digunakan RedSim merupakan kumpulan data berlabel dari sumber-sumber '
        'standar keamanan siber internasional yang terpercaya. Dataset ini berfungsi sebagai '
        'knowledge base dalam pipeline RAG dan disimpan sebagai vector embeddings di Pinecone '
        'untuk retrieval semantik yang efisien.',
        size=11)

    doc.add_paragraph()

    # Tabel ringkasan dataset
    add_heading(doc, '2.1  Ringkasan Dataset', level=2, color='1A1A2E')
    doc.add_paragraph()

    headers_ds = ['No.', 'Nama Dataset', 'Sumber', 'Format', 'Ukuran (approx)', 'Keterangan']
    rows_ds = [
        ['1', 'NVD CVE 2020–2026', 'NIST National\nVulnerability Database', 'JSON (NVD 2.0)', '~110 MB\n(7 file/tahun)', 'Data kerentanan CVE resmi beserta skor CVSS v3, deskripsi, referensi, dan produk yang terdampak.'],
        ['2', 'CWE (Common Weakness\nEnumeration) v4.20', 'MITRE Corporation', 'XML', '~18 MB', 'Taksonomi kelemahan perangkat lunak dan perangkat keras; digunakan untuk klasifikasi akar penyebab kerentanan.'],
        ['3', 'MITRE ATT&CK STIX\n(Enterprise, Mobile, ICS)', 'MITRE ATT&CK', 'JSON (STIX 2.1)', '~150+ MB\n(multi-versi)', 'Knowledge base taktik, teknik, dan prosedur (TTP) adversary berbasis observasi dunia nyata.'],
        ['4', 'CAPEC (Common Attack\nPattern Enumeration)', 'MITRE Corporation', 'XML', '~3.8 MB', 'Katalog pola serangan umum; digunakan untuk menggambarkan bagaimana aktor ancaman mengeksploitasi kelemahan.'],
        ['5', 'CISA Known Exploited\nVulnerabilities (KEV)', 'CISA (US Gov)', 'JSON', '~1.4 MB', 'Daftar kerentanan yang telah terbukti dieksploitasi secara aktif di dunia nyata; diprioritaskan untuk remediasi.'],
        ['6', 'OWASP Cheat Sheet Series', 'OWASP Foundation', 'Markdown', '~Multiple MD\nfiles', 'Panduan praktik terbaik keamanan aplikasi web; digunakan sebagai referensi rekomendasi remediasi.'],
        ['7', 'OWASP WSTG\n(Web Security Testing Guide)', 'OWASP Foundation', 'Markdown', '~Multiple MD\nfiles', 'Panduan pengujian keamanan aplikasi web yang komprehensif; menjadi referensi metodologi pengujian.'],
        ['8', 'Data Scan Internal\n(RedSim DB)', 'RedSim Platform\n(Generated)', 'PostgreSQL\n/ JSON', 'Dinamis\n(growable)', 'Data historis hasil scan platform sendiri: target, skor, temuan, verdict, dan rantai serangan — digunakan untuk fine-tuning dan evaluasi.'],
    ]

    tbl_ds = doc.add_table(rows=1 + len(rows_ds), cols=len(headers_ds))
    tbl_ds.alignment = WD_TABLE_ALIGNMENT.CENTER
    tbl_ds.style = 'Table Grid'

    # Header row
    for i, h in enumerate(headers_ds):
        c = tbl_ds.cell(0, i)
        set_cell_bg(c, '1A1A2E')
        c.paragraphs[0].clear()
        p = c.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(h)
        r.bold = True
        r.font.size = Pt(9)
        r.font.color.rgb = RGBColor.from_string('FFFFFF')

    # Data rows
    for row_i, row_data in enumerate(rows_ds):
        bg = 'F8F9FA' if row_i % 2 == 0 else 'FFFFFF'
        for col_i, cell_text in enumerate(row_data):
            c = tbl_ds.cell(row_i + 1, col_i)
            set_cell_bg(c, bg)
            c.paragraphs[0].clear()
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER if col_i < 4 else WD_ALIGN_PARAGRAPH.LEFT
            r = p.add_run(cell_text)
            r.font.size = Pt(8.5)
            r.font.color.rgb = RGBColor.from_string('2C3E50')

    doc.add_paragraph()

    # 2.2 Detail setiap dataset
    add_heading(doc, '2.2  Detail Dataset per Sumber', level=2, color='1A1A2E')

    datasets_detail = [
        {
            'title': 'Dataset 1: NVD CVE 2020–2026 (National Vulnerability Database)',
            'desc': (
                'NVD adalah repository data manajemen kerentanan berbasis standar pemerintah AS yang '
                'dikelola oleh NIST. Dataset ini mencakup seluruh CVE (Common Vulnerabilities and '
                'Exposures) yang dipublikasikan dari tahun 2020 hingga 2026.'
            ),
            'attrs': [
                ('Format: ', 'JSON (NVD CVE API 2.0 Schema)'),
                ('Jumlah File: ', '7 file (satu per tahun: 2020, 2021, 2022, 2023, 2024, 2025, 2026)'),
                ('Total Ukuran: ', '~110 MB (terkompresi dalam ZIP)'),
                ('Fitur Utama: ', 'cveId, sourceIdentifier, published, lastModified, vulnStatus, descriptions (EN), metrics (CVSS v3.1/v3.0/v2.0), weaknesses (CWE), configurations (CPE), references'),
                ('Kegunaan dalam RedSim: ', 'Sebagai sumber data utama untuk pencocokan CVE pada temuan scan. RAG pipeline mencari konteks CVE yang relevan berdasarkan teknologi yang terdeteksi dan pola kerentanan.'),
                ('Sumber Resmi: ', 'https://nvd.nist.gov/vuln/data-feeds'),
            ],
        },
        {
            'title': 'Dataset 2: CWE v4.20 (Common Weakness Enumeration)',
            'desc': (
                'CWE adalah sistem kategorisasi kelemahan perangkat lunak dan perangkat keras yang '
                'dikembangkan oleh komunitas dan dipelihara oleh MITRE Corporation. Setiap entri CWE '
                'mendefinisikan tipe kelemahan, penyebab, konsekuensi, dan mitigasinya.'
            ),
            'attrs': [
                ('Format: ', 'XML (cwec_v4.20.xml, ~18 MB)'),
                ('Cakupan: ', '963 kelemahan unik dalam taksonomi hierarkis (Weakness, Category, View)'),
                ('Fitur Utama: ', 'ID CWE, Name, Description, Extended Description, Related Weaknesses, Applicable Platforms, Common Consequences, Detection Methods, Potential Mitigations, Observed Examples'),
                ('Kegunaan dalam RedSim: ', 'Memberikan konteks taksonomi untuk setiap kerentanan yang ditemukan; membantu LLM menjelaskan akar penyebab (root cause) dan rekomendasi mitigasi yang tepat.'),
                ('Sumber Resmi: ', 'https://cwe.mitre.org/data/downloads.html'),
            ],
        },
        {
            'title': 'Dataset 3: MITRE ATT&CK STIX 2.1 (Enterprise, Mobile, ICS)',
            'desc': (
                'MITRE ATT&CK adalah knowledge base global tentang taktik, teknik, dan prosedur (TTP) '
                'yang digunakan oleh adversary berdasarkan observasi dunia nyata. Dataset ini disimpan '
                'dalam format STIX 2.1 (Structured Threat Information Expression).'
            ),
            'attrs': [
                ('Format: ', 'JSON (STIX 2.1 Collection Bundles)'),
                ('Domain: ', 'Enterprise ATT&CK, Mobile ATT&CK, ATT&CK for ICS'),
                ('Cakupan: ', '14 taktik, 200+ teknik, 400+ sub-teknik (Enterprise); kelompok APT dan perangkat lunak berbahaya'),
                ('Fitur Utama: ', 'Tactic (TA-XXXX), Technique (T-XXXX), Sub-technique, Mitigation, Detection, Groups (APT), Software, Campaign data'),
                ('Kegunaan dalam RedSim: ', 'Menjadi dasar pembuatan "rantai serangan" (attack chain) dan pemetaan fase_attck dalam tabel simulasi_serangan. Memungkinkan RedSim menghasilkan threat modeling yang realistis.'),
                ('Sumber Resmi: ', 'https://github.com/mitre-attack/attack-stix-data'),
            ],
        },
        {
            'title': 'Dataset 4: CAPEC (Common Attack Pattern Enumeration and Classification)',
            'desc': (
                'CAPEC adalah katalog komprehensif pola serangan siber yang menggambarkan cara-cara '
                'umum yang digunakan oleh adversary untuk mengeksploitasi kerentanan dalam sistem.'
            ),
            'attrs': [
                ('Format: ', 'XML (capec_latest.xml, ~3.8 MB)'),
                ('Cakupan: ', '500+ pola serangan yang terklasifikasi dalam hierarki'),
                ('Fitur Utama: ', 'Attack Pattern ID, Name, Description, Attack Steps, Prerequisites, Skills Required, Resources Required, Mitigations, Related Weaknesses (CWE), Related Attack Patterns'),
                ('Kegunaan dalam RedSim: ', 'Memperkaya analisis dengan menjelaskan "bagaimana" serangan dilakukan secara step-by-step, melengkapi ATT&CK yang menjelaskan "apa" yang dilakukan.'),
                ('Sumber Resmi: ', 'https://capec.mitre.org/data/downloads.html'),
            ],
        },
        {
            'title': 'Dataset 5: CISA Known Exploited Vulnerabilities (KEV)',
            'desc': (
                'CISA KEV adalah daftar resmi pemerintah AS (Cybersecurity and Infrastructure Security '
                'Agency) yang berisi kerentanan yang telah terbukti dieksploitasi secara aktif di '
                'dunia nyata. Dataset ini diperbarui secara berkala.'
            ),
            'attrs': [
                ('Format: ', 'JSON (known_exploited_vulnerabilities.json, ~1.4 MB)'),
                ('Cakupan: ', '1.000+ entri CVE dengan status eksploitasi aktif'),
                ('Fitur Utama: ', 'cveID, vendorProject, product, vulnerabilityName, dateAdded, shortDescription, requiredAction, dueDate, notes, knownRansomwareCampaignUse'),
                ('Kegunaan dalam RedSim: ', 'Memberikan sinyal prioritas tinggi — jika CVE yang ditemukan masuk daftar KEV, skor risiko otomatis dinaikkan dan diberikan peringatan kritis kepada pengguna.'),
                ('Sumber Resmi: ', 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog'),
            ],
        },
        {
            'title': 'Dataset 6 & 7: OWASP Cheat Sheet Series & WSTG',
            'desc': (
                'OWASP (Open Web Application Security Project) menyediakan dua resource utama: '
                'Cheat Sheet Series (panduan implementasi praktis) dan Web Security Testing Guide '
                '(panduan pengujian keamanan komprehensif). Keduanya tersedia dalam format Markdown.'
            ),
            'attrs': [
                ('Format: ', 'Markdown (.md files — multiple files)'),
                ('Cakupan CheatSheet: ', '70+ topik keamanan: SQL Injection Prevention, XSS Prevention, Authentication, CSRF, Input Validation, dll.'),
                ('Cakupan WSTG: ', 'Panduan pengujian terstruktur untuk 12 kategori: Information Gathering, Configuration Testing, Identity Management, Authentication, dll.'),
                ('Kegunaan dalam RedSim: ', 'Dipecah menjadi chunks dan di-index ke Pinecone. Digunakan RAG pipeline untuk menghasilkan rekomendasi remediasi yang spesifik, actionable, dan mengacu pada best practice OWASP.'),
                ('Sumber Resmi: ', 'https://cheatsheetseries.owasp.org & https://owasp.org/www-project-web-security-testing-guide/'),
            ],
        },
    ]

    for ds in datasets_detail:
        doc.add_paragraph()
        add_heading(doc, ds['title'], level=3, color='C0392B', bold=True)
        add_body(doc, ds['desc'], size=11)
        for bold_a, text_a in ds['attrs']:
            add_bullet(doc, text_a, bold_prefix=bold_a)

    doc.add_paragraph()

    # 2.3 Preprocessing
    add_heading(doc, '2.3  Strategi Preprocessing & Chunking', level=2, color='1A1A2E')
    add_body(doc,
        'Sebelum diindeks ke Pinecone Vector Database, setiap dataset melewati pipeline preprocessing:',
        size=11)

    preproc = [
        ('Parsing & Ekstraksi: ', 'File XML/JSON/Markdown diparse untuk mengekstrak field yang relevan (description, mitigation, attack steps, dll.).'),
        ('Normalisasi Teks: ', 'HTML tags dihapus, karakter khusus dinormalisasi, teks dibersihkan dari noise.'),
        ('Chunking Semantik: ', 'Teks panjang dipecah menjadi chunks dengan ukuran optimal (512–1024 token) dengan overlap untuk mempertahankan konteks.'),
        ('Embedding Generation: ', 'Setiap chunk dikonversi menjadi vektor berdimensi tinggi menggunakan Pinecone Integrated Inference (model embedding bawaan).'),
        ('Metadata Enrichment: ', 'Setiap chunk disertai metadata: source, source_id, title, chunk_index untuk traceability dan filtering.'),
        ('Indexing: ', 'Vektor beserta metadata diupload ke Pinecone Serverless index untuk retrieval real-time.'),
    ]
    for b, t in preproc:
        add_bullet(doc, t, bold_prefix=b)

    doc.add_paragraph()

    # 2.4 Statistik
    add_heading(doc, '2.4  Statistik & Volume Data', level=2, color='1A1A2E')

    stat_headers = ['Sumber', 'Perkiraan Jumlah Record', 'Jumlah Chunks (est.)', 'Dimensi Vektor']
    stat_rows = [
        ['NVD CVE (2020–2026)', '~200.000+ CVE entries', '~500.000 chunks', '1024-dim'],
        ['CWE v4.20', '~963 weakness entries', '~5.000 chunks', '1024-dim'],
        ['MITRE ATT&CK STIX', '~700+ techniques & sub-techniques', '~8.000 chunks', '1024-dim'],
        ['CAPEC', '~500+ attack patterns', '~3.000 chunks', '1024-dim'],
        ['CISA KEV', '~1.000+ CVE exploited', '~2.000 chunks', '1024-dim'],
        ['OWASP Cheat Sheet', '70+ cheat sheets', '~4.000 chunks', '1024-dim'],
        ['OWASP WSTG', '~200+ test cases', '~6.000 chunks', '1024-dim'],
        ['RedSim Internal DB', 'Dinamis (real scans)', 'N/A (SQL DB)', 'N/A'],
        ['TOTAL', '~203.000+ records', '~528.000+ chunks', '─'],
    ]

    tbl_stat = doc.add_table(rows=1 + len(stat_rows), cols=4)
    tbl_stat.alignment = WD_TABLE_ALIGNMENT.CENTER
    tbl_stat.style = 'Table Grid'

    for i, h in enumerate(stat_headers):
        c = tbl_stat.cell(0, i)
        set_cell_bg(c, 'C0392B')
        c.paragraphs[0].clear()
        p = c.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(h)
        r.bold = True
        r.font.size = Pt(9)
        r.font.color.rgb = RGBColor.from_string('FFFFFF')

    for row_i, row_data in enumerate(stat_rows):
        is_total = row_i == len(stat_rows) - 1
        bg = '1A1A2E' if is_total else ('F8F9FA' if row_i % 2 == 0 else 'FFFFFF')
        txt_color = 'E74C3C' if is_total else '2C3E50'
        for col_i, ct in enumerate(row_data):
            c = tbl_stat.cell(row_i + 1, col_i)
            set_cell_bg(c, bg)
            c.paragraphs[0].clear()
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            r = p.add_run(ct)
            r.font.size = Pt(9)
            r.bold = is_total
            r.font.color.rgb = RGBColor.from_string(txt_color)

    doc.add_paragraph()
    doc.add_page_break()

    # ══════════════════════════════════════════════════════════════════════════
    # SECTION 3 — TARGET MODEL (Slide 9 content)
    # ══════════════════════════════════════════════════════════════════════════
    add_heading(doc, '3.  TARGET MODEL & OUTPUT AI', level=1, color='C0392B')
    add_divider(doc)

    add_body(doc,
        'Berdasarkan dataset dan arsitektur yang telah dijelaskan, berikut adalah spesifikasi '
        'target model AI yang akan dikembangkan dalam proyek RedSim:',
        size=11)

    doc.add_paragraph()

    # Task table
    task_headers = ['Komponen', 'Tipe Tugas AI', 'Input', 'Output Target']
    task_rows = [
        ['Analisis Kerentanan\n(Core Scanner)',
         'Multi-label Classification\n+ Text Generation (RAG)',
         'Data scan: URL/code/log/zip + OSINT findings',
         'CVE ID, CWE ID, severity level,\nskor risiko (0–100), verdict'],
        ['Attack Chain Generation\n(Threat Modeling)',
         'Conditional Text Generation\n(RAG-guided)',
         'Temuan kerentanan + ATT&CK context',
         'Fase MITRE ATT&CK, profil penyerang,\nskenario eksploitasi step-by-step'],
        ['Remediasi & Rekomendasi\n(Advisory Engine)',
         'Retrieval-Augmented Generation\n(RAG)',
         'CWE/CVE findings + OWASP chunks',
         'Panduan remediasi spesifik,\nreferensi OWASP, prioritas perbaikan'],
        ['Security Q&A\n(AI Chat Assistant)',
         'Open-domain Question Answering\n(RAG)',
         'Pertanyaan pengguna dalam bahasa natural',
         'Jawaban kontekstual berbasis\nknowledge base keamanan'],
        ['CTF Validation\n(Education Module)',
         'Exact Match Classification\n+ Partial Scoring',
         'Jawaban pengguna (flag/kode/pilihan)',
         'Boolean benar/salah + poin\n+ feedback edukatif'],
    ]

    tbl_task = doc.add_table(rows=1 + len(task_rows), cols=4)
    tbl_task.alignment = WD_TABLE_ALIGNMENT.CENTER
    tbl_task.style = 'Table Grid'

    for i, h in enumerate(task_headers):
        c = tbl_task.cell(0, i)
        set_cell_bg(c, '1A1A2E')
        c.paragraphs[0].clear()
        p = c.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(h)
        r.bold = True
        r.font.size = Pt(9)
        r.font.color.rgb = RGBColor.from_string('FFFFFF')

    for row_i, row_data in enumerate(task_rows):
        bg = 'F8F9FA' if row_i % 2 == 0 else 'FFFFFF'
        for col_i, ct in enumerate(row_data):
            c = tbl_task.cell(row_i + 1, col_i)
            set_cell_bg(c, bg)
            c.paragraphs[0].clear()
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT
            r = p.add_run(ct)
            r.font.size = Pt(9)
            r.font.color.rgb = RGBColor.from_string('2C3E50')

    doc.add_paragraph()

    # Metrik evaluasi
    add_heading(doc, '3.1  Metrik Evaluasi Model', level=2, color='1A1A2E')
    metrics = [
        ('Akurasi Klasifikasi CVE/CWE: ', 'Precision, Recall, F1-Score — diukur terhadap dataset benchmark kerentanan yang diketahui.'),
        ('Kualitas Laporan AI: ', 'BLEU/ROUGE score untuk konten naratif + evaluasi manusiawi (expert review) untuk relevansi dan actionability.'),
        ('Skor Risiko Kalibrasi: ', 'Korelasi skor RedSim vs skor CVSS resmi NVD; target korelasi Pearson ≥ 0.85.'),
        ('Latency RAG Pipeline: ', 'Target response time ≤ 30 detik untuk scan standar; ≤ 2 menit untuk scan intensif.'),
        ('False Positive Rate: ', 'Target FPR ≤ 10% untuk temuan kerentanan kritis/tinggi.'),
    ]
    for b, t in metrics:
        add_bullet(doc, t, bold_prefix=b)

    doc.add_paragraph()

    # ══════════════════════════════════════════════════════════════════════════
    # FOOTER
    # ══════════════════════════════════════════════════════════════════════════
    add_divider(doc)
    p_footer = doc.add_paragraph()
    p_footer.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_f = p_footer.add_run(
        'RedSim Project — Kelompok 8  |  Akbar Maulana (6404240019) & Nur Lela Sabila (6404240011)\n'
        'Mata Kuliah: Kecerdasan Buatan untuk Keamanan  |  Sesi 2  |  08 September 2026'
    )
    r_f.font.size = Pt(9)
    r_f.italic = True
    r_f.font.color.rgb = RGBColor.from_string('95A5A6')

    # ── Save ──
    output_path = '/home/abarox/Desktop/projectAI/Tugas_Dataset_Keamanan_Kelompok8_RedSim.docx'
    doc.save(output_path)
    print(f'✅ Dokumen berhasil disimpan: {output_path}')

if __name__ == '__main__':
    main()
