"""
feature_extractor.py — Mapper: data Temuan RedSim → 47-feature vector

Memetakan struktur Temuan dari RedSim (hasil scan LLM) ke format fitur
yang digunakan Random Forest model (ditraining dari NVD CVE dataset).

47 Fitur (sesuai feature_names.txt dari preprocessing Minggu 4):
  Numerical (7):
    num__cwe_count, num__ref_count, num__desc_length,
    num__pub_year, num__pub_month, num__pub_quarter, num__pub_dayofweek

  Categorical vuln_status (6):
    cat__vuln_status_Analyzed, cat__vuln_status_Awaiting Analysis,
    cat__vuln_status_Deferred, cat__vuln_status_Modified,
    cat__vuln_status_Received, cat__vuln_status_Undergoing Analysis

  Categorical attack vector (4):
    cat__attack_vector_ADJACENT_NETWORK, cat__attack_vector_LOCAL,
    cat__attack_vector_NETWORK, cat__attack_vector_PHYSICAL

  Categorical attack complexity (2):
    cat__attack_complexity_HIGH, cat__attack_complexity_LOW

  Categorical privileges required (3):
    cat__privileges_required_HIGH, cat__privileges_required_LOW,
    cat__privileges_required_NONE

  Categorical user interaction (2):
    cat__user_interaction_NONE, cat__user_interaction_REQUIRED

  Categorical impact (9):
    cat__conf_impact_HIGH/LOW/NONE,
    cat__integ_impact_HIGH/LOW/NONE,
    cat__avail_impact_HIGH/LOW/NONE

  Categorical cwe_group (12):
    AUTH, CONFIG, CRYPTO, DESERIALIZATION, DOS, INFO_DISCLOSURE,
    INJECTION, MEMORY, OTHER, PATH_TRAVERSAL, SSRF_CSRF, UNKNOWN

  Boolean (2):
    bool__has_config, bool__has_exploit_ref
"""

import numpy as np
import pandas as pd
from datetime import datetime
import logging

logger = logging.getLogger(__name__)

# ─────────────────────────────────────────────────────────────────
# CWE ID → CWE Group mapping (berdasarkan NVD CVE data patterns)
# ─────────────────────────────────────────────────────────────────
CWE_TO_GROUP = {
    # INJECTION
    'CWE-89': 'INJECTION',   # SQL Injection
    'CWE-79': 'INJECTION',   # XSS
    'CWE-78': 'INJECTION',   # OS Command Injection
    'CWE-77': 'INJECTION',   # Command Injection
    'CWE-94': 'INJECTION',   # Code Injection
    'CWE-74': 'INJECTION',   # Injection
    'CWE-1321': 'INJECTION', # Prototype Pollution
    'CWE-917': 'INJECTION',  # Expression Language Injection
    'CWE-643': 'INJECTION',  # XPath Injection

    # AUTH
    'CWE-287': 'AUTH',   # Improper Authentication
    'CWE-306': 'AUTH',   # Missing Authentication
    'CWE-798': 'AUTH',   # Hardcoded Credentials
    'CWE-284': 'AUTH',   # Improper Access Control
    'CWE-285': 'AUTH',   # Improper Authorization
    'CWE-269': 'AUTH',   # Privilege Escalation
    'CWE-732': 'AUTH',   # Incorrect Permission Assignment
    'CWE-863': 'AUTH',   # Incorrect Authorization

    # CRYPTO
    'CWE-327': 'CRYPTO', # Use of Broken Algorithm
    'CWE-326': 'CRYPTO', # Inadequate Encryption Strength
    'CWE-295': 'CRYPTO', # Certificate Validation
    'CWE-320': 'CRYPTO', # Key Management
    'CWE-338': 'CRYPTO', # Weak PRNG

    # INFO_DISCLOSURE
    'CWE-200': 'INFO_DISCLOSURE', # Info Exposure
    'CWE-209': 'INFO_DISCLOSURE', # Error Message Info Leak
    'CWE-532': 'INFO_DISCLOSURE', # Log File Info Leak
    'CWE-598': 'INFO_DISCLOSURE', # Info in Query String

    # MEMORY
    'CWE-120': 'MEMORY', # Buffer Overflow
    'CWE-125': 'MEMORY', # Out-of-bounds Read
    'CWE-787': 'MEMORY', # Out-of-bounds Write
    'CWE-190': 'MEMORY', # Integer Overflow
    'CWE-416': 'MEMORY', # Use After Free
    'CWE-476': 'MEMORY', # NULL Pointer Dereference

    # SSRF_CSRF
    'CWE-918': 'SSRF_CSRF', # SSRF
    'CWE-352': 'SSRF_CSRF', # CSRF

    # PATH_TRAVERSAL
    'CWE-22': 'PATH_TRAVERSAL',  # Path Traversal
    'CWE-23': 'PATH_TRAVERSAL',  # Relative Path Traversal
    'CWE-36': 'PATH_TRAVERSAL',  # Absolute Path Traversal

    # DOS
    'CWE-400': 'DOS', # Resource Exhaustion
    'CWE-770': 'DOS', # Uncontrolled Resource Consumption
    'CWE-834': 'DOS', # Infinite Loop
    'CWE-1333': 'DOS', # Inefficient Regex (ReDoS)

    # DESERIALIZATION
    'CWE-502': 'DESERIALIZATION', # Deserialization of Untrusted Data

    # CONFIG
    'CWE-16': 'CONFIG',  # Configuration
    'CWE-1173': 'CONFIG', # Improper Input Validation

    # CRYPTO (tambahan)
    'CWE-311': 'CRYPTO', # Missing Encryption
    'CWE-312': 'CRYPTO', # Cleartext Storage
}

