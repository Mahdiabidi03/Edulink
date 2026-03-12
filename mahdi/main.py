"""
EduLink — AI Inference Server
Engines:
  • Dropout Prediction   →  POST /predict/dropout
  • Course Recommender   →  POST /recommend/courses
  • Quality Analysis     →  POST /analyze/course
  • Vector Agent Admin   →  POST /admin/rebuild-index
  • AI Chat Assistant    →  POST /chat

Run: uvicorn mahdi.main:app --reload  (from project root)
  or: uvicorn main:app --reload       (from mahdi/ directory)
"""

import os
import pickle
import logging
from pathlib import Path
from contextlib import asynccontextmanager
import numpy as np
import pandas as pd
from scipy.sparse import issparse
from typing import Optional, Any, List, Dict, cast
from fastapi import FastAPI, HTTPException, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from langchain_community.utilities import SQLDatabase
from langchain_community.agent_toolkits import SQLDatabaseToolkit
from langchain_core.tools import Tool, BaseTool

# ── Custom Mahdi Modules ──
from mahdi.vector_pipeline import build_index
from mahdi.agent import get_agent_response
from mahdi.sql_tool import get_sql_db_tool

# ─────────────────────────────────────────────
# LOGGING
# ─────────────────────────────────────────────

logging.basicConfig(level=logging.INFO, format="%(levelname)s: %(message)s")
log = logging.getLogger(__name__)

# ─────────────────────────────────────────────
# PATHS
# ─────────────────────────────────────────────

MODELS_DIR = Path(__file__).parent / "models"

# ─────────────────────────────────────────────
# GLOBAL MODEL STATE
# ─────────────────────────────────────────────

class ModelStore:
    # ── Dropout prediction ──
    model:             Optional[Any] = None
    scaler:            Optional[Any] = None
    encoder:           Optional[Any] = None
    ready:             bool          = False
    
    # ── Course recommendation ──
    tfidf_vectorizer:  Optional[Any] = None
    course_features:   Optional[Any] = None   # sparse TF-IDF matrix
    index_to_id:       dict          = {}
    recommender_ready: bool          = False
    
    # ── Course performance ──
    performance_model: Optional[Any] = None
    performance_ready: bool          = False

store = ModelStore()


def _load_pkl(path: Path, label: str):
    """Load a pickle file, return None and log a warning on failure."""
    if not path.exists():
        log.warning("⚠️  %s not found at %s — predictions will be unavailable.", label, path)
        return None
    with open(path, "rb") as f:
        obj = pickle.load(f)
    log.info("✅  Loaded %s from %s", label, path)
    return obj


# ─────────────────────────────────────────────
# LIFESPAN — load models at startup
# ─────────────────────────────────────────────

@asynccontextmanager
async def lifespan(app: FastAPI):
    log.info("🚀 Loading model artifacts from %s …", MODELS_DIR)

    # ── Dropout prediction artifacts ──
    store.model   = _load_pkl(MODELS_DIR / "dropout_model.pkl", "RandomForestClassifier")
    store.scaler  = _load_pkl(MODELS_DIR / "scaler.pkl",        "StandardScaler")
    store.encoder = _load_pkl(MODELS_DIR / "encoder.pkl",       "LabelEncoder")
    store.ready   = all([store.model, store.scaler, store.encoder])
    if store.ready:
        log.info("✅  Dropout model loaded — /predict/dropout is active.")
    else:
        log.warning("⚠️  Dropout artifacts missing — /predict/dropout will return 503.")

    # ── Course recommendation artifacts ──
    store.tfidf_vectorizer = _load_pkl(MODELS_DIR / "tfidf_vectorizer.pkl", "TfidfVectorizer")
    store.course_features  = _load_pkl(MODELS_DIR / "course_features.pkl",  "CourseFeatureMatrix")
    index_data             = _load_pkl(MODELS_DIR / "course_index.pkl",      "CourseIndex")
    if index_data:
        store.index_to_id = index_data.get("index_to_id", {})
    store.recommender_ready = (
        store.tfidf_vectorizer is not None and 
        store.course_features is not None and 
        bool(store.index_to_id)
    )
    if store.recommender_ready:
        log.info("✅  Recommendation engine loaded — /recommend/courses is active.")
    else:
        log.warning("⚠️  Recommendation artifacts missing — run train_recommendation.py first.")

    # ── Course performance artifacts ──
    store.performance_model = _load_pkl(MODELS_DIR / "course_performance_model.pkl", "CoursePerformanceModel")
    store.performance_ready = store.performance_model is not None
    if store.performance_ready:
        log.info("✅  Performance engine loaded — /analyze/course is active.")
    else:
        log.warning("⚠️  Performance artifacts missing — run train_course_performance.py first.")

    yield
    log.info("🛑 Shutting down inference server.")


