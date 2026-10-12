$ErrorActionPreference = "Stop"

$E2EEmail = "e2e@cim.test"
$E2EPassword = "CimE2E-Local-Only-2026!"

Write-Host ""
Write-Host "========================================"
Write-Host "CIM Phase 3 E2E"
Write-Host "========================================"

Write-Host ""
Write-Host "[1/5] Building production assets..."

docker compose run --rm node npm run build

if ($LASTEXITCODE -ne 0) {
    throw "Production frontend build failed."
}

Write-Host ""
Write-Host "[2/5] Preparing E2E database fixtures..."

docker compose exec -T `
    -e "E2E_USER_EMAIL=$E2EEmail" `
    -e "E2E_USER_PASSWORD=$E2EPassword" `
    app `
    php artisan db:seed `
        --class=E2ESeeder `
        --force

if ($LASTEXITCODE -ne 0) {
    throw "E2ESeeder failed."
}

Write-Host ""
Write-Host "[3/5] Temporarily stopping Vite..."

docker compose stop node

docker compose exec -T app `
    rm -f public/hot

try {
    Write-Host ""
    Write-Host "[4/5] Running Playwright..."

    docker compose `
        --profile e2e `
        run `
        --rm `
        -e "E2E_USER_EMAIL=$E2EEmail" `
        -e "E2E_USER_PASSWORD=$E2EPassword" `
        e2e `
        npm run e2e

    if ($LASTEXITCODE -ne 0) {
        throw "Playwright smoke test failed."
    }
}
finally {
    Write-Host ""
    Write-Host "[5/5] Restarting Vite..."

    docker compose start node
}

Write-Host ""
Write-Host "[PASS] Phase 3 E2E passed." `
    -ForegroundColor Green