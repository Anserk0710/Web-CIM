# Development Roadmap
## PT Cahya Intan Medika
### Laravel + Docker + CI/CD + Kubernetes Learning Track

**Versi:** 4.0 · **Tanggal:** 5 Oktober 2026  
**Status:** roadmap implementasi; exit criteria belum dicentang karena aplikasi belum diuji.  
**Runtime:** Laravel 13.x / PHP 8.3.x. Capability produksi mengacu [dokumen 00](00-Decisions-and-Hosting-Matrix.md).  

**Primary Goal:** Full application works locally first.  
**Secondary Goal:** Learn Laravel and Docker using a real production-oriented application.  
**Third Goal:** Learn automated testing and CI/CD as part of the normal development lifecycle.  
**Fourth Goal:** Learn Kubernetes using the same application image in a local kind cluster.  
**Final Goal:** Deliver a controlled staging and cPanel production release with rollback.

---

# 1. Recommended Development Order

| Phase | Fokus | Dependensi |
|---|---|---|
| 0 | Scope/decisions, hosting discovery, versi dan Git/tooling | Keputusan pengguna |
| 1 | Laravel PHP 8.3, Docker, public/CMS shell dan CI dasar | 0 |
| 2 | Schema kontrak, Auth/RBAC/audit, storage foundation | 1; DB target terverifikasi |
| 3 | Product vertical slice: draft → publish → public → tests | 2 |
| 4 | CMS E-Catalog/Activity/Career/Media/Settings/pages | 3 |
| 5 | Public pages + Inquiry/Application end-to-end | 4; executor mail/queue diketahui |
| 6 | Content migration + penguatan test/retention | 5 |
| 7 | Docker/runtime hardening | 6 |
| 8 | CI lengkap dan enforcement quality gates | 7; CI dasar sudah ada sejak 1 |
| 9 | Release artifact cPanel + GHCR versioned images | 8; deployment method terverifikasi |
| 10 | kind learning track mandiri | 9; tidak memblokir 11–13 |
| 11 | Staging + delivery + restore/rollback rehearsal | 9, bukan 10 |
| 12 | Security/performance/accessibility/content QA | 11 |
| 13 | Production release + monitor | 12 |

Tests tumbuh bersama setiap feature. Tidak menunggu Phase 6 untuk menulis test dan tidak menunggu Phase 8 untuk menjalankan CI. Phase 10 dapat dikerjakan paralel waktunya dengan staging atau sesudah produksi sesuai prioritas belajar.

Referensi acceptance IDs dan metode: [dokumen 08](08-Acceptance-and-Traceability.md). Setiap fase mencatat commit dan evidence. Phase selesai hanya setelah exit criteria yang berlaku terbukti, bukan karena file dokumentasi telah dibuat.

---

# PHASE 0 — Tooling, Git & Repository Foundation

## Objective

Menyiapkan development workstation dan repository yang reproducible.

## Install / Verify

- Git.
- Docker Desktop / Docker Engine.
- Docker Compose v2.
- PHP untuk optional host tooling only; Docker tetap source of truth.
- Composer untuk optional host tooling only.
- Node.js untuk optional host tooling only.
- Runtime target Laravel 13 / PHP 8.3 dicatat.
- Database target dan capability hosting diperiksa pada matriks dokumen 00.
- `kubectl` dan `kind` dipasang saat memasuki learning Phase 10, bukan syarat fondasi aplikasi.

Optional:

- VS Code.
- Docker extension.
- PHP/Intelephense.
- Laravel extension.

## Repository Baseline

```text
project/
├─ app/
├─ bootstrap/
├─ config/
├─ database/
├─ public/
├─ resources/
├─ routes/
├─ storage/
├─ tests/
├─ docker/
├─ k8s/
├─ scripts/
├─ .github/workflows/
├─ Dockerfile
├─ compose.yml
├─ .env.example
├─ AGENTS.md
└─ README.md
```

## Git Baseline

Recommended lightweight workflow:

```text
feature/*
    ↓
Pull Request
    ↓
main
```

Define:

- `.gitignore`.
- branch protection target.
- PR template later in CI phase.
- semantic commit conventions optional but recommended.

## Learn in This Phase

Git:

- clone.
- branch.
- add/commit.
- merge/rebase basics.
- PR concept.

Docker:

