$ErrorActionPreference = "Stop"

$Failures = 0

function Pass {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    Write-Host "[PASS] $Message" `
        -ForegroundColor Green
}

function Fail {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    $script:Failures++

    Write-Host "[FAIL] $Message" `
        -ForegroundColor Red
}

function Header {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    Write-Host ""
    Write-Host "========================================"
    Write-Host $Message
    Write-Host "========================================"
}

Header "CIM Phase 4 Verification"

Header "1. Laravel"

docker compose exec -T app `
    php artisan optimize:clear

if ($LASTEXITCODE -eq 0) {
    Pass "Laravel caches cleared."
}
else {
    Fail "Laravel cache clear failed."
}

docker compose exec -T app `
    php artisan migrate --force

if ($LASTEXITCODE -eq 0) {
    Pass "Migrations are current."
}
else {
    Fail "Migration failed."
}

Header "2. PHP Quality"

docker compose exec -T app `
    composer check-platform-reqs

if ($LASTEXITCODE -eq 0) {
    Pass "PHP platform requirements pass."
}
else {
    Fail "PHP platform requirements failed."
}

docker compose exec -T app `
    composer lint

if ($LASTEXITCODE -eq 0) {
    Pass "PHP formatting passes."
}
else {
    Fail "PHP formatting failed."
}

Header "3. Backend Regression"

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan test

if ($LASTEXITCODE -eq 0) {
    Pass "Backend regression suite passes."
}
else {
    Fail "Backend regression suite failed."
}

Header "4. Frontend"

docker compose exec -T node `
    npm run lint

if ($LASTEXITCODE -eq 0) {
    Pass "Frontend lint passes."
}
else {
    Fail "Frontend lint failed."
}

docker compose exec -T node `
    npm run typecheck

if ($LASTEXITCODE -eq 0) {
    Pass "TypeScript passes."
}
else {
    Fail "TypeScript failed."
}

docker compose exec -T node `
    npm run build

if ($LASTEXITCODE -eq 0) {
    Pass "Frontend production build passes."
}
else {
    Fail "Frontend build failed."
}

Header "5. Routes"

$Routes = (docker compose exec -T app `
    php artisan route:list `
    --path=admin) -join "`n"

if (
    $Routes -match "admin/ecatalog" `
        -and $Routes -match "admin/activities" `
        -and $Routes -match "admin/careers" `
        -and $Routes -match "admin/settings" `
        -and $Routes -match "admin/audit"
) {
    Pass "Phase 4 routes are registered."
}
else {
    Fail "One or more Phase 4 routes are missing."
}

Header "6. Storage Persistence"

$Marker = "phase4-" + [Guid]::NewGuid().ToString()

docker compose exec -T app `
    php -r "file_put_contents('/var/www/html/storage/app/public/phase4-persistence.txt', '$Marker');"

docker compose restart app nginx

Start-Sleep -Seconds 3

$Stored = (
    docker compose exec -T app `
        php -r "echo file_get_contents('/var/www/html/storage/app/public/phase4-persistence.txt');"
).Trim()

if ($Stored -eq $Marker) {
    Pass "Public mounted media storage persists."
}
else {
    Fail "Public storage persistence failed."
}

docker compose exec -T app `
    rm -f `
    /var/www/html/storage/app/public/phase4-persistence.txt

Header "7. Existing Phase 3 E2E"

powershell `
    -ExecutionPolicy Bypass `
    -File .\scripts\phase3-e2e.ps1

if ($LASTEXITCODE -eq 0) {
    Pass "Existing Product E2E regression passes."
}
else {
    Fail "Product E2E regression failed."
}

Header "Result"

if ($Failures -eq 0) {
    Write-Host ""
    Write-Host "[PASS] Phase 4 automated verification passed." `
        -ForegroundColor Green

    exit 0
}

Write-Host ""
Write-Host "[FAIL] Phase 4 found $Failures problem(s)." `
    -ForegroundColor Red

exit 1