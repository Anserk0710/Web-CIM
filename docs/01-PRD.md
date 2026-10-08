# Product Requirements Document (PRD)

**Versi dokumen:** 4.0 · **Diperbarui:** 5 Oktober 2026  
**Status:** spesifikasi implementasi; belum ada bukti aplikasi berjalan dalam paket ini.  
**Kontrak runtime:** Laravel 13.x + PHP **8.3.x**. PHP maksimum cPanel 8.3 dikonfirmasi pengguna; kemampuan hosting lain mengikuti [matriks verifikasi](00-Decisions-and-Hosting-Matrix.md).  
**Kontrak domain:** [Data Model & Business Rules](06-Data-Model-and-Business-Rules.md). **Pengujian:** [Acceptance](08-Acceptance-and-Traceability.md).
## PT Cahya Intan Medika — Company Profile + CMS
### Laravel + Docker + CI/CD + Kubernetes Learning Baseline

**Document Status:** Development Baseline v4  
**Primary Delivery Priority:** Full system works locally and repeatably with Docker before production deployment.  
**Production Target:** Existing shared hosting/cPanel.  
**Application Model:** One Laravel application containing public website, CMS/Admin, server-side business logic, authentication, storage, and integrations.  
**Public Rendering:** Laravel Blade + Tailwind CSS + lightweight JavaScript/Alpine where needed.  
**CMS Rendering:** React + TypeScript + Inertia.js + Tailwind CSS + shadcn/ui.  
**Database:** MySQL + Eloquent ORM.  
**Primary Local Runtime:** Docker + Docker Compose.  
**CI/CD:** GitHub Actions + GitHub Environments + release artifacts + GHCR.  
**Kubernetes Learning Runtime:** kind (Kubernetes IN Docker).  
**Production UI Baseline:** Clean Flat Design + Bento Grid.  
**Approved Palette:** `#03045e`, `#0077b6`, `#00b4d8`, `#90e0ef`, `#caf0f8`.

---

# 1. Executive Summary

PT Cahya Intan Medika membutuhkan rebuild website company profile menjadi satu platform terintegrasi yang mencakup public company profile dan CMS operasional.

Platform harus mencakup:

- Public Company Profile.
- Product dan Product Detail.
- E-Catalog.
- Activity/News.
- Career dan Career Detail.
- Contact dan Inquiry.
- CMS/Admin.
- Authentication.
- User, Role, Permission.
- Media Library.
- Career Applicant Management.
- Contact Inquiry Management.
- Audit Log.
- Site Settings.
- SEO.
- Private document handling.
- Automated testing.
- CI/CD pipeline.
- Docker-based local development.
- Local Kubernetes learning environment.

Prinsip delivery utama:

> Semua fitur harus terlebih dahulu berjalan penuh, testable, dan repeatable di local Docker environment sebelum deployment ke staging atau production diprioritaskan.

Kubernetes digunakan sebagai **learning and orchestration validation layer**, bukan sebagai dependency harian untuk menulis fitur.

CI/CD digunakan untuk memastikan bahwa perubahan source code melewati quality gate sebelum menjadi release.

---

# 2. Product Vision

Membangun digital platform yang menampilkan PT Cahya Intan Medika sebagai:

- Partner alat kesehatan dan diagnostik yang kredibel.
- Penyedia Product yang mudah dicari dan dipahami.
- Partner procurement melalui E-Catalog.
- Perusahaan dengan technical capability dan after-sales support.
- Organisasi yang aktif melalui Activity.
- Tempat kerja profesional melalui Career.
- Organisasi yang dapat mengelola konten tanpa perubahan source code rutin.

Platform harus:

- Professional.
- Responsive.
- Fast.
- Accessible.
- Maintainable.
- Secure.
- Search-engine friendly.
- Container-ready.
- Testable.
- Deployable melalui pipeline yang terdokumentasi.
- Portable antar environment.
- Cocok menjadi media pembelajaran Docker, CI/CD, dan Kubernetes.

---

# 3. Delivery Philosophy

## 3.1 Local first, kompatibilitas diverifikasi sejak awal

