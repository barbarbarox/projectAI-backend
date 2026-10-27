"""
main.py — RedSim ML Prediction Service v2.0 (Ensemble Edition)

Upgrade dari single Random Forest → Dual-Model Soft Voting Ensemble
menggunakan Random Forest (Minggu 5) + XGBoost (Minggu 6).

Soft Voting: avg_prob = (prob_rf * W_rf + prob_xgb * W_xgb)
  W_rf  = 0.40 (Macro F1 = 0.9320)
  W_xgb = 0.60 (Macro F1 = 0.9595, FN CRITICAL lebih rendah)

Endpoint:
  GET  /health      → status check kedua model
  POST /predict     → prediksi ensemble dari data Temuan
  GET  /info        → info detail kedua model + ensemble config
  GET  /models      → ringkasan model yang aktif

Jalankan dengan:
  uvicorn main:app --host 127.0.0.1 --port 5001 --reload
"""

import logging
import numpy as np
from contextlib import asynccontextmanager
from typing import Optional

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

from model_loader import load_models, LABEL_MAP, LABEL_TO_IDX, SEVERITY_ORDER
from feature_extractor import extract_features

# ─── Logging ─────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(name)s — %(message)s'
)
logger = logging.getLogger("redsim.ml_service")

# ─── Bobot ensemble (berdasarkan Macro F1-Score dari eksperimen Minggu 5 & 6) ─
WEIGHT_RF  = 0.40   # RF  Macro F1 = 0.9320
WEIGHT_XGB = 0.60   # XGB Macro F1 = 0.9595, FN CRITICAL 19.4% lebih rendah


# ─── Lifespan: preload kedua model saat startup ───────────────────────────────
@asynccontextmanager
async def lifespan(app: FastAPI):
    logger.info("=" * 65)
    logger.info("  RedSim ML Prediction Service v2.0 — Ensemble Edition")
    logger.info("=" * 65)
    try:
        rf_model, xgb_model, feature_names = load_models()
        logger.info(f"  ✅ Random Forest: {rf_model.n_estimators} trees, "
                    f"{rf_model.n_features_in_} features")
        n_trees = getattr(xgb_model, 'n_estimators', '?')
        logger.info(f"  ✅ XGBoost:       {n_trees} trees")
        logger.info(f"  ✅ Features:      {len(feature_names)} fitur")
        logger.info(f"  ✅ Ensemble:      Soft Voting (RF={WEIGHT_RF}, XGB={WEIGHT_XGB})")
        logger.info(f"  ✅ Kelas:         {list(LABEL_MAP.values())}")
    except Exception as e:
        logger.error(f"  ❌ GAGAL load model: {e}")
        logger.error("  Service akan tetap jalan tapi /predict akan error.")
    logger.info("=" * 65)
    yield
    logger.info("[ML Service] Shutdown.")


# ─── FastAPI app ──────────────────────────────────────────────────────────────
app = FastAPI(
    title="RedSim ML Prediction Service",
    description=(
        "Ensemble microservice (RF + XGBoost) untuk prediksi severity CVE. "
        "Soft Voting Ensemble: RF (w=0.40) + XGBoost (w=0.60). "
        "Ditraining dari 150.444 sampel NVD CVE 2022–2026 dengan SMOTE. "
        "Digunakan oleh RedSim untuk validasi prediksi LLM."
    ),
    version="2.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000", "http://127.0.0.1:8000"],
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)


# ─── Schema Input ─────────────────────────────────────────────────────────────
class TemuanInput(BaseModel):
    tipe: str = Field(default="lainnya", description="Tipe temuan: injeksi, autentikasi_lemah, dll.")
    tingkat_keparahan: str = Field(default="sedang", description="Severity dari LLM: kritis/tinggi/sedang/rendah/info")
    cwe_id: Optional[str] = Field(default="", description="CWE ID, e.g. CWE-89")
    cve_id: Optional[str] = Field(default="", description="CVE ID, e.g. CVE-2023-1234")
    deskripsi: Optional[str] = Field(default="", description="Deskripsi temuan dari LLM")
    tautan_referensi: Optional[str] = Field(default="", description="Referensi/URL terkait")

    class Config:
        json_schema_extra = {
            "example": {
                "tipe": "injeksi",
                "tingkat_keparahan": "kritis",
                "cwe_id": "CWE-89",
                "cve_id": "",
                "deskripsi": "SQL Injection ditemukan pada endpoint /api/users. Attacker dapat mengakses seluruh database.",
                "tautan_referensi": "https://owasp.org/www-community/attacks/SQL_Injection"
            }
        }


# ─── Schema Output ────────────────────────────────────────────────────────────
class ModelDetail(BaseModel):
    severity: str
    confidence: float
    class_probabilities: dict