# ─────────────────────────────────────────────
# APP
# ─────────────────────────────────────────────

app = FastAPI(
    title="EduLink — AI Inference API",
    description=(
        "**Dropout Prediction** — `POST /predict/dropout`\n\n"
        "**Course Recommender** — `POST /recommend/courses`\n\n"
        "**Quality Analysis** — `POST /analyze/course`\n\n"
        "**Vector Admin** — `POST /admin/rebuild-index` (Rebuild Knowledge Base)\n\n"
        "**AI Chat Assistant** — `POST /chat` (Natural Language Interface)"
    ),
    version="1.3.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],   # tighten in production
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)


# ─────────────────────────────────────────────
# SCHEMA
# ─────────────────────────────────────────────

class StudentFeatures(BaseModel):
    """All fields that the model was trained on.
    Provide the same column names as in your CSV (case-sensitive).
    Categorical fields (e.g. course_category) are encoded automatically.
    """
    student_id:               int   = Field(..., example=101)
    course_category:          str   = Field(..., example="Web Development")
    login_frequency_per_week: float = Field(..., example=4.5)
    avg_session_minutes:      float = Field(..., example=30.0)
    course_progress_percent:  float = Field(..., example=50.0)
    assignments_completed:    float = Field(..., example=5.0)
    quiz_average_score:       float = Field(..., example=72.0)
    forum_interactions:       float = Field(..., example=3.0)
    video_watch_percent:      float = Field(..., example=70.0)
    device_type:              str   = Field(..., example="Desktop")

    class Config:
        extra = "allow"


class PredictionResponse(BaseModel):
    dropout_probability: float = Field(..., description="Probability of dropout (0.0 – 1.0)")
    dropout_predicted:   bool  = Field(..., description="True if model predicts dropout")
    confidence:          str   = Field(..., description="Human-readable confidence label")


# ─────────────────────────────────────────────
CATEGORICAL_FIELDS = {"course_category", "CourseCategory", "Category", "course_type"}

EXPECTED_FEATURES = [
    "student_id", "course_category", "login_frequency_per_week", 
    "avg_session_minutes", "course_progress_percent", "assignments_completed", 
    "quiz_average_score", "forum_interactions", "video_watch_percent", "device_type"
]

def _preprocess(data: dict) -> np.ndarray:
    """
    Apply the same preprocessing pipeline used during training:
    """
    # Real Data Defaults
    if "avg_session_minutes" not in data or data["avg_session_minutes"] is None:
        data["avg_session_minutes"] = 30.0
    
    df = pd.DataFrame([data])
    
    # Map 'Desktop' / 'Mobile' to float as it was a float in the kaggle CSV (e.g. 0.0 or 1.0)
    # The training dataset's device_type was numerical.
    if "device_type" in df.columns:
        if df["device_type"].iloc[0] == "Desktop":
            df["device_type"] = 0.0
        elif df["device_type"].iloc[0] == "Mobile":
            df["device_type"] = 1.0
        else:
            try:
                df["device_type"] = float(df["device_type"].iloc[0])
            except ValueError:
                df["device_type"] = 0.0

    df = df[EXPECTED_FEATURES]

    # Encode categorical columns
    cat_cols = [c for c in df.columns if c in CATEGORICAL_FIELDS]
    for col in cat_cols:
        encoder = cast(Any, store.encoder)
        if encoder:
            try:
                df[col] = encoder.transform(df[col].astype(str))
            except ValueError:
                log.warning("Unseen label '%s' in col '%s', encoding as -1.", df[col].iloc[0], col)
                df[col] = -1
        else:
            df[col] = -1

    # Scale numerical columns
    scaler = cast(Any, store.scaler)
    if scaler and hasattr(scaler, "feature_names_in_"):
        num_cols = list(scaler.feature_names_in_)
        # Just in case some features are missing, fill them with 0
        for missing in num_cols:
            if missing not in df.columns:
                df[missing] = 0.0
        df[num_cols] = scaler.transform(df[num_cols])
    
    return df[EXPECTED_FEATURES].values


