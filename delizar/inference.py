import os
import sys
from classifier import JournalClassifier
from tag_generator import TagGenerator
from semantic_search import SemanticSearch

def main():
    # If a text argument is provided, use it. Otherwise, use a default test sentence.
    if len(sys.argv) > 1:
        test_text = " ".join(sys.argv[1:])
    else:
        test_text = "I spent all day debugging the React frontend and feeling quite exhausted."
        
    print(f"\n📝 Unseen Journal Entry: \"{test_text}\"")
    print("-" * 60)
    
    base_dir = os.path.dirname(__file__)
    
    # --- 1. CLASSIFICATION ---
    clf = JournalClassifier(model_path=os.path.join(base_dir, 'models', 'classifier.pkl'))
    clf.load()
    predicted_category = clf.predict(test_text)
    print(f"📂 Predicted Category:   \033[94m{predicted_category}\033[0m")
    
    # --- 2. TAG GENERATOR ---
    tagger = TagGenerator(
        model_path=os.path.join(base_dir, 'models', 'tagger.pkl'),
        mlb_path=os.path.join(base_dir, 'models', 'tagger_mlb.pkl')
    )
    tagger.load()
    predicted_tags = tagger.generate_tags(test_text)
    tags_str = ", ".join(predicted_tags) if predicted_tags else "None"
    print(f"🏷️  Generated Tags:       \033[92m{tags_str}\033[0m")
    
    # --- 3. SEMANTIC SEARCH ---
    searcher = SemanticSearch(
        model_path=os.path.join(base_dir, 'models', 'semantic_w2v.model'),
        vectors_path=os.path.join(base_dir, 'models', 'semantic_vectors.npy'),
        original_data_path=os.path.join(base_dir, 'models', 'semantic_corpus.pkl')
    )
    searcher.load()
    similar_entries = searcher.search(test_text, top_k=3)
    
    print("\n🔍 Top 3 Semantically Similar Past Entries:")
    if not similar_entries:
        print("   No similar entries found (vocabulary unknown).")
    else:
        for i, (text, score) in enumerate(similar_entries, 1):
            print(f"   {i}. (Score: {score:.4f}) -> {text}")
            
    print("\n" + "=" * 60)

if __name__ == "__main__":
    main()
