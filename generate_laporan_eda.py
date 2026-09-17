#!/usr/bin/env python3
"""
Generator Laporan EDA Minggu 3 — RedSim Kelompok 8
Disesuaikan dengan output aktual dari Google Colab.
Melanjutkan Tugas_Dataset_Keamanan_Kelompok8_RedSim.docx (Minggu 2).

Jalankan: python generate_laporan_eda.py
Output:   Laporan_EDA_Minggu3_Kelompok8.docx
"""

from docx import Document
from docx.shared import Pt, Inches, RGBColor, Cm, Emu
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
import os
from datetime import datetime


# ═══════════════════════════════════════════════════════════
# HELPER FUNCTIONS
# ═══════════════════════════════════════════════════════════

def set_cell_bg(cell, hex_color):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'), 'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'), hex_color)
    tcPr.append(shd)


def set_cell_borders(cell, color='D5D8DC', sz='4'):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    tcBorders = OxmlElement('w:tcBorders')
    for edge in ('top', 'left', 'bottom', 'right'):
        el = OxmlElement(f'w:{edge}')
        el.set(qn('w:val'), 'single')
        el.set(qn('w:sz'), sz)
        el.set(qn('w:space'), '0')
        el.set(qn('w:color'), color)
        tcBorders.append(el)
    tcPr.append(tcBorders)


def add_run(paragraph, text, bold=False, italic=False, size=11, color='2C3E50', font='Calibri'):
    run = paragraph.add_run(text)
    run.bold = bold
    run.italic = italic
    run.font.size = Pt(size)
    run.font.color.rgb = RGBColor.from_string(color)
    run.font.name = font
    return run


def heading(doc, text, level=1, color='1A1A2E'):
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
    sizes = {0: 20, 1: 15, 2: 12, 3: 11}
    add_run(p, text, bold=True, size=sizes.get(level, 11), color=color)
    p.paragraph_format.space_before = Pt(14 if level <= 1 else 8)
    p.paragraph_format.space_after = Pt(4)
    return p


def body(doc, text, bold=False, italic=False, size=10.5, color='2C3E50'):
    p = doc.add_paragraph()
    add_run(p, text, bold=bold, italic=italic, size=size, color=color)
    p.paragraph_format.space_after = Pt(3)
    p.paragraph_format.line_spacing = Pt(15)
    return p


def bullet(doc, text, bold_prefix=None):
    p = doc.add_paragraph(style='List Bullet')
    if bold_prefix:
        add_run(p, bold_prefix, bold=True, size=10, color='34495E')
        add_run(p, ' ' + text, size=10, color='2C3E50')
    else:
        add_run(p, text, size=10, color='2C3E50')
    p.paragraph_format.space_after = Pt(1)
    return p


def table_styled(doc, headers, rows, header_bg='2C3E50', header_fg='FFFFFF'):
    tbl = doc.add_table(rows=1 + len(rows), cols=len(headers))
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    for i, h in enumerate(headers):
        c = tbl.rows[0].cells[i]
        c.text = ''
        p = c.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        add_run(p, h, bold=True, size=9, color=header_fg)
        set_cell_bg(c, header_bg)
        set_cell_borders(c, color=header_bg)
        c.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
    for ri, row in enumerate(rows):
        bg = 'F8F9FA' if ri % 2 == 0 else 'FFFFFF'
        for ci, val in enumerate(row):
            c = tbl.rows[ri + 1].cells[ci]
            c.text = ''
            p = c.paragraphs[0]
            add_run(p, str(val), size=9, color='2C3E50')
            set_cell_bg(c, bg)
            set_cell_borders(c)
            c.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
    return tbl


def insert_image(doc, path, width_inches=5.5, caption=None):
    if os.path.exists(path):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        run = p.add_run()
        run.add_picture(path, width=Inches(width_inches))
        if caption:
            cap = doc.add_paragraph()
            cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
            add_run(cap, caption, italic=True, size=9, color='7F8C8D')
            cap.paragraph_format.space_after = Pt(8)
    else:
        body(doc, f'[Gambar tidak ditemukan: {path}]', italic=True, color='E74C3C')


def divider(doc):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(3)
    p.paragraph_format.space_after = Pt(3)
    add_run(p, '─' * 80, size=6, color='D5D8DC')


