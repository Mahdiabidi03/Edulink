import os
import pickle
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.pipeline import Pipeline
from sklearn.metrics import classification_report

class JournalClassifier:
    """
    A custom Text Classification model trained from scratch.
    It uses TF-IDF to convert text into token frequencies, and a Logistic
    Regression model to classify the text into one of our predefined categories.
    """
    def __init__(self, model_path: str = None):
        self.model_path = model_path
        self.pipeline = None

    def train(self, data_path: str):
        """Trains the model from scratch using the provided CSV data."""
        print("Loading training data for Classification Model...")
        if not os.path.exists(data_path):
            raise FileNotFoundError(f"Data file not found at {data_path}")
            
        df = pd.read_csv(data_path)
        
        # Ensure we have the required columns
        if 'text' not in df.columns or 'category' not in df.columns:
            raise ValueError("CSV must contain 'text' and 'category' columns.")
            
        # Basic preprocessing (lowercasing, though TfidfVectorizer handles this)
        X = df['text'].fillna('')
        y = df['category']
        
        # Build the architecture from scratch: 
        # 1. TF-IDF (Term Frequency-Inverse Document Frequency)
        # 2. Logistic Regression (Linear Classifier)
        self.pipeline = Pipeline([
            ('tfidf', TfidfVectorizer(max_features=5000, stop_words='english', lowercase=True)),
            ('clf', LogisticRegression(random_state=42, max_iter=1000, class_weight='balanced'))
        ])
        
        print("Training Journal Text Classifier from scratch...")
        self.pipeline.fit(X, y)
        
        # Evaluate on the training set to ensure it learned successfully
        predictions = self.pipeline.predict(X)
        print("\n--- Classification Training Report ---")
        print(classification_report(y, predictions))
        print("--------------------------------------\n")

    def save(self):
        """Serializes the trained model to disk."""
        if not self.pipeline:
            raise RuntimeError("Model is not trained yet. Call train() first.")
        
        if not self.model_path:
            raise ValueError("Cannot save: No model path configured.")
            
        # Ensure directory exists
        os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
            
        with open(self.model_path, 'wb') as f:
            pickle.dump(self.pipeline, f)
        print(f"✅ Text Classifier saved to: {self.model_path}")

    def load(self):
        """Loads a pre-trained model from disk."""
        if not os.path.exists(self.model_path):
            raise FileNotFoundError(f"Model file not found at {self.model_path}")
            
        with open(self.model_path, 'rb') as f:
            self.pipeline = pickle.load(f)
        print(f"Loaded Text Classifier from: {self.model_path}")

    def predict(self, text: str) -> str:
        """Predicts the category of a raw string of text."""
        if not self.pipeline:
            raise RuntimeError("Model is not loaded or trained. Cannot predict.")
            
        # The pipeline automatically handles TF-IDF vectorization and classification
        prediction = self.pipeline.predict([text])
        return prediction[0]
