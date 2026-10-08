# Project Documentation + Folder Structure

**Versi dokumen:** 4.0 · **Diperbarui:** 5 Oktober 2026  
**Status:** spesifikasi implementasi; belum ada bukti aplikasi berjalan dalam paket ini.  
**Kontrak runtime:** Laravel 13.x + PHP **8.3.x**. PHP maksimum cPanel 8.3 dikonfirmasi pengguna; kemampuan hosting lain mengikuti [matriks verifikasi](00-Decisions-and-Hosting-Matrix.md).  
**Kontrak domain:** [Data Model & Business Rules](06-Data-Model-and-Business-Rules.md). **Pengujian:** [Acceptance](08-Acceptance-and-Traceability.md).
## PT Cahya Intan Medika — Company Profile + CMS
### Laravel + Docker + CI/CD + Kubernetes Learning

This document is the practical developer handbook for running, understanding, and extending the project.

---

# 1. Project Summary

The application is one Laravel modular monolith containing:

```text
Public Company Website
+
CMS/Admin
+
Authentication / RBAC
+
Business Modules
+
Storage
+
Email
+
Audit
```

Rendering model:

```text
Public Website
→ Blade + Tailwind + lightweight JavaScript/Alpine

CMS/Admin
→ React + TypeScript + Inertia.js + Tailwind + shadcn/ui
```

Data/infrastructure:

```text
MySQL
Laravel Eloquent
Laravel Filesystem
Laravel Mail
Database-backed session/queue baseline
```

Development/delivery:

```text
Docker Compose
GitHub Actions
GHCR
kind Kubernetes learning
Staging
cPanel production
```

---

# 2. Engineering Principles

1. Local-first.
2. Docker is the reproducible local source of truth.
3. One Laravel application, not separate frontend/backend applications.
4. Public content is server-rendered for SEO and simple production hosting.
5. CMS uses React/Inertia for rich operational UX.
6. Server-side validation and authorization are authoritative.
7. Private files remain private.
8. Automated tests protect critical workflows.
9. CI validates every Pull Request.
10. Releases are traceable.
11. Kubernetes is a learning layer, not a daily development dependency.
12. Production remains compatible with cPanel/shared hosting.

---

# 3. Main Technology Stack

## Application

- Laravel.
- PHP.
- Blade.
- React.
- TypeScript.
- Inertia.js.
- Tailwind CSS.
- shadcn/ui for CMS.
- Alpine.js/vanilla JavaScript where useful on public pages.
- Vite.

## Data

- MySQL.
- Eloquent ORM.
- Laravel migrations.
- factories/seeders.

## Application Services

- Laravel session authentication.
- Gates/Policies.
- Laravel Filesystem.
- Laravel Mail.
- Laravel Queue.
- Laravel Scheduler.

## Local Infrastructure

- Docker.
- Docker Compose.
- Nginx.
- PHP-FPM.
- MySQL.
- Mailpit.
- optional Adminer.

## Quality

- Pest/PHPUnit.
- Laravel Pint.
- PHPStan/Larastan.
- ESLint.
- Prettier.
- TypeScript type checking.
- Vitest/React Testing Library where valuable.
- Playwright.

## Delivery

- GitHub.
- GitHub Actions.
- GitHub Environments.
- GHCR.
- Semantic Versioning.

## Orchestration Learning

- kubectl.
- kind.

---

# 4. Recommended Repository Structure

