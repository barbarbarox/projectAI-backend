"""
model_loader.py — Dual-Model Singleton Loader (RF + XGBoost Ensemble)

Memuat Random Forest (Minggu 5) dan XGBoost (Minggu 6) sekali ke memory
saat startup FastAPI. Keduanya digunakan secara bersamaan untuk Soft Voting Ensemble.

Memory footprint estimasi:
  - RandomForest (200 trees, 47 fitur): ~50–100 MB
  - XGBoost     (300 trees, 47 fitur): ~4.4 MB
  - Total:  ~55–105 MB (aman pada VPS 4 GB RAM)
"""

import pickle
import os
import logging

logger = logging.getLogger(__name__)

# ─── Singleton state ──────────────────────────────────────────────────────────
_rf_model  = None
_xgb_model = None
_feature_names = None

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# ─── Path model ───────────────────────────────────────────────────────────────
RF_MODEL_PATH = os.path.join(
    BASE_DIR,
    "models/random_forest_model.pkl"
)

XGB_MODEL_PATH = os.path.join(
    BASE_DIR,
    "models/xgboost_model.pkl"
)

FEATURE_NAMES_PATH = os.path.join(
    BASE_DIR,
    "models/feature_names.txt"
)

# ─── Label mapping ────────────────────────────────────────────────────────────
# CRITICAL=0, HIGH=1, LOW=2, MEDIUM=3, NONE=4  (sesuai notebook Minggu 5 & 6)
LABEL_MAP    = {0: 'CRITICAL', 1: 'HIGH', 2: 'LOW', 3: 'MEDIUM', 4: 'NONE'}
LABEL_TO_IDX = {v: k for k, v in LABEL_MAP.items()}

# Urutan prioritas severity (index makin besar = makin rendah bahayanya)
SEVERITY_ORDER = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'NONE']


def _load_pickle(path: str, label: str):
    """Helper: load dan validasi file pickle."""
    resolved = os.path.realpath(path)
    if not os.path.exists(resolved):
        raise FileNotFoundError(
            f"[{label}] Model tidak ditemukan di: {resolved}\n"
            f"Pastikan file pkl sudah ada di folder yang benar."
        )
    size_mb = os.path.getsize(resolved) / 1024 / 1024
    logger.info(f"[ModelLoader] Loading {label} dari: {resolved} ({size_mb:.1f} MB)")
    with open(resolved, 'rb') as f:
        obj = pickle.load(f)
    return obj


def load_models():
    """
    Load kedua model dan feature names ke memory (singleton pattern).
    Dipanggil saat startup FastAPI via lifespan — thread-safe untuk single process.

    Returns:
        tuple: (rf_model, xgb_model, feature_names)
    """
    global _rf_model, _xgb_model, _feature_names

    # Return cache jika sudah di-load
    if _rf_model is not None and _xgb_model is not None and _feature_names is not None:
        return _rf_model, _xgb_model, _feature_names

    # ─── Load Random Forest ───────────────────────────────────────────────────
    _rf_model = _load_pickle(RF_MODEL_PATH, "RandomForest")
    logger.info(
        f"[ModelLoader] RF loaded: {_rf_model.n_estimators} trees, "
        f"{_rf_model.n_features_in_} fitur, {_rf_model.n_classes_} kelas"
    )

    # ─── Load XGBoost ─────────────────────────────────────────────────────────
    _xgb_model = _load_pickle(XGB_MODEL_PATH, "XGBoost")
    n_trees = getattr(_xgb_model, 'n_estimators', '?')
    n_feats = getattr(_xgb_model, 'n_features_in_', '?')
    logger.info(f"[ModelLoader] XGB loaded: {n_trees} trees, {n_feats} fitur")

    # ─── Load Feature Names ───────────────────────────────────────────────────
    feat_path = os.path.realpath(FEATURE_NAMES_PATH)
    if not os.path.exists(feat_path):
        raise FileNotFoundError(f"feature_names.txt tidak ditemukan di: {feat_path}")
    with open(feat_path, 'r') as f:
        _feature_names = [line.strip() for line in f if line.strip()]
    logger.info(f"[ModelLoader] Feature names loaded: {len(_feature_names)} fitur")

    return _rf_model, _xgb_model, _feature_names


def reload_models():
    """Reset singleton state and reload fresh models from disk."""
    global _rf_model, _xgb_model, _feature_names
    _rf_model = None
    _xgb_model = None
    _feature_names = None
    return load_models()


def restore_defaults():
    """Restore models from defaults backup directory and reload."""
    import shutil
    defaults_dir = os.path.join(BASE_DIR, "models/defaults")
    if os.path.exists(defaults_dir):
        for fname in ["random_forest_model.pkl", "xgboost_model.pkl"]:
            src = os.path.join(defaults_dir, fname)
            dst = os.path.join(BASE_DIR, "models", fname)
            if os.path.exists(src):
                shutil.copy2(src, dst)
        return reload_models()
    return False


def get_current_params() -> dict:
    """Mengambil parameter aktif dari kedua model."""
    rf, xgb, feats = load_models()
    rf_params = {
        "n_estimators": getattr(rf, "n_estimators", 200),
        "max_depth": getattr(rf, "max_depth", None),
        "random_state": getattr(rf, "random_state", 42),
        "class_weight": str(getattr(rf, "class_weight", "balanced")),
        "n_features": getattr(rf, "n_features_in_", len(feats)),
        "n_classes": getattr(rf, "n_classes_", 5)
    }

    xgb_params = {
        "n_estimators": int(getattr(xgb, "n_estimators", 300)),
        "max_depth": int(getattr(xgb, "max_depth", 6)),
        "learning_rate": float(getattr(xgb, "learning_rate", 0.1)),
        "random_state": int(getattr(xgb, "random_state", 42)),
        "subsample": float(getattr(xgb, "subsample", 0.8)),
        "colsample_bytree": float(getattr(xgb, "colsample_bytree", 0.8)),
        "n_features": int(getattr(xgb, "n_features_in_", len(feats)))
    }

    return {
        "random_forest": rf_params,
        "xgboost": xgb_params,
        "features_count": len(feats)
    }


# ─── Backward compatibility shim (untuk kode lama yang pakai load_model()) ───
def load_model():
    """Legacy shim — return (rf_model, feature_names). Gunakan load_models() untuk ensemble."""
    rf, _, feats = load_models()
    return rf, feats
