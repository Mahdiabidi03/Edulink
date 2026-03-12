import os
import sys
import json
import warnings

# Ensure the web server can find the pip packages installed individually for the 'deliz' user
sys.path.append(r"C:\Users\deliz\AppData\Roaming\Python\Python311\site-packages")

from classifier import JournalClassifier
from tag_generator import TagGenerator
from semantic_search import SemanticSearch

warnings.filterwarnings('ignore')

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No input text provided"}))
        sys.exit(1)

    text = sys.argv[1]
    
    delizar_dir = os.path.dirname(os.path.abspath(__file__))
    models_dir = os.path.join(delizar_dir, "models")
    
    import io
    
    # Redirect stdout to suppress print statements from models
    original_stdout = sys.stdout
    sys.stdout = io.StringIO()
    
    try:
        # 1. Classification
        classifier = JournalClassifier(model_path=os.path.join(models_dir, "classifier.pkl"))
        classifier.load()
        category = classifier.predict(text)
        
        # 2. Tag Generation
        tagger = TagGenerator(
            model_path=os.path.join(models_dir, "tagger.pkl"),
            mlb_path=os.path.join(models_dir, "tagger_mlb.pkl")
        )
        tagger.load()
        tags = tagger.generate_tags(text)
        
        # 3. Semantic Search
        searcher = SemanticSearch(
            model_path=os.path.join(models_dir, 'semantic_w2v.model'),
            vectors_path=os.path.join(models_dir, 'semantic_vectors.npy'),
            original_data_path=os.path.join(models_dir, 'semantic_corpus.pkl')
        )
        searcher.load()
        similar_entries = searcher.search(text, top_k=3)
        
        results = {
            "category": category,
            "tags": tags,
            "similar_entries": [
                {"text": entry, "score": float(score)} for entry, score in similar_entries
            ]
        }
        
        # Restore stdout and print JSON result
        sys.stdout = original_stdout
        print(json.dumps(results))
        
    except Exception as e:
        sys.stdout = original_stdout
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
