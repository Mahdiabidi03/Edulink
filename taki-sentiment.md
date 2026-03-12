# Task: Taki Sentiment Analysis (Self-Learning)

## 1. Goal
Build a self-learning **Student Sentiment AI** that tracks the community's mood. It will be managed exclusively by the Admin, not teachers. All machine learning scripts and models will be contained in the `taki` directory.

## 2. Architecture & Approach
We will use **Scikit-Learn's `SGDClassifier`** configured for partial fitting (Online Learning). This means the model can learn from new data in real-time without retraining from scratch.

### 2.1 The `taki` Directory Structure
- `taki/sentiment_api.py`: The microservice API (Flask/FastAPI) that the Symfony backend will talk to. It has two main endpoints:
  - `POST /predict_sentiment`: Takes a student's text and returns a sentiment score (Positive/Neutral/Negative).
  - `POST /teach_sentiment`: Takes a text and a *corrected* label from the Admin, and instantly updates the model using `partial_fit()`.
- `taki/init_model.py`: A script to create the initial "brain" (model) so it has a baseline understanding of sentiment before it starts learning from the Edulink admin.
- `taki/model_data/`: A persistent folder to store the `.pkl` files (the trained model and vectorizer).

### 2.2 Symfony Backend Integration
- **Database:** We will add a new entity/table called `AiSentimentLog` or add a `sentiment_score` column to `CommunityPost` or `Message`.
- **Admin Dashboard:** A view where the Admin can see a list of flagged posts or the general sentiment trend. If the Admin disagrees with the AI's sentiment (e.g., AI said "Neutral" but it was actually "Negative"), the Admin clicks a button to correct it. This sends a request to `taki/sentiment_api.py` -> `/teach_sentiment`, updating the AI instantly.

## 3. Implementation Steps
- [ ] **Step 1:** Create `taki/init_model.py` to generate the base `SGDClassifier` and `HashingVectorizer` (which is required for online learning text).
- [ ] **Step 2:** Create `taki/sentiment_api.py` with `/predict_sentiment` and `/teach_sentiment` endpoints.
- [ ] **Step 3:** Test the Python API locally to ensure it learns from `/teach_sentiment`.
- [ ] **Step 4:** Integrate with EduLink's Symfony backend (Create the Admin Interface to view sentiment and correct the AI).
