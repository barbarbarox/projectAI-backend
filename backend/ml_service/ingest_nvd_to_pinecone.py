#!/usr/bin/env python3
"""
ingest_nvd_to_pinecone.py  (v3 — sentence-transformers + vectors API)
======================================================================
Ingest NVD CVE dataset dari file JSON lokal ke Pinecone redsim-nvd-cve.

Menggunakan:
  - ijson: streaming parse file JSON 200MB+ tanpa OOM
  - sentence-transformers (multilingual-e5-base): generate embedding 768-dim
  - Pinecone /vectors/upsert: upload vector dense

Jalankan:
  .venv/bin/python3 ingest_nvd_to_pinecone.py --year 2024 2025 2026
"""

import argparse, json, os, sys, time, traceback
import ijson, requests
import numpy as np

# ─── Config ──────────────────────────────────────────────────────────────────
PINECONE_NVD_API_KEY = os.environ.get(
    "PINECONE_NVD_API_KEY",
    "pcsk_3iVkZ9_73GoYddSMpE6SHqmCUSQs4WRX7RnShuqt2CMqeKytqYebc5aVKUBwBqVjFu6u1A"
)
PINECONE_NVD_INDEX = os.environ.get("PINECONE_NVD_INDEX", "redsim-nvd-cve")
DATASET_PATH = os.environ.get(
    "NVD_DATASET_PATH",
    "/home/abarox/Desktop/projectAI/matkul-kecerdasan-buatan/dataset"
)
PINECONE_BASE_URL = "https://api.pinecone.io"
EMBEDDING_MODEL   = "intfloat/multilingual-e5-base"  # 768-dim, sudah ada di cache

# ─── Lazy-load model ─────────────────────────────────────────────────────────
_model = None

def get_model():
    global _model
    if _model is None:
        from sentence_transformers import SentenceTransformer
        print(f"   🤖 Loading embedding model: {EMBEDDING_MODEL}")
        _model = SentenceTransformer(EMBEDDING_MODEL)
        dim = getattr(_model, 'get_embedding_dimension', _model.get_sentence_embedding_dimension)()
        print(f"   ✅ Model ready (dim={dim})")
    return _model

def embed_texts(texts: list[str]) -> list[list[float]]:
    model  = get_model()
    # multilingual-e5 butuh prefix "query: " untuk search, "passage: " untuk document
    prefixed = [f"passage: {t}" for t in texts]
    vecs = model.encode(prefixed, normalize_embeddings=True, show_progress_bar=False)
    return vecs.tolist()

# ─── Pinecone host ────────────────────────────────────────────────────────────
_host_cache = {}

def get_pinecone_host() -> str:
    if PINECONE_NVD_INDEX in _host_cache:
        return _host_cache[PINECONE_NVD_INDEX]
    r = requests.get(
        f"{PINECONE_BASE_URL}/indexes/{PINECONE_NVD_INDEX}",
        headers={"Api-Key": PINECONE_NVD_API_KEY},
        timeout=15
    )
    r.raise_for_status()
    host = f"https://{r.json()['host']}"
    _host_cache[PINECONE_NVD_INDEX] = host
    print(f"   🔗 Pinecone host: {host}", flush=True)
    return host


# ─── Upsert batch via /vectors/upsert ────────────────────────────────────────
def upsert_batch(records: list, host: str) -> bool:
    """Upsert ke Pinecone menggunakan /vectors/upsert (dense vector API)."""
    texts = [r["text"] for r in records]
    try:
        vectors_val = embed_texts(texts)
    except Exception as e:
        print(f"   ❌ Embedding error: {e}", flush=True)
        return False

    vectors = []
    for rec, vec in zip(records, vectors_val):
        # Metadata hanya bisa string/number/bool di Pinecone
        meta = {k: v for k, v in rec.get("metadata", {}).items() if v is not None}
        # Simpan text di metadata untuk retrieval
        meta["text"] = rec["text"][:2000]  # max 2000 chars
        vectors.append({"id": rec["id"], "values": vec, "metadata": meta})

    payload = {"vectors": vectors, "namespace": "default"}

    for attempt in range(3):
        try:
            r = requests.post(
                f"{host}/vectors/upsert",
                headers={
                    "Api-Key":      PINECONE_NVD_API_KEY,
                    "Content-Type": "application/json",
                },
                json=payload,
                timeout=60,
            )
            if r.status_code in (200, 201):
                return True
            print(f"   ⚠️  Upsert HTTP {r.status_code}: {r.text[:200]}", flush=True)
        except Exception as e:
            print(f"   ⚠️  Upsert attempt {attempt+1} error: {e}", flush=True)
        time.sleep(2 * (attempt + 1))
    return False