- image.
- container.
- port.
- network.
- volume.
- environment variable.
- Dockerfile.
- Compose service.

Kubernetes:

Concept only:

- cluster.
- node.
- pod.

## Test

```bash
git --version
docker --version
docker compose version
```

## Exit Criteria

- [ ] Git works.
- [ ] Docker works.
- [ ] Docker Compose works.
- [ ] PHP maksimum 8.3 tercatat; actual web/CLI/patch/extensions memiliki bukti atau gap bertanggung jawab.
- [ ] Database engine/version dikonfirmasi sebelum Phase 2.
- [ ] Scope, status, permission dan CMS mapping dokumen 06 dipahami.
- [ ] Gap hosting memiliki batas milestone; tidak menghalangi pekerjaan local yang independen.
- [ ] Repository created.
- [ ] `.env.example` exists without real secrets.
- [ ] `main` is clean baseline.

---

# PHASE 1 — Laravel + Docker Application Foundation

## Objective

Membuat application skeleton yang benar-benar berjalan melalui Docker Compose.

## Build

- Laravel 13 application pada PHP 8.3.x.
- Node 24 LTS untuk build; lockfiles/pinned image version setelah compatibility checks.
- Separate public.ts/admin.tsx entry + shared theme CSS.
- CI dasar PR: lint/types/build dan test skeleton yang meaningful.
- Bootstrap dependency/env/key lokal/migration dan persistent public/private storage didokumentasi.
- Blade public shell.
- React + TypeScript + Inertia CMS shell.
- Tailwind CSS.
- shadcn/ui baseline for CMS.
- Vite.
- Nginx.
- PHP-FPM.
- MySQL.
- Mailpit.

## Docker Compose Services

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

## App Baseline

Public:

```text
/
```

CMS:

```text
/admin/login
/admin
```

At this phase pages may still be shell-level, but stack integration must be real.

## Initial Health Endpoint

Create a lightweight health endpoint for Docker/Kubernetes checks, e.g.:

```text
/up
```

It should validate application boot. DB-specific readiness may be separated if needed.

## Learn

- Laravel request lifecycle.
- Blade rendering.
- Inertia request/response.
- React mounting through Vite.
- Nginx → PHP-FPM.
- Compose DNS.
- MySQL connection.
- Mailpit SMTP.
- Bind mount vs named volume.

## Test

- Public page opens.
- CMS shell opens.
- Laravel connects to `mysql` service name.
- Mailpit web UI opens.
- Vite development flow works.
- Container recreation does not delete MySQL volume.

## Exit Criteria

- [ ] `docker compose up --build` succeeds.
- [ ] Nginx serves Laravel.
- [ ] Laravel runs through PHP-FPM.
- [ ] MySQL connection works.
- [ ] Vite/React/Inertia works.
- [ ] Mailpit works.
- [ ] Health endpoint responds.
- [ ] Fresh setup mengikuti handbook tanpa langkah tersembunyi.
- [ ] CI dasar berjalan pada PR.
- [ ] Dependency tidak memerlukan PHP di atas 8.3.
- [ ] Media volume/mount dan file permissions tersedia untuk vertical slice berikutnya.

---

# PHASE 2 — Database + Authentication + RBAC + Audit Core

## Objective

Membuat application security and domain foundation sebelum full CMS.

## Database Models

Create migrations/models for:

- User.
- Role.
- Permission.
- Role-Permission relation.
- User-Role relation.
- Product.
- Product Category.
- Product Media.
- Product Specification Group/Item.
- E-Catalog.
- Activity.
- Activity Block.
- Career.
- Career Application.
- Inquiry.
- Media.
- Site Setting.
- Audit Log.

Schema mengikuti dokumen 06, termasuk WORKING/PUBLISHED revisions, media usages, consent, page sections, office, redirects, status history dan access guard. Revisi keputusan domain harus dicatat; tidak menebak cardinality saat coding. Migration feature-specific boleh dibangun saat feature tersebut dimulai dengan kontrak tetap konsisten.

## Authentication

Implement:

- Login.
- Logout.
- Forgot password.
- Reset password.
- Change password.
- Disable public registration.
- Active/inactive user enforcement.

## RBAC

Implement:

- Roles.
- Permissions.
- Permission assignment.
- Gates/Policies.
- Permission-aware admin navigation.

Critical rule:

> UI permission is convenience; server authorization is authoritative.

## Audit

Implement reusable audit service/action for:

- User/role changes.
- Settings changes.
- content mutations.
- sensitive workflow updates.

## Seed

Create development seeders for:

- Super Admin.
- Core permissions.
- Initial roles.
- Safe demo content where needed.

No real production credential may be committed.

## Test

Feature tests:

- Guest denied admin route.
- Valid user logs in.
- Inactive user denied.
- Permission without access returns 403.
- Super Admin allowed.
- Password reset email reaches Mailpit.
- Audit event created for selected actions.

## Exit Criteria

- [ ] Database schema migrates from zero.
- [ ] Seeder runs.
- [ ] Login/logout works.
- [ ] Reset password works.
- [ ] RBAC works server-side.
- [ ] Audit base works.
- [ ] Auth/RBAC feature tests exist.
- [ ] Active session revocation, privilege grant dan last-Super-Admin concurrency tests pass.
- [ ] Public/private storage boundary dan safe media reference foundation tersedia.
- [ ] Database engine/version nyata masuk pengujian integrasi.

---

# PHASE 3 — Product Vertical Slice

## Objective

Membuktikan satu alur lengkap dari CMS hingga halaman publik sebelum memperluas modul.

## Build

- CMS shell/sidebar/breadcrumb/environment badge dan states yang dapat digunakan ulang.
- Product/category/brand/type dengan satu kategori utama; semua field gallery/spec/highlight/brochure/related/SEO sesuai dokumen 06.
- WORKING/PUBLISHED revision, separate publish/archive, preview authenticated, slug redirect, optimistic locking.
- Media upload/reference/deletion safety untuk draft maupun published.
- Public Products listing/detail, search/filter/pagination dan metadata/sitemap visibility.
- Feature tests setiap aksi dan Playwright smoke login → draft save → publish → public visibility.

## Exit Criteria

- [ ] Create/edit Product nyata menyimpan seluruh field/relasi dan bertahan setelah restart.
- [ ] Draft revision tidak mengubah halaman live.
- [ ] Editor tanpa publish ditolak oleh server, termasuk crafted request.
- [ ] Publish/slug/media failures tidak membuat update parsial.
- [ ] Stale edit terdeteksi.
- [ ] Archive/unpublish menyembunyikan publik tanpa menghapus data terkait.
- [ ] CI menjalankan regression suite dan E2E smoke yang sudah tersedia.

Acceptance: PROD-01–03, REV-01–02, TAX-01, MEDIA-01–03, RBAC-01, SEO-01–02, PERF-01.

---

# PHASE 4 — Remaining CMS Modules and Content Administration

## Objective

Memperluas pola Product yang sudah terbukti ke E-Catalog, Activity, Career, Media, User/Role, Settings/page content dan Audit. Applications/Inquiries diselesaikan end-to-end pada Phase 5.

## 4.1 CMS Shell

Build:

- Admin layout.
- Sidebar.
- Breadcrumb.
- Permission-aware navigation.
- Environment badge.
- Data table primitives.
- Form primitives.
- Dialog.
- Toast.
- Loading.
- Error.
- Empty state.

## 4.2 Product

Full:

- CRUD.
- Search/filter/sort.
- Draft/publish/archive.
- Categories.
- Brand/type.
- Featured.
- Media/gallery.
- Brochure.
- Specs.
- Related Product.
- E-Catalog relation.
- SEO.
- Preview.

## 4.3 E-Catalog

Full:

- CRUD.
- Product relation.
- Catalog fields.
- Availability.
- Publish state.
- Procurement fields.
- SEO.

## 4.4 Activity

Full:

- CRUD.
- Categories.
- Media.
- Structured block editor dengan schema/sanitization, media usage registry dan WORKING/PUBLISHED revision.
- Reorder.
- Publish/archive.
- Related Product.
- SEO.

## 4.5 Career

Full:

- CRUD.
- Draft/open/close/archive; deadline UTC/WITA dan update+publish untuk edit data live.
- Dates.
- Department.
- Location.
- Employment type.
- Responsibilities.
- Requirements.
- SEO.

## 4.6 Media

- Upload.
- Reuse.
- Alt text.
- Caption.
- Replace.
- Safe delete.
- Reference protection.

## 4.7 User & Role