# ═══════════════════════════════════════════════════════════
# MAIN DOCUMENT
# ═══════════════════════════════════════════════════════════

def main():
    IMG_DIR = 'gambar-visual-google-collab'
    doc = Document()

    section = doc.sections[0]
    section.page_width = Inches(8.27)
    section.page_height = Inches(11.69)
    section.left_margin = Cm(2.5)
    section.right_margin = Cm(2.5)
    section.top_margin = Cm(2.5)
    section.bottom_margin = Cm(2.5)

    # ────────────────── HALAMAN JUDUL ──────────────────
    for _ in range(3):
        doc.add_paragraph()

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    add_run(p, 'LAPORAN TUGAS MINGGU 3', bold=True, size=24, color='1A1A2E')

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    add_run(p, 'Exploratory Data Analysis (EDA)', size=14, color='2C3E50')

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    add_run(p, 'Dataset NVD CVE — Proyek RedSim', size=12, color='7F8C8D')

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    add_run(p, 'Project Milestone 2: Data Understanding', size=11, color='95A5A6')

    doc.add_paragraph()
    divider(doc)

    info = [
        ['Mata Kuliah', 'Kecerdasan Buatan untuk Keamanan (KSI251525)'],
        ['Dosen Pengampu', 'Zuliar Efendi, M.Kom'],
        ['Kelompok', '8'],
        ['Anggota', 'Akbar Maulana (6404240019)\nNur Lela Sabila (6404240011)'],
        ['Proyek', 'RedSim — AI-Assisted Security Analytics Prototype'],
        ['Dataset', 'NVD CVE 2025–2026 (63.663 record)'],
        ['Sumber', 'https://nvd.nist.gov/'],
        ['Tanggal', '15 September 2026'],
    ]
    tbl = doc.add_table(rows=len(info), cols=2)
    for i, (k, v) in enumerate(info):
        ck = tbl.rows[i].cells[0]
        cv = tbl.rows[i].cells[1]
        ck.text = ''
        cv.text = ''
        add_run(ck.paragraphs[0], k, bold=True, size=10, color='C0392B')
        add_run(cv.paragraphs[0], v, size=10, color='2C3E50')

    doc.add_page_break()

    # ────────────────── 1. PENDAHULUAN ──────────────────
    heading(doc, '1. Pendahuluan')

    body(doc,
         'Laporan ini merupakan kelanjutan dari Tugas Dataset Keamanan Kelompok 8 (Minggu 2) '
         'dan menyajikan hasil Exploratory Data Analysis (EDA) terhadap dataset NVD CVE tahun 2025–2026. '
         'EDA difokuskan pada pemahaman bentuk, kualitas, dan pola data sebelum tahap preprocessing '
         'dan modeling di minggu berikutnya.')

    body(doc,
         'Dataset NVD dipilih karena menjadi sumber data utama knowledge base RedSim. '
         'Pada Tugas Minggu 2, kelompok telah menjelaskan bahwa dataset NVD digunakan dalam pipeline '
         'RAG untuk pencocokan CVE pada temuan scan. Pada EDA ini, fokus analisis adalah distribusi '
         'keparahan, jenis kerentanan, dan kualitas data — hal-hal yang menentukan keputusan preprocessing.')

    divider(doc)

    # ────────────────── 2. DATA DICTIONARY ──────────────────
    heading(doc, '2. Data Dictionary')

    body(doc,
         'Dari 23 kolom yang diekstrak, berikut 10 fitur utama beserta makna keamanannya. '
         'Daftar lengkap tersedia di notebook EDA.')

    dd_headers = ['No', 'Fitur', 'Tipe', 'Makna Keamanan', 'Status', 'Catatan']
    dd_rows = [
        ['1', 'cve_id', 'String', 'ID unik kerentanan (CVE-2025-XXXX)', 'Metadata', 'Bukan fitur model'],
        ['2', 'base_score', 'Float (0–10)', 'Skor keparahan CVSS v3.1', 'Fitur utama', '⚠ Leakage ke severity'],
        ['3', 'base_severity', 'Kategori', 'NONE / LOW / MEDIUM / HIGH / CRITICAL', 'Label/Target', 'Imbalance tinggi'],
        ['4', 'attack_vector', 'Kategori', 'Jalur serangan: NETWORK, LOCAL, dll.', 'Fitur', 'Mayoritas NETWORK'],
        ['5', 'attack_complexity', 'Kategori', 'Kesulitan eksploitasi: LOW / HIGH', 'Fitur', '—'],
        ['6', 'privileges_required', 'Kategori', 'Hak akses: NONE / LOW / HIGH', 'Fitur', '—'],
        ['7', 'primary_cwe', 'Kategori', 'Jenis kelemahan (CWE-79 = XSS, dll.)', 'Fitur', 'Kardinalitas tinggi'],
        ['8', 'exploitability_score', 'Float (0–4)', 'Kemudahan eksploitasi', 'Fitur', '⚠ Leakage ke severity'],
        ['9', 'impact_score', 'Float (0–6)', 'Besarnya dampak jika dieksploitasi', 'Fitur', '⚠ Leakage ke severity'],
        ['10', 'has_exploit_ref', 'Boolean', 'Ada referensi exploit publik', 'Fitur', 'Penting untuk risk score'],
    ]
    table_styled(doc, dd_headers, dd_rows)

    doc.add_paragraph()
    heading(doc, 'Potensi Data Leakage', level=2, color='C0392B')
    body(doc,
         'base_severity diturunkan langsung dari base_score melalui threshold CVSS '
         '(0.1→LOW, 4.0→MEDIUM, 7.0→HIGH, 9.0→CRITICAL). Jika target prediksi adalah severity, '
         'maka base_score, exploitability_score, dan impact_score wajib dihapus dari fitur '
         'karena membocorkan label secara deterministik.')

    divider(doc)

    # ────────────────── 3. DATA PROFILING ──────────────────
    heading(doc, '3. Data Profiling')

    heading(doc, '3.1 Dimensi Dataset', level=2)
    body(doc,
         'Dataset terdiri dari 2 file JSON NVD (tahun 2025 dan 2026) yang digabung menjadi satu DataFrame.')

    table_styled(doc, ['Metrik', 'Nilai'], [
        ['Jumlah baris (record)', '63.663'],
        ['Jumlah kolom (fitur)', '23'],
        ['Total sel data', '1.464.249'],
        ['Sumber file', 'nvdcve-2.0-2025.json (44.367) + nvdcve-2.0-2026.json (19.296)'],
    ])

    doc.add_paragraph()
    insert_image(doc, f'{IMG_DIR}/4.1(1).jpeg', 6.0, 'Gambar 3.1 — Preview 5 baris pertama DataFrame (5 rows × 25 columns)')

    # 3.2 Missing Values
    heading(doc, '3.2 Missing Values', level=2)
    body(doc,
         'Dari 23 kolom, 12 kolom memiliki data kosong dengan pola yang konsisten: '
         'seluruh missing berasal dari 6.469 CVE (10,16%) yang belum memiliki analisis CVSS v3.1. '
         'Record ini berstatus "Awaiting Analysis" atau "Received" di NVD.')

    table_styled(doc, ['Kolom', 'NaN', 'Empty String', 'Total Missing', '%'], [
        ['base_score', '6.469', '0', '6.469', '10,16'],
        ['base_severity', '0', '6.469', '6.469', '10,16'],
        ['attack_vector', '0', '6.469', '6.469', '10,16'],
        ['attack_complexity', '0', '6.469', '6.469', '10,16'],
        ['privileges_required', '0', '6.469', '6.469', '10,16'],
        ['user_interaction', '0', '6.469', '6.469', '10,16'],
        ['confidentiality_impact', '0', '6.469', '6.469', '10,16'],
        ['integrity_impact', '0', '6.469', '6.469', '10,16'],
        ['availability_impact', '0', '6.469', '6.469', '10,16'],
        ['exploitability_score', '6.469', '0', '6.469', '10,16'],
        ['impact_score', '6.469', '0', '6.469', '10,16'],
        ['primary_cwe', '0', '1.261', '1.261', '1,98'],
    ], header_bg='E74C3C')

    doc.add_paragraph()
    body(doc,
         'Pola ini menunjukkan bahwa missing value bukan acak, melainkan sistematis: '
         'seluruh kolom CVSS kosong secara bersamaan karena NVD belum memproses CVE tersebut. '
         'Kolom primary_cwe memiliki 1.261 missing terpisah — ini adalah CVE yang sudah dianalisis '
         'tetapi belum diklasifikasikan kelemahannya.')

    # 3.3 Duplikasi
    heading(doc, '3.3 Duplikasi', level=2)
    body(doc,
         'Tidak ditemukan duplikasi, baik duplikasi penuh (0 baris) maupun duplikasi CVE ID (0 baris). '
         'Dataset bersih dari segi keunikan record.')

    # 3.4 Statistik Deskriptif
    heading(doc, '3.4 Statistik Deskriptif (Fitur Numerik)', level=2)

    table_styled(doc, ['Fitur', 'Count', 'Mean', 'Median', 'Std', 'Min', 'Max'], [
        ['base_score', '57.194', '6,68', '6,5', '1,70', '0,0', '10,0'],
        ['exploitability_score', '57.194', '2,66', '2,8', '0,92', '0,1', '3,9'],
        ['impact_score', '57.194', '3,85', '3,6', '1,56', '0,0', '6,0'],
        ['cwe_count', '63.663', '1,18', '1,0', '—', '0', '—'],
        ['ref_count', '63.663', '—', '—', '—', '—', '—'],
        ['desc_length', '63.663', '—', '—', '—', '—', '—'],
    ])

    doc.add_paragraph()
    body(doc,
         'Rata-rata base_score sebesar 6,68 (median 6,5) menunjukkan bahwa mayoritas CVE '
         'berada di rentang MEDIUM-HIGH. Standar deviasi 1,70 menandakan sebaran yang cukup lebar '
         'tapi terkonsentrasi di tengah skala CVSS.')

    # 3.5 Distribusi Label
    heading(doc, '3.5 Distribusi Label (Severity)', level=2)

    table_styled(doc, ['Severity', 'Jumlah', 'Persentase'], [
        ['NONE', '20', '0,03%'],
        ['LOW', '2.791', '4,88%'],
        ['MEDIUM', '27.578', '48,22%'],
        ['HIGH', '21.506', '37,60%'],
        ['CRITICAL', '5.299', '9,26%'],
    ], header_bg='C0392B')

    doc.add_paragraph()
    body(doc,
         'Total data berlabel: 57.194. Data tanpa label: 6.469.')
    body(doc,
         'Class imbalance sangat ekstrem. MEDIUM (27.578) berbanding NONE (20) menghasilkan '
         'rasio 1.378,9 : 1. MEDIUM dan HIGH bersama-sama mencapai 85,82% dari seluruh data berlabel. '
         'Accuracy saja tidak cukup — evaluasi harus menggunakan F1-score, recall, dan confusion matrix.')

    divider(doc)

    # ────────────────── 4. VISUALISASI ──────────────────
    heading(doc, '4. Visualisasi EDA')
    body(doc,
         'Berikut 6 visualisasi utama yang dihasilkan dari notebook Google Colab. '
         'Setiap grafik disertai interpretasi terhadap konteks keamanan dan implikasinya terhadap preprocessing.')

    # Viz 1 — CWE
    heading(doc, '4.1 Top 15 CWE (Jenis Kerentanan)', level=2)
    insert_image(doc, f'{IMG_DIR}/viz_03_top_cwe.png', 5.8,
                 'Gambar 4.1 — Top 15 CWE berdasarkan frekuensi kemunculan (2025–2026)')
    body(doc,
         'CWE-79 (XSS) mendominasi dengan 9.500 kemunculan — hampir tiga kali lipat CWE-74 (Injection, 3.302). '
         'Empat besar kerentanan (XSS, Injection, Missing Authorization, SQLi) menyumbang lebih dari '
         'setengah seluruh klasifikasi CWE. Pola ini relevan karena RedSim melakukan deteksi XSS, '
         'SQLi, dan injection lainnya pada URL target.')
    body(doc,
         'Distribusi bersifat long-tail: banyak CWE dengan frekuensi di bawah 1.000. '
         'Untuk modeling, CWE perlu dikelompokkan ke 8–10 kategori utama (Injection, Memory Corruption, '
         'Authorization, Information Disclosure, dll.) dengan sisanya digabung menjadi "Other".')

    # Viz 2 — Heatmap
    heading(doc, '4.2 Heatmap Korelasi Fitur Numerik', level=2)
    insert_image(doc, f'{IMG_DIR}/viz_04_correlation_heatmap.png', 4.5,
                 'Gambar 4.2 — Korelasi Pearson antar fitur numerik')
    body(doc,
         'Korelasi tertinggi ada antara impact_score dan base_score (r = 0,85). '
         'Ini sesuai ekspektasi karena impact_score merupakan komponen utama pembentuk base_score dalam formula CVSS. '
         'Korelasi exploitability_score terhadap base_score lebih rendah (r = 0,40).')
    body(doc,
         'Fitur-fitur metadata (cwe_count, ref_count, desc_length) tidak berkorelasi kuat '
         'dengan skor CVSS (nilai r mendekati 0). Artinya fitur-fitur ini membawa informasi independen '
         'dan berpotensi berguna sebagai fitur tambahan jika target prediksi bukan severity.')
    body(doc,
         'Temuan data leakage: jika base_severity dijadikan label target, maka base_score, '
         'impact_score, dan exploitability_score harus dihapus dari feature set karena korelasi deterministiknya.', bold=True)

    # Viz 3 — Attack Vector vs Severity
    heading(doc, '4.3 Attack Vector vs Severity Level', level=2)
    insert_image(doc, f'{IMG_DIR}/viz_05_attack_vector_severity.png', 5.5,
                 'Gambar 4.3 — Distribusi severity per attack vector (stacked bar)')
    body(doc,
         'NETWORK mendominasi sebagai attack vector terbanyak. Ini masuk akal mengingat '
         'sebagian besar kerentanan modern dapat dieksploitasi dari jaringan tanpa akses fisik. '
         'Untuk RedSim yang memindai URL/website, ini menunjukkan mayoritas CVE dalam knowledge base '
         'relevan dengan target scan.')
    body(doc,
         'Proporsi CRITICAL pada vektor NETWORK tampak lebih tinggi dibanding LOCAL. '
         'Kerentanan PHYSICAL sangat sedikit dan mayoritas berseverity MEDIUM.')

    # Viz 4 — Tren Bulanan
    heading(doc, '4.4 Tren Publikasi CVE per Bulan', level=2)
    insert_image(doc, f'{IMG_DIR}/viz_06_monthly_trend.png', 5.5,
                 'Gambar 4.4 — Jumlah CVE dipublikasikan per bulan (Jan 2025 – Sep 2026)')
    body(doc,
         'Grafik menunjukkan fluktuasi bulanan dengan puncak yang berulang. '
         'Tidak ada tren naik yang monoton — sebaliknya, terjadi pola siklus yang kemungkinan '
         'terkait dengan siklus rilis patch vendor besar (Microsoft Patch Tuesday, dll.) '
         'dan periode disclosure pada konferensi keamanan.')
    body(doc,
         'Data 2026 belum lengkap (s/d September), sehingga total terlihat lebih rendah. '
         'Implikasi: knowledge base RedSim perlu diperbarui secara periodik mengikuti rilis NVD terbaru.')

    # Viz 5 — Boxplot
    heading(doc, '4.5 Boxplot — Deteksi Outlier', level=2)
    insert_image(doc, f'{IMG_DIR}/viz_07_boxplot_outliers.png', 5.5,
                 'Gambar 4.5 — Boxplot base_score, exploitability_score, dan impact_score')
    body(doc,
         'Base score tersebar cukup merata di rentang 4–10 dengan sedikit outlier di bawah 2,0. '
         'Exploitability score menunjukkan distribusi yang terpusat di rentang 2–3,5. '
         'Impact score memiliki variasi lebih besar dengan whisker yang membentang dari 0 hingga 6.')
    body(doc,
         'Outlier pada skor CVSS bukan noise — ini adalah CVE dengan kombinasi vektor yang tidak lazim '
         '(misalnya kerentanan fisik dengan dampak tinggi). '
         'Keputusan: pertahankan semua outlier karena secara domain keamanan nilainya valid.')

    # Viz 6 — Scatter
    heading(doc, '4.6 Scatter Plot — Exploitability vs Impact', level=2)
    insert_image(doc, f'{IMG_DIR}/viz_08_scatter_exploitability_impact.png', 4.5,
                 'Gambar 4.6 — Pemisahan kelas severity pada ruang exploitability × impact')
    body(doc,
         'Scatter plot menunjukkan klaster yang cukup terpisah antar severity level. '
         'CRITICAL terkonsentrasi di kuadran kanan atas (exploitability tinggi + impact tinggi). '
         'MEDIUM tersebar paling luas, mencerminkan variasi kombinasi vektor CVSS yang beragam.')
    body(doc,
         'Pola separasi ini menandakan bahwa model klasifikasi (Random Forest, SVM, dll.) '
         'berpotensi bekerja dengan baik untuk memprediksi severity berdasarkan komponen vektor CVSS — '
         'asalkan masalah data leakage diatasi terlebih dahulu.')

    divider(doc)

    # ────────────────── 5. TEMUAN EDA ──────────────────
    heading(doc, '5. Temuan EDA')
    body(doc,
         'Berdasarkan data profiling dan visualisasi di atas, '
         'berikut 5 temuan utama beserta dampaknya pada tahap preprocessing:')

    temuan = [
        {
            'judul': 'Missing Values Sistematis (10,16%)',
            'detail': '6.469 dari 63.663 record (10,16%) tidak memiliki skor CVSS v3.1. '
                      'Seluruh kolom CVSS kosong secara bersamaan — pola ini menunjukkan bahwa '
                      'missing bukan acak, melainkan karena NVD belum menganalisis CVE tersebut.',
            'dampak': 'Drop 6.469 record tanpa skor untuk analisis severity. '
                      'Alternatif: simpan sebagai kelas terpisah "Unscored" jika relevan.',
        },
        {
            'judul': 'Class Imbalance Ekstrem (rasio 1.379 : 1)',
            'detail': 'MEDIUM (27.578, 48,22%) dan HIGH (21.506, 37,60%) mencakup 85,82% total. '
                      'NONE hanya 20 record (0,03%). Rasio kelas mayoritas terhadap minoritas mencapai 1.378,9 : 1.',
            'dampak': 'Gunakan F1-score dan recall sebagai metrik utama, bukan accuracy. '
                      'Terapkan SMOTE untuk oversampling kelas minoritas, atau gunakan parameter '
                      'class_weight="balanced" pada model.',
        },
        {
            'judul': 'Data Leakage: base_score → base_severity',
            'detail': 'base_severity ditentukan secara deterministik dari base_score melalui threshold CVSS. '
                      'Korelasi impact_score terhadap base_score = 0,85. '
                      'Menyertakan skor sebagai fitur akan membuat model "menghafal" bukan belajar.',
            'dampak': 'Jika target = severity: HAPUS base_score, exploitability_score, dan impact_score. '
                      'Gunakan hanya komponen vektor individu (attack_vector, privileges_required, CIA impact, dll.).',
        },
        {
            'judul': 'CWE Kardinalitas Tinggi (Long-Tail)',
            'detail': 'CWE-79 (XSS) mendominasi dengan 9.500 record. '
                      'Di sisi lain, ratusan CWE lain memiliki frekuensi di bawah 100. '
                      'Distribusi bersifat long-tail dengan ekor yang sangat panjang.',
            'dampak': 'Kelompokkan ke 8–10 kategori utama (Injection, Memory Corruption, Authorization, '
                      'Information Disclosure, Input Validation, dll.) + kategori "Other" untuk CWE langka.',
        },
        {
            'judul': 'Dominasi NETWORK Attack Vector',
            'detail': 'Mayoritas CVE memiliki attack_vector = NETWORK, artinya dapat dieksploitasi '
                      'dari jarak jauh tanpa akses fisik ke sistem target.',
            'dampak': 'Ini sejalan dengan fokus RedSim pada pemindaian URL/website yang bersifat network-based. '
                      'Knowledge base yang didominasi kerentanan network akan menghasilkan rekomendasi yang '
                      'lebih relevan untuk target scan RedSim.',
        },
    ]

    for i, t in enumerate(temuan, 1):
        heading(doc, f'Temuan {i}: {t["judul"]}', level=2, color='C0392B')
        bullet(doc, t['detail'], bold_prefix='Detail:')
        bullet(doc, t['dampak'], bold_prefix='Dampak Preprocessing:')

    divider(doc)

    # ────────────────── 6. RENCANA PREPROCESSING ──────────────────
    heading(doc, '6. Rencana Preprocessing (Minggu 4)')

    table_styled(doc, ['No', 'Masalah', 'Strategi', 'Prioritas'], [
        ['1', 'Missing CVSS (6.469 record)', 'Drop record tanpa skor CVSS v3.1', 'Kritis'],
        ['2', 'Data leakage (score → severity)', 'Hapus base_score, exploitability_score, impact_score dari fitur', 'Kritis'],
        ['3', 'Class imbalance (rasio 1.379:1)', 'SMOTE oversampling + class_weight="balanced"', 'Tinggi'],
        ['4', 'CWE kardinalitas tinggi', 'Grouping ke 8–10 kategori + "Other"', 'Sedang'],
        ['5', 'Fitur kategorikal', 'One-Hot Encoding (attack_vector, privileges_required, dll.)', 'Sedang'],
        ['6', 'Fitur numerik skewed', 'StandardScaler atau MinMaxScaler', 'Sedang'],
        ['7', 'Fitur boolean', 'Konversi has_config, has_exploit_ref ke 0/1', 'Rendah'],
        ['8', 'Fitur temporal', 'Ekstrak bulan dan hari dari published date', 'Rendah'],
    ], header_bg='27AE60')

    doc.add_paragraph()
    heading(doc, 'Preview Rencana Model (Minggu 5)', level=2)
    bullet(doc, 'Klasifikasi severity (multiclass: LOW / MEDIUM / HIGH / CRITICAL)', bold_prefix='Target:')
    bullet(doc, 'attack_vector, attack_complexity, privileges_required, user_interaction, CIA impact, '
                'primary_cwe_group, ref_count, has_exploit_ref', bold_prefix='Fitur:')
    bullet(doc, 'Random Forest, Logistic Regression (baseline)', bold_prefix='Model:')
    bullet(doc, 'F1-score (macro), Recall, Confusion Matrix', bold_prefix='Metrik:')

    divider(doc)

    # ────────────────── 7. REFLEKSI ──────────────────
    heading(doc, '7. Refleksi')

    body(doc, 'Fitur yang paling relevan untuk keputusan keamanan:', bold=True)
    bullet(doc, 'menentukan apakah ancaman dapat dieksploitasi secara remote, '
                'yang langsung relevan dengan target scan RedSim.', bold_prefix='attack_vector —')
    bullet(doc, 'mengidentifikasi akar masalah kerentanan (XSS, SQLi, memory corruption, dll.) '
                'dan menjadi dasar rekomendasi remediasi.', bold_prefix='primary_cwe —')
    bullet(doc, 'menandakan apakah exploit sudah tersedia publik, yang secara langsung '
                'meningkatkan risiko eksploitasi aktif.', bold_prefix='has_exploit_ref —')

    doc.add_paragraph()
    body(doc, 'Fitur yang berpotensi menyesatkan model:', bold=True)
    bullet(doc, 'membocorkan label severity secara deterministik (data leakage). '
                'Korelasi 0,85 dengan impact_score dan hubungan threshold langsung dengan severity.', bold_prefix='base_score —')
    bullet(doc, 'hanya mengukur panjang karakter deskripsi tanpa menyimpan informasi '
                'keamanan yang bermakna. Korelasi dengan semua skor mendekati 0.', bold_prefix='desc_length —')

    divider(doc)

    # ────────────────── PENUTUP ──────────────────
    heading(doc, 'Penutup')
    body(doc,
         'EDA terhadap 63.663 record CVE dari NVD (2025–2026) telah mengidentifikasi '
         '3 masalah kualitas data utama: missing values sistematis (10,16%), '
         'class imbalance ekstrem (rasio 1.379:1), dan risiko data leakage antara skor CVSS dan label severity. '
         'Rencana preprocessing telah disusun dengan 8 strategi yang diprioritaskan. '
         'Notebook EDA lengkap tersedia di file Tugas_Minggu3_EDA_Kelompok8.ipynb.')

    doc.add_paragraph()
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    add_run(p, '───────────────────────────────────────────────────────────────────────────', size=8, color='BDC3C7')
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    add_run(p, 'RedSim Project — Kelompok 8  |  Akbar Maulana (6404240019) & Nur Lela Sabila (6404240011)', size=9, color='7F8C8D')
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    add_run(p, 'Mata Kuliah: Kecerdasan Buatan untuk Keamanan  |  Sesi 3  |  15 September 2026', size=9, color='95A5A6')

    # Save
    output = 'Laporan_EDA_Minggu3_Kelompok8.docx'
    doc.save(output)
    print(f'✅ Laporan disimpan: {output}')
    return output


if __name__ == '__main__':
    main()