```text
cim-website/
│
├─ app/
│  ├─ Actions/
│  │  ├─ Activity/
│  │  ├─ Career/
│  │  ├─ Inquiry/
│  │  ├─ Media/
│  │  ├─ Product/
│  │  └─ User/
│  │
│  ├─ Console/
│  ├─ Enums/
│  ├─ Exceptions/
│  ├─ Http/
│  │  ├─ Controllers/
│  │  │  ├─ Admin/
│  │  │  └─ Public/
│  │  ├─ Middleware/
│  │  └─ Requests/
│  │     ├─ Admin/
│  │     └─ Public/
│  │
│  ├─ Mail/
│  ├─ Models/
│  ├─ Policies/
│  ├─ Providers/
│  ├─ Services/
│  │  ├─ Audit/
│  │  ├─ Media/
│  │  ├─ Seo/
│  │  └─ Storage/
│  ├─ Support/
│  └─ View/
│     └─ Components/
│
├─ bootstrap/
│
├─ config/
│
├─ database/
│  ├─ factories/
│  ├─ migrations/
│  └─ seeders/
│
├─ docker/
│  ├─ nginx/
│  │  └─ default.conf
│  ├─ php/
│  │  └─ php.ini
│  └─ scripts/
│
├─ docs/
│  ├─ 00-Decisions-and-Hosting-Matrix.md
│  ├─ 01-PRD.md
│  ├─ 02-Roadmap.md
│  ├─ 03-UI.md
│  ├─ 04-Design-System.md
│  ├─ 05-Architecture.md
│  ├─ 06-Data-Model-and-Business-Rules.md
│  ├─ 07-Project-Documentation-and-Folder-Structure.md
│  ├─ 08-Acceptance-and-Traceability.md
│  ├─ 09-Operations-and-Release-Runbook.md
│  └─ adr/
│     ├─ 0001-modular-monolith.md
│     ├─ 0002-public-blade-cms-inertia.md
│     └─ ...
│
├─ k8s/
│  ├─ namespace.yaml
│  ├─ configmap.yaml
│  ├─ secret.example.yaml
│  ├─ mysql/
│  │  ├─ service.yaml
│  │  └─ statefulset.yaml
│  ├─ web/
│  │  ├─ deployment.yaml
│  │  └─ service.yaml
│  └─ jobs/
│     └─ migrate.yaml
│
├─ public/
│  ├─ build/
│  ├─ favicon.ico
│  └─ ...
│
├─ resources/
│  ├─ css/
│  │  ├─ theme.css
│  │  ├─ public.css
│  │  └─ admin.css
│  │
│  ├─ js/
│  │  ├─ admin.tsx
│  │  ├─ public.ts
│  │  ├─ bootstrap.ts
│  │  ├─ components/
│  │  │  ├─ admin/
│  │  │  └─ ui/
│  │  ├─ features/
│  │  │  ├─ activity/
│  │  │  ├─ career/
│  │  │  ├─ inquiry/
│  │  │  ├─ media/
│  │  │  ├─ products/
│  │  │  └─ users/
│  │  ├─ layouts/
│  │  ├─ lib/
│  │  ├─ pages/
│  │  │  └─ Admin/
│  │  └─ types/
│  │
│  └─ views/
│     ├─ components/
│     │  ├─ public/
│     │  └─ ui/
│     ├─ admin-root.blade.php
│     ├─ layouts/
│     └─ public/
│        ├─ home.blade.php
│        ├─ company.blade.php
│        ├─ products/
│        ├─ ecatalog/
│        ├─ activity/
│        ├─ career/
│        └─ contact.blade.php
│
├─ routes/
│  ├─ web.php
│  ├─ admin.php
│  └─ console.php
│
├─ scripts/
│  ├─ dev-up.sh
│  ├─ dev-down.sh
│  ├─ reset-db.sh
│  └─ release/
│
├─ storage/
│  ├─ app/
│  │  ├─ public/
│  │  └─ private/
│  ├─ framework/
│  └─ logs/
│
├─ tests/
│  ├─ Feature/
│  │  ├─ Admin/
│  │  ├─ Auth/
│  │  └─ Public/
│  ├─ Unit/
│  └─ Browser/
│
├─ .github/
│  ├─ workflows/
│  │  ├─ ci.yml
│  │  ├─ release.yml
│  │  ├─ deploy-staging.yml
│  │  └─ deploy-production.yml
│  └─ pull_request_template.md
│
├─ .dockerignore
├─ .editorconfig
├─ .env.example
├─ .gitignore
├─ AGENTS.md
├─ artisan
├─ composer.json
├─ composer.lock
├─ compose.yml
├─ Dockerfile
├─ package.json
├─ package-lock.json
├─ phpstan.neon
├─ pint.json
├─ playwright.config.ts
├─ README.md
├─ tsconfig.json
└─ vite.config.ts
```

