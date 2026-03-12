import os
import sys
import json
import warnings
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Dict

# Local imports
from classifier import JournalClassifier
from tag_generator import TagGenerator
from semantic_search import SemanticSearch

warnings.filterwarnings('ignore')

app = FastAPI(title="Delizar Journal AI API")

class JournalRequest(BaseModel):
    text: str

class SimilarEntry(BaseModel):
    text: str
    score: float

class JournalResponse(BaseModel):
    category: str
    tags: List[str]
    similar_entries: List[SimilarEntry]

# Global models
models = {}

@app.on_event("startup")
def load_models():
    delizar_dir = os.path.dirname(os.path.abspath(__file__))
    models_dir = os.path.join(delizar_dir, "models")
    
    try:
        # 1. Classification
        clf = JournalClassifier(model_path=os.path.join(models_dir, "classifier.pkl"))
        clf.load()
        models["classifier"] = clf
        
        # 2. Tag Generation
        tagger = TagGenerator(
            model_path=os.path.join(models_dir, "tagger.pkl"),
            mlb_path=os.path.join(models_dir, "tagger_mlb.pkl")
        )
        tagger.load()
        models["tagger"] = tagger
        
        # 3. Semantic Search
        searcher = SemanticSearch(
            model_path=os.path.join(models_dir, 'semantic_w2v.model'),
            vectors_path=os.path.join(models_dir, 'semantic_vectors.npy'),
            original_data_path=os.path.join(models_dir, 'semantic_corpus.pkl')
        )
        searcher.load()
        models["searcher"] = searcher
        
        print("✅ Delizar models loaded successfully")
    except Exception as e:
        print(f"❌ Error loading Delizar models: {e}")
        # We don't raise here to allow API to start, but requests will fail

@app.get("/health")
def health():
    ready = all(k in models for k in ["classifier", "tagger", "searcher"])
    return {"status": "ok" if ready else "error", "models_loaded": list(models.keys())}

@app.post("/predict", response_model=JournalResponse)
async def predict(request: JournalRequest):
    if not all(k in models for k in ["classifier", "tagger", "searcher"]):
        raise HTTPException(status_code=503, detail="Models not loaded")
    
    try:
        text = request.text
        
        category = models["classifier"].predict(text)
        tags = models["tagger"].generate_tags(text)
        similar_entries_raw = models["searcher"].search(text, top_k=3)
        
        similar_entries = [
            {"text": entry, "score": float(score)} for entry, score in similar_entries_raw
        ]
        
        return {
            "category": category,
            "tags": tags,
            "similar_entries": similar_entries
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=5006)
