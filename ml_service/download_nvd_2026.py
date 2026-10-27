#!/usr/bin/env python3
"""
download_nvd_2026.py
====================
Download dataset NVD CVE 2026 TERBARU dari NVD API v2.0 secara aman dan modular.
NVD API v2.0 membatasi rentang tanggal maksimum 120 hari per request.
Script ini:
  - Membagi request menjadi chunk 90-hari
  - Mendukung auto-checkpoint & resume (jika koneksi terputus)
  - Menyimpan ke file temporary terlebih dahulu agar tidak merusak proses ingest yang sedang aktif
  - Melakukan atomic replacement jika file target tidak sedang dikunci proses lain

Jalankan:
  cd /home/abarox/Desktop/projectAI/backend/ml_service
  .venv/bin/python3 download_nvd_2026.py
"""

import json, os, sys, time, urllib.request, urllib.error, subprocess
from datetime import datetime, timedelta

# ─── Konfigurasi ──────────────────────────────────────────────────────────────
API_KEY          = "2edd699e-fec1-48b5-9e54-6c9044c6303b"
NVD_BASE         = "https://services.nvd.nist.gov/rest/json/cves/2.0"
DATASET_PATH     = "/home/abarox/Desktop/projectAI/matkul-kecerdasan-buatan/dataset"
OUT_DIR          = f"{DATASET_PATH}/nvdcve-2.0-2026.json"
OUT_FILE         = f"{OUT_DIR}/nvdcve-2.0-2026.json"
OUT_LATEST       = f"{OUT_DIR}/nvdcve-2.0-2026-latest.json"
CHECKPOINT_FILE  = f"{OUT_DIR}/.checkpoint_2026.json"
RESULTS_PER_PAGE = 500   # 500 optimal untuk response stabil
CHUNK_DAYS       = 90    # Maksimum NVD API: 120 hari; pakai 90 hari
REQUEST_TIMEOUT  = 120   # Timeout per request (detik)


def fmt_date(dt: datetime) -> str:
    """Format datetime ke format NVD API (tanpa timezone)."""
    return dt.strftime("%Y-%m-%dT%H:%M:%S.000")


def is_file_busy(filepath: str) -> bool:
    """Cek apakah file sedang dibuka/dibaca oleh proses lain (misal ingest)."""
    if not os.path.exists(filepath):
        return False
    try:
        res = subprocess.run(["fuser", filepath], capture_output=True, text=True)
        return bool(res.stdout.strip())
    except Exception:
        return False


def fetch_page(start_date: str, end_date: str, start_index: int) -> dict | None:
    url = (f"{NVD_BASE}?pubStartDate={start_date}&pubEndDate={end_date}"
           f"&startIndex={start_index}&resultsPerPage={RESULTS_PER_PAGE}")
    req = urllib.request.Request(
        url,
        headers={"Accept": "application/json", "apiKey": API_KEY}
    )
    for attempt in range(4):
        try:
            with urllib.request.urlopen(req, timeout=REQUEST_TIMEOUT) as resp:
                if resp.status == 200:
                    return json.loads(resp.read().decode("utf-8"))
                print(f"  ⚠️  HTTP {resp.status} | attempt {attempt+1}/4")
        except urllib.error.HTTPError as e:
            body = e.read().decode()
            print(f"  ⚠️  HTTP {e.code} | attempt {attempt+1}/4 | {body[:100]}")
        except Exception as e:
            print(f"  ⚠️  Error: {type(e).__name__}: {e} | attempt {attempt+1}/4")
        wait = 5 * (attempt + 1)
        print(f"     Retry dalam {wait}s...")
        time.sleep(wait)
    return None


