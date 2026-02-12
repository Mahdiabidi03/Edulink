import requests
import os

files = {
    "public/models/ssd_mobilenetv1_model-shard1": "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/ssd_mobilenetv1_model-shard1",
    "public/models/face_recognition_model-shard1": "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_recognition_model-shard1"
}

for path, url in files.items():
    print(f"Downloading {url} to {path}...")
    try:
        response = requests.get(url, stream=True)
        response.raise_for_status()
        with open(path, 'wb') as f:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
        print(f"Downloaded {path}: {os.path.getsize(path)} bytes")
    except Exception as e:
        print(f"Error downloading {path}: {e}")
