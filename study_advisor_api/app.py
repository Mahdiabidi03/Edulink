"""
EduLink AI Study Advisor Microservice
Generates personalized weekly study advice using Google Gemini API.
Analyzes student Notes (sentiment) and Tasks (completion rate).
"""

import json
import os
from datetime import datetime

from google import genai
from flask import Flask, jsonify, request
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# ──────────────────────────────────────────────
# Configure Gemini API
# ──────────────────────────────────────────────
GEMINI_API_KEY = os.environ.get("GEMINI_API_KEY", "AIzaSyDE8EJSE1UdcsfQwMYuFtw3l9po9uzZTbc")

client = genai.Client(api_key=GEMINI_API_KEY)


def build_prompt(data: dict) -> str:
    """Build a study advice prompt from the student's weekly summary."""

    student_name = data.get("student_name", "Student")
    notes_count = data.get("notes_count", 0)
    notes_tags = data.get("notes_tags", [])
    notes_sentiments = data.get("notes_sentiments", [])
    tasks_total = data.get("tasks_total", 0)
    tasks_completed = data.get("tasks_completed", 0)
    completion_rate = data.get("tasks_completion_rate", 0)

    # Sentiment breakdown
    negative_notes = [n for n in notes_sentiments if n.get("sentiment") == "negative"]
    positive_notes = [n for n in notes_sentiments if n.get("sentiment") == "positive"]
    neutral_notes = [n for n in notes_sentiments if n.get("sentiment") == "neutral"]

    prompt = f"""You are a friendly, encouraging AI study coach for a university student.

Analyze the following weekly activity summary and provide 3-4 short, personalized study tips.

## Student Weekly Summary for {student_name}:

📝 NOTES:
- Wrote {notes_count} notes this week
- Tags used: {', '.join(notes_tags) if notes_tags else 'None'}
- Sentiment breakdown: {len(positive_notes)} positive, {len(neutral_notes)} neutral, {len(negative_notes)} negative
"""

    if negative_notes:
        prompt += "- Negative notes:\n"
        for n in negative_notes[:3]:
            prompt += f"  • \"{n.get('title', 'Untitled')}\"\n"

    if positive_notes:
        prompt += "- Positive notes:\n"
        for n in positive_notes[:3]:
            prompt += f"  • \"{n.get('title', 'Untitled')}\"\n"

    prompt += f"""
✅ TASKS:
- Created {tasks_total} tasks this week
- Completed {tasks_completed} of {tasks_total} ({completion_rate:.0f}% completion rate)

## Rules:
1. Be encouraging but realistic
2. Give specific, actionable advice (not generic)
3. Reference the student's actual data (e.g. "You completed only 3 of 8 tasks...")
4. If sentiment is mostly negative, add emotional support
5. If task completion is low, suggest time management techniques
6. Keep each tip to 2-3 sentences maximum
7. Use emojis to make it friendly
8. Respond in English
9. Format as a numbered list

Respond ONLY with the advice tips, no introduction or greeting needed."""

    return prompt


@app.route("/advice", methods=["POST"])
def get_advice():
    """
    POST /advice
    Body: { student weekly summary JSON }
    Returns: { "advice": "...", "generated_at": "..." }
    """
    data = request.get_json(silent=True)

    if not data:
        return jsonify({"error": "Missing JSON body"}), 400

    prompt = build_prompt(data)
    print(f"[INFO] Prompt length: {len(prompt)} characters")

    try:
        response = client.models.generate_content(
            model="gemini-2.0-flash",
            contents=prompt,
        )
        advice_text = response.text

        return jsonify({
            "advice": advice_text,
            "generated_at": datetime.now().isoformat(),
        }), 200

    except Exception as e:
        print(f"[ERROR] Gemini API error: {e}")
        
        # Fallback for 429 / Rate Limit
        if "429" in str(e) or "RESOURCE_EXHAUSTED" in str(e):
             return jsonify({
                "advice": "⚠️ The Study Advisor is currently very busy. Here is a quick tip: Try to review your notes from this week and focus on the subjects where you completed fewer tasks! We'll have more detailed advice for you soon. 💪",
                "generated_at": datetime.now().isoformat(),
                "warning": "Rate limit reached"
            }), 200

        return jsonify({
            "error": f"Gemini API error: {str(e)}",
            "advice": None,
        }), 500


@app.route("/health", methods=["GET"])
def health():
    """Health check endpoint."""
    api_configured = GEMINI_API_KEY != "YOUR_API_KEY_HERE"
    return jsonify({
        "status": "ok",
        "service": "edulink-study-advisor-api",
        "gemini_configured": api_configured,
    }), 200


if __name__ == "__main__":
    print("🧠 EduLink Study Advisor API starting on port 5002...")
    print("   Endpoint: POST http://localhost:5002/advice")
    print("   Health:   GET  http://localhost:5002/health")

    if GEMINI_API_KEY == "YOUR_API_KEY_HERE":
        print("   ⚠️  WARNING: Set GEMINI_API_KEY environment variable or edit this file!")

    app.run(host="0.0.0.0", port=5002, debug=False)
