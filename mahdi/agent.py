"""
EduLink — Hybrid Manual Agent
Uses Groq (Llama-3.3-70b) for intent classification + SQL generation,
then queries the DB directly and returns a human-friendly answer.
"""

import os
import re
import logging
from pathlib import Path
from typing import Optional, List

from dotenv import load_dotenv
from langchain_groq import ChatGroq
from langchain_community.vectorstores import FAISS
from langchain_community.embeddings import SentenceTransformerEmbeddings
from langchain_core.messages import HumanMessage, SystemMessage
from sqlalchemy import create_engine, text

load_dotenv()

log = logging.getLogger(__name__)
MAHDI_DIR = Path(__file__).parent
FAISS_DIR = MAHDI_DIR / "faiss_index"
DB_URL = "mysql+mysqlconnector://root:@127.0.0.1:3306/edu"

# ─────────────────────────────────────────────
# DB SCHEMA — only expose tables the agent can query
# ─────────────────────────────────────────────
DB_SCHEMA = """
Tables available (ONLY query data for student_id = {uid}):
- enrollment(id, progress, cours_id, student_id)        — student's courses
- cours(id, title)                                      — course details (join with enrollment.cours_id = cours.id)
- notes(id, content, user_id)                           — student's notes
- reservation(id, status, user_id)                      — session reservations
- help_request(id, subject, status, student_id)         — help requests
- transaction(id, amount, type, created_at, user_id)    — wallet transactions
- user(id, full_name, email, xp, wallet_balance)        — user profile (where id = {uid})
- user_matiere_stat(id, points_earned, level, user_id)  — quiz/subject scores
"""

# ─────────────────────────────────────────────
# HELPERS
# ─────────────────────────────────────────────

def _get_llm():
    api_key = os.getenv("GROQ_API_KEY")
    if not api_key:
        log.error("GROQ_API_KEY not found")
        return None
    return ChatGroq(temperature=0.1, model_name="llama-3.3-70b-versatile", groq_api_key=api_key)


def _run_sql(sql: str, user_id: int) -> str:
    """Execute SQL safely, enforcing user_id isolation."""
    # Strip markdown code fences if LLM included them
    sql = re.sub(r"```(?:sql)?", "", sql).strip().strip("`")

    # Safety: reject multi-statement or destructive queries
    if any(kw in sql.upper() for kw in ["DROP", "DELETE", "UPDATE", "INSERT", "TRUNCATE", ";--"]):
        return "Error: Only SELECT queries are allowed."

    # Enforce: every query must reference the user's ID in some form
    user_id_str = str(user_id)
    if user_id_str not in sql:
        return f"Error: Query does not filter for user ID {user_id}. Refused for safety."

    try:
        engine = create_engine(DB_URL)
        with engine.connect() as conn:
            result = conn.execute(text(sql))
            rows = result.fetchall()
            if not rows:
                return "No data found."
            columns = list(result.keys())
            lines = [" | ".join(columns)]
            lines += [" | ".join(str(v) for v in row) for row in rows[:20]]
            return "\n".join(lines)
    except Exception as e:
        log.error(f"SQL error: {sql} — {e}")
        return f"Database error: {e}"


def _check_faiss(user_input: str) -> Optional[str]:
    """Quick FAISS check for public info (courses, events, etc.)"""
    if not FAISS_DIR.exists():
        return None
    try:
        embeddings = SentenceTransformerEmbeddings(model_name="all-MiniLM-L6-v2")
        vectorstore = FAISS.load_local(str(FAISS_DIR), embeddings, allow_dangerous_deserialization=True)
        docs_scores = vectorstore.similarity_search_with_score(user_input, k=1)
        if docs_scores:
            doc, score = docs_scores[0]
            if score < 0.3:  # High similarity threshold
                return doc.page_content
    except Exception as e:
        log.warning(f"FAISS check failed: {e}")
    return None


# ─────────────────────────────────────────────
# MAIN AGENT
# ─────────────────────────────────────────────

def get_agent_response(user_input: str, user_id: int, chat_history: Optional[List] = None) -> str:
    """
    1. Check FAISS for high-similarity public info
    2. Classify intent: PERSONAL (needs SQL) or GENERAL
    3. If PERSONAL: LLM generates SQL → execute → LLM formats answer
    4. Else: LLM answers directly
    """

    # Step 1: FAISS fast-path for public content
    faiss_result = _check_faiss(user_input)
    if faiss_result:
        return faiss_result

    # Step 2: Get LLM
    llm = _get_llm()
    if not llm:
        return "I'm sorry, my core AI engine is currently unavailable."

    # Step 3: Classify intent
    classification_prompt = (
        f"You are an intent classifier for EduLink, an educational platform.\n"
        f"Student ID: {user_id}\n"
        "Classify the following student question into exactly one category:\n"
        "- 'PERSONAL' if it asks about their own data (my XP, my score, my courses, my notes, my wallet, my balance, my reservations)\n"
        "- 'GENERAL' if it asks about the platform, events, how things work, or general knowledge\n\n"
        f"Question: {user_input}\n\n"
        "Respond with ONLY the single word: PERSONAL or GENERAL"
    )
    classification = llm.invoke([HumanMessage(content=classification_prompt)]).content.strip().upper()
    log.info(f"[Agent] Intent: {classification}")

    if "PERSONAL" in classification:
        # Step 4a: Generate SQL
        schema = DB_SCHEMA.format(uid=user_id)
        sql_prompt = (
            f"You are a SQL expert for EduLink database.\n"
            f"Student user_id = {user_id}. Generate a single safe SELECT query for this question.\n"
            f"Schema:\n{schema}\n\n"
            f"Question: {user_input}\n\n"
            "Rules:\n"
            f"- ALWAYS filter by user_id = {user_id} (or student_id = {user_id})\n"
            "- Return ONLY the raw SQL query. No explanation, no markdown.\n"
            "- LIMIT 10 rows max.\n"
            "- For XP or wallet, query: SELECT xp, wallet_balance FROM user WHERE id = {uid}\n".format(uid=user_id)
        )
        raw_sql = llm.invoke([HumanMessage(content=sql_prompt)]).content.strip()
        log.info(f"[Agent] Generated SQL: {raw_sql}")

        # Step 4b: Execute SQL
        db_result = _run_sql(raw_sql, user_id)
        log.info(f"[Agent] DB result: {db_result}")

        # Step 4c: Format response for user
        answer_prompt = (
            "You are EduLink Assistant, a friendly study helper.\n"
            "The student asked a question and here is the raw database result.\n"
            "Give a short, friendly, human-readable answer based on this data.\n"
            "Do NOT show SQL, technical errors, or column names unless needed.\n"
            f"Student question: {user_input}\n"
            f"Database result:\n{db_result}"
        )
        return llm.invoke([HumanMessage(content=answer_prompt)]).content.strip()

    else:
        # General knowledge / platform guidance
        general_prompt = (
            "You are EduLink Assistant, a helpful study assistant for an educational platform.\n"
            "Answer the student's question clearly and helpfully.\n"
            "If they ask about platform features (events, courses, forum, challenges), give practical guidance.\n"
            f"Question: {user_input}"
        )
        return llm.invoke([HumanMessage(content=general_prompt)]).content.strip()


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO)
    print(get_agent_response("How many XP do I have?", user_id=1))
    print("---")
    print(get_agent_response("How do I access the events page?", user_id=1))
