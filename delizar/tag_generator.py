import os
import pickle
import pandas as pd
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.multioutput import MultiOutputClassifier
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import MultiLabelBinarizer

class TagGenerator:
    """
    A custom Multi-Label Tag Generator trained from scratch.
    It uses CountVectorizer for text features, and MultiOutputClassifier
    to predict the presence or absence of multiple potential tags simultaneously.
    """
    def __init__(self, model_path: str = None, mlb_path: str = None):
        self.model_path = model_path
        self.mlb_path = mlb_path
        self.pipeline = None
        self.mlb = MultiLabelBinarizer()

    def train(self, data_path: str):
        print("Loading training data for Tag Generator Model...")
        if not os.path.exists(data_path):
            raise FileNotFoundError(f"Data file not found at {data_path}")
            
        df = pd.read_csv(data_path)
        
        # Ensure we have the required columns
        if 'text' not in df.columns or 'tags' not in df.columns:
            raise ValueError("CSV must contain 'text' and 'tags' columns.")
            
        X = df['text'].fillna('')
        
        # Tags are saved as comma-separated strings. We need to split them into lists.
        # e.g., "urgent,meeting" -> ["urgent", "meeting"]
        y_raw = df['tags'].fillna('').apply(lambda x: [t.strip() for t in x.split(',') if t.strip()])
        
        # MultiLabelBinarizer converts lists of tags into a binary matrix
        # e.g., [1, 0, 1, 0, 0...] where 1 indicates presence of the specific tag column
        y = self.mlb.fit_transform(y_raw)
        
        print(f"Discovered {len(self.mlb.classes_)} unique tags.")
        
        # Architecture:
        # 1. CountVectorizer creates a bag-of-words matrix
        # 2. MultiOutputClassifier wraps LogisticRegression, effectively training
        #    one distinct Logistic Regression model for EVERY single tag.
        self.pipeline = Pipeline([
            ('vectorizer', CountVectorizer(max_features=5000, stop_words='english', lowercase=True)),
            ('clf', MultiOutputClassifier(LogisticRegression(random_state=42, max_iter=1000, class_weight='balanced')))
        ])
        
        print("Training Tag Generator from scratch...")
        self.pipeline.fit(X, y)
        print("✅ Tag Generator training completed.")

    def save(self):
        """Serializes the trained pipeline and the MultiLabelBinarizer."""
        if not self.pipeline:
            raise RuntimeError("Model is not trained yet. Call train() first.")
        
        if not self.model_path or not self.mlb_path:
            raise ValueError("Cannot save: Model or MLB path not configured.")
            
        os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
            
        # Save Pipeline
        with open(self.model_path, 'wb') as f:
            pickle.dump(self.pipeline, f)
            
        # Save MLB (we need it to inverse-transform binary arrays back to string tags during inference)
        with open(self.mlb_path, 'wb') as f:
            pickle.dump(self.mlb, f)
            
        print(f"✅ Tag Generator saved to: {self.model_path} & {self.mlb_path}")

    def load(self):
        """Loads pre-trained model and MLB from disk."""
        if not os.path.exists(self.model_path) or not os.path.exists(self.mlb_path):
            raise FileNotFoundError("Model or MLB file not found.")
            
        with open(self.model_path, 'rb') as f:
            self.pipeline = pickle.load(f)
            
        with open(self.mlb_path, 'rb') as f:
            self.mlb = pickle.load(f)
            
        print(f"Loaded Tag Generator from: {self.model_path}")

    def generate_tags(self, text: str) -> list[str]:
        """Predicts and returns a list of tags for a raw string of text."""
        if not self.pipeline:
            raise RuntimeError("Model is not loaded or trained. Cannot predict.")
            
        # Predict returns a 2D binary array: [[0, 1, 0, 1...]]
        prediction_binary = self.pipeline.predict([text])
        
        # Inverse transform maps the 1s back to their string tag names
        predicted_tags = self.mlb.inverse_transform(prediction_binary)
        
        # predicted_tags is a list of tuples, we want just a flat list for the first (and only) prediction
        return list(predicted_tags[0])
