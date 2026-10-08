# path: /cim-website/scripts/phase0-check.ps1

$ErrorActionPreference = "Continue"

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

function Write-Pass {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    Write-Host "[PASS] $Message" -ForegroundColor Green
}

function Write-WarningMessage {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    Write-Host "[WARN] $Message" -ForegroundColor Yellow
}

function Write-Failure {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Message
    )

    $script:Failures++
    Write-Host "[FAIL] $Message" -ForegroundColor Red
}

function Test-CommandExists {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Command
    )

    return $null -ne (Get-Command $Command -ErrorAction SilentlyContinue)
}

Write-Header "CIM Phase 0 Verification"

Write-Host "Runtime contract:"
Write-Host "  Laravel : 13.x"
Write-Host "  PHP     : 8.3.x"
Write-Host "  Node.js : 24 LTS target for Phase 1"
Write-Host "  Database: MySQL 8.4 LTS local target"

Write-Header "1. Required Tooling"

if (Test-CommandExists "git") {
    Write-Pass "Git is installed."
    & git --version
} else {
    Write-Failure "Git is not installed or is not available in PATH."
}

if (Test-CommandExists "docker") {
    Write-Pass "Docker is installed."
    & docker --version

    & docker info *> $null

    if ($LASTEXITCODE -eq 0) {
        Write-Pass "Docker daemon is running."
    } else {
        Write-Failure "Docker is installed but the Docker daemon is not reachable."
    }

    & docker compose version *> $null

    if ($LASTEXITCODE -eq 0) {
        Write-Pass "Docker Compose v2 is available."
        & docker compose version
    } else {
        Write-Failure "Docker Compose v2 is not available."
    }
} else {
    Write-Failure "Docker is not installed or is not available in PATH."
}

Write-Header "2. Optional Host Tooling"

if (Test-CommandExists "php") {
    Write-Host -NoNewline "[INFO] Host PHP detected: "
    & php -r "echo PHP_VERSION, PHP_EOL;"
} else {
    Write-WarningMessage "Host PHP is not installed. This is allowed; Docker remains the source of truth."
}

if (Test-CommandExists "composer") {
    Write-Host -NoNewline "[INFO] Host Composer detected: "
    & composer --version
} else {
    Write-WarningMessage "Host Composer is not installed. This is allowed."
}

if (Test-CommandExists "node") {
    Write-Host -NoNewline "[INFO] Host Node.js detected: "
    & node --version
} else {
    Write-WarningMessage "Host Node.js is not installed. This is allowed."
}

if (Test-CommandExists "npm") {
    Write-Host -NoNewline "[INFO] Host npm detected: "
    & npm --version
} else {
    Write-WarningMessage "Host npm is not installed. This is allowed."
}

Write-Header "3. Repository"

& git rev-parse --is-inside-work-tree *> $null

if ($LASTEXITCODE -eq 0) {
    Write-Pass "Current directory is a Git repository."

    $CurrentBranch = (& git branch --show-current).Trim()

    if ($CurrentBranch -eq "main") {
        Write-Pass "Current branch is main."
    } else {
        Write-Failure "Current branch is '$CurrentBranch'; expected 'main'."
    }

    $GitStatus = & git status --porcelain

    if ([string]::IsNullOrWhiteSpace(($GitStatus -join "`n"))) {
        Write-Pass "Git working tree is clean."
    } else {
        Write-Failure "Git working tree contains uncommitted changes."
        & git status --short
    }
} else {
    Write-Failure "Current directory is not a Git repository."
}

Write-Header "4. Environment Safety"

if (Test-Path ".env.example" -PathType Leaf) {
    Write-Pass ".env.example exists."
} else {
    Write-Failure ".env.example does not exist."
}

& git rev-parse --is-inside-work-tree *> $null

if ($LASTEXITCODE -eq 0) {
    & git ls-files --error-unmatch .env *> $null

    if ($LASTEXITCODE -eq 0) {
        Write-Failure ".env is tracked by Git. Remove it from Git immediately."
    } else {
        Write-Pass ".env is not tracked by Git."
    }
}

if (Test-Path ".gitignore" -PathType Leaf) {
    Write-Pass ".gitignore exists."
} else {
    Write-Failure ".gitignore does not exist."
}

if (Test-Path ".gitattributes" -PathType Leaf) {
    Write-Pass ".gitattributes exists."
} else {
    Write-Failure ".gitattributes does not exist."
}

if (Test-Path ".editorconfig" -PathType Leaf) {
    Write-Pass ".editorconfig exists."
} else {
    Write-Failure ".editorconfig does not exist."
}

Write-Header "5. Documentation Baseline"

$RequiredDocs = @(
    "docs/00-Decisions-and-Hosting-Matrix.md",
    "docs/01-PRD.md",
    "docs/02-Roadmap.md",
    "docs/03-UI.md",
    "docs/04-Design-System.md",
    "docs/05-Architecture.md",
    "docs/06-Data-Model-and-Business-Rules.md",
    "docs/07-Project-Documentation-and-Folder-Structure.md",
    "docs/08-Acceptance-and-Traceability.md",
    "docs/09-Operations-and-Release-Runbook.md",
    "AGENTS.md",
    "docs/evidence/phase-0.md"
)

foreach ($File in $RequiredDocs) {
    if (Test-Path $File -PathType Leaf) {
        Write-Pass "$File exists."
    } else {
        Write-Failure "$File is missing."
    }
}

Write-Header "6. Hosting Verification Status"

Write-Host "The following values must NOT be assumed:"
Write-Host "  - Active production PHP web version"
Write-Host "  - Active production PHP CLI version"
Write-Host "  - PHP 8.3 patch version"
Write-Host "  - PHP extensions"
Write-Host "  - Production MySQL/MariaDB engine and version"
Write-Host "  - Cron/worker capability"
Write-Host "  - SSH/Terminal/SFTP capability"
Write-Host "  - Document root/rewrite capability"
Write-Host "  - Symlink capability"
Write-Host "  - SMTP capability"
Write-Host "  - Storage/upload limits"
Write-Host "  - Backup/restore capability"
Write-Host ""
Write-Host "Record evidence or an explicit verification gap in:"
Write-Host "  docs/evidence/phase-0.md"

Write-Header "Result"

if ($Failures -eq 0) {
    Write-Pass "Phase 0 local repository/tooling checks passed."
    Write-Host "[INFO] Hosting acceptance remains dependent on recorded evidence/gaps."
    exit 0
}

Write-Failure "Phase 0 found $Failures problem(s)."
exit 1