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

Header "CIM Phase 5 Batch 2 Verification"

Header "1. Clear Laravel Cache"

docker compose exec -T app `
    php artisan optimize:clear

if ($LASTEXITCODE -eq 0) {
    Pass "Laravel caches cleared."
}
else {
    Fail "Laravel cache clear failed."
}

Header "2. Route Verification"

$Routes = (docker compose exec -T app `
    php artisan route:list) -join "`n"
$RoutesExitCode = $LASTEXITCODE

if (
    $RoutesExitCode -eq 0 `
    -and $Routes -match "careers/\{slug\}/apply" `
    -and $Routes -match "admin/applications" `
    -and $Routes -match "applications/\{application\}/status" `
    -and $Routes -match "applications/\{application\}/notes" `
    -and $Routes -match "applications/\{application\}/cv"
) {
    Pass "Career Application routes registered."
}
else {
    Fail "One or more Career Application routes are missing."
}

Header "3. Local Consent"

docker compose exec -T app `
    php artisan db:seed `
    --class=LocalConsentVersionSeeder

if ($LASTEXITCODE -eq 0) {
    Pass "Local consent version available."
}
else {
    Fail "Local consent seeder failed."
}

Header "4. PHP Formatting"

docker compose exec -T app `
    composer lint

if ($LASTEXITCODE -eq 0) {
    Pass "PHP formatting passes."
}
else {
    Fail "PHP formatting failed."
}

Header "5. Career Application Feature Tests"

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan test `
    tests/Feature/Phase5/CareerApplicationSubmissionTest.php `
    tests/Feature/Phase5/CareerApplicationAdminWorkflowTest.php

if ($LASTEXITCODE -eq 0) {
    Pass "Career Application feature tests pass."
}
else {
    Fail "Career Application feature tests failed."
}

Header "6. Full Backend Regression"

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan test

if ($LASTEXITCODE -eq 0) {
    Pass "Full Laravel regression passes."
}
else {
    Fail "Laravel regression failed."
}

Header "7. Frontend Lint"

docker compose exec -T node `
    npm run lint

if ($LASTEXITCODE -eq 0) {
    Pass "Frontend lint passes."
}
else {
    Fail "Frontend lint failed."
}

Header "8. TypeScript"

docker compose exec -T node `
    npm run typecheck

if ($LASTEXITCODE -eq 0) {
    Pass "TypeScript passes."
}
else {
    Fail "TypeScript failed."
}

Header "9. Production Build"

docker compose exec -T node `
    npm run build

if ($LASTEXITCODE -eq 0) {
    Pass "Frontend build passes."
}
else {
    Fail "Frontend build failed."
}

Header "10. Private Storage Boundary"

$PrivateRoot = docker compose exec -T app `
    php artisan tinker --execute="echo config('filesystems.disks.private.root');"
$PrivateRootExitCode = $LASTEXITCODE

$PublicRoot = docker compose exec -T app `
    php artisan tinker --execute="echo config('filesystems.disks.public.root');"
$PublicRootExitCode = $LASTEXITCODE

$PrivateRoot = ($PrivateRoot -join "`n").Trim()
$PublicRoot = ($PublicRoot -join "`n").Trim()

if (
    $PrivateRootExitCode -eq 0 `
    -and $PublicRootExitCode -eq 0 `
    -and $PrivateRoot -match "storage/app/private" `
    -and $PublicRoot -match "storage/app/public" `
    -and $PrivateRoot -ne $PublicRoot
) {
    Pass "Public/private storage roots are separated."
}
else {
    Fail "Storage boundary is incorrect."
}

Header "Result"

if ($Failures -eq 0) {
    Write-Host ""
    Write-Host "[PASS] Phase 5 Batch 2 verification passed." `
        -ForegroundColor Green

    exit 0
}

Write-Host ""
Write-Host "[FAIL] Phase 5 Batch 2 found $Failures problem(s)." `
    -ForegroundColor Red

exit 1