def _confidence_label(prob: float) -> str:
    if prob >= 0.80:
        return "High risk"
    if prob >= 0.50:
        return "Medium risk"
    return "Low risk"


# ─────────────────────────────────────────────
# ENDPOINTS
# ─────────────────────────────────────────────

@app.get("/", tags=["Health"])
def root():
    return {"status": "ok", "model_ready": store.ready}


@app.get("/health", tags=["Health"])
def health():
    return {
        "status": "ok" if (store.ready and store.recommender_ready) else "degraded",
        # Dropout engine
        "dropout_model_loaded":   store.model   is not None,
        "scaler_loaded":          store.scaler  is not None,
        "encoder_loaded":         store.encoder is not None,
        # Recommendation engine
        "tfidf_vectorizer_loaded": store.tfidf_vectorizer is not None,
        "course_features_loaded":  store.course_features  is not None,
        "course_index_loaded":     bool(store.index_to_id),
        # Performance engine
        "performance_model_loaded": store.performance_model is not None,
    }


@app.post("/predict/dropout", response_model=PredictionResponse, tags=["Prediction"])
def predict_dropout(features: StudentFeatures):
    """
    Predict the dropout probability for a given student's engagement data.

    - **login_frequency**: Avg weekly logins
    - **quiz_score**: 0–100
    - **assignment_completion**: 0.0–1.0
    - **forum_posts**: count
    - **video_watch_rate**: 0.0–1.0
    - **course_category**: string label (e.g. "Math", "Science")
    """
    if not store.ready:
        raise HTTPException(
            status_code=503,
            detail="Model artifacts not loaded. Make sure the models/ directory contains the .pkl files.",
        )

    try:
        payload = features.model_dump()
        X = _preprocess(payload)
        
        model = cast(Any, store.model)
        if not model:
            raise ValueError("Model artifacts missing.")
            
        proba = model.predict_proba(X)         # shape (1, n_classes)
        # Class index 1 = dropout (positive class)
        dropout_prob = float(proba[0][1])
        predicted    = dropout_prob >= 0.5

        return PredictionResponse(**{
            "dropout_probability": round(float(dropout_prob), 4),
            "dropout_predicted":   bool(predicted),
            "confidence":          str(_confidence_label(dropout_prob)),
        })

    except Exception as exc:
        log.error("Prediction failed: %s", exc, exc_info=True)
        raise HTTPException(status_code=500, detail=f"Inference error: {str(exc)}")


# ─────────────────────────────────────────────
# RECOMMENDATION SCHEMAS
# ─────────────────────────────────────────────

class RecommendRequest(BaseModel):
    user_text: str = Field(
        ...,
        example="I want to learn web development and php",
        description="Free-text description of what the user wants to learn",
    )
    top_n: int = Field(default=5, ge=1, le=20, description="Number of courses to return")


class RecommendResponse(BaseModel):
    recommended_course_ids: list[int]  = Field(..., description="Ordered list of course IDs (best match first)")
    scores:                 list[float] = Field(..., description="Cosine similarity scores (0.0 – 1.0)")


# ─────────────────────────────────────────────
# RECOMMENDATION ENDPOINT
# ─────────────────────────────────────────────