This structure is a target, not a rule that every directory must exist from day one. Paket ZIP ini hanya berisi Markdown; folder aplikasi, Compose, migrations dan scripts belum dibuat. Jangan menganggap command contoh sudah diuji.

Create folders when their contents become real.

---

# 5. Folder Responsibilities

## `app/Actions`

Contains explicit business operations when a workflow deserves its own unit.

Examples:

```text
PublishProduct
ArchiveProduct
CreateCareerApplication
UpdateApplicationStatus
StoreMedia
DeleteMediaSafely
UpdateUserRoles
```

Do not create an Action class for every one-line model update.

## `app/Http/Controllers`

Split public and admin concerns.

```text
Controllers/Public
Controllers/Admin
```

Controllers should remain thin.

## `app/Http/Requests`

Server validation and request authorization.

## `app/Models`

Eloquent models, relationships, casts, useful scopes.

## `app/Policies`

Entity-level authorization.

## `app/Services`

Cross-cutting or reusable domain services such as:

- SEO generation.
- audit.
- storage/media helpers.

Avoid generic `SomethingService` classes without clear responsibility.

---

# 6. Public UI Structure

Blade templates live under:

```text
resources/views/public
```

Shared public components:

```text
resources/views/components/public
```

Generic UI Blade primitives:

```text
resources/views/components/ui
```

Recommended public hierarchy:

```text
layouts/public.blade.php
public/home.blade.php
public/company.blade.php
public/products/index.blade.php
public/products/show.blade.php
...
```

Essential public content is server-rendered.

---

# 7. CMS React Structure

CMS entry `resources/js/admin.tsx` dimuat hanya oleh Inertia root. Public entry `resources/js/public.ts` dan public.css dimuat layout publik. Keduanya memakai theme.css; public tidak menarik React/shadcn/admin bundle.

CMS lives under:

```text
resources/js
```

## `pages/Admin`

Inertia route-level pages.

Example:

```text
pages/Admin/Dashboard.tsx
pages/Admin/Products/Index.tsx
pages/Admin/Products/Create.tsx
pages/Admin/Products/Edit.tsx
```

## `components/ui`

Generic reusable React primitives.

## `components/admin`

CMS shell and cross-feature admin components.

## `features`

Domain reusable code/components.

Example:

```text
features/products/components/ProductForm.tsx
features/products/types.ts
features/products/utils.ts
```

Do not duplicate route pages inside features.

---

# 8. Routes

Recommended separation:

```text
routes/web.php
→ public website + public forms

routes/admin.php
→ CMS routes

routes/console.php
→ scheduled/console behavior
```

If project bootstrap does not yet load `admin.php`, keep routes together initially and split only when useful.

Naming convention:

```text
public.products.index
public.products.show
admin.products.index
admin.products.store
admin.products.update
admin.products.destroy
```

---

# 9. Database Conventions

## Migrations

Every schema change is migration-driven.

Never edit a migration already used by shared/staging/production environments to change history. Add a new migration instead.

During early local-only development, migration squashing/reset may be considered deliberately before first shared release.

## Factories

Use factories for tests and development demo data.

## Seeders

Recommended:

```text
PermissionSeeder
RoleSeeder
AdminUserSeeder
DevelopmentContentSeeder
DatabaseSeeder
```

Do not commit real production passwords.

---

# 10. Environment Variables

`.env.example` documents required configuration without secrets.

Typical groups:

```text
APP_*
DB_*
MAIL_*
SESSION_*
QUEUE_*
FILESYSTEM_*
```

Environment roles:

## Local

Local Docker values.

## CI

Ephemeral test-only values.

## Staging

Separate secrets/data from production.

## Production

Stored/configured on hosting or GitHub production Environment as appropriate.