class FeatureImportance(BaseModel):
    feature: str
    importance: float


class EnsemblePredictionOutput(BaseModel):
    # ── Ensemble result (primary) ──────────────────────────────────────────
    ensemble_severity: str          = Field(description="Prediksi final ensemble: CRITICAL/HIGH/MEDIUM/LOW/NONE")
    ensemble_confidence: float      = Field(description="Confidence ensemble (0.0–1.0)")
    ensemble_probabilities: dict    = Field(description="Probabilitas per kelas dari ensemble")

    # ── Per-model detail ───────────────────────────────────────────────────
    rf: ModelDetail                 = Field(description="Detail prediksi Random Forest")
    xgb: ModelDetail                = Field(description="Detail prediksi XGBoost")
    models_agree: bool              = Field(description="True jika RF dan XGBoost sepakat")
    disagreement_delta: float       = Field(description="Selisih confidence antar model (0=identik)")

    # ── LLM konsensus ─────────────────────────────────────────────────────
    llm_severity_normalized: str    = Field(description="Severity LLM setelah dinormalisasi ke English")
    konsensus: str                  = Field(description="Konsensus: severity tertinggi dari Ensemble + LLM")
    agreement: bool                 = Field(description="True jika LLM dan Ensemble sepakat")

    # ── Feature importance ─────────────────────────────────────────────────
    top_features: list[FeatureImportance] = Field(description="Top 5 fitur terpenting (dari XGBoost)")

    # ── Backward compat fields (agar MLPredictionService.php lama tidak crash) ─
    ml_predicted_severity: str      = Field(description="Alias: sama dengan ensemble_severity")
    ml_confidence: float            = Field(description="Alias: sama dengan ensemble_confidence")
    class_probabilities: dict       = Field(description="Alias: sama dengan ensemble_probabilities")


# ─── LLM label normalisasi ───────────────────────────────────────────────────
LLM_LABEL_MAP = {
    'kritis': 'CRITICAL', 'critical': 'CRITICAL',
    'tinggi': 'HIGH',     'high':     'HIGH',
    'sedang': 'MEDIUM',   'medium':   'MEDIUM',
    'rendah': 'LOW',      'low':      'LOW',
    'info':   'NONE',     'none':     'NONE',
}


def _normalize_llm(raw: str) -> str:
    return LLM_LABEL_MAP.get(raw.lower().strip(), 'MEDIUM')


def _compute_consensus(llm_sev: str, ensemble_sev: str) -> str:
    """Konsensus: ambil yang lebih severe (index kecil = lebih berbahaya)."""
    try:
        idx_llm      = SEVERITY_ORDER.index(llm_sev)
        idx_ensemble = SEVERITY_ORDER.index(ensemble_sev)
        return SEVERITY_ORDER[min(idx_llm, idx_ensemble)]
    except ValueError:
        return ensemble_sev


def _soft_vote(prob_rf: np.ndarray, prob_xgb: np.ndarray) -> np.ndarray:
    """Weighted Soft Voting: avg = RF*W_rf + XGB*W_xgb (sudah normalized)."""
    return prob_rf * WEIGHT_RF + prob_xgb * WEIGHT_XGB


def _get_xgb_top_features(xgb_model, feature_names: list, top_n: int = 5) -> list[FeatureImportance]:
    """Ambil top N feature importance dari XGBoost (lebih stabil dari RF untuk ensembles)."""
    try:
        importances = xgb_model.feature_importances_
        top_idx = np.argsort(importances)[::-1][:top_n]
        return [
            FeatureImportance(
                feature=feature_names[i],
                importance=round(float(importances[i]), 5)
            )
            for i in top_idx
        ]
    except Exception:
        return []


# ─── Endpoints ────────────────────────────────────────────────────────────────
@app.get("/health", tags=["System"])
def health_check():
    """Cek apakah service berjalan, KEDUA model ML siap, dan embedding model siap."""
    result: dict = {
        "status": "ok",
        "service": "RedSim ML Prediction Service v2.0",
        "ensemble": "Soft Voting (RF + XGBoost)",
        "embedding_model": "intfloat/multilingual-e5-base",
        "embedding_dim": 768,
    }

    # ── Cek ML prediction models ───────────────────────────────────────────────
    try:
        rf, xgb, feats = load_models()
        result["models"] = {
            "random_forest": {
                "loaded": True,
                "n_estimators": rf.n_estimators,
                "n_features": rf.n_features_in_,
                "weight": WEIGHT_RF,
            },
            "xgboost": {
                "loaded": True,
                "n_estimators": getattr(xgb, 'n_estimators', '?'),
                "weight": WEIGHT_XGB,
            }
        }
        result["n_features"] = len(feats)
    except Exception as e:
        result["status"] = "degraded"
        result["ml_error"] = str(e)

    # ── Cek embedding model (lazy-loaded) ──────────────────────────────────────
    try:
        embed_model = _get_embed_model()
        if embed_model is not None:
            try:
                dim = embed_model.get_embedding_dimension()
            except AttributeError:
                dim = embed_model.get_sentence_embedding_dimension()  # fallback
            result["embedding_ready"] = True
            result["embedding_dim"]   = dim
        else:
            result["embedding_ready"] = False
            result["embedding_error"] = "Model belum dimuat (lazy load belum dipicu)"
    except Exception as e:
        result["embedding_ready"] = False
        result["embedding_error"] = str(e)

    return result


