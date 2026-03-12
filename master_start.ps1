$ProjectRoot = Get-Location

Write-Host "--- EduLink AI Microservices Orchestrator ---" -ForegroundColor Cyan

# 1. Ali ML (Port 5000) - Toxicity, Matching, Sentiment
Start-Process -FilePath "python" -ArgumentList "api.py" -WorkingDirectory (Join-Path $ProjectRoot "ali\ml") -NoNewWindow
Write-Host "[OK] Port 5000: Ali ML" -ForegroundColor Green

# 2. Sentiment (Port 5001) - NLTK Vader
Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "sentiment_api") -NoNewWindow
Write-Host "[OK] Port 5001: Sentiment" -ForegroundColor Green

# 3. Study Advisor (Port 5002) - Gemini Advisor
Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "study_advisor_api") -NoNewWindow
Write-Host "[OK] Port 5002: Study Advisor" -ForegroundColor Green

# 4. Yassine (Port 5003) - Event Prediction
Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "yassine") -NoNewWindow
Write-Host "[OK] Port 5003: Yassine Events" -ForegroundColor Green

# 5. Ali Challenge (Port 5004) - Challenge Generator
Start-Process -FilePath "python" -ArgumentList "-m", "uvicorn", "app:app", "--port", "5004" -WorkingDirectory (Join-Path $ProjectRoot "ali") -NoNewWindow
Write-Host "[OK] Port 5004: Ali Challenge" -ForegroundColor Green

# 6. Taki Sentiment (Port 5005) - Secondary Sentiment
Start-Process -FilePath "python" -ArgumentList "sentiment_api.py" -WorkingDirectory (Join-Path $ProjectRoot "taki") -NoNewWindow
Write-Host "[OK] Port 5005: Taki Sentiment" -ForegroundColor Green

# 7. Delizar Journal (Port 5006) - Journal Analysis (Classifier/Tags/Search)
Start-Process -FilePath "python" -ArgumentList "-m", "uvicorn", "app:app", "--port", "5006" -WorkingDirectory (Join-Path $ProjectRoot "delizar") -NoNewWindow
Write-Host "[OK] Port 5006: Delizar Journal" -ForegroundColor Green

# 8. Mahdi AI (Port 8001) - Dropout/Recommendation Inference
# Check if already running first
$mahdiActive = Get-NetTCPConnection -LocalPort 8001 -ErrorAction SilentlyContinue
if (-not $mahdiActive) {
    Start-Process -FilePath "python" -ArgumentList "-m", "uvicorn", "main:app", "--port", "8001" -WorkingDirectory (Join-Path $ProjectRoot "mahdi") -NoNewWindow
    Write-Host "[OK] Port 8001: Mahdi AI" -ForegroundColor Green
} else {
    Write-Host "[SKIP] Port 8001: Mahdi AI already running" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "All AI Microservices have been triggered." -ForegroundColor Cyan
Write-Host "Run 'powershell -File check_ports.ps1' to verify connection status." -ForegroundColor Gray