Never share one `APP_KEY`/DB credential across staging and production.

---

# 11. Local Docker Services

Recommended `compose.yml`:

```text
nginx
app
node
mysql
mailpit
```

Optional:

```text
adminer
```

## nginx

Local HTTP server.

## app

PHP-FPM + Laravel runtime.

## node

Vite/npm/TypeScript/frontend tooling.

## mysql

Persistent database service.

## mailpit

Development SMTP capture.

---

# 12. Local Startup Contract

Target PHP 8.3.x, Node 24 LTS, database sesuai keputusan terverifikasi. Bootstrap harus didokumentasikan sebagai satu urutan repeatable:

1. Clone, baca keputusan/AGENTS; copy .env.example ke .env dengan nilai local non-production.
2. Build service images dan install Composer/npm dependencies dari lockfile melalui container. Bind mounts tidak boleh menyembunyikan vendor/assets tanpa langkah install yang jelas.
3. Start MySQL/Mailpit dan tunggu healthcheck; gunakan service name untuk koneksi internal.
4. Generate APP_KEY **hanya local environment baru**; jalankan migration dan development seed yang aman.
5. Siapkan persistent public/private storage, public link/controlled route, cache permissions dan Vite/HMR binding.
6. Start app/Nginx/node dan worker bila digunakan; buka URL public/CMS/Mailpit yang benar-benar dikonfigurasi.
7. Jalankan backend/frontend checks dan smoke; recreate container untuk verifikasi persistensi.

Implementasi Phase 1 membuat helper `scripts/dev-setup.sh` atau command setara, beserta dev-up/down. README aplikasi mencantumkan URL/port dan strategi akun development tanpa production secret. Target akhirnya developer cukup Git+Docker; host PHP/MySQL/Node tidak wajib.

`docker compose up --build` bukan pengganti langkah bootstrap yang belum diimplementasikan. Pada Windows, dokumentasikan shell helper yang dipilih (misalnya WSL2) dan berikan equivalent Docker commands; jangan mengasumsikan bash tersedia pada PowerShell.

---

# 13. Common Development Commands

Recommended project-level aliases/scripts should exist for these operations.

Raw examples:

```bash
# Start
docker compose up -d

# Logs
docker compose logs -f

# Laravel shell
docker compose exec app sh

# Artisan
docker compose exec app php artisan about

# Migration
docker compose exec app php artisan migrate

# Seed
docker compose exec app php artisan db:seed

# Reset ONLY disposable local/test database; never staging/production
docker compose exec app php artisan migrate:fresh --seed

# Backend tests
docker compose exec app php artisan test

# Frontend install/build
docker compose exec node npm ci
docker compose exec node npm run build
```

Exact scripts should be normalized once implementation begins. `reset-db.sh` wajib menolak environment non-local/non-testing dan meminta konfirmasi local reset; connection target ditampilkan tanpa password. Command contoh bukan bukti executable repository sudah tersedia.

---

# 14. Recommended NPM Scripts

