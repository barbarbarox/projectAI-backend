"""
trainer.py — Modul Pelatihan Ulang & Hyperparameter Tuning (Fase 4 RedSim)

Memungkinkan admin / user mengonfigurasi hyperparameter:
- Random Forest: n_estimators, max_depth, random_state
- XGBoost: n_estimators, learning_rate, max_depth, random_state
- Data sampling: Cepat (misal 25.000 sampel) atau Penuh (120.000 sampel)
- Evaluasi langsung pada test set (Accuracy, Macro F1, Per-Class F1)
"""

import os
import time
import json
import pickle
import logging
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, precision_recall_fscore_support
from sklearn.utils.class_weight import compute_sample_weight

logger = logging.getLogger("redsim.trainer")

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, "models")
DATA_DIR = os.path.join(BASE_DIR, "data")
REPORT_PATH = os.path.join(MODELS_DIR, "last_training_report.json")

# Fallback path jika data symlink tidak ada
ALT_DATA_DIR = "/home/abarox/Desktop/projectAI/matkul-kecerdasan-buatan/preprocessed_output"

LABEL_MAP = {0: 'CRITICAL', 1: 'HIGH', 2: 'LOW', 3: 'MEDIUM', 4: 'NONE'}
CLASS_NAMES = ['CRITICAL', 'HIGH', 'LOW', 'MEDIUM', 'NONE']


def get_data_dir():
    if os.path.exists(os.path.join(DATA_DIR, "X_train.csv")):
        return DATA_DIR
    if os.path.exists(os.path.join(ALT_DATA_DIR, "X_train.csv")):
        return ALT_DATA_DIR
    # Coba parent path untuk VPS
    vps_path = "/var/www/redsim/matkul-kecerdasan-buatan/preprocessed_output"
    if os.path.exists(os.path.join(vps_path, "X_train.csv")):
        return vps_path
    raise FileNotFoundError("Dataset X_train.csv tidak ditemukan di direktori data manapun.")


