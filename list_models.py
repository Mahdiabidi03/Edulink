import json

try:
    with open('gemini_models.json', 'r', encoding='utf-16') as f:
        data = json.load(f)
except:
    try:
        with open('gemini_models.json', 'r', encoding='utf-8') as f:
            data = json.load(f)
    except:
         print("Could not read file")
         exit()

print("Available Models:")
for m in data.get('models', []):
    print(m['name'])
