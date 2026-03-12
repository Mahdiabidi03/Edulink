# EduLink Master AI Services Starter
# Starts all microservices on their designated ports.

$ProjectRoot = Get-Location

Write-Host "EduLink AI Orchestrator Starting..." -ForegroundColor Cyan

# 1. Ali ML API (Core Functions)
Write-Host "  Starting Ali ML API (Port 5000)..." -ForegroundColor Gray
$aliMlJob = Start-Process -FilePath "python" -ArgumentList "api.py" -WorkingDirectory (Join-Path $ProjectRoot "ali\ml") -PassThru -NoNewWindow
Write-Host "  [OK] Ali ML API started (PID: $($aliMlJob.Id))" -ForegroundColor Green

# 2. Sentiment API (NLTK)
Write-Host "  Starting Sentiment API (Port 5001)..." -ForegroundColor Gray
$sentimentJob = Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "sentiment_api") -PassThru -NoNewWindow
Write-Host "  [OK] Sentiment API started (PID: $($sentimentJob.Id))" -ForegroundColor Green

# 3. Study Advisor API (Gemini)
Write-Host "  Starting Study Advisor API (Port 5002)..." -ForegroundColor Gray
$advisorJob = Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "study_advisor_api") -PassThru -NoNewWindow
Write-Host "  [OK] Study Advisor API started (PID: $($advisorJob.Id))" -ForegroundColor Green

# 4. Yassine Event Prediction
Write-Host "  Starting Yassine API (Port 5003)..." -ForegroundColor Gray
$yassineJob = Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "yassine") -PassThru -NoNewWindow
Write-Host "  [OK] Yassine API started (PID: $($yassineJob.Id))" -ForegroundColor Green

# 5. Ali Challenge/Task Generator
Write-Host "  Starting Ali Challenge API (Port 5004)..." -ForegroundColor Gray
$aliSvcJob = Start-Process -FilePath "python" -ArgumentList "-m", "uvicorn", "app:app", "--port", "5004" -WorkingDirectory (Join-Path $ProjectRoot "ali") -PassThru -NoNewWindow
Write-Host "  [OK] Ali Challenge API started (PID: $($aliSvcJob.Id))" -ForegroundColor Green

# 6. Taki Sentiment API
Write-Host "  Starting Taki Sentiment API (Port 5005)..." -ForegroundColor Gray
$takiJob = Start-Process -FilePath "python" -ArgumentList "sentiment_api.py" -WorkingDirectory (Join-Path $ProjectRoot "taki") -PassThru -NoNewWindow
Write-Host "  [OK] Taki Sentiment API started (PID: $($takiJob.Id))" -ForegroundColor Green

Write-Host ""
Write-Host "All AI services are running!" -ForegroundColor Yellow
Write-Host "Ports: 5000 (ML), 5001 (Sentiment), 5002 (Advisor), 5003 (Yassine), 5004 (Ali), 5005 (Taki)" -ForegroundColor Cyan
Write-Host "Mahdi: Port 8001 (FastAPI)" -ForegroundColor Cyan
Write-Host "Symfony: Port 8000 (Web)" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop all servers (this window will terminate them)" -ForegroundColor DarkGray

try {
    # Keep the script alive. In a real PowerShell session, this would wait for user input.
    # Here we wait for any process to end, then cleanup.
    Wait-Process -Id $aliMlJob.Id
} finally {
    Write-Host "Stopping all AI services..." -ForegroundColor Yellow
    if ($aliMlJob -and -not $aliMlJob.HasExited) { Stop-Process -Id $aliMlJob.Id -Force }
    if ($sentimentJob -and -not $sentimentJob.HasExited) { Stop-Process -Id $sentimentJob.Id -Force }
    if ($advisorJob -and -not $advisorJob.HasExited) { Stop-Process -Id $advisorJob.Id -Force }
    if ($yassineJob -and -not $yassineJob.HasExited) { Stop-Process -Id $yassineJob.Id -Force }
    if ($aliSvcJob -and -not $aliSvcJob.HasExited) { Stop-Process -Id $aliSvcJob.Id -Force }
    if ($takiJob -and -not $takiJob.HasExited) { Stop-Process -Id $takiJob.Id -Force }
    Write-Host "All AI services stopped." -ForegroundColor Red
}
