"""
Hybrid LangChain Agent — Vector Pipeline
Fetches public text from all major entities (Courses, Resources, Events, Challenges, Community)
Embeds using SentenceTransformer and stores in FAISS locally.
"""

import os
import logging
from pathlib import Path
from typing import List

import mysql.connector
import pandas as pd
from langchain_community.vectorstores import FAISS
from langchain_community.embeddings import SentenceTransformerEmbeddings
from langchain_core.documents import Document

# ─────────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────────

DB_CONFIG = {
    "host":     "127.0.0.1",
    "port":     3306,
    "user":     "root",
    "password": "",
    "database": "edu",
}

MAHDI_DIR = Path(__file__).parent
FAISS_DIR = MAHDI_DIR / "faiss_index"

log = logging.getLogger(__name__)

# ─────────────────────────────────────────────
# INDEXING LOGIC
# ─────────────────────────────────────────────

def _fetch_from_table(table_name: str, text_cols: List[str], id_col: str = "id") -> List[Document]:
    """Helper to fetch rows and build LangChain Documents."""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        # Build SQL: SELECT id, col1, col2 FROM table
        cols_sql = ", ".join([id_col] + text_cols)
        # Filter by status='APPROVED' if column exists
        query = f"SELECT {cols_sql} FROM {table_name}"
        if table_name in ["cours", "resource", "event"]:
             query += " WHERE status = 'APPROVED'"
        
        cursor.execute(query)
        rows = cursor.fetchall()
        cursor.close()
        conn.close()
        
        docs = []
        for row in rows:
            # Concatenate all text columns
            text = " ".join([str(row[c]) if row[c] else "" for c in text_cols]).strip()
            if not text:
                continue
                
            doc = Document(
                page_content=text,
                metadata={
                    "source": table_name,
                    "id": row[id_col],
                }
            )
            docs.append(doc)
            
        return docs
    except Exception as e:
        log.error("Failed to fetch %s: %s", table_name, e)
        return []


def build_index():
    """Main function to refresh the FAISS vector index."""
    log.info("[Index] Starting Vector Index Build...")
    
    # 1. Gather all documents
    all_docs = []
    
    log.info("   - Parsing Courses...")
    all_docs.extend(_fetch_from_table("cours", ["title", "description"]))
    
    log.info("   - Parsing Resources...")
    all_docs.extend(_fetch_from_table("resource", ["title"]))
    
    log.info("   - Parsing Events...")
    all_docs.extend(_fetch_from_table("event", ["title", "description"]))
    
    log.info("   - Parsing Challenges...")
    all_docs.extend(_fetch_from_table("challenge", ["title", "description"]))
    
    log.info("   - Parsing Community Posts...")
    all_docs.extend(_fetch_from_table("community_post", ["content"]))
    
    if not all_docs:
        log.warning("⚠️ No documents were found to index!")
        return False
        
    log.info("[Done] Gathered %d total text documents.", len(all_docs))
    
    # 2. Embedding Model (Local)
    log.info("[Model] Initializing SentenceTransformer (all-MiniLM-L6-v2)...")
    embeddings = SentenceTransformerEmbeddings(model_name="all-MiniLM-L6-v2")
    
    # 3. Build & Save FAISS
    log.info("[Build] Creating FAISS index...")
    vectorstore = FAISS.from_documents(all_docs, embeddings)
    
    log.info("[Save] Saving index to %s...", FAISS_DIR)
    FAISS_DIR.mkdir(exist_ok=True)
    vectorstore.save_local(str(FAISS_DIR))
    
    log.info("[Success] Vector Pipeline build complete.")
    return True

if __name__ == "__main__":
    # Allow manual invocation for testing
    logging.basicConfig(level=logging.INFO, format="%(levelname)s: %(message)s")
    build_index()
