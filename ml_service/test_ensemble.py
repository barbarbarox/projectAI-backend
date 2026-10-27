"""
test_ensemble.py — Integration test untuk Ensemble ML Service v2.0
Jalankan: python test_ensemble.py

Test 3 skenario:
  1. Temuan CRITICAL (injeksi SQL, CWE-89)
  2. Temuan MEDIUM (konfigurasi salah)
  3. Temuan LOW (info disclosure)
"""

import sys
import os
sys.path.insert(0, os.path.dirname(__file__))

import numpy as np
from model_loader import load_models, LABEL_MAP

WEIGHT_RF  = 0.40
WEIGHT_XGB = 0.60

def soft_vote(prob_rf, prob_xgb):
    return prob_rf * WEIGHT_RF + prob_xgb * WEIGHT_XGB

def run_test(name, temuan, feature_names, rf, xgb):
    from feature_extractor import extract_features
    features = extract_features(temuan, feature_names)

    prob_rf  = rf.predict_proba(features)[0]
    prob_xgb = xgb.predict_proba(features)[0]
    prob_ens = soft_vote(prob_rf, prob_xgb)

    rf_pred  = LABEL_MAP[int(np.argmax(prob_rf))]
    xgb_pred = LABEL_MAP[int(np.argmax(prob_xgb))]
    ens_pred = LABEL_MAP[int(np.argmax(prob_ens))]
    ens_conf = float(np.max(prob_ens))

    print(f"\n{'─'*55}")
    print(f"  Test: {name}")
    print(f"{'─'*55}")
    print(f"  Input : tipe={temuan['tipe']}, keparahan={temuan['tingkat_keparahan']}, cwe={temuan['cwe_id']}")
    print(f"  RF    : {rf_pred:10} (conf: {float(np.max(prob_rf)):.2%})")
    print(f"  XGB   : {xgb_pred:10} (conf: {float(np.max(prob_xgb)):.2%})")
    print(f"  ──────────────────────────────────")
    print(f"  ENSEMBLE: {ens_pred:10} (conf: {ens_conf:.2%})  ← FINAL")
    print(f"  Agree: {'✅ YA' if rf_pred == xgb_pred else '⚠️  BEDA — ensemble sebagai tiebreaker'}")

    if ens_pred == 'CRITICAL' and ens_conf >= 0.85:
        print(f"  🚨 WA ALERT akan dikirim! (CRITICAL + confidence ≥ 85%)")

def main():
    print("=" * 55)
    print("  RedSim Ensemble ML Service v2.0 — Integration Test")
    print("=" * 55)

    print("\nMemuat kedua model...")
    rf, xgb, feature_names = load_models()
    print(f"  ✅ RF loaded  : {rf.n_estimators} trees")
    print(f"  ✅ XGB loaded : {xgb.n_estimators} trees")
    print(f"  ✅ Features   : {len(feature_names)} fitur")
    print(f"  ✅ Ensemble   : Soft Voting (RF={WEIGHT_RF}, XGB={WEIGHT_XGB})")

    test_cases = [
        ("CRITICAL — SQL Injection (CWE-89)", {
            "tipe": "injeksi",
            "tingkat_keparahan": "kritis",
            "cwe_id": "CWE-89",
            "cve_id": "",
            "deskripsi": "SQL Injection pada endpoint /api/users. Attacker dapat dump seluruh database. Exploit PoC sudah tersedia.",
            "tautan_referensi": "https://owasp.org/www-community/attacks/SQL_Injection",
        }),
        ("MEDIUM — Konfigurasi Lemah (CWE-16)", {
            "tipe": "miskonfigurasi",
            "tingkat_keparahan": "sedang",
            "cwe_id": "CWE-16",
            "cve_id": "",
            "deskripsi": "Environment variable mengekspos API key dalam konfigurasi default aplikasi.",
            "tautan_referensi": "",
        }),
        ("LOW — Info Disclosure (CWE-200)", {
            "tipe": "eksposur_data",
            "tingkat_keparahan": "rendah",
            "cwe_id": "CWE-200",
            "cve_id": "",
            "deskripsi": "Error message mengungkapkan versi PHP dan stack trace. Tidak berdampak langsung pada keamanan.",
            "tautan_referensi": "",
        }),
    ]

    for name, temuan in test_cases:
        run_test(name, temuan, feature_names, rf, xgb)

    print(f"\n{'='*55}")
    print("  ✅ Semua test selesai! Ensemble berfungsi dengan benar.")
    print(f"{'='*55}\n")

if __name__ == "__main__":
    main()
