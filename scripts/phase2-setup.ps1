$ErrorActionPreference = "Stop"

function Get-EnvValue {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Name
    )

    $Line = Get-Content ".env" |
        Where-Object {
            $_ -match "^$([regex]::Escape($Name))="
        } |
        Select-Object -First 1

    if ($null -eq $Line) {
        return ""
    }

    return (
        $Line.Substring(
            $Line.IndexOf("=") + 1
        )
    ).Trim().Trim('"')
}

Write-Host ""
Write-Host "========================================"
Write-Host "CIM Phase 2 Setup"
Write-Host "========================================"

Write-Host ""
Write-Host "[1/8] Rebuilding PHP image with pcntl..."

docker compose build app

if ($LASTEXITCODE -ne 0) {
    throw "Application image build failed."
}

Write-Host ""
Write-Host "[2/8] Starting stack..."

docker compose up -d --wait

if ($LASTEXITCODE -ne 0) {
    throw "Docker Compose failed."
}

Write-Host ""
Write-Host "[3/8] Clearing Laravel configuration cache..."

docker compose exec -T app php artisan config:clear

if ($LASTEXITCODE -ne 0) {
    throw "Laravel config:clear failed."
}

$RootPassword = Get-EnvValue "MYSQL_ROOT_PASSWORD"
$DatabaseUser = Get-EnvValue "DB_USERNAME"

if ([string]::IsNullOrWhiteSpace($RootPassword)) {
    throw "MYSQL_ROOT_PASSWORD is missing from .env."
}

if ([string]::IsNullOrWhiteSpace($DatabaseUser)) {
    throw "DB_USERNAME is missing from .env."
}

Write-Host ""
Write-Host "[4/8] Creating isolated MySQL test database..."

$Sql = @"
CREATE DATABASE IF NOT EXISTS cim_test
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES
ON cim_test.*
TO '$DatabaseUser'@'%';

FLUSH PRIVILEGES;
"@

docker compose exec -T mysql `
    mysql `
    -uroot `
    "-p$RootPassword" `
    -e $Sql

if ($LASTEXITCODE -ne 0) {
    throw "Could not prepare cim_test."
}

Write-Host ""
Write-Host "[5/8] Migrating development database..."

docker compose exec -T app `
    php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    throw "Development migration failed."
}

Write-Host ""
Write-Host "[6/8] Running development seeders..."

docker compose exec -T app `
    php artisan db:seed --force

if ($LASTEXITCODE -ne 0) {
    throw "Seeder failed."
}

Write-Host ""
Write-Host "[7/8] Verifying migration from zero on cim_test..."

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan migrate:fresh --force

if ($LASTEXITCODE -ne 0) {
    throw "Fresh migration failed."
}

Write-Host ""
Write-Host "[8/8] Running Phase 2 tests against cim_test..."

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan test

if ($LASTEXITCODE -ne 0) {
    throw "Phase 2 tests failed."
}

Write-Host ""
Write-Host "========================================"
Write-Host "Phase 2 foundation setup complete"
Write-Host "========================================"
Write-Host ""
Write-Host "CMS Login:"
Write-Host "  http://localhost:8080/admin/login"
Write-Host ""
Write-Host "Mailpit:"
Write-Host "  http://localhost:8025"
Write-Host ""
Write-Host "All PHPUnit tests were executed with:"
Write-Host "  APP_ENV=testing"
Write-Host "  DB_DATABASE=cim_test"
Write-Host ""
Write-Host "If the Super Admin was created with a"
Write-Host "generated password, copy the password"
Write-Host "printed by the seeder."
Write-Host ""