Bangun aplikasi penuh di Docker terlebih dahulu. Phase 0 memeriksa capability hosting dan versi; pemeriksaan ini tidak berarti melakukan deployment lebih awal. PHP target 8.3.x, Laravel 13.x. MySQL 8.4 LTS adalah target awal lokal, bukan klaim versi database hosting.

Alur produk: local Docker → tests dan CI sejak fondasi → artifact versioned → staging → production. kind adalah jalur pembelajaran mandiri setelah image siap; keberhasilannya tidak menjadi syarat staging/produksi cPanel.

## 3.2 Tiga definisi selesai

| Target | Bukti |
|---|---|
| Local application | Semua primary workflows end-to-end, persistent data, private CV, tests dan setup repeatable |
| Production application | Staging acceptance, hosting gate, artifact yang sama, backup/restore, post-activation smoke dan rollback |
| Learning | Latihan Docker/CI/CD/kind selesai dengan penjelasan dan evidence tersendiri |

Seluruh modul inti v3 tetap menjadi release scope. Local daily workflow tidak bergantung pada Kubernetes. CI dasar dibuat Phase 1, suite bertambah bersama fitur; tidak menunggu aplikasi lengkap.

## 3.3 Quality dan release

PR memeriksa formatting, static analysis, types/lint, test yang tersedia dan build. Baseline penuh menambah E2E critical, container build, audit dependencies, required checks. Produksi melalui release/approval yang terverifikasi tersedia pada repository; tidak otomatis setiap commit.

Fresh onboarding memiliki urutan bootstrap dependency, environment, key lokal, migration, seed, storage link/route, dan Vite yang terdokumentasi. `docker compose up --build` adalah target kenyamanan setelah bootstrap terpenuhi, bukan jaminan tanpa file konfigurasi.

---

# 4. User Personas

## 4.1 Public Visitor

Mencakup:

- Hospital staff.
- Laboratory staff.
- Clinic staff.
- Procurement staff.
- Partner.
- Job applicant.
- General visitor.

Tujuan utama:

- Mengetahui profil perusahaan.
- Menemukan Product.
- Melihat E-Catalog.
- Membaca Activity.
- Menemukan vacancy.
- Mengirim inquiry.
- Mengajukan lamaran.

## 4.2 Super Admin

Memiliki seluruh permission dan dapat mengelola user, role, settings, audit, dan seluruh content module.

## 4.3 Admin

Mengelola operasional CMS sesuai permission.

## 4.4 Editor

Berfokus pada content-oriented module.

## 4.5 HR Role

Berfokus pada Career dan Applicant.

## 4.6 Product Role

Berfokus pada Product dan E-Catalog.

## 4.7 Read Only

Memiliki akses baca sesuai scope yang diberikan.

RBAC harus permission-based agar role dapat disusun dinamis.

---

# 5. Public Website Scope

Routes utama:

```text
/
/company
/products
/products/{slug}
/e-catalog
/activity
/activity/{slug}
/career
/career/{slug}
/contact
/privacy
```

System routes:

```text
/sitemap.xml
/robots.txt
/404
/error
```

Public pages menggunakan Blade/server-rendered HTML agar SEO dan delivery di cPanel tetap sederhana.

Interaksi client-side menggunakan JavaScript/Alpine hanya bila dibutuhkan.

---

# 6. CMS Scope

```text
/admin/login
/admin/forgot-password
/admin/reset-password/{token}

/admin
/admin/products
/admin/e-catalog
/admin/activity
/admin/career
/admin/applications
/admin/inquiries
/admin/media
/admin/taxonomies
/admin/pages
/admin/users
/admin/roles
/admin/settings
/admin/audit
/admin/account
```

CMS menggunakan React + TypeScript + Inertia.js.

Tidak ada primary module yang boleh berhenti sebagai mock sebelum local acceptance.

---

# 7. Technology Stack

## 7.1 Backend

- Laravel.
- PHP **8.3.x**; Laravel **13.x**, dengan dependency lock diuji pada PHP 8.3.
- Node.js **24 LTS** untuk build. MySQL target awal **8.4 LTS**, engine/versi produksi perlu verifikasi sebelum schema stabil.
- Eloquent ORM.
- Laravel Form Request / Validator.
- Service/Action classes untuk business logic yang kompleks.
- Laravel Policies dan Gates.
- Session-based authentication.
- Laravel Mail.
- Laravel Filesystem.
- Laravel Queue.
- Laravel Scheduler.

