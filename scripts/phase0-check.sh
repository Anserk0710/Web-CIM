#!/usr/bin/env sh

set -u

FAILURES=0

print_header() {
    printf '\n========================================\n'
    printf '%s\n' "$1"
    printf '========================================\n'
}

pass() {
    printf '[PASS] %s\n' "$1"
}

warn() {
    printf '[WARN] %s\n' "$1"
}

fail() {
    printf '[FAIL] %s\n' "$1"
    FAILURES=$((FAILURES + 1))
}

check_command() {
    command_name="$1"
    display_name="$2"

    if command -v "$command_name" >/dev/null 2>&1; then
        pass "$display_name is installed."
        return 0
    fi

    fail "$display_name is not installed or is not available in PATH."
    return 1
}

print_header "CIM Phase 0 Verification"

printf 'Runtime contract:\n'
printf '  Laravel : 13.x\n'
printf '  PHP     : 8.3.x\n'
printf '  Node.js : 24 LTS target for Phase 1\n'
printf '  Database: MySQL 8.4 LTS local target\n'
printf '\n'

print_header "1. Required Tooling"

if check_command git "Git"; then
    git --version
fi

if check_command docker "Docker"; then
    docker --version

    if docker info >/dev/null 2>&1; then
        pass "Docker daemon is running."
    else
        fail "Docker is installed but the Docker daemon is not reachable."
    fi

    if docker compose version >/dev/null 2>&1; then
        pass "Docker Compose v2 is available."
        docker compose version
    else
        fail "Docker Compose v2 is not available."
    fi
fi

print_header "2. Optional Host Tooling"

if command -v php >/dev/null 2>&1; then
    printf '[INFO] Host PHP detected: '
    php -r 'echo PHP_VERSION, PHP_EOL;' 2>/dev/null || php --version
else
    warn "Host PHP is not installed. This is allowed; Docker remains the source of truth."
fi

if command -v composer >/dev/null 2>&1; then
    printf '[INFO] Host Composer detected: '
    composer --version
else
    warn "Host Composer is not installed. This is allowed."
fi

if command -v node >/dev/null 2>&1; then
    printf '[INFO] Host Node.js detected: '
    node --version
else
    warn "Host Node.js is not installed. This is allowed."
fi

if command -v npm >/dev/null 2>&1; then
    printf '[INFO] Host npm detected: '
    npm --version
else
    warn "Host npm is not installed. This is allowed."
fi

print_header "3. Repository"

if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    pass "Current directory is a Git repository."

    CURRENT_BRANCH="$(git branch --show-current 2>/dev/null || true)"

    if [ "$CURRENT_BRANCH" = "main" ]; then
        pass "Current branch is main."
    else
        fail "Current branch is '$CURRENT_BRANCH'; expected 'main'."
    fi

    if [ -z "$(git status --porcelain)" ]; then
        pass "Git working tree is clean."
    else
        fail "Git working tree contains uncommitted changes."
        git status --short
    fi
else
    fail "Current directory is not a Git repository."
fi

print_header "4. Environment Safety"

if [ -f ".env.example" ]; then
    pass ".env.example exists."
else
    fail ".env.example does not exist."
fi

if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    if git ls-files --error-unmatch .env >/dev/null 2>&1; then
        fail ".env is tracked by Git. Remove it from Git immediately."
    else
        pass ".env is not tracked by Git."
    fi
fi

if [ -f ".gitignore" ]; then
    pass ".gitignore exists."
else
    fail ".gitignore does not exist."
fi

if [ -f ".gitattributes" ]; then
    pass ".gitattributes exists."
else
    fail ".gitattributes does not exist."
fi

if [ -f ".editorconfig" ]; then
    pass ".editorconfig exists."
else
    fail ".editorconfig does not exist."
fi

print_header "5. Documentation Baseline"

REQUIRED_DOCS="
docs/00-Decisions-and-Hosting-Matrix.md
docs/01-PRD.md
docs/02-Roadmap.md
docs/03-UI.md
docs/04-Design-System.md
docs/05-Architecture.md
docs/06-Data-Model-and-Business-Rules.md
docs/07-Project-Documentation-and-Folder-Structure.md
docs/08-Acceptance-and-Traceability.md
docs/09-Operations-and-Release-Runbook.md
AGENTS.md
docs/evidence/phase-0.md
"

for file in $REQUIRED_DOCS; do
    if [ -f "$file" ]; then
        pass "$file exists."
    else
        fail "$file is missing."
    fi
done

print_header "6. Hosting Verification Status"

printf '%s\n' "The following values must NOT be assumed:"
printf '%s\n' "  - Active production PHP web version"
printf '%s\n' "  - Active production PHP CLI version"
printf '%s\n' "  - PHP 8.3 patch version"
printf '%s\n' "  - PHP extensions"
printf '%s\n' "  - Production MySQL/MariaDB engine and version"
printf '%s\n' "  - Cron/worker capability"
printf '%s\n' "  - SSH/Terminal/SFTP capability"
printf '%s\n' "  - Document root/rewrite capability"
printf '%s\n' "  - Symlink capability"
printf '%s\n' "  - SMTP capability"
printf '%s\n' "  - Storage/upload limits"
printf '%s\n' "  - Backup/restore capability"
printf '\n'
printf '%s\n' "Record evidence or an explicit verification gap in:"
printf '%s\n' "  docs/evidence/phase-0.md"

print_header "Result"

if [ "$FAILURES" -eq 0 ]; then
    printf '[PASS] Phase 0 local repository/tooling checks passed.\n'
    printf '[INFO] Hosting acceptance remains dependent on recorded evidence/gaps.\n'
    exit 0
fi

printf '[FAIL] Phase 0 found %s problem(s).\n' "$FAILURES"
exit 1