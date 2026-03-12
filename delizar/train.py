import os
from classifier import JournalClassifier
from tag_generator import TagGenerator
from semantic_search import SemanticSearch
import generate_data

def main():
    base_dir = os.path.dirname(__file__)
    data_path = os.path.join(base_dir, 'data', 'journal_entries.csv')
    
    # 1. Generate data if not exists
    if not os.path.exists(data_path):
        print("Data not found. Running synthetic data generator...")
        generate_data.main()
        
    # 2. Train Text Classification Model
    clf = JournalClassifier(model_path=os.path.join(base_dir, 'models', 'classifier.pkl'))
    clf.train(data_path)
    clf.save()
    
    print("\n" + "="*50 + "\n")
    
    # 3. Train Tag Generator Model
    tagger = TagGenerator(
        model_path=os.path.join(base_dir, 'models', 'tagger.pkl'),
        mlb_path=os.path.join(base_dir, 'models', 'tagger_mlb.pkl')
    )
    tagger.train(data_path)
    tagger.save()
    
    print("\n" + "="*50 + "\n")
    
    # 4. Train Semantic Search Model
    searcher = SemanticSearch(
        model_path=os.path.join(base_dir, 'models', 'semantic_w2v.model'),
        vectors_path=os.path.join(base_dir, 'models', 'semantic_vectors.npy'),
        original_data_path=os.path.join(base_dir, 'models', 'semantic_corpus.pkl')
    )
    searcher.train(data_path)
    searcher.save()
    
    print("\n" + "="*50)
    print("🎉 All 3 Journal Models trained from absolute scratch and saved successfully!")
    print("Run `python inference.py` to test them out.")

if __name__ == "__main__":
    main()
