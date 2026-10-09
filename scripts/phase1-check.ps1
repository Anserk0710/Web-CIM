$ErrorActionPreference = "Stop"

$Failures = 0

function Write-Header {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Title
    )

    Write-Host ""
    Write-Host "========================================"
    Write-Host $Title
    Write-Host "========================================"
}

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

function Test-HttpEndpoint {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Name,

        [Parameter(Mandatory = $true)]
        [string] $Url
    )

    try {
        $Response = Invoke-WebRequest `
            -Uri $Url `
            -UseBasicParsing `
            -TimeoutSec 10

        if ($Response.StatusCode -eq 200) {
            Pass "$Name responds with HTTP 200."
        } else {
            Fail "$Name returned HTTP $($Response.StatusCode)."
        }
    } catch {
        Fail "$Name request failed: $($_.Exception.Message)"
    }
}

Write-Header "CIM Phase 1 Verification"

Write-Header "1. Compose Services"

docker compose ps

if ($LASTEXITCODE -ne 0) {
    Fail "docker compose ps failed."
}

$ExpectedServices = @(
    "app",
    "nginx",
    "node",
    "mysql",
    "mailpit"
)

foreach ($Service in $ExpectedServices) {
    $ContainerId = (
        docker compose ps -q $Service
    ).Trim()

    if ([string]::IsNullOrWhiteSpace($ContainerId)) {
        Fail "$Service container is not running."
    } else {
        Pass "$Service container exists."
    }
}

Write-Header "2. Runtime Versions"

$PhpVersion = (
    docker compose exec -T app php -r "echo PHP_VERSION;"
).Trim()

if ($PhpVersion.StartsWith("8.3.")) {
    Pass "PHP runtime is $PhpVersion."
} else {
    Fail "Expected PHP 8.3.x, got $PhpVersion."
}

$NodeVersion = (
    docker compose exec -T node node --version
).Trim()

if ($NodeVersion.StartsWith("v24.")) {
    Pass "Node runtime is $NodeVersion."
} else {
    Fail "Expected Node 24.x, got $NodeVersion."
}

$MySqlVersion = (
    docker compose exec -T mysql mysql --version
).Trim()

if ($MySqlVersion -match "8\.4") {
    Pass "MySQL runtime is 8.4.x."
} else {
    Fail "Expected MySQL 8.4.x. Output: $MySqlVersion"
}

Write-Header "3. HTTP Integration"

Test-HttpEndpoint `
    -Name "Public homepage" `
    -Url "http://localhost:8080/"

Test-HttpEndpoint `
    -Name "CMS login shell" `
    -Url "http://localhost:8080/admin/login"

Test-HttpEndpoint `
    -Name "CMS dashboard shell" `
    -Url "http://localhost:8080/admin"

Test-HttpEndpoint `
    -Name "Laravel health endpoint" `
    -Url "http://localhost:8080/up"

Test-HttpEndpoint `
    -Name "Database readiness endpoint" `
    -Url "http://localhost:8080/ready"

Test-HttpEndpoint `
    -Name "Mailpit web UI" `
    -Url "http://localhost:8025"

Test-HttpEndpoint `
    -Name "Vite development server" `
    -Url "http://localhost:5173/@vite/client"

Write-Header "4. Backend Checks"

docker compose exec -T app composer check-platform-reqs

if ($LASTEXITCODE -eq 0) {
    Pass "Composer platform requirements pass."
} else {
    Fail "Composer platform requirements failed."
}

docker compose exec -T app composer lint

if ($LASTEXITCODE -eq 0) {
    Pass "PHP lint/format check passes."
} else {
    Fail "PHP lint/format check failed."
}

docker compose exec -T app php artisan test

if ($LASTEXITCODE -eq 0) {
    Pass "Laravel test suite passes."
} else {
    Fail "Laravel test suite failed."
}

Write-Header "5. Frontend Checks"

docker compose exec -T node npm run lint

if ($LASTEXITCODE -eq 0) {
    Pass "Frontend lint passes."
} else {
    Fail "Frontend lint failed."
}

docker compose exec -T node npm run typecheck

if ($LASTEXITCODE -eq 0) {
    Pass "TypeScript typecheck passes."
} else {
    Fail "TypeScript typecheck failed."
}

docker compose exec -T node npm run build

if ($LASTEXITCODE -eq 0) {
    Pass "Production frontend build passes."
} else {
    Fail "Production frontend build failed."
}

Write-Header "6. Public / Private Storage Persistence"

docker compose exec -T app sh -lc `
    'echo "phase1-public" > storage/app/public/phase1-public-probe.txt && echo "phase1-private" > storage/app/private/phase1-private-probe.txt'

if ($LASTEXITCODE -ne 0) {
    Fail "Could not create storage persistence probes."
} else {
    Pass "Storage persistence probes created."
}

docker compose up -d --force-recreate --wait app nginx

if ($LASTEXITCODE -ne 0) {
    Fail "Could not recreate application containers."
}

docker compose exec -T app sh -lc `
    'test -f storage/app/public/phase1-public-probe.txt && test -f storage/app/private/phase1-private-probe.txt'

if ($LASTEXITCODE -eq 0) {
    Pass "Public and private storage survived container recreation."
} else {
    Fail "Persistent storage did not survive container recreation."
}

docker compose exec -T app rm -f `
    storage/app/public/phase1-public-probe.txt `
    storage/app/private/phase1-private-probe.txt

Write-Header "7. MySQL Volume Persistence"

# Use MYSQL_PWD to avoid password warnings.
# Send SQL through stdin to prevent PowerShell quoting issues.
$MySqlCommand = 'export MYSQL_PWD="$MYSQL_ROOT_PASSWORD"; exec mysql --user=root --database="$MYSQL_DATABASE" --batch --skip-column-names'

# ------------------------------------------------------------
# 7.1. Create persistence probe
# ------------------------------------------------------------

$ProbeSql = @(
    'CREATE TABLE IF NOT EXISTS phase1_persistence_probe (id INT PRIMARY KEY, `value` VARCHAR(64) NOT NULL);'
    'REPLACE INTO phase1_persistence_probe (id, `value`) VALUES (1, 0x707265736572766564);'
)

$ProbeSql | docker compose exec -T mysql sh -c $MySqlCommand

$ProbeExitCode = $LASTEXITCODE

if ($ProbeExitCode -ne 0) {
    Fail "Could not initialize MySQL persistence probe."
} else {
    Pass "MySQL persistence probe created."

    # --------------------------------------------------------
    # 7.2. Recreate MySQL container without deleting volumes
    # --------------------------------------------------------

    docker compose up -d --force-recreate --wait mysql

    $RecreateExitCode = $LASTEXITCODE

    if ($RecreateExitCode -ne 0) {
        Fail "Could not recreate MySQL container."
    } else {

        # ----------------------------------------------------
        # 7.3. Verify persistence after container recreation
        # ----------------------------------------------------

        $PersistenceOutput = @(
            'SELECT `value` FROM phase1_persistence_probe WHERE id = 1;' |
                docker compose exec -T mysql sh -c $MySqlCommand
        )

        $QueryExitCode = $LASTEXITCODE

        # Safely handle empty or null query output.
        $PersistenceValue = ($PersistenceOutput -join "`n").Trim()

        if ($QueryExitCode -ne 0) {
            Fail "Could not query MySQL persistence probe."
        } elseif ($PersistenceValue -eq "preserved") {
            Pass "MySQL data survived container recreation."
        } else {
            Fail "MySQL persistence probe was lost. Actual value: '$PersistenceValue'."
        }
    }

    # --------------------------------------------------------
    # 7.4. Cleanup persistence probe
    # --------------------------------------------------------

    'DROP TABLE IF EXISTS phase1_persistence_probe;' |
        docker compose exec -T mysql sh -c $MySqlCommand

    $CleanupExitCode = $LASTEXITCODE

    if ($CleanupExitCode -ne 0) {
        Fail "Could not clean up MySQL persistence probe table."
    } else {
        Pass "MySQL persistence probe cleaned up."
    }
}

# ============================================================
# Final Verification Result
# ============================================================

Write-Header "Result"

if ($Failures -eq 0) {
    Write-Host "[PASS] Phase 1 local verification passed." -ForegroundColor Green
    exit 0
}

Write-Host "[FAIL] Phase 1 found $Failures problem(s)." -ForegroundColor Red
exit 1