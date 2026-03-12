import sys
import json
import os
import warnings

# Ensure the web server can find the pip packages installed individually for the 'deliz' user
sys.path.append(r"C:\Users\deliz\AppData\Roaming\Python\Python311\site-packages")

from semantic_search import SemanticSearch

warnings.filterwarnings('ignore')

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Missing arguments (query)"}))
        sys.exit(1)

    query = sys.argv[1]
    
    try:
        input_data = sys.stdin.read()
        documents = json.loads(input_data) # format: [{"id": 1, "text": "my note content"}, ...]
    except Exception as e:
        print(json.dumps({"error": f"Invalid JSON documents format from STDIN: {str(e)}"}))
        sys.exit(1)

    delizar_dir = os.path.dirname(os.path.abspath(__file__))
    models_dir = os.path.join(delizar_dir, "models")
    
    import io
    original_stdout = sys.stdout
    sys.stdout = io.StringIO()
    
    try:
        searcher = SemanticSearch(
            model_path=os.path.join(models_dir, 'semantic_w2v.model'),
            vectors_path=os.path.join(models_dir, 'semantic_vectors.npy'),
            original_data_path=os.path.join(models_dir, 'semantic_corpus.pkl')
        )
        searcher.load()
        
        # Calculate semantic score for each document against the query
        scored_docs = []
        for doc in documents:
            q_vec = searcher._get_document_vector(query)
            d_vec = searcher._get_document_vector(doc["text"])
            
            from scipy.spatial.distance import cosine
            import numpy as np
            
            if np.all(q_vec == 0) or np.all(d_vec == 0):
                score = 0.0
            else:
                score = 1 - cosine(q_vec, d_vec)
                
            scored_docs.append({
                "id": doc["id"],
                "score": float(score)
            })
            
        # Sort by best score descending
        scored_docs.sort(key=lambda x: x["score"], reverse=True)
        
        sys.stdout = original_stdout
        print(json.dumps({"success": True, "results": scored_docs}))
        
    except Exception as e:
        sys.stdout = original_stdout
        print(json.dumps({"success": False, "error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