@app.get("/models", tags=["System"])
def list_models():
    """Ringkasan model yang aktif dalam ensemble."""
    return {
        "ensemble_strategy": "Soft Voting",
        "models": [
            {
                "name": "Random Forest",
                "version": "minggu5",
                "macro_f1": 0.9320,
                "recall_critical": 0.9078,
                "fn_critical": 294,
                "weight": WEIGHT_RF,
            },
            {
                "name": "XGBoost",
                "version": "minggu6",
                "macro_f1": 0.9595,
                "recall_critical": 0.9257,
                "fn_critical": 237,
                "weight": WEIGHT_XGB,
            }
        ],
        "dataset": "NVD CVE 2022–2026 (150.444 sampel, 47 fitur, SMOTE balanced)",
    }


@app.get("/info", tags=["System"])
def model_info():
    """Informasi detail tentang ensemble dan kedua model."""
    rf, xgb, feature_names = load_models()
    rf_imp  = rf.feature_importances_
    xgb_imp = xgb.feature_importances_

    rf_top_idx  = np.argsort(rf_imp)[::-1][:10]
    xgb_top_idx = np.argsort(xgb_imp)[::-1][:10]

    return {
        "ensemble": {
            "strategy": "Weighted Soft Voting",
            "weights": {"random_forest": WEIGHT_RF, "xgboost": WEIGHT_XGB},
        },
        "random_forest": {
            "type": "RandomForestClassifier",
            "n_estimators": rf.n_estimators,
            "n_features": rf.n_features_in_,
            "classes": [LABEL_MAP[i] for i in range(rf.n_classes_)],
            "macro_f1": 0.9320,
            "top_10_features": [
                {"rank": i+1, "feature": feature_names[idx], "importance": round(float(rf_imp[idx]), 5)}
                for i, idx in enumerate(rf_top_idx)
            ],
        },
        "xgboost": {
            "type": "XGBClassifier",
            "n_estimators": getattr(xgb, 'n_estimators', '?'),
            "n_features": getattr(xgb, 'n_features_in_', '?'),
            "macro_f1": 0.9595,
            "top_10_features": [
                {"rank": i+1, "feature": feature_names[idx], "importance": round(float(xgb_imp[idx]), 5)}
                for i, idx in enumerate(xgb_top_idx)
            ],
        },
    }


