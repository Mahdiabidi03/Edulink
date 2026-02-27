<#
.SYNOPSIS
    EduLink 5.0 - Full Project Setup Script (Windows)
.DESCRIPTION
    Installs all dependencies, creates the database, sets up upload directories,
    and starts the Symfony server + Python ML microservices.
.NOTES
    Prerequisites (must be installed manually):
    - PHP >= 8.1 (with extensions: pdo_mysql, mbstring, intl, gd, zip, xml, ctype, iconv)
    - Composer (https://getcomposer.org)
    - MySQL/MariaDB (XAMPP, WAMP, or standalone)
    - Python >= 3.9 (https://python.org)
    - Symfony CLI (optional but recommended: https://symfony.com/download)
#>

param(
    [switch]$SkipComposer,
    [switch]$SkipPython,
    [switch]$SkipDatabase,
    [switch]$StartServers
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   EduLink 5.0 - Project Setup" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ─────────────────────────────────────────────
# 1. CHECK PREREQUISITES
# ─────────────────────────────────────────────
Write-Host "[1/7] Checking prerequisites..." -ForegroundColor Yellow

$checks = @(
    @{ Name = "PHP";      Cmd = "php --version" },
    @{ Name = "Composer"; Cmd = "composer --version" },
    @{ Name = "Python";   Cmd = "python --version" },
    @{ Name = "MySQL";    Cmd = "mysql --version" }
)

$allGood = $true
foreach ($check in $checks) {
    try {
        $null = Invoke-Expression $check.Cmd 2>&1
        Write-Host "  [OK] $($check.Name) found" -ForegroundColor Green
    } catch {
        Write-Host "  [MISSING] $($check.Name) not found in PATH!" -ForegroundColor Red
        $allGood = $false
    }
}

if (-not $allGood) {
    Write-Host ""
    Write-Host "  Some prerequisites are missing. Install them first:" -ForegroundColor Red
    Write-Host "  - PHP 8.1+: https://windows.php.net/download (or use XAMPP)" -ForegroundColor Gray
    Write-Host "  - Composer: https://getcomposer.org/download" -ForegroundColor Gray
    Write-Host "  - Python 3.9+: https://www.python.org/downloads" -ForegroundColor Gray
    Write-Host "  - MySQL: included with XAMPP/WAMP or https://dev.mysql.com/downloads" -ForegroundColor Gray
    Write-Host ""
    Read-Host "Press Enter to continue anyway, or Ctrl+C to abort"
}

# Check PHP extensions
Write-Host ""
Write-Host "  Checking PHP extensions..." -ForegroundColor Gray
$requiredExts = @("pdo_mysql", "mbstring", "intl", "gd", "zip", "xml", "ctype", "iconv", "curl", "openssl")
foreach ($ext in $requiredExts) {
    $loaded = php -r "echo extension_loaded('$ext') ? 'yes' : 'no';" 2>$null
    if ($loaded -eq "yes") {
        Write-Host "    [OK] ext-$ext" -ForegroundColor DarkGreen
    } else {
        Write-Host "    [WARN] ext-$ext not enabled - uncomment in php.ini" -ForegroundColor DarkYellow
    }
}

# ─────────────────────────────────────────────
# 2. ENVIRONMENT FILE
# ─────────────────────────────────────────────
Write-Host ""
Write-Host "[2/7] Setting up environment..." -ForegroundColor Yellow

$envFile = Join-Path $ProjectRoot ".env"
$envLocalFile = Join-Path $ProjectRoot ".env.local"

if (-not (Test-Path $envLocalFile)) {
    Write-Host "  Creating .env.local from .env..." -ForegroundColor Gray
    Copy-Item $envFile $envLocalFile
    Write-Host "  [OK] .env.local created" -ForegroundColor Green
    Write-Host ""
    Write-Host "  >>> IMPORTANT: Edit .env.local with your settings <<<" -ForegroundColor Magenta
    Write-Host "  - DATABASE_URL (MySQL credentials)" -ForegroundColor Gray
    Write-Host "  - MAILER_DSN (Gmail app password)" -ForegroundColor Gray
    Write-Host "  - GROQ_API_KEY, ali_apiKey (AI services)" -ForegroundColor Gray
    Write-Host "  - GEMINI_API_KEY (Google Gemini)" -ForegroundColor Gray
} else {
    Write-Host "  [OK] .env.local already exists" -ForegroundColor Green
}

# ─────────────────────────────────────────────
# 3. COMPOSER INSTALL (Symfony + PHP)
# ─────────────────────────────────────────────
Write-Host ""
Write-Host "[3/7] Installing PHP dependencies (Composer)..." -ForegroundColor Yellow

if (-not $SkipComposer) {
    Set-Location $ProjectRoot
    composer install --no-interaction --optimize-autoloader 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [OK] Composer dependencies installed" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] Composer install failed" -ForegroundColor Red
    }
} else {
    Write-Host "  [SKIP] Composer install skipped (-SkipComposer)" -ForegroundColor DarkGray
}

# ─────────────────────────────────────────────
# 4. DATABASE SETUP
# ─────────────────────────────────────────────
Write-Host ""
Write-Host "[4/7] Setting up database..." -ForegroundColor Yellow

if (-not $SkipDatabase) {
    Set-Location $ProjectRoot

    # Create database if not exists
    Write-Host "  Creating database..." -ForegroundColor Gray
    php bin/console doctrine:database:create --if-not-exists --no-interaction 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [OK] Database ready" -ForegroundColor Green
    } else {
        Write-Host "  [WARN] Database creation issue (may already exist)" -ForegroundColor DarkYellow
    }

    # Run migrations
    Write-Host "  Running migrations..." -ForegroundColor Gray
    php bin/console doctrine:migrations:migrate --no-interaction 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [OK] Migrations applied" -ForegroundColor Green
    } else {
        Write-Host "  [WARN] Migration issue - you may need to run manually" -ForegroundColor DarkYellow
        Write-Host "         Try: php bin/console doctrine:schema:update --force" -ForegroundColor Gray
    }
} else {
    Write-Host "  [SKIP] Database setup skipped (-SkipDatabase)" -ForegroundColor DarkGray
}

# ─────────────────────────────────────────────
# 5. UPLOAD DIRECTORIES
# ─────────────────────────────────────────────
Write-Host ""
Write-Host "[5/7] Creating upload directories..." -ForegroundColor Yellow

$uploadDirs = @(
    "public/uploads/events",
    "public/uploads/categories",
    "public/uploads/chat",
    "public/uploads/notes",
    "public/uploads/proofs",
    "public/uploads/resources",
    "var/share"
)

foreach ($dir in $uploadDirs) {
    $fullPath = Join-Path $ProjectRoot $dir
    if (-not (Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
        Write-Host "  [CREATED] $dir" -ForegroundColor Green
    } else {
        Write-Host "  [EXISTS]  $dir" -ForegroundColor DarkGreen
    }
}

# ─────────────────────────────────────────────
# 6. PYTHON DEPENDENCIES
# ─────────────────────────────────────────────
Write-Host ""
Write-Host "[6/7] Installing Python dependencies..." -ForegroundColor Yellow

if (-not $SkipPython) {
    $pythonServices = @(
        @{ Name = "ML API (Toxicity, Matching, Sentiment)"; Path = "ml" },
        @{ Name = "Sentiment API (NLTK)";                   Path = "sentiment_api" },
        @{ Name = "Study Advisor API (Gemini)";             Path = "study_advisor_api" }
    )

    foreach ($svc in $pythonServices) {
        $reqFile = Join-Path $ProjectRoot "$($svc.Path)/requirements.txt"
        if (Test-Path $reqFile) {
            Write-Host "  Installing: $($svc.Name)..." -ForegroundColor Gray
            python -m pip install -r $reqFile --quiet 2>&1
            if ($LASTEXITCODE -eq 0) {
                Write-Host "  [OK] $($svc.Name)" -ForegroundColor Green
            } else {
                Write-Host "  [WARN] Some packages may have failed for $($svc.Name)" -ForegroundColor DarkYellow
            }
        } else {
            Write-Host "  [SKIP] $($svc.Path)/requirements.txt not found" -ForegroundColor DarkGray
        }
    }

    # Download NLTK data for sentiment analysis
    Write-Host "  Downloading NLTK data..." -ForegroundColor Gray
    python -c "import nltk; nltk.download('vader_lexicon', quiet=True); nltk.download('punkt', quiet=True)" 2>&1
    Write-Host "  [OK] NLTK data downloaded" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] Python install skipped (-SkipPython)" -ForegroundColor DarkGray
}

# ─────────────────────────────────────────────
# 7. CLEAR CACHE & FINAL CHECKS
# ─────────────────────────────────────────────
Write-Host ""
Write-Host "[7/7] Final setup..." -ForegroundColor Yellow

Set-Location $ProjectRoot
php bin/console cache:clear --no-interaction 2>&1
Write-Host "  [OK] Symfony cache cleared" -ForegroundColor Green

php bin/console assets:install public --no-interaction 2>&1
Write-Host "  [OK] Assets installed" -ForegroundColor Green

# ─────────────────────────────────────────────
# SUMMARY
# ─────────────────────────────────────────────
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   Setup Complete!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  To start the application:" -ForegroundColor White
Write-Host ""
Write-Host "  1. Start MySQL (XAMPP/WAMP or mysql service)" -ForegroundColor Gray
Write-Host ""
Write-Host "  2. Start Symfony server:" -ForegroundColor Gray
Write-Host "     symfony server:start" -ForegroundColor White
Write-Host "     (or: php -S 127.0.0.1:8000 -t public)" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  3. Start ML API (new terminal):" -ForegroundColor Gray
Write-Host "     cd ml && python api.py" -ForegroundColor White
Write-Host "     (runs on http://127.0.0.1:5000)" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  4. Start Sentiment API (new terminal):" -ForegroundColor Gray
Write-Host "     cd sentiment_api && python app.py" -ForegroundColor White
Write-Host "     (runs on http://127.0.0.1:5001)" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  5. Start Study Advisor API (new terminal):" -ForegroundColor Gray
Write-Host "     cd study_advisor_api && python app.py" -ForegroundColor White
Write-Host "     (runs on http://127.0.0.1:5002)" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  6. Open browser:" -ForegroundColor Gray
Write-Host "     http://127.0.0.1:8000" -ForegroundColor White
Write-Host ""
Write-Host "  Quick start (all in one):" -ForegroundColor Yellow
Write-Host "     .\setup.ps1 -StartServers" -ForegroundColor White
Write-Host ""

# ─────────────────────────────────────────────
# OPTIONAL: START SERVERS
# ─────────────────────────────────────────────
if ($StartServers) {
    Write-Host "Starting servers..." -ForegroundColor Yellow
    Write-Host ""

    # Start ML API on port 5000
    Write-Host "  Starting ML API on port 5000..." -ForegroundColor Gray
    $mlJob = Start-Process -FilePath "python" -ArgumentList "api.py" -WorkingDirectory (Join-Path $ProjectRoot "ml") -PassThru -NoNewWindow
    Write-Host "  [OK] ML API started (PID: $($mlJob.Id))" -ForegroundColor Green

    # Start Sentiment API on port 5001
    Write-Host "  Starting Sentiment API on port 5001..." -ForegroundColor Gray
    $sentimentJob = Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "sentiment_api") -PassThru -NoNewWindow
    Write-Host "  [OK] Sentiment API started (PID: $($sentimentJob.Id))" -ForegroundColor Green

    # Start Study Advisor API on port 5002
    Write-Host "  Starting Study Advisor API on port 5002..." -ForegroundColor Gray
    $advisorJob = Start-Process -FilePath "python" -ArgumentList "app.py" -WorkingDirectory (Join-Path $ProjectRoot "study_advisor_api") -PassThru -NoNewWindow
    Write-Host "  [OK] Study Advisor API started (PID: $($advisorJob.Id))" -ForegroundColor Green

    # Start Symfony server on port 8000
    Write-Host "  Starting Symfony server on port 8000..." -ForegroundColor Gray
    try {
        $sfJob = Start-Process -FilePath "symfony" -ArgumentList "server:start", "--no-tls" -WorkingDirectory $ProjectRoot -PassThru -NoNewWindow
        Write-Host "  [OK] Symfony server started (PID: $($sfJob.Id))" -ForegroundColor Green
    } catch {
        Write-Host "  Symfony CLI not found, using PHP built-in server..." -ForegroundColor DarkYellow
        $sfJob = Start-Process -FilePath "php" -ArgumentList "-S", "127.0.0.1:8000", "-t", "public" -WorkingDirectory $ProjectRoot -PassThru -NoNewWindow
        Write-Host "  [OK] PHP server started (PID: $($sfJob.Id))" -ForegroundColor Green
    }

    Write-Host ""
    Write-Host "  Application:    http://127.0.0.1:8000" -ForegroundColor Cyan
    Write-Host "  ML API:         http://127.0.0.1:5000/health" -ForegroundColor Cyan
    Write-Host "  Sentiment API:  http://127.0.0.1:5001" -ForegroundColor Cyan
    Write-Host "  Study Advisor:  http://127.0.0.1:5002" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  Press Ctrl+C to stop all servers" -ForegroundColor DarkGray

    try {
        Wait-Process -Id $sfJob.Id
    } finally {
        # Cleanup all processes
        if ($mlJob -and -not $mlJob.HasExited) { Stop-Process -Id $mlJob.Id -Force }
        if ($sentimentJob -and -not $sentimentJob.HasExited) { Stop-Process -Id $sentimentJob.Id -Force }
        if ($advisorJob -and -not $advisorJob.HasExited) { Stop-Process -Id $advisorJob.Id -Force }
        if ($sfJob -and -not $sfJob.HasExited) { Stop-Process -Id $sfJob.Id -Force }
        Write-Host "  All servers stopped." -ForegroundColor Yellow
    }
}
