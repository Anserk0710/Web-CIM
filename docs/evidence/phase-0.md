# Phase 0 Evidence

## Status

Status: **NOT_RUN**

Phase: **0 — Tooling, Git & Repository Foundation**

Acceptance references:

- HOST-01
- HOST-02 (partial; verification continues through later milestones)

This document records evidence only. Do not mark an item as PASS without observed evidence.

---

## 1. Runtime Contract

Laravel target:

`13.x`

PHP project target:

`8.3.x`

Node.js Phase 1 build target:

`24 LTS`

Local database target:

`MySQL 8.4 LTS`

Production database engine/version:

`PERLU VERIFIKASI`

Business timezone:

`Asia/Makassar`

---

## 2. Local Development Tooling

### Git

Status:

`NOT_RUN`

Command:

`git --version`

Observed version:

`TODO`

Evidence date:

`TODO`

---

### Docker

Status:

`NOT_RUN`

Commands:

`docker --version`

`docker info`

Observed version:

`TODO`

Docker daemon reachable:

`TODO`

Evidence date:

`TODO`

---

### Docker Compose

Status:

`NOT_RUN`

Command:

`docker compose version`

Observed version:

`TODO`

Compose v2 confirmed:

`TODO`

Evidence date:

`TODO`

---

## 3. Optional Host Tooling

Host PHP:

`OPTIONAL / TODO`

Host Composer:

`OPTIONAL / TODO`

Host Node.js:

`OPTIONAL / TODO`

These tools are not the local runtime source of truth. Docker remains authoritative.

---

## 4. Repository Baseline

Git repository created:

`NOT_RUN`

Default branch is `main`:

`NOT_RUN`

Working tree clean after baseline commit:

`NOT_RUN`

`.gitignore` exists:

`NOT_RUN`

`.gitattributes` exists:

`NOT_RUN`

`.editorconfig` exists:

`NOT_RUN`

`.env.example` exists:

`NOT_RUN`

Real `.env` is not tracked:

`NOT_RUN`

Documentation baseline exists under `docs/`:

`NOT_RUN`

Root `AGENTS.md` exists:

`NOT_RUN`

---

## 5. HOST-01 — PHP Hosting Evidence

Known information:

PHP maximum available in cPanel:

`8.3`

Source:

`User-confirmed project decision`

This does NOT prove that PHP 8.3 is currently active for the production web runtime or CLI runtime.

### Web PHP

Status:

`PERLU VERIFIKASI`

Observed version:

`TODO`

Patch version:

`TODO`

Handler:

`TODO`

Evidence source:

`TODO`

Do not leave a public `phpinfo()` file deployed as evidence.

### CLI PHP

Status:

`PERLU VERIFIKASI`

Command when terminal access is available:

`php -v`

Observed version:

`TODO`

Evidence source:

`TODO`

### Required PHP Extensions

Status:

`PERLU VERIFIKASI`

Minimum verification set:

`PDO / pdo_mysql`

`mbstring`

`XML / DOM`

`cURL`

`fileinfo`

`openssl`

Image processing requirements will be finalized when the implementation dependency is selected.

Observed extensions:

`TODO`

Gap owner:

`Developer / IT executor`

Verification boundary:

`Before Phase 1 is considered fully complete and before runtime dependencies are locked.`

---

## 6. HOST-02 — Hosting Capability Evidence

### Production Database

Status:

`PERLU VERIFIKASI`

Engine:

`TODO`

Version:

`TODO`

Charset:

`TODO`

Collation:

`TODO`

Migration permission available:

`TODO`

Backup/dump capability:

`TODO`

Verification boundary:

`Must be confirmed before Phase 2 schema stabilization.`

---

### Document Root / Rewrite

Status:

`PERLU VERIFIKASI`

Can domain/subdomain point to Laravel public directory:

`TODO`

Rewrite/routing capability:

`TODO`

Verification boundary:

`Before final deployment method is selected.`

---

### SSH / Terminal / SFTP

Status:

`PERLU VERIFIKASI`

SSH:

`TODO`

Terminal:

`TODO`

SFTP:

`TODO`

Verified deployment transport:

`TODO`

Verification boundary:

`Before Phase 9.`

---

### Composer on Hosting

Status:

`PERLU VERIFIKASI`

Composer available:

`TODO`

Version:

`TODO`

If unavailable, vendor will need to be packaged into the release artifact.

Verification boundary:

`Before Phase 9.`

---

### Cron / Worker

Status:

`PERLU VERIFIKASI`

Cron available:

`TODO`

Minimum interval:

`TODO`

Long-running worker allowed:

`TODO`

CLI PHP path/version:

`TODO`

Verification boundary:

`Before Phase 5 notification workflow and Phase 9 packaging.`

---

### Symlink

Status:

`PERLU VERIFIKASI`

Supported:

`TODO`

Verification boundary:

`Before Phase 9.`

---

### SMTP

Status:

`PERLU VERIFIKASI`

Endpoint available:

`TODO`

TLS mode:

`TODO`

Sending limit:

`TODO`

Do not record SMTP passwords in this document.

Verification boundary:

`Before staging forms and password-reset validation.`

---

### Storage

Status:

`PERLU VERIFIKASI`

Quota:

`TODO`

Inode limit:

`TODO`

Writable application storage:

`TODO`

Private/non-webroot storage capability:

`TODO`

Verification boundary:

`Before staging upload/backup validation.`

---

### Upload Limits

Status:

`PERLU VERIFIKASI`

PHP upload limit:

`TODO`

POST limit:

`TODO`

Web server limit:

`TODO`

Verification boundary:

`Before staging.`

---

### HTTPS / Domain / Staging

Status:

`PERLU VERIFIKASI`

Production domain:

`TODO`

Staging domain/subdomain:

`TODO`

HTTPS certificate capability:

`TODO`

Verification boundary:

`Before Phase 11.`

---

### Backup / Restore

Status:

`PERLU VERIFIKASI`

Database dump method:

`TODO`

Upload backup method:

`TODO`

Separate backup location:

`TODO`

Restore procedure:

`TODO`

Verification boundary:

`Before Phase 11 restore rehearsal.`

---

## 7. Phase 0 Exit Criteria

Git works:

`NOT_RUN`

Docker works:

`NOT_RUN`

Docker Compose v2 works:

`NOT_RUN`

PHP maximum 8.3 is recorded:

`RECORDED`

Actual PHP web/CLI/patch/extensions have evidence or an explicit verification gap:

`GAP RECORDED`

Production database engine/version confirmed:

`PERLU VERIFIKASI BEFORE PHASE 2`

Scope/status/permission/CMS mapping in document 06 reviewed:

`REVIEWED AS DOCUMENTATION BASELINE`

Hosting gaps have milestone boundaries:

`RECORDED`

Repository created:

`NOT_RUN`

`.env.example` exists without real secrets:

`NOT_RUN`

`main` is a clean baseline:

`NOT_RUN`

---

## 8. Verification Commands

Bash / WSL2:

`scripts/phase0-check.sh`

Windows PowerShell:

`powershell -ExecutionPolicy Bypass -File scripts/phase0-check.ps1`

Paste relevant command outputs or reference screenshots/logs here after execution.

Do not include passwords, API keys, SMTP credentials, database passwords, APP_KEY, or other secrets.

---

## 9. Final Phase Status

Current status:

**NOT_RUN**

Change to `PASS` only when all Phase 0 exit criteria that can be completed locally have evidence and all unresolved hosting capabilities are explicitly recorded with their required verification milestone.