Example target:

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "typecheck": "tsc --noEmit",
    "lint": "eslint .",
    "format:check": "prettier --check .",
    "test": "vitest run",
    "test:e2e": "playwright test"
  }
}
```

Only keep scripts actually configured.

---

# 15. Recommended Composer Scripts

Potential convenience scripts:

```text
composer test
composer analyse
composer lint
```

These should wrap actual project commands consistently.

---

# 16. Authentication Development

Local reset-password email should be sent to Mailpit.

Mailpit provides visual inspection without sending real internet email.

Public sign-up remains disabled.

---

# 17. Permission Development

Gunakan daftar domain/action dan template role final pada dokumen 06 bagian 3. Contoh: product.read/update/publish, application.read/download/export, user.assign_roles. Jangan memakai permission baru yang tidak tercatat.

Saat menambah izin: update constant/seed → policy/middleware → controller/action → Inertia props/UI → backend bypass/read/download tests → role defaults bila dibutuhkan → dokumen 06/08. Definisi role bukan frontend-only authorization. CV membutuhkan read+download; CSV read+export. Last Super Admin/delegation guard tidak boleh dilewati job/controller lain.

---

# 18. Product Development Flow

Admin React Form → Inertia mutation → authentication + active-user check → Form Request + policy → action/transaction → WORKING revision/relations + lock_version + audit → redirect/flash.

Publish action tersendiri memeriksa product.publish, version, slug, field/media/relations, kemudian atomik menyalin WORKING ke PUBLISHED dan mengubah public_slug/status. Public GET hanya membaca entity PUBLISHED dengan PUBLISHED revision. Preview authenticated membaca WORKING sesuai permission. Detail state machine dan schema ada pada dokumen 06.

---

# 19. Storage Development

Public assets:

```text
storage/app/public
```

Private assets:

```text
storage/app/private
```

Examples:

```text
public/products
public/activities
public/company
public/brochures
private/career
```

Do not access private CV by direct URL.

Use an authorized Laravel download response.

---

# 20. Media Development Rules

Upload pipeline:

```text
Validate
→ generate safe filename
→ store through Filesystem
→ write metadata
→ audit where needed
```

Deletion must check references where required.

Do not let deleting one media record silently break Product/Activity content.

---

# 21. SEO Development

Public page controllers/view models provide required SEO data.

Shared SEO helpers may live under:

```text
app/Services/Seo
```

or another simple equivalent.

Validate:

- title.
- description.
- canonical.
- OG.
- JSON-LD.
- indexability.

Sitemap only includes intended public records.

---

# 22. Testing Structure

Recommended:

```text
tests/Feature/Auth

tests/Feature/Admin/Products

tests/Feature/Admin/Roles

tests/Feature/Public/Products

tests/Feature/Public/CareerApplication

tests/Browser
```

Use tests close to behavior, not implementation internals.

---

# 23. CI Workflow Structure

Recommended files:

```text
.github/workflows/ci.yml
.github/workflows/release.yml
.github/workflows/deploy-staging.yml
.github/workflows/deploy-production.yml
```

Start with fewer workflows if easier, then split when responsibilities become stable.

---

# 24. `ci.yml` Responsibilities

Trigger on:

- Pull Request.
- relevant push.

Validate:

```text
Composer validation/install
Pint
PHPStan/Larastan
Backend tests
npm ci
TypeScript
ESLint
Frontend tests
Vite build
Docker build
security audit baseline
```

Use isolated CI MySQL.

---

# 25. Release Workflow

Checkout exact approved tag/commit → trusted quality checks → build vendor produksi pada PHP 8.3 + assets dari lockfiles → create cPanel artifact + SHA-256 manifest → build/push PHP-FPM/Nginx images with digest → publish release notes → staging → approval → artifact yang sama ke produksi.

Public/secret runtime config dipisahkan. Artifact tidak mengandung .env, uploads, node_modules, cached production config, dev credentials, logs atau public/hot. Runbook canonical ada pada dokumen 09.

---

# 26. Container Registry

Registry:

```text
GitHub Container Registry (GHCR)
```

Recommended image identity:

```text
ghcr.io/<owner>/<repo>:sha-<commit>
ghcr.io/<owner>/<repo>:v1.0.0
```

Do not rely only on mutable `latest` for traceability.

---

# 27. Kubernetes Folder

Suggested structure:

```text
k8s/
├─ namespace.yaml
├─ configmap.yaml
├─ secret.example.yaml
├─ mysql/
│  ├─ service.yaml
│  └─ statefulset.yaml
├─ web/
│  ├─ deployment.yaml
│  └─ service.yaml
└─ jobs/
   └─ migrate.yaml
```

Never commit a real secret manifest.

For learning, generate/apply secret values locally.

---

# 28. Kubernetes Workflow

Typical:

```bash
kind create cluster --name cim

docker build -t cim-web:local .
kind load docker-image cim-web:local --name cim