# ─────────────────────────────────────────────────────────────────
# Tipe Temuan RedSim → CWE Group (fallback jika CWE ID tidak ada)
# ─────────────────────────────────────────────────────────────────
TIPE_TO_CWE_GROUP = {
    'injeksi': 'INJECTION',
    'autentikasi_lemah': 'AUTH',
    'otorisasi_lemah': 'AUTH',
    'kriptografi_lemah': 'CRYPTO',
    'eksposur_data': 'INFO_DISCLOSURE',
    'miskonfigurasi': 'CONFIG',
    'kerentanan': 'OTHER',
    'ketergantungan_rentan': 'OTHER',
    'lainnya': 'OTHER',
}

# ─────────────────────────────────────────────────────────────────
# Severity LLM → CVSS vector proxy
# Mapping dari tingkat_keparahan RedSim (Indonesian) ke fitur CVSS
# ─────────────────────────────────────────────────────────────────
# Format: (attack_vector, attack_complexity, privileges_required, user_interaction)
SEVERITY_TO_CVSS_VECTOR = {
    'kritis': ('NETWORK',  'LOW',  'NONE',   'NONE'),
    'tinggi': ('NETWORK',  'LOW',  'LOW',    'NONE'),
    'sedang': ('NETWORK',  'HIGH', 'LOW',    'REQUIRED'),
    'rendah': ('LOCAL',    'HIGH', 'LOW',    'REQUIRED'),
    'info':   ('LOCAL',    'HIGH', 'HIGH',   'REQUIRED'),
    # English fallback
    'critical': ('NETWORK',  'LOW',  'NONE',   'NONE'),
    'high':     ('NETWORK',  'LOW',  'LOW',    'NONE'),
    'medium':   ('NETWORK',  'HIGH', 'LOW',    'REQUIRED'),
    'low':      ('LOCAL',    'HIGH', 'LOW',    'REQUIRED'),
    'none':     ('LOCAL',    'HIGH', 'HIGH',   'REQUIRED'),
}

# Format: (conf_impact, integ_impact, avail_impact)
SEVERITY_TO_IMPACT = {
    'kritis':   ('HIGH', 'HIGH', 'HIGH'),
    'tinggi':   ('HIGH', 'HIGH', 'LOW'),
    'sedang':   ('LOW',  'HIGH', 'LOW'),
    'rendah':   ('LOW',  'LOW',  'LOW'),
    'info':     ('NONE', 'NONE', 'NONE'),
    # English fallback
    'critical': ('HIGH', 'HIGH', 'HIGH'),
    'high':     ('HIGH', 'HIGH', 'LOW'),
    'medium':   ('LOW',  'HIGH', 'LOW'),
    'low':      ('LOW',  'LOW',  'LOW'),
    'none':     ('NONE', 'NONE', 'NONE'),
}