- Users.
- Activate/deactivate.
- Roles.
- Permission matrix.

## 4.8 Settings

- Company.
- Contact.
- Offices.
- Coverage.
- SEO defaults.
- Logo/favicon.
- Footer.
- Structured page forms untuk hero/capability/lifecycle/values/milestones/career branding sesuai mapping dokumen 06.

## 4.9 Audit UI

- List.
- Search/filter.
- Detail where useful.

## Test

For every module, verify:

```text
create
read
update
delete/archive
permission
validation
persistence
loading/error/empty
```

## Exit Criteria

- [ ] Seluruh module pada cakupan Phase 4 real; Application/Inquiry menjadi acceptance Phase 5.
- [ ] No mock primary workflow.
- [ ] Data persists across restart.
- [ ] Authorization is server-side.
- [ ] Audit events are produced.
- [ ] Media persists through mounted storage.
- [ ] CMS responsive baseline works.

---

# PHASE 5 — Public Website, Forms and Operational Workflows

## Objective

Menyelesaikan public pages dan alur Application/Inquiry dengan CMS nyata. Public memakai Blade + Tailwind + JS ringan. Fields/states/scope mengikuti dokumen 06; media foundation sudah ada sejak fase sebelumnya.

## Global

- Navbar.
- Footer.
- Skip link.
- SEO base.
- sitemap.
- robots.txt.
- 404.
- error page.
- responsive layout.
- reduced motion support.

## Home

- Approved blue palette.
- Hero.
- Bento.
- Metrics.
- Sticky service lifecycle.
- Horizontal Product rail.
- Scroll progress.
- Subtle parallax.
- Activity.
- CTA.

## Company

- Story.
- Vision.
- Mission.
- Values.
- Metrics.
- Milestones.
- Coverage.

## Products

- Search.
- Filters.
- Grid.
- Pagination/load-more strategy.
- Product detail.
- Gallery.
- Specs.
- Related products.

## E-Catalog

- Search/filter.
- Procurement cards.
- Linked product.
- CTA.

## Activity

- Feature story.
- Bento/list.
- Category/search.
- Detail renderer.

## Career

- Employer brand.
- Vacancy listing.
- Vacancy detail.

## Contact

- Offices.
- Hours.
- Location/map presentation.
- Inquiry form UI shell.

## SEO Test

Verify rendered HTML for:

- title.
- meta description.
- canonical.
- OG metadata.
- JSON-LD.
- indexability status.


## Career Application

Catatan implementasi bertahap: route POST `careers.apply` dan `inquiries.store` baru didaftarkan setelah handler beserta workflow terkait tersedia. Sebelum itu, halaman publik menampilkan pemberitahuan formulir belum tersedia; konfigurasi privasi/consent saja tidak mengaktifkan submission.

Public:

- Form.
- Validation.
- CV upload.
- Consent.
- Rate limit.
- Anti-abuse baseline.

CMS:

- List.
- Detail.
- Status.
- Notes.
- Secure CV download.
- CSV export.

## Contact Inquiry

Public:

- Inquiry type.
- Form.
- Validation.
- Honeypot/rate limit.
- Persistence.

CMS:

- List.
- Detail.
- Status.
- Notes.
- CSV export.

## Storage

Public:

```text
storage/app/public
```

Private:

```text
storage/app/private
```

Validate:

- Public media persists after app container recreation.
- Private CV cannot be opened directly.
- Authenticated authorized download works.


## Additional Contract

- Career DRAFT/OPEN/CLOSED/ARCHIVED; deadline dicek server tanpa menunggu cron.
- Application unique email+vacancy, CV PDF <=5 MiB, consent version/time, transitions/reopen/notes/history.
- Application CV tidak masuk Media Library; download/read/export terpisah.
- Inquiry types/field/relations/status/reopen dan CSV <=5.000 baris dengan formula protection.
- Notifications setelah commit; executor queue/cron atau synchronous fallback yang aman sudah diverifikasi.
- Page forms terstruktur mencakup semua konten publik; zero-vacancy tidak menerima lamaran umum.
- Retention/anonymized states dan UI tindak lanjut HR tersedia.

## Exit Criteria