kubectl apply -f k8s/namespace.yaml
# apply config/services/statefulset/job/deployment as documented
```

Or use registry image after GHCR learning is implemented.

Access initially through port-forward.

---

# 29. Staging Documentation

Staging must have:

- independent database.
- independent storage.
- independent app key.
- independent SMTP config.
- `APP_ENV=staging` or equivalent.
- CMS environment badge.

Do not copy production secrets into staging unnecessarily.

Production personal/sensitive data should not be casually copied to staging.

---

# 30. Production Documentation

Production target:

```text
cPanel/shared hosting
PHP
MySQL
persistent filesystem
SMTP
HTTPS
```

Production does **not** assume:

- Docker support.
- Kubernetes.
- Node server.
- Redis.

Node is primarily a build tool.

---

# 31. Production Hosting Capability Checklist

Gunakan [matriks dokumen 00](00-Decisions-and-Hosting-Matrix.md). Satu informasi telah dikonfirmasi pengguna: **maksimum PHP cPanel 8.3**. Web/CLI aktif, patch, ekstensi, database, SMTP, cron, SSH/SFTP, Composer, symlink, root public, storage dan backup masih perlu bukti.

Verifikasi awal Phase 0–1; database sebelum Phase 2 schema, executor sebelum forms/queue dan deployment route sebelum Phase 9. Jangan menunda penemuan incompatibility sampai Phase 13. Developer boleh melanjutkan local foundation yang tidak bergantung fakta belum diketahui, dengan gap tercatat.

---

# 32. Production Deployment Model

Preferred `releases/<id>`, `shared/.env`, `shared/storage`, `current` → active release, document root current/public. Fallback non-symlink memakai maintenance/update terkontrol yang diuji pada provider. Tidak membutuhkan Docker/Kubernetes/Node runtime di cPanel.

Dokumen 09 bagian 3–5 mengatur capability gate, artifact inventory, CLI/platform checks dan fallback. Jangan menjadikan root project sebagai webroot atau membuat public artisan route.

---

# 33. Production Deployment Steps

Urutan tunggal: approved artifact → backup → upload/checksum → bind env/storage → PHP 8.3 platform check → migrate → runtime cache/preflight → activate → post-activation smoke → monitor/rollback.

Tidak menjalankan composer update/npm build ulang setelah staging acceptance. APP_KEY stabil; storage persist. Semua command/path provider dicatat setelah rehearsal, bukan ditebak. Detail dan smoke checklist ada pada dokumen 09.

---

# 34. Rollback

Ikuti dokumen 09 bagian 6. Application rollback mengaktifkan artifact sebelumnya yang masih compatible dengan schema, preserve env/storage dan smoke. Database recovery memerlukan snapshot konsisten dan penilaian data yang masuk sesudah backup; migrate:rollback bukan prosedur otomatis universal. Catat release, recovery duration dan hasil verifikasi.

---

# 35. Logging

Application:

```text
storage/logs
```

Local:

```bash
docker compose logs
```

Kubernetes:

```bash
kubectl logs
kubectl describe
kubectl get events
```

Production:

Use Laravel and cPanel/server logs.

Never log credentials or full private documents.

---

# 36. Error Handling Rules

Development may expose detailed errors locally.

Staging/production should use safe user-facing errors and detailed server logs.

Do not expose:

- SQL.
- stack traces.
- filesystem paths.
- environment variables.
- secrets.

---

# 37. Backup and Retention Baseline

DB dan public/private uploads dibackup harian serta sebelum deployment berisiko; retention 30 hari, salinan terenkripsi/akses terbatas terpisah dari hosting utama. Simpan recovery APP_KEY/config secara aman. Target RPO 24 jam/RTO 4 jam dibuktikan oleh restore drill sebelum go-live dan bulanan.

Retensi pelamar/inquiry/audit/log dan pemulihan deletion ledger mengikuti dokumen 06/09. Kebutuhan executor/kuota provider yang belum tersedia adalah gap operasional, bukan klaim backup berhasil.

---

# 38. Development Workflow

Use `AGENTS.md` process:

```text
PLAN
  ↓
SPECIFY
  ↓
BUILD
  ↓
