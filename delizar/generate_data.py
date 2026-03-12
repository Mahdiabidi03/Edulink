import os
import random
import pandas as pd

# -------------------------------------------------------------------
# Configuration
# -------------------------------------------------------------------
NUM_SAMPLES = 1000
OUTPUT_DIR = os.path.join(os.path.dirname(__file__), 'data')
OUTPUT_FILE = os.path.join(OUTPUT_DIR, 'journal_entries.csv')

# Ensure output directory exists
os.makedirs(OUTPUT_DIR, exist_ok=True)

# -------------------------------------------------------------------
# Data Vocabularies by Category
# -------------------------------------------------------------------
CATEGORIES = ["Work", "Study", "Health", "Personal", "Project"]

# Dictionaries to combinatorially generate sentences
VOCAB = {
    "Work": {
        "subjects": ["My manager", "The client", "The team", "Our new colleague", "I"],
        "verbs": ["presented", "completed", "struggled with", "reviewed", "discussed", "scheduled"],
        "objects": ["the quarterly report", "the project deadline", "a new feature", "a difficult bug", "the budget"],
        "contexts": ["during the morning standup.", "and it was very stressful.", "which took much longer than expected.", "successfully today.", "with great feedback."],
        "tags": ["urgent", "meeting", "stress", "success", "colleagues", "planning"]
    },
    "Study": {
        "subjects": ["I", "The professor", "My study group", "We"],
        "verbs": ["read", "prepared for", "failed", "passed", "reviewed", "summarized"],
        "objects": ["chapter 4 of the textbook", "the final exam", "the machine learning assignment", "my lecture notes", "a difficult calculus problem"],
        "contexts": ["all night long.", "and feel pretty confident about it.", "but I still don't understand the main concepts.", "at the library today.", "with the help of online tutorials."],
        "tags": ["exam", "reading", "difficult", "focus", "homework", "progress"]
    },
    "Health": {
        "subjects": ["I", "My doctor", "My trainer"],
        "verbs": ["ran", "lifted", "cooked", "slept", "meditated", "experienced"],
        "objects": ["for 5 kilometers", "heavy weights", "a healthy meal", "for 8 hours", "a strong headache", "some back pain"],
        "contexts": ["this morning.", "and I feel amazing.", "but I feel quite exhausted now.", "to improve my physical condition.", "because I have been neglecting self-care."],
        "tags": ["fitness", "diet", "sleep", "pain", "mental-health", "routine"]
    },
    "Personal": {
        "subjects": ["My friend", "I", "My family", "We"],
        "verbs": ["visited", "chatted about", "enjoyed", "cleaned", "bought", "watched"],
        "objects": ["the new museum", "life goals", "a great movie", "the apartment", "some groceries", "a nice dinner"],
        "contexts": ["over the weekend.", "and it was very relaxing.", "which made me really happy.", "after a long busy week.", "to clear my mind."],
        "tags": ["friends", "family", "relax", "chores", "entertainment", "happy"]
    },
    "Project": {
        "subjects": ["I", "The open-source community", "My co-founder"],
        "verbs": ["deployed", "debugged", "designed", "architected", "brainstormed", "refactored"],
        "objects": ["the new database schema", "the UI components", "the authentication flow", "a severe memory leak", "the landing page"],
        "contexts": ["to production.", "using React and Node.", "and it finally works flawlessly.", "which was causing the server to crash.", "to improve the overall performance."],
        "tags": ["coding", "design", "deployment", "bug-fix", "feature", "milestone"]
    }
}

# General filler words to add variation
FILLERS = [
    "Today, ", "Yesterday, ", "This afternoon, ", "In the morning, ", 
    "Surprisingly, ", "As expected, ", "Unfortunately, ", "Thankfully, ", ""
]

# -------------------------------------------------------------------
# Generation Logic
# -------------------------------------------------------------------
def generate_entry(category: str) -> tuple[str, str]:
    """Generates a random sentence and a random subset of tags for a given category."""
    parts = VOCAB[category]
    
    filler = random.choice(FILLERS)
    subject = random.choice(parts["subjects"])
    verb = random.choice(parts["verbs"])
    obj = random.choice(parts["objects"])
    context = random.choice(parts["contexts"])
    
    # Adjust grammar slightly if subject is "I" vs Third Person
    if subject == "I":
        # simple heuristic to make sentences slightly more natural
        pass
        
    text = f"{filler}{subject} {verb} {obj} {context}".strip()
    
    # Capitalize first letter
    text = text[0].upper() + text[1:]
    
    # Pick 1 to 3 random tags relevant to this category
    num_tags = random.randint(1, 3)
    chosen_tags = random.sample(parts["tags"], num_tags)
    
    return text, ",".join(chosen_tags)


def main():
    print(f"Generating {NUM_SAMPLES} synthetic journal entries...")
    
    data = []
    
    for _ in range(NUM_SAMPLES):
        # Pick a random category
        cat = random.choice(CATEGORIES)
        
        # Generate text and tags
        text, tags = generate_entry(cat)
        
        data.append({
            "text": text,
            "category": cat,
            "tags": tags
        })
        
    # Create DataFrame
    df = pd.DataFrame(data)
    
    # Shuffle the dataset
    df = df.sample(frac=1).reset_index(drop=True)
    
    # Save to CSV
    df.to_csv(OUTPUT_FILE, index=False, encoding='utf-8')
    print(f"Dataset successfully saved to: {OUTPUT_FILE}")
    print("\nSample Data:")
    print(df.head())

if __name__ == "__main__":
    main()