- [ ] Semua public pages menggunakan CMS data nyata.
- [ ] Inquiry dan Application bekerja end-to-end dengan failure/duplicate/deadline tests.
- [ ] Private CV protection, upload boundary dan reference safety terbukti.
- [ ] SMTP failure tidak menghilangkan data dan dapat ditindaklanjuti.
- [ ] CMS/public forms responsive, keyboard dan reduced-motion baseline bekerja.
- [ ] JSON-LD/sitemap/redirect tidak mengekspos draft atau lowongan aktif palsu.

Acceptance: ECAT/ACT/CAREER/APP/CV/INQ/MAIL/EXPORT/PAGE/AUDIT/SEO/UI sesuai dokumen 08.

---

# PHASE 6 — Content Migration and Test Reinforcement

## Objective

Melengkapi coverage dan import konten setelah tests/CI telah berjalan bersama feature sejak fase awal.

## Content Migration

Inventaris content/media/URL lama, mapping sumber→target, normalisasi, import idempotent dan error report. Rehearsal sebelum cutover; jangan mengarang spesifikasi/metric. Periksa jumlah record, media integrity, SEO redirects, kontak aktual dan delta import/content freeze. Test consent/retention purge dan HR handover memakai clock/fixture sintetis.

## Backend Tests

Use Pest/PHPUnit.

Cover:

- Authentication.
- RBAC.
- Products.
- E-Catalog relations.
- Activity state visibility.
- Career lifecycle.
- Inquiry.
- Career Application.
- private file authorization.
- media validation.
- settings permission.
- audit generation.

## Frontend Tests

Use Vitest/React Testing Library selectively for high-value React behavior:

- complex form state.
- permission-aware components.
- reusable data-table behavior.
- block editor logic.

Do not test implementation details unnecessarily.

## Browser Tests

Use Playwright for critical flows:

1. Admin login.
2. Product create/publish.
3. Published Product visible publicly.
4. Draft Product hidden publicly.
5. Contact form submission.
6. Career application submission.
7. Unauthorized user blocked.

## Test Data

- Factories.
- Dedicated test database.
- Deterministic test data.
- No dependency on developer's local DB state.

## Exit Criteria

- [ ] Migrated content/mappings divalidasi pemilik konten; source provenance tercatat.
- [ ] Retention/cleanup/failure compensation tests tersedia.
- [ ] Critical feature tests exist.
- [ ] Tests run from clean DB state.
- [ ] Browser tests cover core path.
- [ ] `composer test` or documented equivalent succeeds.
- [ ] Frontend test command succeeds.

---

# PHASE 7 — Docker Hardening

## Objective

Turn Docker from a development convenience into a clean reproducible build/runtime foundation.

## Dockerfile

Create production-oriented multi-stage build untuk PHP 8.3 FPM dan image Nginx/assets dari release yang sama. Artifact cPanel adalah archive terpisah dari image.

Recommended logical stages:

```text
composer-deps
node-assets
runtime
```

or another clear equivalent.

Requirements:

- Layer caching.
- Production Composer dependencies.
- Vite production assets.
- Minimal runtime tools.
- Non-root runtime where compatible with deployment image design.
- Correct storage/cache permissions.
- `.dockerignore`.

## Compose

Improve:

- Healthcheck.
- Named MySQL volume.
- Explicit network.
- Storage mounts.
- Mailpit.
- restart policies where useful.
- dev/prod command separation.

## Environment

Maintain:

```text
.env.example
```

Optional:

```text
.env.local.docker
```

Never commit real secrets.

## Helper Commands

Recommended Makefile/scripts aliases for:

```text
up
down
rebuild
shell
migrate
seed
test
reset-db
logs
```

## Learning Exercises

1. Build image manually.
2. Run application image without Compose where practical.
3. Inspect image layers.
4. Inspect network.
5. Inspect named volume.
6. Delete/recreate application container.
7. Verify DB persists.
8. Delete/recreate MySQL container without volume removal.
9. Verify DB persists.
10. Inspect logs.
11. Execute shell inside container.

## Exit Criteria

- [ ] Fresh clone starts using documented commands.
- [ ] Production image build is reproducible tanpa source bind mount host.
- [ ] Public pages tidak mengunduh CMS React bundle.
- [ ] Composer platform requirements lolos PHP 8.3 dan DB target diuji.
- [ ] DB persistence understood.
- [ ] storage persistence understood.
- [ ] Developer can explain app/nginx/mysql/node roles.

---

# PHASE 8 — Full CI Quality Gates