def fetch_chunk(start_dt: datetime, end_dt: datetime, resume_index: int = 0) -> list:
    """Fetch semua CVE dalam satu chunk (maks 120 hari) dengan pagination."""
    start_str = fmt_date(start_dt)
    end_str   = fmt_date(end_dt)
    chunk_label = f"{start_dt.strftime('%Y-%m-%d')} → {end_dt.strftime('%Y-%m-%d')}"
    print(f"\n  📦 Chunk: {chunk_label}")

    first = fetch_page(start_str, end_str, 0)
    if not first:
        print(f"  ❌ Gagal fetch header chunk {chunk_label}")
        return []

    total = first.get("totalResults", 0)
    items = list(first.get("vulnerabilities", [])) if resume_index == 0 else []
    print(f"     Total di API: {total:,} CVEs | Memulai dari index: {resume_index}")

    start_index = RESULTS_PER_PAGE if resume_index == 0 else resume_index
    page = (start_index // RESULTS_PER_PAGE) + 1

    while start_index < total:
        time.sleep(0.7)  # Rate limiting sesuai kebijakan NIST NVD API
        data = fetch_page(start_str, end_str, start_index)
        if not data:
            print(f"  ⚠️  Halaman {page} (index {start_index}) gagal")
            start_index += RESULTS_PER_PAGE
            page += 1
            continue
        batch = data.get("vulnerabilities", [])
        items.extend(batch)
        print(f"     Halaman {page}: +{len(batch):,} | Total didownload: {len(items):,}/{total:,}")
        start_index += RESULTS_PER_PAGE
        page += 1

    return items


def main():
    print("═" * 60)
    print("  Download NVD CVE 2026 (Terbaru dari NVD API v2.0)")
    print("═" * 60)
    print(f"  API Key  : {API_KEY[:20]}...")
    print(f"  Target   : {OUT_LATEST}")
    print(f"  Chunk    : {CHUNK_DAYS} hari per request")
    print()

    os.makedirs(OUT_DIR, exist_ok=True)

    # Cek checkpoint jika ada proses download sebelumnya yang terhenti
    all_vulns = []
    seen_ids = set()

    if os.path.exists(CHECKPOINT_FILE):
        try:
            with open(CHECKPOINT_FILE, "r", encoding="utf-8") as f:
                checkpoint_data = json.load(f)
                all_vulns = checkpoint_data.get("vulnerabilities", [])
                for v in all_vulns:
                    cid = v.get("cve", {}).get("id")
                    if cid:
                        seen_ids.add(cid)
            print(f"🔄 Checkpoint ditemukan: Melanjutkan dengan {len(all_vulns):,} CVE yang sudah diunduh.")
        except Exception as e:
            print(f"⚠️ Checkpoint rusak ({e}), memulai dari awal.")

    year_start = datetime(2026, 1, 1)
    today      = datetime.now()

    chunk_start = year_start
    while chunk_start < today:
        chunk_end = min(chunk_start + timedelta(days=CHUNK_DAYS - 1), today)
        chunk_str = chunk_start.strftime('%Y-%m-%d')

        items = fetch_chunk(chunk_start, chunk_end)

        # Deduplicate
        added = 0
        for item in items:
            cve_id = item.get("cve", {}).get("id")
            if cve_id and cve_id not in seen_ids:
                seen_ids.add(cve_id)
                all_vulns.append(item)
                added += 1

        print(f"     +{added:,} CVE baru ditambahkan ke koleksi. Total saat ini: {len(all_vulns):,}")

        # Simpan checkpoint per chunk
        try:
            with open(CHECKPOINT_FILE, "w", encoding="utf-8") as f:
                json.dump({"timestamp": datetime.now().isoformat(), "vulnerabilities": all_vulns}, f)
            print(f"     💾 Checkpoint tersimpan ({len(all_vulns):,} CVE)")
        except Exception as e:
            print(f"     ⚠️ Gagal simpan checkpoint: {e}")

        chunk_start = chunk_end + timedelta(days=1)
        time.sleep(1)

    print(f"\n✅ Seluruh chunk 2026 selesai diunduh: {len(all_vulns):,} CVE unik")

    # Format JSON NVD v2.0 standar
    output = {
        "resultsPerPage": len(all_vulns),
        "startIndex":     0,
        "totalResults":   len(all_vulns),
        "format":         "NVD_CVE",
        "version":        "2.0",
        "timestamp":      datetime.now().strftime("%Y-%m-%dT%H:%M:%S.000"),
        "vulnerabilities": all_vulns,
    }

    # Tulis ke file temporary terbaru
    temp_target = f"{OUT_LATEST}.tmp"
    with open(temp_target, "w", encoding="utf-8") as f:
        json.dump(output, f, ensure_ascii=False, separators=(",", ":"))

    os.replace(temp_target, OUT_LATEST)
    file_mb = os.path.getsize(OUT_LATEST) / 1024 / 1024
    print(f"✅ Berhasil disimpan ke: {OUT_LATEST} ({file_mb:.1f} MB, {len(all_vulns):,} CVEs)")

    # Bersihkan checkpoint
    if os.path.exists(CHECKPOINT_FILE):
        os.remove(CHECKPOINT_FILE)

    # Cek apakah file utama nvdcve-2.0-2026.json sedang aktif digunakan oleh ingest
    if is_file_busy(OUT_FILE):
        print("\n" + "═" * 60)
        print("⚠️ PERHATIAN: File nvdcve-2.0-2026.json saat ini sedang dibaca oleh proses ingest!")
        print("Dataset baru telah disimpan dengan aman di:")
        print(f"   {OUT_LATEST}")
        print("\nSetelah proses ingest selesai, Anda dapat menggantinya dengan perintah:")
        print(f"   mv {OUT_FILE} {OUT_FILE}.old && mv {OUT_LATEST} {OUT_FILE}")
        print("═" * 60 + "\n")
    else:
        # File tidak sedang digunakan, backup dan ganti otomatis
        if os.path.exists(OUT_FILE):
            backup_file = f"{OUT_FILE}.backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}"
            os.rename(OUT_FILE, backup_file)
            print(f"📦 Backup file lama disimpan ke: {backup_file}")

        os.rename(OUT_LATEST, OUT_FILE)
        print(f"🎉 File utama berhasil diperbarui: {OUT_FILE}")

    print("\nUntuk meng-ingest dataset terbaru ke Pinecone:")
    print("  nohup .venv/bin/python3 ingest_nvd_to_pinecone.py --year 2026 > /tmp/ingest_2026_new.log 2>&1 &")


if __name__ == "__main__":
    main()