def _resolve_cwe_group(cwe_id: str, tipe: str, deskripsi: str = '') -> str:
    """Resolusi CWE group dari CWE ID, deskripsi temuan, atau tipe temuan."""
    if cwe_id:
        cwe_normalized = cwe_id.strip().upper()
        if not cwe_normalized.startswith('CWE-'):
            cwe_normalized = f'CWE-{cwe_normalized}'
        group = CWE_TO_GROUP.get(cwe_normalized)
        if group:
            return group

    # Ekstraksi berbasis kata kunci deskripsi teknis
    d = (deskripsi or '').lower()
    if any(k in d for k in ['sql', 'query', 'sqli', 'xpath', 'command injection', 'rce', 'code execution']):
        return 'INJECTION'
    if any(k in d for k in ['cookie', 'session', 'sesi', 'auth', 'login', 'token', 'jwt', 'credential', 'password']):
        return 'AUTH'
    if any(k in d for k in ['header', 'cors', 'csp', 'hsts', 'config', 'konfigurasi', 'options', 'miskonfigurasi']):
        return 'CONFIG'
    if any(k in d for k in ['ssl', 'tls', 'cipher', 'enkripsi', 'crypto', 'certificate', 'sertifikat']):
        return 'CRYPTO'
    if any(k in d for k in ['traversal', 'path', 'lfi', 'rfi', 'directory listing']):
        return 'PATH_TRAVERSAL'
    if any(k in d for k in ['ssrf', 'csrf', 'forgery']):
        return 'SSRF_CSRF'
    if any(k in d for k in ['dos', 'denial of service', 'exhaustion', 'memory leak', 'loop']):
        return 'DOS'
    if any(k in d for k in ['leak', 'bocor', 'exposure', 'disclosure', 'informasi', 'version']):
        return 'INFO_DISCLOSURE'

    # Fallback ke tipe temuan
    tipe_lower = (tipe or 'lainnya').lower().strip()
    return TIPE_TO_CWE_GROUP.get(tipe_lower, 'OTHER')


def _extract_cvss_vector(deskripsi: str, tipe: str, cwe_id: str, severity_raw: str) -> tuple[str, str, str, str]:
    """Ekstraksi metrik CVSS (AV, AC, PR, UI) secara independen dari teks temuan."""
    d = (deskripsi or '').lower()
    t = (tipe or '').lower()

    # 1. Attack Vector (AV)
    if any(k in d for k in ['local file', 'privilege escalation', 'local privilege', 'cron', 'kernel', 'root exploit']):
        av = 'LOCAL'
    elif any(k in d for k in ['wifi', 'bluetooth', 'lan', 'arp', 'adjacent']):
        av = 'ADJACENT_NETWORK'
    elif any(k in d for k in ['physical', 'usb', 'console']):
        av = 'PHYSICAL'
    else:
        av = 'NETWORK'  # Mayoritas temuan web/URL adalah NETWORK

    # 2. Attack Complexity (AC)
    if any(k in d for k in ['race condition', 'timing attack', 'heap spray', 'brute force', 'bypass waf', 'kompleks']):
        ac = 'HIGH'
    elif any(k in d for k in ['missing', 'tidak ada', 'unauthenticated', 'default', 'direct', 'tanpa proteksi', 'langsung']):
        ac = 'LOW'
    else:
        ac = 'LOW' if severity_raw in ['kritis', 'tinggi', 'critical', 'high'] else 'HIGH'

    # 3. Privileges Required (PR)
    if any(k in d for k in ['admin', 'root', 'superuser', 'internal network only', 'privilege']):
        pr = 'HIGH'
    elif any(k in d for k in ['authenticated', 'user login', 'member', 'sesi login']):
        pr = 'LOW'
    elif any(k in d for k in ['unauthenticated', 'pre-auth', 'public', 'tanpa autentikasi', 'tanpa login', 'header', 'cookie']):
        pr = 'NONE'
    else:
        pr = 'NONE' if severity_raw in ['kritis', 'critical'] else 'LOW'

    # 4. User Interaction (UI)
    if any(k in d for k in ['xss', 'clickjacking', 'csrf', 'phishing', 'social engineering', 'korban', 'klik', 'browser']):
        ui = 'REQUIRED'
    elif any(k in d for k in ['rce', 'sql injection', 'sqli', 'ssrf', 'unauthenticated remote', 'direct']):
        ui = 'NONE'
    else:
        ui = 'NONE' if severity_raw in ['kritis', 'critical'] else 'REQUIRED'

    return av, ac, pr, ui


def _extract_impacts(deskripsi: str, tipe: str, severity_raw: str) -> tuple[str, str, str]:
    """Ekstraksi dampak Conf/Integ/Avail dari semantik deskripsi temuan."""
    d = (deskripsi or '').lower()

    # RCE / Takeover penuh
    if any(k in d for k in ['rce', 'remote code execution', 'command execution', 'takeover', 'full control', 'shell']):
        return ('HIGH', 'HIGH', 'HIGH')

    # Data leak / SQL Injection
    if any(k in d for k in ['sql injection', 'sqli', 'database leak', 'dump', 'credential leak', 'password leak']):
        return ('HIGH', 'HIGH', 'LOW')

    # XSS / Defacement / Tampering
    if any(k in d for k in ['xss', 'deface', 'tamper', 'manipulasi', 'cross-site scripting']):
        return ('LOW', 'HIGH', 'NONE')

    # DoS / Resource Exhaustion
    if any(k in d for k in ['dos', 'denial of service', 'crash', 'exhaustion', 'loop']):
        return ('NONE', 'NONE', 'HIGH')

    # Missing Security Header / Informational / Cookie Flags
    if any(k in d for k in ['missing header', 'header tidak ditemukan', 'httponly', 'samesite', 'hsts', 'csp', 'x-frame-options']):
        return ('LOW', 'LOW', 'NONE')

    # Fallback ke proxy default
    return SEVERITY_TO_IMPACT.get(severity_raw, ('LOW', 'LOW', 'LOW'))