def train_models(
    rf_params: dict = None,
    xgb_params: dict = None,
    sample_size: int = 30000,
    train_rf: bool = True,
    train_xgb: bool = True,
    weight_rf: float = 0.40,
    weight_xgb: float = 0.60
) -> dict:
    """
    Latih model Random Forest dan/atau XGBoost dengan hyperparameter custom.
    """
    t_start = time.time()
    data_dir = get_data_dir()
    logger.info(f"[Trainer] Membaca data dari: {data_dir} (sample_size={sample_size})")

    # 1. Load Data
    nrows = None if (sample_size is None or sample_size <= 0 or sample_size >= 120000) else sample_size
    X_train = pd.read_csv(os.path.join(data_dir, "X_train.csv"), nrows=nrows)
    y_train = pd.read_csv(os.path.join(data_dir, "y_train.csv"), nrows=nrows).iloc[:, 0]

    # Test set (maks 10.000 sampel untuk kecepatan evaluasi web)
    X_test = pd.read_csv(os.path.join(data_dir, "X_test.csv"), nrows=10000)
    y_test = pd.read_csv(os.path.join(data_dir, "y_test.csv"), nrows=10000).iloc[:, 0]

    feature_names_path = os.path.join(MODELS_DIR, "feature_names.txt")
    if os.path.exists(feature_names_path):
        with open(feature_names_path, "r") as f:
            feature_names = [line.strip() for line in f if line.strip()]
    else:
        feature_names = list(X_train.columns)

    report = {
        "timestamp": time.strftime("%Y-%m-%d %H:%M:%S"),
        "train_samples": len(X_train),
        "test_samples": len(X_test),
        "features_count": len(feature_names),
        "rf_results": None,
        "xgb_results": None,
        "ensemble_results": None
    }

    rf_proba = None
    xgb_proba = None

    # 2. Train Random Forest
    if train_rf:
        rf_cfg = rf_params or {}
        rf_n_estimators = int(rf_cfg.get("n_estimators", 200))
        rf_max_depth = rf_cfg.get("max_depth")
        rf_max_depth = int(rf_max_depth) if rf_max_depth and rf_max_depth != "None" else None
        rf_random_state = int(rf_cfg.get("random_state", 42))

        logger.info(f"[Trainer] Training Random Forest: n_estimators={rf_n_estimators}, max_depth={rf_max_depth}, random_state={rf_random_state}")
        t_rf_start = time.time()

        rf_model = RandomForestClassifier(
            n_estimators=rf_n_estimators,
            max_depth=rf_max_depth,
            min_samples_split=5,
            min_samples_leaf=2,
            class_weight='balanced',
            random_state=rf_random_state,
            n_jobs=-1,
            verbose=0
        )
        rf_model.fit(X_train, y_train)
        rf_train_duration = round(time.time() - t_rf_start, 2)

        # Evaluasi RF
        y_pred_rf = rf_model.predict(X_test)
        rf_proba = rf_model.predict_proba(X_test)

        acc_rf = accuracy_score(y_test, y_pred_rf)
        prec_rf, rec_rf, f1_rf, _ = precision_recall_fscore_support(y_test, y_pred_rf, average='macro', zero_division=0)
        per_class_p, per_class_r, per_class_f1, _ = precision_recall_fscore_support(y_test, y_pred_rf, average=None, zero_division=0)

        # Top Features
        importances = rf_model.feature_importances_
        top_idx = np.argsort(importances)[::-1][:8]
        top_features = [{"feature": feature_names[i] if i < len(feature_names) else f"f_{i}", "importance": round(float(importances[i]), 4)} for i in top_idx]

        per_class_metrics = {}
        for idx, cname in LABEL_MAP.items():
            if idx < len(per_class_f1):
                per_class_metrics[cname] = {
                    "f1": round(float(per_class_f1[idx]), 4),
                    "precision": round(float(per_class_p[idx]), 4),
                    "recall": round(float(per_class_r[idx]), 4)
                }

        report["rf_results"] = {
            "n_estimators": rf_n_estimators,
            "max_depth": rf_max_depth,
            "random_state": rf_random_state,
            "duration_seconds": rf_train_duration,
            "accuracy": round(float(acc_rf), 4),
            "macro_f1": round(float(f1_rf), 4),
            "macro_precision": round(float(prec_rf), 4),
            "macro_recall": round(float(rec_rf), 4),
            "per_class": per_class_metrics,
            "top_features": top_features
        }

        # Simpan model RF ke disk
        rf_save_path = os.path.join(MODELS_DIR, "random_forest_model.pkl")
        with open(rf_save_path, "wb") as f:
            pickle.dump(rf_model, f)
        logger.info(f"[Trainer] RF saved to {rf_save_path} (F1={f1_rf:.4f})")
    else:
        # Load existing RF untuk evaluasi ensemble
        rf_save_path = os.path.join(MODELS_DIR, "random_forest_model.pkl")
        if os.path.exists(rf_save_path):
            with open(rf_save_path, "rb") as f:
                rf_model = pickle.load(f)
            rf_proba = rf_model.predict_proba(X_test)

    # 3. Train XGBoost
    if train_xgb:
        try:
            import xgboost as xgb
            xgb_cfg = xgb_params or {}
            xgb_n_estimators = int(xgb_cfg.get("n_estimators", 300))
            xgb_lr = float(xgb_cfg.get("learning_rate", 0.1))
            xgb_max_depth = int(xgb_cfg.get("max_depth", 6))
            xgb_random_state = int(xgb_cfg.get("random_state", 42))

            logger.info(f"[Trainer] Training XGBoost: n_estimators={xgb_n_estimators}, lr={xgb_lr}, max_depth={xgb_max_depth}")
            t_xgb_start = time.time()

            sample_weights = compute_sample_weight(class_weight='balanced', y=y_train)

            xgb_model = xgb.XGBClassifier(
                n_estimators=xgb_n_estimators,
                max_depth=xgb_max_depth,
                learning_rate=xgb_lr,
                subsample=0.8,
                colsample_bytree=0.8,
                min_child_weight=3,
                gamma=0.1,
                reg_alpha=0.1,
                reg_lambda=1.0,
                objective='multi:softprob',
                num_class=5,
                eval_metric='mlogloss',
                tree_method='hist',
                random_state=xgb_random_state,
                n_jobs=-1,
                verbosity=0
            )
            xgb_model.fit(X_train, y_train, sample_weight=sample_weights, verbose=False)
            xgb_train_duration = round(time.time() - t_xgb_start, 2)

            y_pred_xgb = xgb_model.predict(X_test)
            xgb_proba = xgb_model.predict_proba(X_test)

            acc_xgb = accuracy_score(y_test, y_pred_xgb)
            prec_xgb, rec_xgb, f1_xgb, _ = precision_recall_fscore_support(y_test, y_pred_xgb, average='macro', zero_division=0)
            per_class_p_xgb, per_class_r_xgb, per_class_f1_xgb, _ = precision_recall_fscore_support(y_test, y_pred_xgb, average=None, zero_division=0)

            per_class_metrics_xgb = {}
            for idx, cname in LABEL_MAP.items():
                if idx < len(per_class_f1_xgb):
                    per_class_metrics_xgb[cname] = {
                        "f1": round(float(per_class_f1_xgb[idx]), 4),
                        "precision": round(float(per_class_p_xgb[idx]), 4),
                        "recall": round(float(per_class_r_xgb[idx]), 4)
                    }

            report["xgb_results"] = {
                "n_estimators": xgb_n_estimators,
                "learning_rate": xgb_lr,
                "max_depth": xgb_max_depth,
                "random_state": xgb_random_state,
                "duration_seconds": xgb_train_duration,
                "accuracy": round(float(acc_xgb), 4),
                "macro_f1": round(float(f1_xgb), 4),
                "macro_precision": round(float(prec_xgb), 4),
                "macro_recall": round(float(rec_xgb), 4),
                "per_class": per_class_metrics_xgb
            }

            xgb_save_path = os.path.join(MODELS_DIR, "xgboost_model.pkl")
            with open(xgb_save_path, "wb") as f:
                pickle.dump(xgb_model, f)
            logger.info(f"[Trainer] XGBoost saved to {xgb_save_path} (F1={f1_xgb:.4f})")
        except Exception as e:
            logger.error(f"[Trainer] Gagal melatih XGBoost: {e}")
            report["xgb_results"] = {"error": str(e)}
    else:
        xgb_save_path = os.path.join(MODELS_DIR, "xgboost_model.pkl")
        if os.path.exists(xgb_save_path):
            with open(xgb_save_path, "rb") as f:
                xgb_model = pickle.load(f)
            xgb_proba = xgb_model.predict_proba(X_test)

    # 4. Evaluasi Ensemble (Soft Voting)
    if rf_proba is not None and xgb_proba is not None:
        total_w = weight_rf + weight_xgb
        w_rf_norm = weight_rf / total_w if total_w > 0 else 0.5
        w_xgb_norm = weight_xgb / total_w if total_w > 0 else 0.5

        ens_proba = (w_rf_norm * rf_proba) + (w_xgb_norm * xgb_proba)
        y_pred_ens = np.argmax(ens_proba, axis=1)

        acc_ens = accuracy_score(y_test, y_pred_ens)
        prec_ens, rec_ens, f1_ens, _ = precision_recall_fscore_support(y_test, y_pred_ens, average='macro', zero_division=0)

        report["ensemble_results"] = {
            "strategy": "Soft Voting",
            "weight_rf": round(w_rf_norm, 2),
            "weight_xgb": round(w_xgb_norm, 2),
            "accuracy": round(float(acc_ens), 4),
            "macro_f1": round(float(f1_ens), 4),
            "macro_precision": round(float(prec_ens), 4),
            "macro_recall": round(float(rec_ens), 4)
        }

    total_duration = round(time.time() - t_start, 2)
    report["total_duration_seconds"] = total_duration

    # Simpan report terakhir
    try:
        with open(REPORT_PATH, "w") as f:
            json.dump(report, f, indent=2)
    except Exception as e:
        logger.warning(f"Gagal simpan training report: {e}")

    return report