## Objective

Memperkuat CI dasar yang sudah berjalan sejak Phase 1 menjadi quality gate lengkap untuk PR dan release.

## GitHub Actions Workflow

Recommended jobs:

```text
quality-php
quality-js
test-backend
test-frontend
build-assets
build-container
security-audit
```

Jobs may be consolidated initially, then parallelized after stable.

## PHP Quality

- `composer validate`.
- Pint check.
- PHPStan/Larastan.
- Pest/PHPUnit.

## JS Quality

- `npm ci`.
- TypeScript check.
- ESLint.
- frontend tests.
- E2E smoke pada PR dan full critical suite pada release candidate.
- Vite build.

## Database Service

CI test job provisions isolated MySQL service/container.

Run migrations before feature tests.

## Docker Validation

Build application production image in CI.

CI does not need to push image yet in this phase.

## Security Baseline

- `composer audit`.
- package audit policy.
- secret exposure prevention practices.

## Branch Protection

Require selected CI checks before merge to `main` bila fitur repository tersedia dan sudah dikonfigurasi. Catat dan verifikasi fallback review/release manual bila enforcement tidak tersedia; jangan menyebut merge terblokir otomatis tanpa bukti.

## Exit Criteria

- [ ] PR automatically runs CI.
- [ ] PHP quality checks pass.
- [ ] TypeScript/ESLint checks pass.
- [ ] Backend tests pass.
- [ ] Frontend tests pass.
- [ ] Production assets build.
- [ ] Docker image builds.
- [ ] Required failed checks block merge.

---

# PHASE 9 — Release Management + Container Registry

## Objective

Create traceable releases and versioned build artifacts.

## Versioning

Use Semantic Versioning:

```text
v0.1.0
v0.2.0
v1.0.0
```

## Release Artifact

Untuk cPanel buat artifact source + vendor produksi + Vite assets dari commit/lockfiles yang sama pada PHP 8.3; exclude env/uploads/dev tools/runtime cache. Simpan SHA-256 manifest, image digests dan release notes. Artifact yang diuji staging dipromosikan tanpa rebuild ke produksi. Runbook: dokumen 09.

## GHCR

Push versioned Docker image to GitHub Container Registry.

Recommended tags:

```text
sha-<commit>
v1.0.0
latest     # optional and never used as sole deployment identity
```

## Traceability

Every release should map:

```text
Git commit
↕
Git tag
↕
Release artifact
↕
Docker image
```

## Changelog

Maintain human-readable release notes.

## Exit Criteria

- [ ] Version tag creates release workflow.
- [ ] Release artifact generated.
- [ ] Docker image pushed to GHCR.
- [ ] Commit/tag/image relationship traceable.
- [ ] Release notes generated/maintained.

---

# PHASE 10 — Kubernetes Local Learning with kind

## Objective

Run the same production-oriented container image through Kubernetes locally.

## Rule

Do not redesign application business logic for Kubernetes. Fase ini mandiri dan tidak menjadi dependency Phase 11–13. Pasang/verifikasi kubectl/kind saat memulai fase ini.

## 10.1 Create Cluster

```bash
kind create cluster --name cim
kubectl cluster-info --context kind-cim
kubectl get nodes
```

## 10.2 Namespace

```text
cim-local
```

## 10.3 Image

Options:

- Load locally built image into kind.
- Pull a versioned image from registry when configured.

Learn:

- image identity.
- image pull policy.
- registry access.

## 10.4 ConfigMap & Secret

ConfigMap:

- non-sensitive app config.

Secret:

- app key.
- DB password.
- other local K8s secrets.

No production secret in repository.

## 10.5 MySQL StatefulSet

Create:

```text
StatefulSet mysql
Service mysql
PVC mysql-data
```

Learn:

- stateful workload.
- PVC.
- stable service identity.

## 10.6 Laravel Deployment

Create:

```text
Deployment cim-web (Nginx + PHP-FPM containers)
Service cim-web (HTTP port 80 → Nginx)
```

Initial replica:

```text
1
```

Add:

- readiness probe pada HTTP app yang siap menerima request.
- liveness tidak menggunakan outage DB sebagai alasan restart loop.
- liveness probe.
- resource requests/limits baseline.

## 10.7 Migration Job

Create:

```text
Job migrate
```

Run migration separately from normal web startup.

