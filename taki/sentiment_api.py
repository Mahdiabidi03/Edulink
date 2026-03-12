import os
import joblib
from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np

app = Flask(__name__)
CORS(app)

MODEL_DIR = os.path.join(os.path.dirname(__file__), 'model_data')
MODEL_PATH = os.path.join(MODEL_DIR, 'sentiment_sgd.pkl')

model_data = None
classifier = None
vectorizer = None

def load_brain():
    global model_data, classifier, vectorizer
    if not os.path.exists(MODEL_PATH):
        raise FileNotFoundError(f"[ERROR] Run `python init_model.py` first to generate the base model at {MODEL_PATH}")
    
    model_data = joblib.load(MODEL_PATH)
    classifier = model_data['classifier']
    vectorizer = model_data['vectorizer']
    print("[OK] Taki Sentiment Brain Loaded - Ready to learn!")

@app.route('/predict_sentiment', methods=['POST'])
def predict_sentiment():
    """
    Called whenever a student posts a comment or message.
    Returns: The predicted Sentiment (Positive/Neutral/Negative) and confidence.
    """
    data = request.get_json(silent=True)
    if not data or 'text' not in data:
        return jsonify({'error': 'Missing "text" field'}), 400

    text = data['text']
    # If the text is empty or just spaces
    if not text.strip():
        return jsonify({'sentiment': 'Neutral', 'confidence': 1.0})

    X = vectorizer.transform([text])
    
    # Predict the label and probability
    predicted_class = classifier.predict(X)[0]
    probabilities = classifier.predict_proba(X)[0]
    
    # Get highest probability
    confidence = float(max(probabilities))

    return jsonify({
        'sentiment': predicted_class,
        'confidence': round(confidence, 4)
    })

@app.route('/teach_sentiment', methods=['POST'])
def teach_sentiment():
    """
    Called ONLY by the Admin.
    When the Admin clicks 'Correct AI', the Symfony backend sends the text + the Correct Label.
    The AI model is instantly updated using `partial_fit` and re-saved.
    """
    data = request.get_json(silent=True)
    if not data or 'text' not in data or 'correct_label' not in data:
        return jsonify({'error': 'Needs "text" and "correct_label"'}), 400

    text = data['text']
    correct_label = data['correct_label'] # E.g., 'Positive', 'Neutral', 'Negative'

    # 1. Transform the new text
    X = vectorizer.transform([text])
    
    # 2. Update the model brain with the new feedback
    # `partial_fit` does NOT erase old memory. It nudges the weights towards
    # the correct answer!
    classifier.partial_fit(X, [correct_label])
    
    # 3. Save the brain so it remembers next time it is restarted!
    joblib.dump(model_data, MODEL_PATH)

    return jsonify({
        'status': 'success',
        'message': f'AI learned that "{text[:20]}..." is actually {correct_label}. Brain updated!'
    })

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'online',
        'modules': ['predict_sentiment', 'teach_sentiment']
    })

if __name__ == '__main__':
    load_brain()
    print("--- TAKI AI SENTIMENT SERVER ---")
    print("Running on http://127.0.0.1:5005")
    app.run(host='127.0.0.1', port=5005, debug=False)
