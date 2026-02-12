import urllib.request
import os

files = {
    "public/models/ssd_mobilenetv1_model-shard1": "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/ssd_mobilenetv1_model-shard1",
    "public/models/face_recognition_model-shard1": "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_recognition_model-shard1"
}

for path, url in files.items():
    print(f"Downloading {url} to {path}...")
    try:
        with urllib.request.urlopen(url) as response, open(path, 'wb') as out_file:
            data = response.read() # Read all at once or chunk if needed
            out_file.write(data)
        print(f"Downloaded {path}: {os.path.getsize(path)} bytes")
    except Exception as e:
        print(f"Error downloading {path}: {e}")