## 10.8 Access

Start with:

```bash
kubectl port-forward service/cim-web 8080:80 -n cim-local
```

Ingress only after Service fundamentals are understood.

## 10.9 Debugging Exercises

Use:

```bash
kubectl get pods
kubectl describe pod ...
kubectl logs ...
kubectl exec ...
kubectl get events
```

Intentionally break:

- DB password.
- service name.
- readiness endpoint.

## 10.10 Scaling Exercise

Scale stateless web deployment.

Observe:

- multiple pods.
- service routing.
- session/storage implications.

## 10.11 Rolling Update / Rollback

Deploy another image version.

Observe:

```bash
kubectl rollout status deployment/cim-web -n cim-local
kubectl rollout history deployment/cim-web -n cim-local
kubectl rollout undo deployment/cim-web -n cim-local
```

## Exit Criteria

- [ ] kind cluster Ready.
- [ ] Namespace used.
- [ ] ConfigMap works.
- [ ] Secret works.
- [ ] MySQL StatefulSet Ready.
- [ ] MySQL data persists.
- [ ] Laravel Deployment Ready.
- [ ] Service works.
- [ ] Migration Job succeeds.
- [ ] readiness/liveness work.
- [ ] logging/debugging understood.
- [ ] scaling demonstrated.
- [ ] rolling update demonstrated.
- [ ] rollback demonstrated.

---

# PHASE 11 — Staging + Continuous Delivery

## Objective

Create a safe environment between local development and production. Dependensi Phase 9; Phase 10 tidak memblokir. Gunakan runtime/web-server/storage behavior semirip hosting produksi yang sudah diverifikasi.

## Hosting

Recommended:

```text
staging.<domain>
```

Staging must use:

- separate database.
- separate storage.
- separate app key.
- separate email configuration.
- visible CMS `STAGING` environment badge.
- access restriction + noindex dan mail sink/recipient allowlist.

## GitHub Environment

Create:

```text
staging
```

Store staging secrets there.

## CD Flow

Approved artifact → backup → upload/checksum → bind env/storage → PHP 8.3 platform check → migrate → runtime cache/preflight → activate → post-activation smoke → monitor/rollback. Detail authoritative: dokumen 09.

## Deployment Transport

Choose based on actual hosting capability:

1. SSH/rsync/SFTP automated deployment preferred.
2. SFTP artifact upload fallback.
3. Manual deployment only as documented temporary fallback.

## Staging Smoke Tests

- Home opens.
- Admin login works.
- DB works.
- Public Product works.
- Inquiry works.
- Application works.
- media served.
- private CV protected.

## Exit Criteria

- [ ] Staging isolated from production.
- [ ] CD pipeline deploys staging.
- [ ] Secrets separated.
- [ ] Migration step works.
- [ ] Smoke test documented/automated where practical.
- [ ] Environment badge clearly shows STAGING.
- [ ] Restore drill membuktikan RPO/RTO target dan data/file consistency.
- [ ] Rollback aplikasi diuji dengan schema compatible.
- [ ] Artifact checksum disimpan untuk promosi produksi.

---

# PHASE 12 — Security, Performance & QA

## Objective

Turn staging build into a production release candidate.

## Functional QA

Test:

- Auth.
- RBAC.
- Products.
- E-Catalog.
- Activity.
- Career.
- Inquiry.
- Application.
- Media.
- Settings.
- Audit.

## Security QA

- authorization bypass.
- CSRF baseline.
- invalid file upload.
- private CV direct access.
- path traversal.
- rate limiting.
- safe errors.
- dependency audits.
- no exposed `.env`.

## Performance QA

- DB query inspection.
- N+1 checks.
- pagination.
- image optimization.
- production asset size.
- unnecessary JS review.
- caching only where justified.

## Accessibility QA

- Keyboard.
- focus.
- labels.
- dialog behavior.
- contrast.
- reduced motion.
- responsive zoom/readability.

## Responsive QA

Minimum representative widths:

```text
320
390
430
768/820
1366
1440
wide desktop
```

## Docker QA

- restart.
- rebuild.
- DB persistence.
- storage persistence.
- fresh setup.

## CI/CD QA

- failed CI blocks release.
- release artifact traceable.
- staging deployment repeatable.

## Exit Criteria

