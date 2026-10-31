#!/bin/bash
# run.sh — Startup script untuk RedSim ML Prediction Service
# Jalankan dari direktori backend/: bash ml_service/run.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "======================================"
echo "  RedSim ML Prediction Service"
echo "======================================"
echo ""

# Cek Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 tidak ditemukan. Install dulu."
    exit 1
fi

# Cek model file
MODEL_PATH="models/random_forest_model.pkl"
if [ ! -f "$MODEL_PATH" ]; then
    echo "❌ Model tidak ditemukan di: $MODEL_PATH"
    echo "   Pastikan random_forest_model.pkl ada di folder models/"
    exit 1
fi

MODEL_SIZE=$(du -h "$MODEL_PATH" | cut -f1)
echo "✅ Model ditemukan: $MODEL_SIZE"
echo ""

# Buat venv jika belum ada
if [ ! -d ".venv" ]; then
    echo "📦 Membuat virtual environment..."
    python3 -m venv .venv
fi

# Aktifkan venv
source .venv/bin/activate

# Install dependencies
echo "📦 Installing dependencies..."
pip install -r requirements.txt -q
echo "✅ Dependencies terinstall"
echo ""

echo "🚀 Menjalankan ML service di http://127.0.0.1:5001"
echo "   Docs: http://127.0.0.1:5001/docs"
echo "   Stop: Ctrl+C"
echo ""

uvicorn main:app --host 127.0.0.1 --port 5001 --workers 1 --log-level info