## 7.2 Public Frontend

- Blade.
- Tailwind CSS.
- Vite.
- Alpine.js atau vanilla JavaScript seperlunya.

## 7.3 CMS Frontend

- React.
- TypeScript strict mode.
- Inertia.js.
- Tailwind CSS.
- shadcn/ui.
- Base UI hanya jika ada komponen yang memang membutuhkannya.

## 7.4 Database

- MySQL.
- Eloquent ORM.
- Laravel migrations.
- Laravel seeders/factories.

## 7.5 Development Services

- Docker.
- Docker Compose.
- Nginx.
- PHP-FPM.
- MySQL.
- Mailpit.
- Optional Adminer.

## 7.6 Quality Tooling

- Pest/PHPUnit.
- Laravel Pint.
- PHPStan + Larastan.
- ESLint.
- Prettier.
- TypeScript compiler.
- React Testing Library/Vitest untuk component logic bila dibutuhkan.
- Playwright untuk end-to-end/browser test.

## 7.7 Delivery Tooling

- Git.
- GitHub.
- GitHub Actions.
- GitHub Environments.
- GitHub Container Registry (GHCR).
- Semantic Versioning.

---

# 8. Authentication Requirements

Required:

- Login.
- Logout.
- Database-backed session.
- Forgot password.
- Reset password.
- Change password.
- Public registration disabled.
- User activate/deactivate.
- Server-side route protection.
- Rate limiting.
- Audit sensitive authentication actions.

Local reset-password email harus dapat diperiksa melalui Mailpit.

---

# 9. RBAC Requirements

Daftar permission dan template role authoritative ada pada [dokumen 06 bagian 3](06-Data-Model-and-Business-Rules.md). Pola `<domain>.<action>` tetap digunakan. Read, download, export, publish, archive/close, role assignment dan pengelolaan user dipisahkan.

- Server memeriksa list/detail/download/export selain mutation; props/dashboard counts mengikuti scope izin.
- `application.read` + `application.download` untuk CV; read + export untuk CSV.
- `product.update` tidak memberi kewenangan menerbitkan melalui field status terselubung.
- Last active Super Admin dilindungi dari deaktivasi/demotion, termasuk operasi bersamaan.
- Dynamic role tidak boleh menaikkan hak pemegangnya sendiri; assignment Super Admin dibatasi.
- Deaktivasi akun mencabut sesi/remember token; perubahan permission efektif pada request berikutnya.
- Role baseline: Super Admin, Admin, Editor, HR, Product, Read Only. Permission aktual adalah sumber otorisasi; label role bukan pengganti policy.

Acceptance: RBAC-01–04, AUTH-02, CV-01 dan EXPORT-01–02 pada dokumen 08.

---

# 10. Product Module

CMS requirements:

- CRUD.
- Draft/Publish/Archive.
- Search.
- Filter.
- Sort.
- Category.
- Brand.
- Type.
- Featured.
- Featured image.
- Gallery.
- Brochure.
- Description.
- Product highlights.
- Specification groups.
- Related products.
- E-Catalog relation.
- Slug.
- SEO fields.
- Preview yang memerlukan login dan read permission.
- WORKING/PUBLISHED revision: simpan draft tidak mengubah konten live; publish atomik memerlukan izin terpisah.
- Optimistic locking untuk mencegah edit saling menimpa.

Public requirements:

- Search.
- Filter.
- Product grid.
- Product detail.
- Gallery.
- Key specs.
- Full specs.
- Related products.
- Brochure download.
- E-Catalog state.
- Product inquiry CTA.

---

# 11. E-Catalog Module

E-Catalog terhubung ke Product: satu Product dapat memiliki beberapa E-Catalog, satu E-Catalog menunjuk satu Product. Kode katalog unik.

Visibility memerlukan status Published, Product Published, serta masa berlaku aktif. Availability terpisah dari publish state. Edit data live memerlukan update + publish; draft dapat diedit Editor. Aturan waktu dan state lengkap ada pada dokumen 06.

Fields minimum:

- Product reference.
- Catalog code.
- Procurement title.
- Procurement category.
- Description/notes.
- Unit/package.
- Availability.
- Reference URL.
- Effective date.
- Expiry date.
- Status.
- Sort order.
- SEO.

Public requirements:

- Search/filter.
- Product linkage.
- Procurement CTA.

---

# 12. Activity Module

CMS:

- CRUD.
- Draft/Publish/Archive.
- Category.
- Featured media.
- Gallery.
- Structured content blocks.
- WORKING/PUBLISHED revision sama dengan Product; schema blok dan sanitasi server mengikuti dokumen 06.
- Reorder block.
- Related products.
- SEO.

Supported blocks:

- Paragraph.
- Heading.
- Image.
- Gallery.
- Quote.
- Video.
- CTA.
- Related Product.
- Divider.

---

# 13. Career Module

CMS:

- CRUD.
- Draft/Open/Close/Archive.
- Listing hanya lowongan aktif; CLOSED dapat mempertahankan halaman informasi tanpa form/JobPosting aktif.
- Batas waktu diverifikasi saat submit di server, tidak bergantung pada cron.
- Tidak menerima lamaran umum pada release awal.
- Department.
- Location.
- Employment type.
- Responsibilities.
- Requirements.
- Closing date.
- SEO.

Public:

- Employer brand.
- Vacancy list.
- Vacancy detail.
- Application form.

---

# 14. Career Application

Stored data:

- Vacancy.
- Full name.
- Email.
- Phone.
- CV.
- Consent.
- Optional introduction.
- Optional portfolio/LinkedIn.

Statuses:

```text
NEW
REVIEWING
SHORTLISTED
INTERVIEW
REJECTED
HIRED
```

CV requirements:

- PDF saja, maksimum 5 MiB; server validation dan compensation file gagal wajib.
- Satu normalized email per vacancy; unique constraint dan penanganan submit bersamaan.
- Consent menyimpan versi pemberitahuan dan timestamp.
- Retensi kandidat REJECTED: 6 bulan kalender setelah keputusan akhir; HIRED mengikuti handover HR dalam dokumen 09.

- Private storage.
- Tidak memiliki direct public URL.
- Download hanya melalui authenticated + authorized controller/action.

CMS:

- Search/filter.
- Notes.
- Status updates.
- Secure CV download.
- CSV export dengan application.read + application.export; maksimal 5.000 baris/filter, mitigasi formula injection dan audit.

---

# 15. Contact Inquiry

Types:

- Product Inquiry.
- E-Catalog.
- Technical Support.
- Partnership.
- General.

Statuses:

```text
NEW
IN_PROGRESS
RESOLVED
SPAM
```

Requirements:

- DB persistence.
- Validation.
- Rate limiting.
- Honeypot/anti-spam baseline.
- Internal notes.
- CSV export dengan inquiry.read + inquiry.export; maksimal 5.000 baris/filter, mitigasi formula injection dan audit.
- Field wajib, relasi Product/E-Catalog, reopening dan retensi mengikuti dokumen 06.
- Kegagalan email tidak menghilangkan inquiry yang sudah tersimpan.

---

# 16. Media Library

Supported initial formats:

- JPG.
- JPEG.
- PNG.
- WebP.
- PDF.
- AVIF only after environment verification.

Security:

- MIME/signature validation.
- File-size limit.
- Randomized storage filename.
- Executable uploads forbidden.
- SVG disabled initially.
- Referenced asset deletion protection untuk draft/live/page/block dan operasi bersamaan.
- Replace membuat media baru dan mengganti referensi terpilih; CV tidak masuk Media Library publik.
- Gambar maksimal 5 MiB/25 megapixel, brochure PDF maksimal 10 MiB, CV PDF maksimal 5 MiB; lihat dokumen 06.

---

# 17. Site Settings and Editable Public Content

Settings global: company identity, founded year, contact/address/offices/hours/coverage/social, logo/favicon, SEO default dan footer. Form page terstruktur mengelola hero, capability bento, service lifecycle, metrics terverifikasi, values/milestones, featured Product/Activity dan Career branding.