# ─── Metric extractors ────────────────────────────────────────────────────────
def extract_severity(metrics: dict) -> str:
    for key in ("cvssMetricV31", "cvssMetricV30", "cvssMetricV2"):
        if metrics.get(key):
            m = metrics[key][0]
            return m.get("cvssData", {}).get("baseSeverity") or m.get("baseSeverity", "NONE")
    return "NONE"

def extract_cvss_score(metrics: dict):
    for key in ("cvssMetricV31", "cvssMetricV30", "cvssMetricV2"):
        if metrics.get(key):
            m = metrics[key][0]
            return m.get("cvssData", {}).get("baseScore") or m.get("baseScore")
    return None

def extract_cvss_vector(metrics: dict):
    for key in ("cvssMetricV31", "cvssMetricV30"):
        if metrics.get(key):
            return metrics[key][0].get("cvssData", {}).get("vectorString")
    return None


# ─── Format satu CVE ─────────────────────────────────────────────────────────
def format_cve(vuln: dict):
    cve    = vuln.get("cve", {})
    cve_id = cve.get("id")
    if not cve_id:
        return None

    published = cve.get("published", "")[:10]
    modified  = cve.get("lastModified", "")[:10]

    desc = ""
    for d in cve.get("descriptions", []):
        if d.get("lang") == "en":
            desc = d.get("value", "").strip()
            break
    if not desc:
        return None

    metrics     = cve.get("metrics", {})
    severity    = extract_severity(metrics)
    cvss_score  = extract_cvss_score(metrics)
    cvss_vector = extract_cvss_vector(metrics)

    cwe_ids = set()
    for w in cve.get("weaknesses", []):
        for d in w.get("description", []):
            val = d.get("value", "")
            if d.get("lang") == "en" and val.startswith("CWE-"):
                cwe_ids.add(val)
    cwe_str = ", ".join(sorted(cwe_ids)) or "N/A"

    products = []
    for config in cve.get("configurations", []):
        for node in config.get("nodes", []):
            for cpe in node.get("cpeMatch", []):
                if cpe.get("vulnerable"):
                    parts = cpe.get("criteria", "").split(":")
                    if len(parts) >= 5:
                        entry = f"{parts[3]}/{parts[4]}"
                        if len(parts) > 5 and parts[5] not in ("*", ""):
                            entry += f" {parts[5]}"
                        products.append(entry)
                if len(products) >= 5:
                    break

    refs = [r["url"] for r in cve.get("references", [])[:3] if r.get("url")]

    cvss_info = f"CVSS Score: {cvss_score}" if cvss_score else "CVSS: N/A"
    if cvss_vector:
        cvss_info += f" | Vector: {cvss_vector}"

    text  = f"CVE ID: {cve_id}\n"
    text += f"Severity: {severity}\n"
    text += f"{cvss_info}\n"
    text += f"CWE: {cwe_str}\n"
    text += f"Published: {published} | Modified: {modified}\n"
    text += f"Description: {desc}"
    if products:
        text += f"\nAffected: {'; '.join(dict.fromkeys(products))}"
    if refs:
        text += f"\nReferences: {'; '.join(refs)}"

    return {
        "id":   cve_id.lower().replace("-", "_"),
        "text": text,
        "metadata": {
            "cve_id":     cve_id,
            "severity":   severity.upper(),
            "cvss_score": float(cvss_score) if cvss_score is not None else None,
            "cwe_id":     cwe_str,
            "published":  published,
            "source":     "nvd-cve",
        },
    }


