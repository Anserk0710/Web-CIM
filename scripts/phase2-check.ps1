$ErrorActionPreference = "Stop"

$Failures = 0

function Pass {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    Write-Host "[PASS] $Message" -ForegroundColor Green
}

function Fail {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    $script:Failures++

    Write-Host "[FAIL] $Message" -ForegroundColor Red
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

Header "CIM Phase 2 Verification"

Header "1. Runtime"

$Pcntl = (
    docker compose exec -T app `
        php -r "echo function_exists('pcntl_fork') ? 'yes' : 'no';"
).Trim()

if ($Pcntl -eq "yes") {
    Pass "pcntl is available."
} else {
    Fail "pcntl is missing."
}

Header "2. Laravel Test Safety"

docker compose exec -T app `
    php artisan optimize:clear

if ($LASTEXITCODE -eq 0) {
    Pass "Laravel caches cleared."
} else {
    Fail "Could not clear Laravel caches."
}

Header "3. Schema"

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan migrate:fresh --force

if ($LASTEXITCODE -eq 0) {
    Pass "Schema migrates from zero on cim_test."
} else {
    Fail "Fresh migration failed."
}

Header "4. Seeders"

docker compose exec -T app `
    php artisan db:seed --force

if ($LASTEXITCODE -eq 0) {
    Pass "Development seeders run."
} else {
    Fail "Development seeders failed."
}

Header "5. PHP Quality"

docker compose exec -T app `
    composer check-platform-reqs

if ($LASTEXITCODE -eq 0) {
    Pass "PHP 8.3 platform requirements pass."
} else {
    Fail "Composer platform check failed."
}

docker compose exec -T app `
    composer lint

if ($LASTEXITCODE -eq 0) {
    Pass "PHP formatting check passes."
} else {
    Fail "PHP formatting check failed."
}

Header "6. Security Tests"

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=cim_test `
    app `
    php artisan test

if ($LASTEXITCODE -eq 0) {
    Pass "Authentication/RBAC/audit/concurrency tests pass."
} else {
    Fail "Backend test suite failed."
}

Header "7. Frontend"

docker compose exec -T node `
    npm run lint

if ($LASTEXITCODE -eq 0) {
    Pass "Frontend lint passes."
} else {
    Fail "Frontend lint failed."
}

docker compose exec -T node `
    npm run typecheck

if ($LASTEXITCODE -eq 0) {
    Pass "TypeScript typecheck passes."
} else {
    Fail "TypeScript typecheck failed."
}

docker compose exec -T node `
    npm run build

if ($LASTEXITCODE -eq 0) {
    Pass "Production frontend build passes."
} else {
    Fail "Frontend build failed."
}

Header "8. HTTP Security"

try {
    $Login = Invoke-WebRequest `
        -Uri "http://localhost:8080/admin/login" `
        -UseBasicParsing `
        -TimeoutSec 10

    if ($Login.StatusCode -eq 200) {
        Pass "Login page responds."
    } else {
        Fail "Login page did not return 200."
    }
} catch {
    Fail "Login page failed: $($_.Exception.Message)"
}

$Handler = $null
$Client = $null
$Response = $null

try {
    # Windows PowerShell 5.1 throws without a response when IWR blocks a redirect.
    Add-Type -AssemblyName System.Net.Http
    $Handler = New-Object System.Net.Http.HttpClientHandler
    $Handler.AllowAutoRedirect = $false
    $Handler.UseCookies = $false
    $Client = New-Object System.Net.Http.HttpClient -ArgumentList $Handler
    $Client.Timeout = [TimeSpan]::FromSeconds(10)

    $AdminUri = [Uri] "http://localhost:8080/admin"
    $LoginUri = [Uri] "http://localhost:8080/admin/login"
    $Response = $Client.GetAsync($AdminUri).GetAwaiter().GetResult()
    $StatusCode = [int] $Response.StatusCode
    $Location = $Response.Headers.Location
    $RedirectUri = $null

    if ($null -ne $Location) {
        $RedirectUri = [Uri]::new($AdminUri, $Location)
    }

    if ($StatusCode -eq 302 -and $RedirectUri -eq $LoginUri) {
        Pass "Guest admin request is redirected to login."
    } else {
        Fail "Unexpected guest admin response: status=$StatusCode; location=$Location"
    }
} catch {
    Fail "Guest admin request failed: $($_.Exception.Message)"
} finally {
    if ($null -ne $Response) { $Response.Dispose() }
    if ($null -ne $Client) { $Client.Dispose() }
    if ($null -ne $Handler) { $Handler.Dispose() }
}

Header "Result"

if ($Failures -eq 0) {
    Write-Host ""
    Write-Host "[PASS] Phase 2 automated verification passed." `
        -ForegroundColor Green

    exit 0
}

Write-Host ""
Write-Host "[FAIL] Phase 2 found $Failures problem(s)." `
    -ForegroundColor Red

exit 1