- [ ] No critical functional defects.
- [ ] No known critical security defect.
- [ ] Accessibility baseline accepted.
- [ ] Responsive QA accepted.
- [ ] CI/CD accepted.
- [ ] Acceptance non-learning yang berlaku pada dokumen 08 memiliki evidence PASS.
- [ ] Hosting/backup/queue/retention gaps telah diselesaikan.
- [ ] Release candidate approved; tidak menganggap dokumentasi sebagai hasil runtime QA.

---

# PHASE 13 — Production Deployment + Rollback

## Objective

Deploy release candidate to existing cPanel/shared hosting safely.

## 13.1 Hosting Capability Gate

Recheck bukti yang dikumpulkan sejak Phase 0; ini bukan pemeriksaan hosting pertama. PHP maksimum dikonfirmasi 8.3, namun PHP aktif/patch/extensions dan capability lainnya harus dibuktikan:

- supported PHP version.
- required PHP extensions.
- Composer availability or build strategy.
- MySQL.
- SMTP.
- persistent writable storage.
- cron capability.
- HTTPS.
- domain/subdomain management.
- SSH availability.
- symlink support.
- SFTP.

## 13.2 Production Environment

Use dedicated production values for:

- `APP_ENV`.
- `APP_KEY`.
- DB.
- mail.
- URL.
- storage path.
- logging.

## 13.3 Deployment Flow

Gunakan artifact staging yang sama: approval → backup → upload/checksum → bind env/storage → PHP 8.3 platform check → migrate → runtime cache/preflight → activate → post-activation smoke → monitor/rollback. Prosedur provider/fallback authoritative pada dokumen 09. Tidak rebuild dependency/assets dan tidak regenerate APP_KEY.

## 13.4 Rollback

Maintain:

- previous application release.
- DB backup.
- release identifier.
- documented rollback command/process.

Prefer backward-compatible migration patterns where practical.

## 13.5 Post-Deploy Test

- Home.
- Product.
- Activity.
- Career.
- Inquiry.
- Application.
- Admin login.
- file upload.
- private file download authorization.
- email.

## Exit Criteria

- [ ] Backup exists.
- [ ] Release deployed from traceable artifact.
- [ ] DB migration succeeds.
- [ ] Smoke test succeeds.
- [ ] Production secrets not exposed.
- [ ] Rollback path verified.
- [ ] Domain cutover accepted.

---

# 2. Daily Development Workflow

Recommended normal workflow:

```text
Select task
   ↓
Plan
   ↓
Specify acceptance criteria
   ↓
Create feature branch
   ↓
Build in Docker environment
   ↓
Test locally
   ↓
Commit
   ↓
Push
   ↓
Pull Request
   ↓
CI
   ↓
Review
   ↓
Merge to main
```

Normal local command:

```bash
docker compose up
```

Use Kubernetes only when specifically learning/testing orchestration.

---

# 3. Definition of Local Done

A feature is locally done when:

1. Requirement is clear.
2. Acceptance criteria are explicit.
3. Implementation follows project architecture.
4. Authorization and validation are included.
5. Appropriate automated tests exist.
6. Tests pass.
7. Lint/static analysis passes locally where practical.
8. UI has loading/error/empty states where relevant.
9. Documentation is updated if behavior/config changed.
10. No secrets or temporary debugging code remain.

---

# 4. Definition of Project Done

Project is considered complete when:

1. Fresh Docker environment can run the full project.
2. CMS workflows persist in MySQL.
3. Public site reads real CMS data.
4. Storage and private CV behavior are correct.
5. Auth and RBAC work.
6. Contact and Career workflows work end-to-end.
7. Approved design works responsively and accessibly.
8. Automated tests protect critical behavior.
9. CI quality gate runs on Pull Requests.
10. Versioned release artifacts are generated.
11. Container images versioned tersedia; kind completion dilacak sebagai learning outcome terpisah dan tidak memblokir production.
12. Staging deployment is repeatable.
13. Production deployment is documented and traceable.
14. Rollback is documented and tested.

# 5. Definition of Learning Done

Developer dapat menjalankan dan menjelaskan kind namespace/config/secret, HTTP Nginx→FPM, DB StatefulSet/PVC, migration Job, probes, logs, scaling constraints, rollout dan rollback. Evidence K8S-01/02 dicatat terpisah dari penerimaan aplikasi cPanel.