# ─── Streaming process file ───────────────────────────────────────────────────
def process_file(file_path: str, year: str, severity_filter, batch_size: int,
                 dry_run: bool, host: str) -> dict:
    stats = {"processed": 0, "uploaded": 0, "skipped": 0, "failed": 0}

    file_mb = os.path.getsize(file_path) / 1024 / 1024
    print(f"\n📂 File: nvdcve-2.0-{year}.json ({file_mb:.1f} MB) [streaming+embed]", flush=True)

    batch = []
    done  = 0
    start = time.time()

    with open(file_path, "rb") as f:
        for vuln in ijson.items(f, "vulnerabilities.item"):
            record = format_cve(vuln)
            if not record:
                stats["skipped"] += 1
                continue

            if severity_filter and record["metadata"]["severity"] != severity_filter:
                stats["skipped"] += 1
                continue

            batch.append(record)
            stats["processed"] += 1
            done += 1

            if len(batch) >= batch_size:
                if not dry_run:
                    ok = upsert_batch(batch, host)
                    stats["uploaded" if ok else "failed"] += len(batch)
                    time.sleep(0.1)
                else:
                    stats["uploaded"] += len(batch)
                batch = []

                if done % 1000 == 0:
                    elapsed = time.time() - start
                    rate    = done / elapsed if elapsed > 0 else 0
                    print(f"   ⏳ {done:,} CVEs processed | uploaded: {stats['uploaded']:,} | "
                          f"{rate:.0f} CVE/s", flush=True)

    if batch:
        if not dry_run:
            ok = upsert_batch(batch, host)
            stats["uploaded" if ok else "failed"] += len(batch)
        else:
            stats["uploaded"] += len(batch)

    elapsed = time.time() - start
    print(f"   ✅ Tahun {year}: {stats['uploaded']:,} uploaded | "
          f"{stats['failed']:,} failed | {elapsed:.0f}s", flush=True)
    return stats


# ─── Main ─────────────────────────────────────────────────────────────────────
def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--year",       nargs="+", default=["2024", "2025", "2026"])
    parser.add_argument("--severity",   default=None)
    parser.add_argument("--batch-size", type=int, default=32)  # 32 optimal untuk embedding
    parser.add_argument("--dry-run",    action="store_true")
    args = parser.parse_args()

    sev = args.severity.upper() if args.severity else None

    print("\n" + "═" * 60)
    print("  NVD CVE Ingest (streaming+embed) → Pinecone redsim-nvd-cve")
    print("═" * 60)
    print(f"  Model    : {EMBEDDING_MODEL} (768-dim)")
    print(f"  Years    : {', '.join(args.year)}")
    print(f"  Batch    : {args.batch_size} | Severity: {sev or 'ALL'}")
    print(f"  Dry Run  : {'YES' if args.dry_run else 'NO'}")
    print()

    if args.dry_run:
        host = "http://dry-run"
    else:
        print("🔗 Resolving Pinecone host...")
        try:
            host = get_pinecone_host()
        except Exception as e:
            print(f"❌ {e}")
            sys.exit(1)

    total_all = {"processed": 0, "uploaded": 0, "skipped": 0, "failed": 0}
    global_start = time.time()

    for year in args.year:
        fp = f"{DATASET_PATH}/nvdcve-2.0-{year}.json/nvdcve-2.0-{year}.json"
        if not os.path.exists(fp):
            print(f"\n⚠️  File tidak ditemukan: {fp}")
            continue
        try:
            stats = process_file(fp, year, sev, args.batch_size, args.dry_run, host)
            for k in total_all:
                total_all[k] += stats[k]
        except Exception as e:
            print(f"\n❌ Error tahun {year}: {e}")
            traceback.print_exc()

    elapsed = time.time() - global_start
    elapsed_str = f"{elapsed/60:.1f} menit" if elapsed > 60 else f"{elapsed:.1f}s"
    print("\n" + "═" * 60)
    print(f"  SELESAI dalam {elapsed_str}")
    print(f"  Diproses  : {total_all['processed']:,}")
    print(f"  Uploaded  : {total_all['uploaded']:,}")
    if total_all['failed']:
        print(f"  Gagal     : {total_all['failed']:,}")
    print("═" * 60 + "\n")


if __name__ == "__main__":
    main()
