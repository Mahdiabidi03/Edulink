import os
import numpy as np
import pandas as pd
from gensim.models import Word2Vec
from sklearn.metrics.pairwise import cosine_similarity
import re

class SemanticSearch:
    """
    A Custom Semantic Search model trained from absolute scratch.
    It builds its own word embeddings using Gensim's Word2Vec on the dataset,
    creates average "document vectors" for each journal entry, and retrieves
    semantically similar entries using Cosine Similarity.
    """
    def __init__(self, model_path: str = None, vectors_path: str = None, original_data_path: str = None):
        self.model_path = model_path
        self.vectors_path = vectors_path
        self.original_data_path = original_data_path
        self.w2v_model = None
        self.document_vectors = None
        self.corpus = None # Keep original text to return as search results

    def _tokenize(self, text: str) -> list[str]:
        """Simple tokenizer to clean and split text into words."""
        text = str(text).lower()
        # Keep only alphanumeric characters
        text = re.sub(r'[^a-z0-9\s]', '', text)
        return text.split()

    def _get_document_vector(self, tokens: list[str]) -> np.ndarray:
        """
        Creates a single vector representing the entire document by 
        averaging the Word2Vec embeddings of all its constituent words.
        """
        valid_words = [word for word in tokens if word in self.w2v_model.wv.key_to_index]
        if not valid_words:
            # Fallback for empty/unrecognized documents
            return np.zeros(self.w2v_model.vector_size)
        
        # Get vectors for all known words and compute the mean along the columns
        vectors = [self.w2v_model.wv[word] for word in valid_words]
        return np.mean(vectors, axis=0)

    def train(self, data_path: str):
        print("Loading training data for Semantic Search Model...")
        if not os.path.exists(data_path):
            raise FileNotFoundError(f"Data file not found at {data_path}")
            
        df = pd.read_csv(data_path)
        if 'text' not in df.columns:
            raise ValueError("CSV must contain a 'text' column.")
            
        self.corpus = df['text'].fillna('').tolist()
        
        # 1. Tokenize the entire corpus into lists of words
        tokenized_corpus = [self._tokenize(doc) for doc in self.corpus]
        
        # 2. Train Word2Vec FROM SCRATCH on our synthetic corpus.
        # vector_size=100 (creates 100-dimensional embeddings)
        # window=5 (looks at 5 words before and after context)
        # min_count=1 (since our vocabulary is small, keep all words)
        print("Training custom Word2Vec embeddings from scratch...")
        self.w2v_model = Word2Vec(sentences=tokenized_corpus, vector_size=100, window=5, min_count=1, workers=4, epochs=50)
        
        # 3. Compute the average document vectors for the entire corpus
        print("Computing average document vectors...")
        self.document_vectors = np.array([self._get_document_vector(tokens) for tokens in tokenized_corpus])
        print("✅ Semantic Search model training completed.")

    def save(self):
        """Saves the Word2Vec model, the precomputed document vectors, and the original corpus."""
        if not self.w2v_model or self.document_vectors is None:
            raise RuntimeError("Model is not trained yet. Call train() first.")
        
        if not self.model_path or not self.vectors_path or not self.original_data_path:
            raise ValueError("Cannot save: Paths not configured.")
            
        os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
            
        # Save Gensim Model
        self.w2v_model.save(self.model_path)
        
        # Save Numpy Document Vectors
        np.save(self.vectors_path, self.document_vectors)
        
        # Save original corpus to a simple txt/csv or pickle (using pandas pickle here)
        pd.Series(self.corpus).to_pickle(self.original_data_path)
            
        print(f"✅ Semantic Search artifacts saved.")

    def load(self):
        """Loads the pre-trained Word2Vec model and precomputed document vectors."""
        if not os.path.exists(self.model_path) or not os.path.exists(self.vectors_path) or not os.path.exists(self.original_data_path):
            raise FileNotFoundError("One or more Semantic Search artifact files are missing.")
            
        self.w2v_model = Word2Vec.load(self.model_path)
        self.document_vectors = np.load(self.vectors_path)
        self.corpus = pd.read_pickle(self.original_data_path).tolist()
        print(f"Loaded Semantic Search artifacts from disk.")

    def search(self, query: str, top_k: int = 3) -> list[tuple[str, float]]:
        """
        Takes a natural language query, computes its average custom Word2Vec vector,
        and returns the top K most semantically similar journal entries.
        """
        if not self.w2v_model or self.document_vectors is None:
            raise RuntimeError("Model is not loaded or trained. Cannot search.")
            
        # 1. Convert query to vector
        query_tokens = self._tokenize(query)
        query_vector = self._get_document_vector(query_tokens)
        
        # If the query had absolutely no recognizable words
        if np.all(query_vector == 0):
            return []
            
        # 2. Compute Cosine Similarity between query vector and ALL document vectors
        # Note: cosine_similarity expects 2D arrays, so we reshape the query vector
        similarities = cosine_similarity(query_vector.reshape(1, -1), self.document_vectors)[0]
        
        # 3. Get indices of top K highest similarity scores
        # argsort sorts ascending, so we take the last `top_k` and reverse them `[::-1]`
        top_indices = np.argsort(similarities)[-top_k:][::-1]
        
        # 4. Map indices back to original texts and scores
        results = []
        for idx in top_indices:
            score = similarities[idx]
            # Optional threshold to ignore complete mismatches (e.g. score < 0.2)
            if score > 0.0:
                results.append((self.corpus[idx], round(score, 4)))
                
        return results