Setiap section memiliki sumber data, field, permission, order dan empty behavior sesuai mapping dokumen 06 bagian 9. Section opsional kosong disembunyikan. Kontak utama, identitas perusahaan, dan privacy wajib diverifikasi sebelum go-live. Jangan mengisi fakta perusahaan/technical specs tanpa sumber.

Page update membutuhkan page.update; settings update membutuhkan settings.update. Perubahan live langsung oleh pengguna berizin dengan confirmation dan audit; tidak menyediakan page builder arbitrary atau upload custom script.

---

# 18. Audit Log

Capture:

- Actor.
- Action.
- Entity.
- Entity ID.
- Changed fields where reasonable.
- Timestamp.
- Result.
- IP/user agent where reasonable.

Track at minimum:

- CRUD sensitive actions.
- Publish/archive.
- User/role changes.
- Settings changes.
- Application status.
- Inquiry status.
- Authentication-sensitive actions.
- CV download, CSV export, revision publish, access grant dan retention purge dengan metadata minimal.

---

# 19. Storage Model

Local model:

```text
storage/app/public/...
storage/app/private/...
```

Public files dapat dipublikasikan melalui Laravel public storage mechanism atau controlled media route sesuai kebutuhan.

Private files tidak boleh memiliki direct public URL.

Storage access harus melalui Laravel Filesystem abstraction agar mapping production dapat diganti tanpa mengubah business logic.

---

# 20. SEO Requirements

Required:

- Dynamic title/description.
- Canonical URL.
- Open Graph.
- Sitemap.
- robots.txt.
- Organization schema.
- Product schema.
- Article schema.
- JobPosting schema.
- Breadcrumb schema.
- Clean slugs.
- Indexability rules per content status.

Draft/unpublished content tidak dapat dibaca publik dan tidak masuk sitemap/schema. Preview memerlukan authentication/authorization. Noindex bukan pengganti access control.

Slug lama mempunyai redirect 301 hanya ke entity publik yang sama; CLOSED Career tidak memuat JobPosting aktif. Staging dibatasi akses dan noindex. Aturan detail SEO-01–03 ada pada dokumen 08.

---

# 21. UI / UX Baseline

Style:

```text
Clean Flat Design
+
Bento Grid
+
Scroll Progress
+
Subtle Parallax
+
Pin / Sticky Scroll
+
Horizontal Product Rail
```

Palette:

```text
#03045e
#0077b6
#00b4d8
#90e0ef
#caf0f8
```

CMS menggunakan brand system yang sama, tetapi lebih functional dan minim decorative motion.

---

# 22. Non-Functional Requirements

## 22.1 Security

- Server-side authorization.
- CSRF protection.
- Input validation.
- Secure sessions.
- Rate limiting.
- Upload validation.
- Private file protection.
- No secrets in repository.
- Safe error messages.
- Audit sensitive operations.
- Dependency audit baseline.

## 22.2 Performance

- Production asset build/minification.
- Optimized image delivery where practical.
- Avoid unnecessary client JavaScript on public pages.
- Database indexes for high-frequency filters/search.
- Pagination for large data sets.
- Cache only after correctness and invalidation strategy are clear.

## 22.3 Accessibility

Target: WCAG 2.2 AA baseline.

- Keyboard navigation.
- Visible focus.
- Proper labels.
- Semantic landmarks.
- Accessible dialogs.
- Alternative text.
- Reduced motion support.
- Adequate contrast.

## 22.4 Maintainability

- Clear domain boundaries.
- DRY without over-abstraction.
- Business rules outside controllers when complexity warrants.
- Small reusable UI components.
- Explicit permission checks.
- Automated tests for critical flows.

## 22.5 Observability

- Laravel logs.
- Docker logs.
- CI logs.
- Kubernetes logs during learning.
- Health endpoint for orchestration checks.
- Error tracking may be added later.

## 22.6 Deployability

Every release must be reproducible from source control and documented build steps. Staging dan produksi memakai artifact checksum yang sama; PHP 8.3 platform requirements wajib dipenuhi.

Production release must have rollback procedure. Target backup harian/retensi 30 hari, RPO 24 jam dan RTO 4 jam harus dibuktikan lewat rehearsal, sesuai dokumen 09.

---

# 23. Automated Testing Requirements

Critical backend test coverage must include:

- Authentication.
- Authorization bypass prevention.
- Product lifecycle.
- E-Catalog relations.
- Career application.
- Contact inquiry.
- Private CV access.
- Media validation.
- Settings permissions.
- Audit event generation.

Browser/E2E scenarios should cover at least:

- Admin login.
- Product create/publish.
- Public product visibility.
- Contact form submission.
- Career application submission.
- Restricted route access.

---

# 24. CI Requirements

CI dasar sejak Phase 1; lint/types/build dan test yang sudah tersedia dijalankan pada PR. PHP 8.3 dan engine DB target dipakai pada suite integrasi. Baseline lengkap Phase 8:

```text
Composer validate/install → Pint → PHPStan/Larastan → Backend tests
npm ci → TypeScript → ESLint → Relevant component tests → Vite build
E2E smoke → Production container build → Dependency/security audit
```

E2E critical lengkap wajib sebelum release acceptance. Required checks harus benar-benar dikonfigurasi sesuai kemampuan repository. Detail frekuensi, scope dan bukti: dokumen 08 bagian 3.

---

# 25. Release & CD Requirements

Feature branch → PR + CI → main → protected/version tag → build artifact dan image → deploy artifact ke staging → E2E/smoke/operational acceptance → approval → deploy **artifact yang sama** ke produksi.

Artifact membundel dependency produksi dan Vite assets dari lockfile/commit yang sama. Tidak berisi .env, upload pengguna, node_modules, runtime cache atau credentials. Secret environment tidak berubah setiap release.

Urutan deployment authoritative di [dokumen 09](09-Operations-and-Release-Runbook.md): backup → upload/verify artifact → bind env/storage → platform check → migrate → runtime caches/preflight → activate → post-activation smoke → monitor/rollback. Node server, Docker dan Kubernetes tidak diperlukan pada produksi cPanel.

---

# 26. Kubernetes Learning Requirements

Resources minimum:

```text
Namespace
ConfigMap
Secret
Deployment / web
Service / web
StatefulSet / mysql
Service / mysql
PersistentVolumeClaim / mysql
Job / migrate
```

Storage/media PVC dapat ditambahkan untuk learning, dengan catatan bahwa shared writable filesystem pada multi-replica production memerlukan desain khusus.

Kubernetes bukan target production pada shared hosting. Learning gate berdiri sendiri; staging/produksi tidak menunggu Phase 10. Runtime HTTP wajib jelas: Nginx + PHP-FPM, dengan asset release yang sama.

---

# 27. Environment Model

## Local

- Docker Compose.
- Local MySQL.
- Mailpit.
- Local storage.

## CI

- Ephemeral test environment.
- Dedicated test database/service.
- No production secrets.

## K8s Local

- kind.
- Kubernetes resources.
- Non-production secrets.

## Staging

- Separate database.
- Separate storage.
- Separate app key/secrets.
- Environment badge visible in CMS.

## Production

- cPanel/PHP.
- MySQL.
- SMTP.
- Persistent filesystem.
- HTTPS.
- Production secrets.

---

# 28. Content Migration

Source content dari website existing dipindahkan secara terkontrol.

Migration includes:

- Company profile.
- Vision/mission.
- Contact.
- Product categories.
- Valid Product content.
- Product media where usable.
- E-Catalog relationships.
- Activity.
- Existing gallery/company media.
- Career baseline copy.

Rules:

- Do not invent technical Product specs.
- Normalize inconsistent structures.
- Validate contact information before final production cutover.
- Preserve provenance of migrated assets where useful.

---

# 29. Local Acceptance Criteria

Local application gate (CI sudah berjalan sejak fondasi; bukan prasyarat untuk memulai CI):

- [ ] `docker compose up --build` succeeds.
- [ ] Public website opens locally.
- [ ] CMS opens locally.
- [ ] MySQL persists after container restart.
- [ ] Mailpit receives reset-password email.
- [ ] Auth works.
- [ ] RBAC works server-side.
- [ ] Product CRUD works.
- [ ] E-Catalog CRUD works.
- [ ] Activity CRUD works.
- [ ] Career CRUD works.
- [ ] Inquiry works.
- [ ] Application works.
- [ ] CV remains private.
- [ ] Media persists.
- [ ] Public website uses DB data.
- [ ] Core automated tests pass.