@app.post("/predict", response_model=EnsemblePredictionOutput, tags=["Prediction"])
def predict_severity(temuan: TemuanInput):
    """
    Prediksi severity CVE menggunakan Weighted Soft Voting Ensemble (RF + XGBoost).

    Bobot: RF=0.40, XGBoost=0.60 (berdasarkan Macro F1-Score eksperimen NVD).
    Konsensus tiga lapis: Ensemble ML + LLM → ambil yang paling severe.
    """
    try:
        rf_model, xgb_model, feature_names = load_models()
    except FileNotFoundError as e:
        raise HTTPException(status_code=503, detail=f"Model tidak tersedia: {str(e)}")

    try:
        # ── Ekstrak fitur ──────────────────────────────────────────────────────
        features = extract_features(temuan.model_dump(), feature_names)

        # ── Prediksi RF ────────────────────────────────────────────────────────
        rf_pred_idx  = int(rf_model.predict(features)[0])
        rf_proba     = rf_model.predict_proba(features)[0]
        rf_severity  = LABEL_MAP[rf_pred_idx]
        rf_conf      = float(rf_proba[rf_pred_idx])
        rf_class_proba = {LABEL_MAP[i]: round(float(p), 4) for i, p in enumerate(rf_proba)}

        # ── Prediksi XGBoost ───────────────────────────────────────────────────
        xgb_pred_idx = int(xgb_model.predict(features)[0])
        xgb_proba    = xgb_model.predict_proba(features)[0]
        xgb_severity = LABEL_MAP[xgb_pred_idx]
        xgb_conf     = float(xgb_proba[xgb_pred_idx])
        xgb_class_proba = {LABEL_MAP[i]: round(float(p), 4) for i, p in enumerate(xgb_proba)}

        # ── Soft Voting Ensemble ───────────────────────────────────────────────
        ensemble_proba    = _soft_vote(rf_proba, xgb_proba)
        ensemble_pred_idx = int(np.argmax(ensemble_proba))
        ensemble_severity = LABEL_MAP[ensemble_pred_idx]
        ensemble_conf     = float(ensemble_proba[ensemble_pred_idx])
        ensemble_class_proba = {LABEL_MAP[i]: round(float(p), 4) for i, p in enumerate(ensemble_proba)}

        # ── Agreement check ────────────────────────────────────────────────────
        models_agree = (rf_severity == xgb_severity)
        disagreement_delta = round(abs(rf_conf - xgb_conf), 4)

        # ── LLM konsensus ──────────────────────────────────────────────────────
        llm_normalized = _normalize_llm(temuan.tingkat_keparahan)
        konsensus      = _compute_consensus(llm_normalized, ensemble_severity)
        agreement      = (llm_normalized == ensemble_severity)

        # ── Feature importance dari XGBoost (lebih akurat untuk ensemble) ─────
        top_features = _get_xgb_top_features(xgb_model, feature_names, top_n=5)

        logger.info(
            f"[Predict] tipe={temuan.tipe} | "
            f"LLM={llm_normalized} | RF={rf_severity}({rf_conf:.1%}) | "
            f"XGB={xgb_severity}({xgb_conf:.1%}) | "
            f"Ensemble={ensemble_severity}({ensemble_conf:.1%}) | "
            f"Agree={models_agree} | Konsensus={konsensus}"
        )

        return EnsemblePredictionOutput(
            # Ensemble
            ensemble_severity       = ensemble_severity,
            ensemble_confidence     = round(ensemble_conf, 4),
            ensemble_probabilities  = ensemble_class_proba,
            # Per-model
            rf  = ModelDetail(severity=rf_severity,  confidence=round(rf_conf, 4),  class_probabilities=rf_class_proba),
            xgb = ModelDetail(severity=xgb_severity, confidence=round(xgb_conf, 4), class_probabilities=xgb_class_proba),
            models_agree            = models_agree,
            disagreement_delta      = disagreement_delta,
            # LLM
            llm_severity_normalized = llm_normalized,
            konsensus               = konsensus,
            agreement               = agreement,
            # Features
            top_features            = top_features,
            # Backward compat
            ml_predicted_severity   = ensemble_severity,
            ml_confidence           = round(ensemble_conf, 4),
            class_probabilities     = ensemble_class_proba,
        )

    except Exception as e:
        logger.exception(f"[Predict] Error: {e}")
        raise HTTPException(status_code=500, detail=f"Prediction error: {str(e)}")


# ─── /embed — Text Embedding Endpoint (untuk NVD RAG) ────────────────────────
_embed_model = None


def _get_embed_model():
    """Lazy-load multilingual-e5-base dari HuggingFace cache lokal."""
    global _embed_model
    if _embed_model is not None:
        return _embed_model
    try:
        from sentence_transformers import SentenceTransformer
        logger.info("[Embed] Loading intfloat/multilingual-e5-base...")
        _embed_model = SentenceTransformer("intfloat/multilingual-e5-base")
        try:
            dim = _embed_model.get_embedding_dimension()
        except AttributeError:
            dim = _embed_model.get_sentence_embedding_dimension()  # fallback older versions
        logger.info(f"[Embed] Model ready (dim={dim})")
    except ImportError:
        logger.warning("[Embed] sentence-transformers belum terinstall.")
        return None
    except Exception as e:
        logger.error(f"[Embed] Gagal load model: {e}")
        return None
    return _embed_model


class EmbedRequest(BaseModel):
    text: str
    prefix: str = "query"  # "query" atau "passage"


class EmbedResponse(BaseModel):
    embedding: list
    dim: int
    model: str


@app.post("/embed", response_model=EmbedResponse, tags=["NVD RAG"])
def embed_text(req: EmbedRequest):
    """Generate dense vector embedding (multilingual-e5-base, 768-dim)."""
    model = _get_embed_model()
    if model is None:
        raise HTTPException(
            status_code=503,
            detail="Embedding model belum siap. Jalankan: pip install sentence-transformers"
        )
    prefixed = f"{req.prefix}: {req.text}"
    try:
        vec = model.encode(prefixed, normalize_embeddings=True)
        return EmbedResponse(embedding=vec.tolist(), dim=len(vec), model="intfloat/multilingual-e5-base")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Embedding error: {str(e)}")