TEST
```

Normal Git path:

```text
feature branch
    ↓
local test
    ↓
push
    ↓
Pull Request
    ↓
CI
    ↓
review
    ↓
main
```

---

# 39. Pull Request Expectations

A PR should state:

- Goal.
- Main changes.
- Migration/config changes.
- Screenshots for meaningful UI changes where useful.
- Test performed.
- Known limitations.

Avoid massive unrelated PRs.

---

# 40. Definition of Ready

Before implementation:

- Requirement clear.
- Acceptance criteria known.
- Data impact known.
- authorization known.
- architecture impact understood.

---

# 41. Definition of Done

Feature/change is done when:

- behavior implemented.
- validation implemented.
- authorization implemented.
- tests appropriate to risk exist.
- tests pass.
- static checks pass.
- UI states complete.
- accessibility considered.
- docs/config updated.
- Docker/CI remains working.

---

# 42. Naming Conventions

## PHP

Use Laravel/PHP conventions:

```text
PascalCase classes
camelCase methods/variables
snake_case DB columns
```

## React

Components:

```text
PascalCase.tsx
```

Hooks/utilities:

```text
camelCase
```

## Routes

Use meaningful dot-separated names.

## Permissions

Use:

```text
domain.action
```

---

# 43. Data Status Conventions

Prefer enums/constants for finite domain statuses. Allowed transitions lengkap ada pada dokumen 06; jangan menyimpulkan semua status boleh saling berpindah. Career menggunakan DRAFT/OPEN/CLOSED/ARCHIVED.

Examples:

```text
DRAFT
PUBLISHED
ARCHIVED
```

```text
NEW
REVIEWING
SHORTLISTED
INTERVIEW
REJECTED
HIRED
```

Avoid scattered magic strings.

---

# 44. Architecture Change Process

For material changes such as:

- replacing storage strategy.
- introducing Redis.
- separating API/frontends.
- moving production to VPS/Kubernetes.
- adding search infrastructure.

Create/update an ADR with:

```text
Context
Decision
Alternatives
Consequences
Migration plan
```

Do not make architecture changes implicitly inside a feature PR.

---

# 45. New Developer Onboarding Checklist

A new developer should be able to:

```text
[ ] Clone repository
[ ] Read README and AGENTS.md
[ ] Copy .env.example
[ ] Start Docker Compose
[ ] Generate app key if required
[ ] Run migrations/seed
[ ] Open public site
[ ] Open CMS
[ ] Open Mailpit
[ ] Login using documented development account strategy
[ ] Run backend tests
[ ] Run frontend checks
[ ] Understand where public Blade and CMS React code live
```

If this requires undocumented manual host configuration, onboarding is not complete.

---

# 46. Repository Cleanliness

Do not commit:

- `.env`.
- production secrets.
- temporary SQL dump containing sensitive data.
- local editor cache.
- local upload files unless intentionally fixtures.
- vendor/node_modules when dependency strategy excludes them.
- debug logs.

Fixtures must be safe synthetic/test data.

---

# 47. Documentation Map

README menautkan file asli bernomor pada paket ini. 00 mengatur keputusan/hosting; 01 produk; 02 urutan; 03 interaksi; 04 visual; 05 arsitektur; 06 data/domain; 07 handbook; 08 acceptance/evidence; 09 operasi; AGENTS contribution; CHANGELOG histori perubahan paket.

Saat dipindah ke repository aplikasi, salin dokumen bernomor ke docs/ dan AGENTS ke root, lalu perbarui relative links secara terkontrol. Jangan mengganti nama file menjadi PRD.md/Roadmap.md tanpa memperbaiki seluruh referensi.

---

# 48. Final Repository Philosophy

The repository should make the intended system obvious:

```text
One Laravel application.
One MySQL data model.
One shared design language.
Public SSR with Blade.
Rich CMS with React/Inertia.
Docker-reproducible development.
Tests before release.
CI before merge.
Traceable artifacts before deployment.
Kubernetes for learning.
cPanel for current production.
```