---

# 30. CI Acceptance Criteria

- [ ] Pull Request triggers CI automatically.
- [ ] Formatting check passes.
- [ ] Static analysis passes.
- [ ] TypeScript check passes.
- [ ] ESLint passes.
- [ ] Backend tests pass.
- [ ] Frontend tests pass where applicable.
- [ ] Production asset build succeeds.
- [ ] Docker image builds successfully.
- [ ] Required failed checks block merge.
- [ ] Secrets are absent from committed files.

---

# 31. Kubernetes Acceptance Criteria — Independent Learning Gate

- [ ] kind cluster can be created.
- [ ] Namespace exists.
- [ ] Web pod becomes Ready.
- [ ] MySQL StatefulSet becomes Ready.
- [ ] Service discovery works.
- [ ] Migration Job succeeds.
- [ ] App connects to MySQL.
- [ ] Persistent DB data survives pod recreation.
- [ ] Readiness/liveness probes work.
- [ ] Logs/describe/events can diagnose a failure.
- [ ] Rolling update demonstrated.
- [ ] Rollback demonstrated.
- [ ] Web replica count can be changed for learning.

---

# 32. Production Acceptance Criteria

- [ ] Hosting capability gate passed.
- [ ] PHP web dan CLI **8.3.x**, patch serta extensions aktual dikonfirmasi.
- [ ] Engine/versi database dan cara menjalankan migration dikonfirmasi.
- [ ] Staging dan produksi memakai checksum artifact yang sama.
- [ ] Required PHP extensions confirmed.
- [ ] MySQL available.
- [ ] SMTP available.
- [ ] Persistent writable storage available.
- [ ] HTTPS active.
- [ ] Backup DB/upload completed; restore rehearsal memenuhi target atau gap diselesaikan.
- [ ] Production release artifact generated from CI.
- [ ] Migration executed successfully.
- [ ] Smoke test passes.
- [ ] Rollback path verified.
- [ ] Final content migration validated.
- [ ] Domain cutover completed.

---

# 33. Out of Scope for Initial Release

Unless explicitly added later:

- Public user registration.
- Customer portal.
- E-commerce checkout/payment.
- Real-time chat.
- Native mobile application.
- Production Kubernetes cluster.
- Microservices architecture.
- Elasticsearch/OpenSearch.
- Redis as mandatory infrastructure dependency.

---

# 34. Final Product Definition

Project dianggap lengkap secara local ketika developer baru yang memiliki Git dan Docker dapat:

1. Clone/extract repository.
2. Configure documented environment variables.
3. Run documented Docker commands.
4. Obtain the same Laravel + MySQL + Mailpit application environment.
5. Execute all critical CMS/public workflows.
6. Run automated tests.
7. Build production assets and image.
8. Understand the release path from Git → CI → staging → production.

Kubernetes completion adalah requirement untuk learning track, bukan prerequisite setiap code change.

# 35. Kontrak rinci dan traceability

- [00 — Keputusan & Hosting](00-Decisions-and-Hosting-Matrix.md): accepted decisions, fakta dan hal yang belum diverifikasi.
- [06 — Data & Business Rules](06-Data-Model-and-Business-Rules.md): schema, status, revision, permissions, field validation dan mapping CMS.
- [08 — Acceptance](08-Acceptance-and-Traceability.md): ID requirement/test per fase, status seluruh runtime test masih NOT_RUN.
- [09 — Operations](09-Operations-and-Release-Runbook.md): deployment, backup, rollback, queue, retensi dan cutover.

# 36. Route dan resource contract tambahan

- `/admin/taxonomies`: kategori Product/Activity, Brand, Type; taxonomy.read/manage.
- `/admin/pages`: form halaman terstruktur; page.read/update.
- `/admin/applications/{application}/cv`: authorized download, bukan direct file path.
- Preview Product/Activity berada pada route admin authenticated; tidak menggunakan slug publik untuk membuka draft.
- Public form POST memakai web middleware/CSRF/rate limit dan validator; status response web/Inertia tidak dibuat seolah REST API terpisah.
- 404/error adalah HTTP status/error views; jangan mengembalikan HTTP 200 untuk resource tidak ditemukan.
