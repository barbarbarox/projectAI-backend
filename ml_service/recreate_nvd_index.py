#!/usr/bin/env python3
"""
recreate_nvd_index.py
=====================
Hapus index redsim-nvd-cve yang ada (dense vector biasa),
lalu buat ulang sebagai Inference Index dengan model multilingual-e5-large.

Inference Index = Pinecone yang auto-embed teks → tidak perlu library embedding lokal.

Jalankan SEKALI:
  .venv/bin/python3 recreate_nvd_index.py
"""

import os, time, sys, requests

API_KEY    = "pcsk_3iVkZ9_73GoYddSMpE6SHqmCUSQs4WRX7RnShuqt2CMqeKytqYebc5aVKUBwBqVjFu6u1A"
INDEX_NAME = "redsim-nvd-cve"
BASE_URL   = "https://api.pinecone.io"

HEADERS = {
    "Api-Key": API_KEY,
    "Content-Type": "application/json",
    "X-Pinecone-API-Version": "2025-04",
}

def get_index_info():
    r = requests.get(f"{BASE_URL}/indexes/{INDEX_NAME}", headers=HEADERS, timeout=15)
    if r.status_code == 200:
        return r.json()
    return None

def delete_index():
    r = requests.delete(f"{BASE_URL}/indexes/{INDEX_NAME}", headers=HEADERS, timeout=15)
    return r.status_code in (200, 202, 204)

def create_inference_index():
    """Buat Inference Index — Pinecone auto-embed teks menggunakan multilingual-e5-large."""
    payload = {
        "name": INDEX_NAME,
        "metric": "cosine",
        "spec": {
            "serverless": {
                "cloud": "aws",
                "region": "us-east-1"
            }
        },
        "embed": {
            "model": "multilingual-e5-large",
            "field_map": {
                "text": "text"   # field 'text' di record → di-embed otomatis
            }
        }
    }
    r = requests.post(f"{BASE_URL}/indexes", headers=HEADERS, json=payload, timeout=30)
    return r.status_code in (200, 201), r.json()

def wait_until_ready(max_wait=180):
    print("⏳ Menunggu index siap...", end="", flush=True)
    for _ in range(max_wait // 5):
        time.sleep(5)
        r = requests.get(f"{BASE_URL}/indexes/{INDEX_NAME}", headers=HEADERS, timeout=15)
        if r.status_code == 200:
            data = r.json()
            if data.get("status", {}).get("ready"):
                print(" ✅")
                return True, data
        print(".", end="", flush=True)
    print(" ⚠️ Timeout")
    return False, None

def main():
    print("=" * 58)
    print("  Recreate Pinecone NVD Index → Inference Index")
    print("=" * 58)
    print(f"  API Key : {API_KEY[:20]}...")
    print(f"  Index   : {INDEX_NAME}")
    print(f"  Model   : multilingual-e5-large (auto-embedding)")
    print()

    # Cek info index saat ini
    info = get_index_info()
    if info:
        dim    = info.get("dimension", "?")
        has_embed = "embed" in info.get("spec", {}) or info.get("spec", {}).get("embed")
        itype  = "Inference" if has_embed else "Dense Vector"
        print(f"📋 Index saat ini:")
        print(f"   Tipe      : {itype}")
        print(f"   Dimension : {dim}")
        print(f"   Host      : {info.get('host', 'N/A')}")
        print()
        if has_embed:
            print("✅ Index sudah Inference Index! Tidak perlu recreate.")
            return 0

        # Konfirmasi hapus
        print("⚠️  Index saat ini adalah Dense Vector (dim=768), BUKAN Inference Index.")
        print("   Akan DIHAPUS dan dibuat ulang sebagai Inference Index.")
        print()

    # Hapus index
    print("🗑️  Menghapus index lama...")
    if info and not delete_index():
        print("❌ Gagal menghapus index.")
        return 1
    print("   ✅ Dihapus.")
    time.sleep(3)

    # Buat ulang
    print(f"🔨 Membuat Inference Index '{INDEX_NAME}' (multilingual-e5-large)...")
    ok, resp = create_inference_index()
    if not ok:
        print(f"❌ Gagal membuat index: {resp}")
        return 1
    print(f"   Response: {resp.get('status', resp)}")

    # Tunggu ready
    ready, info = wait_until_ready()
    if ready:
        print()
        print(f"   Host      : {info.get('host', 'N/A')}")
        dim = info.get("dimension", "auto")
        print(f"   Dimension : {dim} (ditentukan oleh model)")
        print()
        print("✅ Inference Index siap! Sekarang jalankan ingest:")
        print("   .venv/bin/python3 ingest_nvd_to_pinecone.py --year 2024 2025")
        print("   .venv/bin/python3 ingest_nvd_to_pinecone.py --year 2026  (setelah download selesai)")
        return 0
    else:
        print("⚠️ Index dibuat tapi belum ready. Cek Pinecone dashboard.")
        return 1

if __name__ == "__main__":
    sys.exit(main())