@app.post("/recommend/courses", response_model=RecommendResponse, tags=["Recommendation"])
def recommend_courses(body: RecommendRequest):
    """
    Return the top-N most relevant courses for a given user query.
    """
    if not store.recommender_ready:
        raise HTTPException(
            status_code=503,
            detail="Recommendation engine not loaded. Run train_recommendation.py first.",
        )

    try:
        tfidf = cast(Any, store.tfidf_vectorizer)
        course_feats = cast(Any, store.course_features)
        
        if tfidf is None or course_feats is None:
             raise ValueError("Recommender artifacts missing.")

        log.info("🔍 Recommendation Query: '%s' (top_n=%d)", body.user_text[:100], body.top_n)

        # Vectorize user query
        user_vec = tfidf.transform([body.user_text])  # (1, n_features)

        # Cosine similarity: user_vec · course_features^T  →  (1, n_courses)
        sims = (user_vec @ course_feats.T)
        if issparse(sims):
            sims = sims.toarray()
        sims = sims.flatten()

        max_score = float(sims.max())
        log.info("📈 Max similarity score: %.4f", max_score)

        THRESHOLD = 0.15
        
        # New Strict Logic:
        # If matches > 0.15 exist, return all (up to top_n)
        # If no matches > 0.15, return only the single best match
        valid_indices = np.where(sims >= THRESHOLD)[0]
        
        if len(valid_indices) > 0:
            # Sort valid indices by score descending
            sorted_indices = valid_indices[np.argsort(sims[valid_indices])[::-1]]
            top_indices = sorted_indices[:body.top_n]
        else:
            # Fallback: only the single best match regardless of threshold
            best_idx = np.argmax(sims)
            top_indices = [best_idx]

        course_ids = [int(store.index_to_id.get(int(i), -1)) for i in top_indices]
        scores     = [round(float(sims[i]), 4) for i in top_indices]

        log.info("🎯 Top Recommended IDs: %s with scores: %s", course_ids, scores)

        return RecommendResponse(**{
            "recommended_course_ids": [cid for cid in course_ids if cid != -1],
            "scores": scores,
        })

    except Exception as exc:
        log.error("Recommendation failed: %s", exc, exc_info=True)
        raise HTTPException(status_code=500, detail=f"Recommendation error: {str(exc)}")

@app.post("/admin/refresh-models", tags=["Admin"])
async def refresh_models():
    """Manually reload all model artifacts from disk without restarting."""
    try:
        # dropout
        store.model   = _load_pkl(MODELS_DIR / "dropout_model.pkl", "RandomForestClassifier")
        store.scaler  = _load_pkl(MODELS_DIR / "scaler.pkl",        "StandardScaler")
        store.encoder = _load_pkl(MODELS_DIR / "encoder.pkl",       "LabelEncoder")
        store.ready   = all([store.model, store.scaler, store.encoder])

        # recommender
        store.tfidf_vectorizer = _load_pkl(MODELS_DIR / "tfidf_vectorizer.pkl", "TfidfVectorizer")
        store.course_features  = _load_pkl(MODELS_DIR / "course_features.pkl",  "CourseFeatureMatrix")
        index_data             = _load_pkl(MODELS_DIR / "course_index.pkl",      "CourseIndex")
        if index_data:
            store.index_to_id = index_data.get("index_to_id", {})
        store.recommender_ready = (store.tfidf_vectorizer is not None and store.course_features is not None)

        # performance
        store.performance_model = _load_pkl(MODELS_DIR / "course_performance_model.pkl", "CoursePerformanceModel")
        store.performance_ready = store.performance_model is not None

        return {"status": "success", "message": "All artifacts reloaded."}
    except Exception as e:
        log.error("Manual refresh failed: %s", e)
        raise HTTPException(status_code=500, detail=str(e))


# ─────────────────────────────────────────────
# PERFORMANCE SCHEMAS
# ─────────────────────────────────────────────

class AnalyzeCourseRequest(BaseModel):
    enrollment_count: int   = Field(..., example=120, description="Total number of enrollments")
    resource_count:   int   = Field(..., example=15,  description="Total number of resources")
    completion_rate:  float = Field(..., example=65.5, description="Average progress / completion rate (0 - 100)")


class AnalyzeCourseResponse(BaseModel):
    predicted_quality_score: float = Field(..., description="Predicted performance score (0 - 100)")
    label:                   str   = Field(..., description="Performance category Label")


# ─────────────────────────────────────────────
# PERFORMANCE ENDPOINT
# ─────────────────────────────────────────────

