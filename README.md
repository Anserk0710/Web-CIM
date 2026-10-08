# PT Cahya Intan Medika Website

Company profile website and CMS for PT Cahya Intan Medika.

## Current Development Phase

Current phase:

**Phase 0 — Tooling, Git & Repository Foundation**

The Laravel application has not been bootstrapped yet.

Application implementation begins in Phase 1 after the Phase 0 repository and local tooling baseline has been verified.

## Architecture Contract

The project is a Laravel modular monolith.

Backend:

Laravel 13.x on PHP 8.3.x.

Public website:

Blade + Tailwind CSS + lightweight JavaScript/Alpine where required.

CMS:

React + TypeScript + Inertia.js + Tailwind CSS + shadcn/ui.

Local runtime:

Docker Compose.

Local database target:

MySQL 8.4 LTS.

Frontend build target:

Node.js 24 LTS.

Production target:

cPanel/shared hosting with PHP 8.3 compatibility.

Kubernetes:

kind is used as a learning track and is not the production deployment target.

## Source of Truth

Project documentation is stored in `docs/`.

Read in this order before implementing a feature:

1. `docs/00-Decisions-and-Hosting-Matrix.md`
2. `docs/01-PRD.md`
3. `docs/06-Data-Model-and-Business-Rules.md`
4. `docs/02-Roadmap.md`
5. `docs/05-Architecture.md`
6. `docs/03-UI.md`
7. `docs/04-Design-System.md`
8. `docs/08-Acceptance-and-Traceability.md`
9. `docs/09-Operations-and-Release-Runbook.md`
10. `AGENTS.md`

Engineering work follows:

`PLAN → SPECIFY → BUILD → TEST`

## Phase 0 Verification

### Bash / WSL2 / Linux / macOS

Run:

`scripts/phase0-check.sh`

### Windows PowerShell

Run:

`powershell -ExecutionPolicy Bypass -File scripts/phase0-check.ps1`

Phase 0 evidence is recorded in:

`docs/evidence/phase-0.md`

Do not mark hosting capabilities as verified without actual evidence.

## Git Workflow

Baseline workflow:

`feature/* → Pull Request → main`

`main` is the integration/release baseline.

Direct feature development on `main` should be avoided once collaborative development starts.

## Environment Security

`.env.example` documents required configuration and must not contain production secrets.

`.env` is local/environment-specific and must never be committed.

Never commit:

- APP_KEY
- production database credentials
- SMTP credentials
- API keys
- private keys
- access tokens
- production candidate personal data

## Documentation Status

The supplied documentation package is an implementation specification.

It is not proof that the application, hosting environment, deployment process, or acceptance tests have already passed.

Runtime and hosting claims must be backed by evidence.