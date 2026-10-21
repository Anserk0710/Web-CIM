$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "========================================"
Write-Host "CIM Test Suite"
Write-Host "========================================"
Write-Host ""
Write-Host "Environment : testing"
Write-Host "Database    : cim_test"
Write-Host ""

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan test @args

if ($LASTEXITCODE -ne 0) {
    throw "Test suite failed."
}

Write-Host ""
Write-Host "[PASS] Test suite completed successfully." `
    -ForegroundColor Green