@app.post("/analyze/course", response_model=AnalyzeCourseResponse, tags=["Performance"])
def analyze_course(body: AnalyzeCourseRequest):
    """
    Predict the quality score and performance label for a given course.

    - Uses a LinearRegression model to predict a score based on course metrics.
    - Labels: High (80+), Good (60+), Moderate (40+), Low (<40).
    """
    if not store.performance_ready:
        raise HTTPException(
            status_code=503,
            detail="Course Performance model not loaded. Run train_course_performance.py first.",
        )

    try:
        # Input features for model
        # Real Data: Hardcode avg_rating to 4.0
        X_input = [[
            body.enrollment_count,
            4.0,  # Hardcoded real rating default
            body.resource_count,
            body.completion_rate
        ]]
        
        perf_model = cast(Any, store.performance_model)
        if not perf_model:
            raise ValueError("Performance model missing.")

        predicted_score = float(perf_model.predict(X_input)[0])
        predicted_score = max(0.0, min(100.0, predicted_score)) # Cap to [0, 100]

        # Determine label
        if predicted_score >= 80:
            label = "High Performing"
        elif predicted_score >= 60:
            label = "Good Performance"
        elif predicted_score >= 40:
            label = "Moderate Performance"
        else:
            label = "Low Performance / Needs Improvement"

        return AnalyzeCourseResponse(**{
            "predicted_quality_score": round(predicted_score, 2),
            "label":                   label
        })

    except Exception as exc:
        log.error("Course analysis failed: %s", exc, exc_info=True)
        raise HTTPException(status_code=500, detail=f"Performance analysis error: {str(exc)}")


# ─────────────────────────────────────────────
# VECTOR ADMIN ENDPOINTS
# ─────────────────────────────────────────────

@app.post("/admin/rebuild-index", tags=["Admin"])
async def trigger_rebuild_index(background_tasks: BackgroundTasks):
    """
    Trigger a full rebuild of the FAISS vector index by fetching current
    content from MySQL. This runs in the background.
    """
    try:
        # Run the build_index function in the background task queue
        background_tasks.add_task(build_index)
        return {
            "status": "queued",
            "message": "Vector index rebuild started in the background. This may take a minute."
        }
    except Exception as exc:
        log.error("Failed to start vector rebuild task: %s", exc)
        raise HTTPException(status_code=500, detail="Could not queue rebuild task.")


# ─────────────────────────────────────────────
# CHAT SCHEMAS
# ─────────────────────────────────────────────

class ChatRequest(BaseModel):
    message: str = Field(..., example="What am I enrolled in?", description="User query for the AI")
    user_id: int = Field(..., example=1, description="ID of the student/user")


class ChatResponse(BaseModel):
    response: str = Field(..., description="Agent's generated response")


# ─────────────────────────────────────────────
# CHAT ENDPOINT
# ─────────────────────────────────────────────

@app.post("/chat", tags=["AI Agent"])
def chat(body: ChatRequest):
    """
    Interact with the EduLink Hybrid Agent.
    Handles general platform knowledge (via FAISS) and specific student data (via SQL).
    """
    try:
        # Pass user context to agent
        reply = get_agent_response(body.message, user_id=body.user_id)
        return ChatResponse(**{"response": reply})
        
    except Exception as exc:
        log.error("Chat error: %s", exc)
        raise HTTPException(status_code=500, detail="The AI Assistant is currently experiencing issues.")

# ─────────────────────────────────────────────
# NEW: QUIZ SCORE FOR ADMIN
# ─────────────────────────────────────────────

@app.get("/student/{student_id}/last-quiz-score", tags=["Admin Tools"])
def get_last_quiz_score(student_id: int):
    """
    Fetch the last quiz score for a student from the database.
    Used for admin analysis.
    """
    import mysql.connector
    
    # Database configuration from environment or hardcoded mapping as seen in .env
    DB_CONFIG = {
        "host":     "127.0.0.1",
        "port":     3306,
        "user":     "root",
        "password": "",
        "database": "edu",
    }
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        # We use the table mentioned in AdminController: 'quiz_result'
        # If it doesn't exist, we fallback to a safe 0.0 or check user_matiere_stat
        try:
            query = "SELECT score FROM quiz_result WHERE user_id = %s ORDER BY id DESC LIMIT 1"
            cursor.execute(query, (student_id,))
            row = cursor.fetchone()
            
            if row:
                score = float(row['score'])
            else:
                # Fallback check in user_matiere_stat if quiz_result is empty or missing
                query = "SELECT pointsEarned FROM user_matiere_stat WHERE user_id = %s LIMIT 1"
                cursor.execute(query, (student_id,))
                row = cursor.fetchone()
                score = float(row['pointsEarned']) if row else 0.0
                
        except mysql.connector.Error:
            # Table doesn't exist or other DB error
            score = 0.0
            
        conn.close()
        return {"student_id": student_id, "last_quiz_score": score}
        
    except Exception as exc:
        log.error("Failed to fetch quiz score: %s", exc)
        return {"student_id": student_id, "last_quiz_score": 0.0, "error": str(exc)}

