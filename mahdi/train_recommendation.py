"""
Course Recommendation Engine — Training Script
Reads courses from MySQL (edu.cours), trains TF-IDF, precomputes cosine
similarity, and saves artifacts to models/.

Run: python mahdi/train_recommendation.py
"""

import os
import pickle
from pathlib import Path

import mysql.connector
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# ─────────────────────────────────────────────
# 1. CONFIG
# ─────────────────────────────────────────────

DB_CONFIG = {
    "host":     "127.0.0.1",
    "port":     3306,
    "user":     "root",
    "password": "",
    "database": "edu",
}

MODELS_DIR = Path(__file__).parent / "models"
MODELS_DIR.mkdir(exist_ok=True)

TFIDF_PATH    = MODELS_DIR / "tfidf_vectorizer.pkl"
FEATURES_PATH = MODELS_DIR / "course_features.pkl"
INDEX_PATH    = MODELS_DIR / "course_index.pkl"

# ─────────────────────────────────────────────
# 2. FETCH COURSES FROM MYSQL
# ─────────────────────────────────────────────

print("[Connect] Connecting to MySQL (edu)...")
conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor(dictionary=True)

cursor.execute("""
    SELECT c.id, c.title, c.description, c.level, m.name AS category
    FROM cours c
    LEFT JOIN matiere m ON c.matiere_id = m.id
""")
rows = cursor.fetchall()
cursor.close()
conn.close()

if not rows:
    raise RuntimeError("[Error] No courses found in `edu.cours`. Make sure the table has data.")

df = pd.DataFrame(rows)
print(f"[Done] Fetched {len(df)} courses from the database.\n")

# ─────────────────────────────────────────────
# 3. BUILD TEXT CORPUS
# ─────────────────────────────────────────────

# Fill NaN with empty string before concatenating
df["title"]       = df["title"].fillna("")
df["description"] = df["description"].fillna("")
df["category"]    = df["category"].fillna("")
df["level"]       = df["level"].fillna("")

# The corpus now includes category and level to ground recommendations
# and prevent hallucinated cross-domain matches
df["text"] = df["category"] + " " + df["level"] + " " + df["title"] + " " + df["description"]

print("[Task] Sample course texts:")
print(df[["id", "text"]].head(3).to_string(index=False))
print()

# ─────────────────────────────────────────────
# 4. TRAIN TF-IDF VECTORIZER
# ─────────────────────────────────────────────

print("[Train] Training TfidfVectorizer...")
vectorizer = TfidfVectorizer(
    strip_accents="unicode",
    analyzer="word",
    ngram_range=(1, 2),      # unigrams + bigrams for better matching
    min_df=1,
    max_features=10_000,
    sublinear_tf=True,
)
tfidf_matrix = vectorizer.fit_transform(df["text"])
print(f"   Matrix shape: {tfidf_matrix.shape}  (courses x features)\n")

# ─────────────────────────────────────────────
# 5. PRECOMPUTE COSINE SIMILARITY MATRIX
# ─────────────────────────────────────────────

print("[Compute] Computing cosine similarity matrix...")
similarity_matrix = cosine_similarity(tfidf_matrix, tfidf_matrix)
print(f"   Similarity matrix shape: {similarity_matrix.shape}\n")

# ─────────────────────────────────────────────
# 6. BUILD INDEX MAPPING  (DataFrame index -> Course ID)
# ─────────────────────────────────────────────

# {0: 12, 1: 15, ...}  — maps row position to actual DB id
index_to_id = dict(enumerate(df["id"].tolist()))
id_to_index = {v: k for k, v in index_to_id.items()}

# ─────────────────────────────────────────────
# 7. SAVE ARTIFACTS
# ─────────────────────────────────────────────

with open(TFIDF_PATH,    "wb") as f: pickle.dump(vectorizer,        f)
with open(FEATURES_PATH, "wb") as f: pickle.dump(tfidf_matrix,      f)
with open(INDEX_PATH,    "wb") as f: pickle.dump({
    "index_to_id": index_to_id,
    "id_to_index": id_to_index,
}, f)

print("[Success] Artifacts saved:")
print(f"   Vectorizer       -> {TFIDF_PATH}")
print(f"   Course features  -> {FEATURES_PATH}")
print(f"   Index mapping    -> {INDEX_PATH}")
print("\n[Complete] Recommendation engine training complete!")
