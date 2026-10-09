$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "========================================"
Write-Host "CIM Development Setup"
Write-Host "========================================"

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"

    Write-Host "[PASS] Created .env from .env.example"
} else {
    Write-Host "[INFO] .env already exists."
}

Write-Host ""
Write-Host "[1/8] Building application image..."
docker compose build app

if ($LASTEXITCODE -ne 0) {
    throw "Docker application image build failed."
}

Write-Host ""
Write-Host "[2/8] Starting MySQL and Mailpit..."
docker compose up -d --wait mysql mailpit

if ($LASTEXITCODE -ne 0) {
    throw "MySQL or Mailpit failed to start."
}

Write-Host ""
Write-Host "[3/8] Installing Composer dependencies..."
docker compose run --rm app composer install --no-interaction --prefer-dist

if ($LASTEXITCODE -ne 0) {
    throw "Composer install failed."
}

Write-Host ""
Write-Host "[4/8] Installing Node dependencies..."
docker compose run --rm node npm ci

if ($LASTEXITCODE -ne 0) {
    throw "npm ci failed."
}

Write-Host ""
Write-Host "[5/8] Generating APP_KEY if required..."

$EnvContent = Get-Content ".env" -Raw

if ($EnvContent -match "(?m)^APP_KEY=\s*$") {
    docker compose run --rm app php artisan key:generate

    if ($LASTEXITCODE -ne 0) {
        throw "APP_KEY generation failed."
    }
} else {
    Write-Host "[INFO] APP_KEY already exists."
}

Write-Host ""
Write-Host "[6/8] Running database migrations..."
docker compose run --rm app php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    throw "Database migration failed."
}

Write-Host ""
Write-Host "[7/8] Building frontend assets..."
docker compose run --rm node npm run build

if ($LASTEXITCODE -ne 0) {
    throw "Frontend build failed."
}

Write-Host ""
Write-Host "[8/8] Starting complete stack..."
docker compose up -d --build --wait

if ($LASTEXITCODE -ne 0) {
    throw "Docker Compose stack failed to start."
}

Write-Host ""
Write-Host "========================================"
Write-Host "CIM Development Environment Ready"
Write-Host "========================================"
Write-Host ""
Write-Host "Public  : http://localhost:8080"
Write-Host "CMS     : http://localhost:8080/admin"
Write-Host "Login   : http://localhost:8080/admin/login"
Write-Host "Health  : http://localhost:8080/up"
Write-Host "Ready   : http://localhost:8080/ready"
Write-Host "Mailpit : http://localhost:8025"
Write-Host "Vite    : http://localhost:5173"
Write-Host ""