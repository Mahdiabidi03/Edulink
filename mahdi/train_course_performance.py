"""
Course Performance & Quality Model — Training Script
Aggregates course data from MySQL (edu), calculates a Success Score,
and trains a LinearRegression model to predict it.

Run: python mahdi/train_course_performance.py
"""

import os
import pickle
from pathlib import Path

import mysql.connector
import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, r2_score

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

MODEL_PATH = MODELS_DIR / "course_performance_model.pkl"

# ─────────────────────────────────────────────
# 2. DATA GATHERING
# ─────────────────────────────────────────────

def fetch_real_data():
    try:
        print("[Connect] Connecting to MySQL (edu)...")
        conn = mysql.connector.connect(**DB_CONFIG)
        
        # Aggregate enrollment data
        query_enrollments = "SELECT cours_id, COUNT(*) as enrollment_count, AVG(progress) as completion_rate FROM enrollment GROUP BY cours_id"
        df_enr = pd.read_sql(query_enrollments, conn)
        
        # Aggregate review data
        query_reviews = "SELECT cours_id, AVG(rating) as avg_rating FROM review GROUP BY cours_id"
        df_rev = pd.read_sql(query_reviews, conn)
        
        # Aggregate resource data
        query_resources = "SELECT cours_id, COUNT(*) as resource_count FROM resource GROUP BY cours_id"
        df_res = pd.read_sql(query_resources, conn)
        
        conn.close()
        
        # Merge dataframes
        df = df_enr.merge(df_rev, on="cours_id", how="left")
        df = df.merge(df_res, on="cours_id", how="left")
        
        # Fill missing values
        df["avg_rating"] = df["avg_rating"].fillna(3.5)  # Neutral rating
        df["resource_count"] = df["resource_count"].fillna(0)
        df["completion_rate"] = df["completion_rate"].fillna(0)
        
        return df
    except Exception as e:
        print(f"[Warning] Could not fetch real data: {e}")
        return None

def generate_dummy_data(n=200):
    print("[Dummy] Generating dummy training data...")
    np.random.seed(42)
    data = {
        "enrollment_count": np.random.randint(5, 500, n),
        "avg_rating":       np.random.uniform(1, 5, n),
        "resource_count":   np.random.randint(1, 30, n),
        "completion_rate":  np.random.uniform(0, 100, n)
    }
    return pd.DataFrame(data)

# ─────────────────────────────────────────────
# 3. LOAD & PREPARE DATA
# ─────────────────────────────────────────────

df = fetch_real_data()

# If real data is too small or failed, augment with dummy data
if df is None or len(df) < 10:
    dummy_df = generate_dummy_data(200 if df is None else 200 - len(df))
    if df is not None:
        df = pd.concat([df, dummy_df], ignore_index=True)
    else:
        df = dummy_df

# Calculate target variable: Success Score
# Formula: (enrollment_count * 0.5) + (avg_rating * 10) + (completion_rate)
if df is not None:
    print(f"[Done] Prepared {len(df)} samples for training.")
    df["success_score"] = (
        (df["enrollment_count"] * 0.5) + 
        (df["avg_rating"] * 10) + 
        (df["completion_rate"])
    )
    # Cap score at 100
    df["success_score"] = df["success_score"].clip(0, 100)
    print(df.head())
else:
    print("[Error] Failed to prepare data. Check database connection or CSV path.")
    exit(1)

# ─────────────────────────────────────────────
# 4. TRAINING
# ─────────────────────────────────────────────

features = ["enrollment_count", "avg_rating", "resource_count", "completion_rate"]
X = df[features]
y = df["success_score"]

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

print("\n[Train] Training LinearRegression model...")
model = LinearRegression()
model.fit(X_train, y_train)

# Evaluate
y_pred = model.predict(X_test)
mse = mean_squared_error(y_test, y_pred)
r2 = r2_score(y_test, y_pred)

print(f"   Mean Squared Error: {mse:.4f}")
print(f"   R2 Score: {r2:.4f}")

# ─────────────────────────────────────────────
# 5. SAVE ARTIFACT
# ─────────────────────────────────────────────

with open(MODEL_PATH, "wb") as f:
    pickle.dump(model, f)

print(f"\n[Success] Model saved to {MODEL_PATH}")
print("\n[Complete] Course Performance training complete!")
