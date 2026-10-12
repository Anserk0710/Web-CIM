$ErrorActionPreference = "Stop"
$Failures = 0

function Pass {
    param([string] $Message)

    Write-Host "[PASS] $Message" `
        -ForegroundColor Green
}

function Fail {
    param([string] $Message)

    $script:Failures++

    Write-Host "[FAIL] $Message" `
        -ForegroundColor Red
}

function Header {
    param([string] $Message)

    Write-Host ""
    Write-Host "========================================"
    Write-Host $Message
    Write-Host "========================================"
}

Header "CIM Phase 3 Verification"

Header "1. Migration"

docker compose exec -T app `
    php artisan migrate --force

if ($LASTEXITCODE -eq 0) {
    Pass "Phase 3 migrations pass."
} else {
    Fail "Migration failed."
}

Header "2. Backend Quality"

docker compose exec -T app composer lint

if ($LASTEXITCODE -eq 0) {
    Pass "PHP formatting passes."
} else {
    Fail "PHP formatting failed."
}

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan test

if ($LASTEXITCODE -eq 0) {
    Pass "Backend regression suite passes."
} else {
    Fail "Backend regression suite failed."
}

Header "3. Frontend Quality"

docker compose exec -T node npm run lint

if ($LASTEXITCODE -eq 0) {
    Pass "Frontend lint passes."
} else {
    Fail "Frontend lint failed."
}

docker compose exec -T node npm run typecheck

if ($LASTEXITCODE -eq 0) {
    Pass "TypeScript passes."
} else {
    Fail "TypeScript failed."
}

docker compose exec -T node npm run build

if ($LASTEXITCODE -eq 0) {
    Pass "Production frontend build passes."
} else {
    Fail "Production build failed."
}

Header "4. Public HTTP"

try {
    $Products = Invoke-WebRequest `
        -Uri "http://localhost:8080/products" `
        -UseBasicParsing `
        -TimeoutSec 10

    if ($Products.StatusCode -eq 200) {
        Pass "Public products listing responds."
    } else {
        Fail "Products listing returned unexpected status."
    }
} catch {
    Fail "Products listing failed."
}

try {
    $Sitemap = Invoke-WebRequest `
        -Uri "http://localhost:8080/sitemap.xml" `
        -UseBasicParsing `
        -TimeoutSec 10

    if ($Sitemap.StatusCode -eq 200) {
        Pass "Sitemap responds."
    } else {
        Fail "Sitemap returned unexpected status."
    }
} catch {
    Fail "Sitemap failed."
}

Header "5. E2E"

powershell `
    -ExecutionPolicy Bypass `
    -File .\scripts\phase3-e2e.ps1

if ($LASTEXITCODE -eq 0) {
    Pass "Playwright smoke passes."
} else {
    Fail "Playwright smoke failed."
}

Header "Result"

if ($Failures -eq 0) {
    Write-Host ""
    Write-Host "[PASS] Phase 3 automated verification passed." `
        -ForegroundColor Green

    exit 0
}

Write-Host ""
Write-Host "[FAIL] Phase 3 found $Failures problem(s)." `
    -ForegroundColor Red

exit 1