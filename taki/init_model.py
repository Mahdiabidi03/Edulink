import os
import joblib
from sklearn.linear_model import SGDClassifier
from sklearn.feature_extraction.text import HashingVectorizer
import numpy as np

MODEL_DIR = os.path.join(os.path.dirname(__file__), 'model_data')
if not os.path.exists(MODEL_DIR):
    os.makedirs(MODEL_DIR)

MODEL_PATH = os.path.join(MODEL_DIR, 'sentiment_sgd.pkl')

# We use HashingVectorizer because it uses a hashing trick.
# This means we don't need to rebuild our vocabulary or re-fit the vectorizer 
# when new, unseen words come in during `partial_fit`!
vectorizer = HashingVectorizer(stop_words='english', alternate_sign=False, n_features=2**16)

# We use SGDClassifier with log_loss to get probabilities (confidence scores)
# and it supports partial_fit for continuous self-learning.
classifier = SGDClassifier(loss='log_loss', penalty='l2', alpha=1e-4, random_state=42)

# Here is some tiny starter data just to give the model the initial classes.
# The 3 classes will be: 'Positive', 'Neutral', 'Negative'
initial_texts = [
    "I love this course, it is amazing and great.",
    "The teacher is wonderful and very helpful.",
    "This is okay, neither good nor bad.",
    "Just a normal day in class.",
    "I hate this, it is terrible and very bad.",
    "I am struggling, feeling frustrated and awful about the exam."
]

initial_labels = [
    "Positive",
    "Positive",
    "Neutral",
    "Neutral",
    "Negative",
    "Negative"
]

# All possible classes the model could ever see
all_classes = np.array(['Positive', 'Neutral', 'Negative'])

print("Initializing the self-learning Sentiment model...")
X = vectorizer.transform(initial_texts)

# We MUST pass `classes` the first time we call partial_fit
classifier.partial_fit(X, initial_labels, classes=all_classes)

# Save the initialized brain
model_data = {
    'classifier': classifier,
    'vectorizer': vectorizer
}
joblib.dump(model_data, MODEL_PATH)

print(f"[SUCCESS] Base model initialized and saved to {MODEL_PATH}")
print("Ready for continuous learning via the API!")