def extract_features(temuan: dict, feature_names: list) -> np.ndarray:
    """
    Konversi data Temuan RedSim ke feature vector untuk Random Forest.

    Args:
        temuan: dict dengan keys: tipe, tingkat_keparahan, cwe_id, cve_id,
                deskripsi, tautan_referensi
        feature_names: list of 47 feature names dari feature_names.txt

    Returns:
        pd.DataFrame shape (1, 47) — siap untuk model.predict()
    """
    row = {f: 0.0 for f in feature_names}

    severity_raw = (temuan.get('tingkat_keparahan') or 'sedang').lower().strip()
    cwe_id = (temuan.get('cwe_id') or '').strip()
    tipe = (temuan.get('tipe') or 'lainnya').strip()
    deskripsi = (temuan.get('deskripsi') or '')
    refs = (temuan.get('tautan_referensi') or '')

    # ─── Numeric features ───────────────────────────────────────
    row['num__cwe_count'] = 1.0 if cwe_id else 0.0
    row['num__ref_count'] = float(refs.count('http'))
    row['num__desc_length'] = float(len(deskripsi))

    now = datetime.now()
    row['num__pub_year'] = float(now.year)
    row['num__pub_month'] = float(now.month)
    row['num__pub_quarter'] = float((now.month - 1) // 3 + 1)
    row['num__pub_dayofweek'] = float(now.weekday())

    # ─── Categorical: vuln_status ────────────────────────────────
    if 'cat__vuln_status_Analyzed' in row:
        row['cat__vuln_status_Analyzed'] = 1.0

    # ─── Categorical: CVSS vector dari semantik temuan ───────────
    av, ac, pr, ui = _extract_cvss_vector(deskripsi, tipe, cwe_id, severity_raw)

    av_key = f'cat__attack_vector_{av}'
    if av_key in row:
        row[av_key] = 1.0

    ac_key = f'cat__attack_complexity_{ac}'
    if ac_key in row:
        row[ac_key] = 1.0

    pr_key = f'cat__privileges_required_{pr}'
    if pr_key in row:
        row[pr_key] = 1.0

    ui_key = f'cat__user_interaction_{ui}'
    if ui_key in row:
        row[ui_key] = 1.0

    # ─── Categorical: Impact dari semantik temuan ─────────────────
    conf_impact, integ_impact, avail_impact = _extract_impacts(deskripsi, tipe, severity_raw)

    for key, val in [
        (f'cat__conf_impact_{conf_impact}', 1.0),
        (f'cat__integ_impact_{integ_impact}', 1.0),
        (f'cat__avail_impact_{avail_impact}', 1.0),
    ]:
        if key in row:
            row[key] = val

    # ─── Categorical: CWE Group ──────────────────────────────────
    cwe_group = _resolve_cwe_group(cwe_id, tipe, deskripsi)
    cwe_group_key = f'cat__cwe_group_{cwe_group}'
    if cwe_group_key in row:
        row[cwe_group_key] = 1.0
    elif 'cat__cwe_group_OTHER' in row:
        row['cat__cwe_group_OTHER'] = 1.0

    # ─── Boolean features ────────────────────────────────────────
    desc_lower = deskripsi.lower()
    row['bool__has_config'] = 1.0 if any(
        w in desc_lower for w in ['config', 'konfigurasi', 'setting', 'environment', 'header']
    ) else 0.0

    row['bool__has_exploit_ref'] = 1.0 if any(
        w in desc_lower for w in [
            'exploit', 'poc', 'payload', 'attack', 'serangan',
            'eksploit', 'weaponized', 'aktif dieksploitasi'
        ]
    ) else 0.0

    # ─── Bangun DataFrame sesuai urutan feature_names ────────────
    # Pakai DataFrame agar feature names dikenali model (no UserWarning)
    feature_dict = {f: [row.get(f, 0.0)] for f in feature_names}
    df = pd.DataFrame(feature_dict)

    logger.debug(f"Feature vector non-zero count: {(df != 0).sum().sum()}")

    return df
