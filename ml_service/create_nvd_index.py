#!/usr/bin/env python3
"""
create_nvd_index.py
===================
Script untuk membuat Pinecone index "redsim-nvd-cve" dengan konfigurasi yang benar.

Jalankan SEKALI sebelum ingest:
    python3 create_nvd_index.py

Spesifikasi Index:
  - Name      : redsim-nvd-cve
  - Dimension : 768 (sesuai multilingual-e5-base)
  - Metric    : cosine
  - Cloud     : aws
  - Region    : us-east-1
  - Type      : Serverless (free tier)
"""

import os, time, sys
import requests

API_KEY    = os.environ.get("PINECONE_API_KEY", "pcsk_3iVkZ9_73GoYddSMpE6SHqmCUSQs4WRX7RnShuqt2CMqeKytqYebc5aVKUBwBqVjFu6u1A")
INDEX_NAME = os.environ.get("PINECONE_NVD_INDEX", "redsim-nvd-cve")
BASE_URL   = "https://api.pinecone.io"

HEADERS = {
    "Api-Key": API_KEY,
    "Content-Type": "application/json",
    "X-Pinecone-API-Version": "2025-04",
}

def check_index_exists():
    r = requests.get(f"{BASE_URL}/indexes/{INDEX_NAME}", headers=HEADERS, timeout=15)
    return r.status_code == 200, r.json() if r.status_code == 200 else None

def create_index():
    payload = {
        "name": INDEX_NAME,
        "dimension": 768,
        "metric": "cosine",
        "spec": {
            "serverless": {
                "cloud": "aws",
                "region": "us-east-1"
            }
        }
    }
    r = requests.post(f"{BASE_URL}/indexes", headers=HEADERS, json=payload, timeout=30)
    return r.status_code in (200, 201), r.json()

def wait_until_ready(max_wait=120):
    print("⏳ Menunggu index siap...", end="", flush=True)
    for _ in range(max_wait // 5):
        time.sleep(5)
        r = requests.get(f"{BASE_URL}/indexes/{INDEX_NAME}", headers=HEADERS, timeout=15)
        if r.status_code == 200:
            data = r.json()
            status = data.get("status", {})
            if status.get("ready"):
                print(" ✅ Ready!")
                return True, data
        print(".", end="", flush=True)
    print(" ⚠️ Timeout")
    return False, None

def main():
    print("=" * 55)
    print("  Pinecone Index Creator — redsim-nvd-cve")
    print("=" * 55)
    print(f"  API Key : {API_KEY[:20]}...")
    print(f"  Index   : {INDEX_NAME}")
    print(f"  Dim     : 768 | Metric: cosine | Cloud: aws/us-east-1")
    print()

    # Cek apakah sudah ada
    exists, info = check_index_exists()
    if exists:
        status = info.get("status", {})
        host   = info.get("host", "N/A")
        print(f"✅ Index '{INDEX_NAME}' sudah ada!")
        print(f"   Host   : {host}")
        print(f"   Status : {'Ready' if status.get('ready') else 'Not Ready'}")
        print(f"   Dim    : {info.get('dimension')}")
        return 0

    # Buat index baru
    print(f"🔨 Membuat index '{INDEX_NAME}'...")
    ok, resp = create_index()
    if not ok:
        print(f"❌ Gagal membuat index: {resp}")
        return 1

    print(f"   Response: {resp.get('status', resp)}")

    # Tunggu sampai ready
    ready, info = wait_until_ready()
    if ready:
        print(f"   Host : {info.get('host', 'N/A')}")
        print()
        print("✅ Index siap digunakan! Lanjutkan dengan:")
        print("   php artisan nvd:ingest-local --year=2024")
        print("   php artisan nvd:ingest-local --year=2025")
        print("   php artisan nvd:ingest-local --year=2026")
        return 0
    else:
        print("⚠️ Index dibuat tapi belum ready. Coba cek di dashboard Pinecone.")
        return 1

if __name__ == "__main__":
    sys.exit(main())
