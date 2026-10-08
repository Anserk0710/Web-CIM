# AGENTS.md

**Versi dokumen:** 4.0 Â· **Diperbarui:** 5 Oktober 2026  
**Status:** spesifikasi implementasi; belum ada bukti aplikasi berjalan dalam paket ini.  
**Kontrak runtime:** Laravel 13.x + PHP **8.3.x**. PHP maksimum cPanel 8.3 dikonfirmasi pengguna; kemampuan hosting lain mengikuti [matriks verifikasi](docs/00-Decisions-and-Hosting-Matrix.md).  
**Kontrak domain:** [Data Model & Business Rules](docs/06-Data-Model-and-Business-Rules.md). **Pengujian:** [Acceptance](docs/08-Acceptance-and-Traceability.md).
## PT Cahya Intan Medika â€” Engineering Agent Operating Guide
### Workflow: Plan â†’ Specify â†’ Build â†’ Test

This document defines how AI coding agents and human contributors should reason about, implement, validate, and document changes in this repository.

The goal is not merely to generate code. The goal is to produce changes that are:

- Correct.
- Minimal.
- Maintainable.
- Secure.
- Testable.
- Compatible with Docker-based local development.
- Compatible with CI/CD.
- Compatible with the current cPanel production strategy.
- Consistent with the project's UI and architecture documents.

---

# 1. Source of Truth and Conflict Resolution

Explicit user/task instructions adalah prioritas tertinggi. Untuk keputusan proyek:

| Jenis keputusan | Acuan |
|---|---|
| Scope/perilaku produk | PRD + keputusan yang disetujui di dokumen 00 |
| Status, field, relation, authorization business rules | Dokumen 06 |
| Cara teknis/runtime/storage/deploy | Architecture + dokumen 09 |
| Interaksi dan visual | UI + Design System |
| Urutan implementasi | Roadmap |
| Bukti penerimaan | Dokumen 08 |
| Proses kontribusi | AGENTS.md |

AGENTS mengatur cara bekerja, bukan mengubah scope/status/retensi yang telah diputuskan. Bila dua kontrak berbeda, catat konflik pada Plan/Specify, gunakan keputusan eksplisit terbaru dan selaraskan dokumen terdampak. Jangan menjadikan urutan file sebagai alasan mengarang aturan bisnis. Existing tested behavior yang intentional diperiksa sebelum editing.

---

# 2. Core Architecture Rules

The project is a **Laravel modular monolith**.

Keep these boundaries:

```text
Backend / Business Logic
â†’ Laravel 13.x / PHP 8.3.x

Public Website
â†’ Blade + Tailwind + lightweight JS/Alpine

CMS
â†’ React + TypeScript + Inertia.js + Tailwind + shadcn/ui

Database
â†’ MySQL + Eloquent

Local Runtime
â†’ Docker Compose

CI/CD
â†’ GitHub Actions

Kubernetes
â†’ kind for local learning/validation

Production
â†’ cPanel/shared hosting
```

Do not introduce a second backend, separate SPA API, microservice, or Node production server unless the task explicitly requires an architectural change and the decision is documented.

---

# 3. Required Workflow

Every non-trivial task follows:

```text
PLAN
  â†“
SPECIFY
  â†“
BUILD
  â†“
TEST
```

Do not skip directly to implementation when the requested change affects more than a trivial isolated line.

For very small fixes, Plan and Specify may be concise, but their intent must still be satisfied.

---

# 4. PLAN

## Goal

Understand the requested change and identify the smallest safe implementation path.

## Required Questions

Before coding, determine:

- What problem is being solved?
- Which user/role is affected?
- Which routes/modules/files are likely affected?
- Does it change database schema?
- Does it change permissions?
- Does it change public behavior or CMS behavior?
- Does it affect storage, email, queue, SEO, or audit?
- Does it require migration/backfill?
- Does it affect Docker/CI/deployment?
- What existing behavior must remain backward-compatible?

## Required Repository Inspection

Inspect relevant code before editing.

Prefer this order:

1. Route.
2. Controller/page entry point.
3. Form Request/validation.
4. Policy/Gate/permission.
5. Service/action/domain logic.
6. Model/relationship.
7. Blade or React UI.
8. Existing tests.
9. Migration/config only if needed.

Do not rewrite a file before understanding how it participates in the request flow.

## Plan Output

A good Plan contains:

```text
Problem
Affected flow
Likely files
Data impact
Security impact
Compatibility concerns
Implementation sequence
Test strategy
```

Keep it proportional to the task.

---

# 5. SPECIFY

## Goal

Convert the request into observable acceptance criteria before implementation.

## Specification Format

Use concrete behavior.

Example:

```text
Given an Editor with product.update but without product.publish,
when the Editor opens the Product editor,
then the Product can be saved as draft,
but Publish is not available,
and a direct publish request returns 403.
```

Prefer measurable behavior over vague wording such as:

```text
Make permissions better.
```

## Specify These Areas When Relevant

### Functional

- Inputs.
- Outputs.
- State transitions.
- Sorting/filtering rules.
- Empty state.
- Error behavior.

### Authorization

- Who can read?
- Who can mutate?
- Which permission protects the operation?
- What happens on direct unauthorized request?

### Validation

- Required fields.
- type/format.
- length/range.
- file MIME/size.
- cross-field/domain rules.

### Data

- New column/table/relation?
- nullable/default?
- existing data compatibility?
- migration/backfill?

### UI

- Loading.
- Error.
- Empty.
- Success.
- Disabled.
- Mobile behavior.
- Accessibility.

### Audit

- Is the action sensitive enough to record?

### SEO

For public content changes:

- metadata.
- canonical.
- sitemap/indexability.
- structured data.

## Acceptance Criteria

Every meaningful task must have acceptance criteria that can be tested manually or automatically.

---

# 6. BUILD

## Goal

Implement the smallest complete solution that satisfies the specification.

## 6.1 General Coding Rules

- Prefer simple code.
- Follow DRY principles, but do not abstract prematurely.
- Favor framework conventions.
- Optimize query and runtime behavior where meaningful.
- Keep methods/components focused.
- Name things according to domain language.
- Avoid clever code when straightforward code is sufficient.
- Remove temporary debug statements.

## 6.2 Controllers

Controllers should:

- coordinate HTTP concerns.
- authorize.
- receive validated input.
- call action/service/model operation.
- return response.

Avoid large workflow logic in controllers.

## 6.3 Validation

Prefer Form Requests for non-trivial forms.

Never rely only on frontend validation.

Server validation is authoritative.

## 6.4 Authorization

Every sensitive server-side mutation must be authorized.

Client-side hiding of a button is not authorization.

Prefer:

- Policies for entity-specific behavior.
- Gates/permission helpers for capability checks.

## 6.5 Models

Models may contain:

- relationships.
- casts.
- useful query scopes.
- concise entity behavior.

Do not turn a model into a giant workflow service.

## 6.6 Services / Actions

Create an action/service when it improves clarity for:

- multi-model updates.
- DB transactions.
- storage + DB coordination.
- publish workflows.
- role/permission changes.
- external side effects.
- reusable domain operation.

Do not create service/repository layers around every simple Eloquent call.

## 6.7 Database Changes

All schema changes use migrations.

Before adding a field:

- Define nullability.
- Define default intentionally.
- Consider existing rows.
- Add index only for real query need.
- Define foreign-key behavior.

For production-facing changes, prefer backward-compatible migrations.

Avoid destructive column/table removal in the same release that first stops using it when rollback compatibility matters.

## 6.8 Transactions

Use a DB transaction when multiple DB changes must succeed/fail together.

Be careful when combining DB transaction with filesystem side effects.

Filesystem changes cannot automatically roll back with MySQL.

Design cleanup/compensation if needed.

## 6.9 Public Blade UI

Public pages should:

- render essential content server-side.
- use shared design tokens.
- keep JavaScript lightweight.
- preserve SEO.
- preserve accessibility.
- work without decorative motion.

Do not move public pages into React solely for visual interaction.

## 6.10 CMS React UI

CMS React pages should:

- use TypeScript.
- use Inertia for server interaction.
- compose shared UI primitives.
- keep domain logic outside low-level UI components.
- handle loading/error/empty/success where relevant.
- respect permission-driven UI.

Avoid unnecessary client state duplication when state already belongs on the server/Inertia props.

## 6.11 shadcn/ui

Use shadcn/ui for CMS primitives where it fits.

Adapt components to project tokens.

Do not install/use a component merely because it exists.

## 6.12 Styling

Use design tokens/Tailwind classes.

Avoid repeated raw hex values.

Avoid arbitrary spacing/radius values unless a design need is documented.

## 6.13 Accessibility

For every UI change check:

- semantic element.
- label/name.
- keyboard access.
- focus-visible.
- error association.
- reduced motion if animation changed.
- status not communicated by color only.

## 6.14 Storage

Use Laravel Filesystem.

Never write private CVs to public storage.

Validate uploads server-side.

Use randomized safe filenames.

## 6.15 Security

Never:

- trust client permission.
- expose `.env` values.
- return stack traces to public users.
- build SQL by concatenating untrusted strings.
- expose private storage path.
- log passwords/secrets.
- accept executable upload types.

## 6.16 Audit

Audit sensitive operations where required by PRD.

Do not audit secrets or full private file contents.

## 6.17 Performance

Prefer:

- eager loading when relations are used.
- server pagination for large datasets.
- indexes for real filters/search.
- selecting only needed data when materially useful.
- minimal public JS.

Avoid:

- N+1 queries.
- fetching entire tables for client filtering.
- premature caching.
- introducing Redis solely for perceived performance.

---

# 7. TEST

## Goal

Prove the implementation satisfies the specification and does not break adjacent behavior.

Testing must include the narrow feature and relevant regression paths.

---

# 8. Test Pyramid for This Project

```text
          Browser / E2E
        Critical user flows
              â–²
              â”‚
       Feature / Integration
       Most backend behavior
              â–²
              â”‚
             Unit
       Focused pure logic
```

Do not force everything into unit tests.

Laravel feature tests are preferred for many application behaviors because they cover routes, auth, validation, DB, and policies together.

---

# 9. Backend Test Rules

Use Pest/PHPUnit.

Test relevant combinations:

- success.
- validation failure.
- unauthorized.
- forbidden.
- not found.
- state transition.
- data persistence.

When fixing a bug, add a regression test when practical.

A bug fix is stronger when the test fails before the fix and passes after it.

---

# 10. Frontend Test Rules

Use component tests only for high-value behavior.

Good candidates:

- complex form state.
- table/filter utility behavior.
- permission-sensitive rendering.
- reusable block editor logic.

Avoid snapshot-heavy tests with little behavioral value.

---

# 11. E2E Test Rules

Use Playwright for critical end-to-end paths.

Core flows include:

- Admin login.
- Product create/publish.
- Public Product visibility.
- Contact inquiry submission.
- Career application submission.
- unauthorized CMS access.

E2E should not replace backend feature tests.

---

# 12. Manual Test Checklist

For UI changes inspect at least:

```text
Desktop
Mobile
Keyboard
Loading
Empty
Error
Permission state
```

For public UI also inspect:

- reduced motion when animation changed.
- server-rendered content.
- metadata when SEO-relevant.

---

# 13. Local Quality Commands

Exact commands may be wrapped by project scripts, but equivalent checks should exist.

Examples:

```bash
# PHP
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test

# JavaScript
npm run typecheck
npm run lint
npm run test
npm run build
```

Run via Docker/project helper commands when Docker is the documented source of truth.

---

# 14. CI Compatibility Rule

A change is not complete if it only works on the developer's host but fails in the documented Docker/CI environment.

Before finalizing:

- Ensure dependency manifests are updated.
- Ensure migrations are committed.
- Ensure generated build artifacts follow repository policy.
- Ensure no local absolute path is introduced.
- Ensure `.env` is not committed.

---

# 15. Docker Rules

Do not require a developer to manually install MySQL or Mailpit on the host.

When Docker configuration changes:

- update compose/Dockerfile.
- update `.env.example` if needed.
- update project documentation.
- test fresh build where feasible.

Use service names for container-to-container connectivity, not hard-coded host LAN IPs.

---

# 16. Kubernetes Rules

Kubernetes manifests are a learning/validation layer. Phase 10 tidak memblokir staging/production; kebenaran production dibuktikan pada cPanel-compatible staging.

Do not:

- create Kubernetes-specific business logic.
- make normal local development depend on cluster availability.
- use K8s secrets from production in repository.

When image/runtime changes, validate that K8s deployment assumptions remain correct during the Kubernetes phase.

---

# 17. CI/CD Rules

CI should validate:

```text
format
static analysis
types
lint
tests
build
container build
security baseline
```

CD should deploy a traceable artifact.

Never deploy an uncommitted local directory as the official production release.

Every production release should map to a commit/tag/release artifact.

---

# 18. Documentation Rule

Update documentation when a change affects:

- setup.
- environment variables.
- folder structure.
- architecture.
- permissions.
- deployment.
- commands.
- business workflow.
- external dependency.

Do not update unrelated documentation just to increase diff size.

---

# 19. File Editing Rule

When iterating on an existing file:

- Change only the necessary area.
- Preserve unrelated working code.
- Include enough surrounding context in proposed patches to make placement clear.
- Avoid full-file rewrites unless the file structure itself must change substantially.

This reduces regression risk and keeps review focused.

---

# 20. Dependency Rule

Before adding a package, answer:

1. Is the framework/native platform already sufficient?
2. Does the package materially reduce complexity or risk?
3. Is it actively maintained?
4. Is it compatible with Laravel/PHP/Node versions used by this project?
5. Is it compatible with cPanel/shared hosting constraints?
6. What security/upgrade burden does it add?

Prefer fewer well-understood dependencies.

---

# 21. Database Safety Rule

Never assume a production database is empty.

Migrations must consider existing records.

For new required fields, prefer a staged transition when necessary:

```text
1. Add nullable/default-safe field.
2. Deploy code that writes it.
3. Backfill existing data.
4. Enforce stricter constraint in later migration.
```

Use the simplest safe approach appropriate to the actual state of the project.

---

# 22. Backward Compatibility Rule

During active development before first production release, compatibility can be relaxed when no persisted shared environment depends on old behavior.

After staging/production begins, explicitly consider:

- old rows.
- old files.
- rollback application compatibility.
- migration reversibility.
- cached/public URLs.

---

# 23. Performance Review After Implementation

After code works, ask:

- Can this be simpler without losing clarity?
- Is there an N+1 query?
- Is too much data loaded?
- Is the client doing work the server should do?
- Is the server doing expensive work repeatedly?
- Is an abstraction adding more complexity than value?

If the most performant approach is materially more complex than the simpler approach, document the trade-off before choosing it.

---

# 24. Security Review After Implementation

Ask:

- Can an unauthenticated user call this?
- Can a logged-in user without permission call this directly?
- Can input escape intended validation?
- Does the response leak sensitive information?
- Can file upload bypass type/path restrictions?
- Is a private file reachable directly?
- Does logging capture sensitive values?
- Does this need rate limiting?

---

# 25. Definition of Ready

A task is ready to Build when:

- [ ] Problem is understood.
- [ ] Affected flow identified.
- [ ] Acceptance criteria written.
- [ ] Security/permission expectations known.
- [ ] Data impact understood.
- [ ] Unknown business rule is not being silently guessed.

---

# 26. Definition of Done

A change is done when:

- [ ] Acceptance criteria satisfied.
- [ ] Code follows architecture.
- [ ] Validation implemented.
- [ ] Authorization implemented where needed.
- [ ] Audit implemented where needed.
- [ ] Automated tests added/updated where valuable.
- [ ] Relevant tests pass.
- [ ] Formatting/lint/static analysis pass.
- [ ] UI states complete where relevant.
- [ ] Mobile/accessibility considered.
- [ ] No debug code/secrets remain.
- [ ] Documentation updated if required.
- [ ] Docker/CI compatibility preserved.

---

# 27. Standard Change Template

Use this structure for substantial development tasks.

## PLAN

```text
Goal:
Affected flow:
Affected files/modules:
Data impact:
Security impact:
Compatibility concerns:
Implementation sequence:
Test strategy:
```

## SPECIFY

```text
Functional behavior:
Authorization:
Validation:
Data changes:
UI states:
Audit/SEO impact:
Acceptance criteria:
```

## BUILD

```text
1. Migration/model if needed.
2. Authorization/validation.
3. Domain action/service.
4. Controller/route.
5. UI.
6. Audit/SEO/side effects.
7. Documentation.
```

Only include steps that actually apply.

## TEST

```text
Automated:
- ...

Manual:
- ...

Regression:
- ...
```

---

# 28. Bug Fix Template

## PLAN

- Reproduce the bug.
- Identify expected vs actual behavior.
- Trace root cause.
- Identify adjacent flows.

## SPECIFY

```text
Given ...
When ...
Then ...
```

Include regression acceptance criteria.

## BUILD

Fix root cause rather than hiding symptom where practical.

Avoid unrelated refactoring in the same bug fix unless necessary.

## TEST

- Add regression test where practical.
- Verify original failure no longer occurs.
- Verify adjacent flow.

---

# 29. Feature Template

## PLAN

- User/business goal.
- Domain location.
- data model impact.
- permission impact.
- UI surfaces.

## SPECIFY

- happy path.
- validation.
- permissions.
- errors.
- empty state.
- responsive/accessibility.

## BUILD

Implement vertically when practical:

```text
Data
â†’ Backend behavior
â†’ Authorization
â†’ UI
â†’ Tests
```

Avoid building disconnected layers that cannot be exercised end-to-end for long periods.

## TEST

- feature tests.
- UI/component tests if useful.
- end-to-end test if critical user journey.

---

# 30. Refactor Template

Refactoring must preserve behavior unless the task explicitly changes behavior.

Before refactor:

- Identify existing tests.
- Add characterization test if behavior is under-protected.

During refactor:

- Keep changes focused.
- Avoid schema/API changes without specification.

After refactor:

- Run regression tests.
- Compare query behavior/performance if relevant.

---

# 31. Migration Template

For schema changes specify:

```text
Current schema:
Target schema:
Existing-data handling:
Index/FK impact:
Rollback behavior:
Application compatibility window:
```

Never add destructive production migrations without explicit review.

---

# 32. UI Change Template

Before coding identify:

- page/component.
- desktop behavior.
- mobile behavior.
- keyboard behavior.
- loading/error/empty state.
- reduced-motion impact.
- design token usage.

CMS components must remain consistent with shadcn/project token conventions.

Public Blade components must remain lightweight and server-rendered.

---

# 33. Review Checklist for AI Agents

Before presenting final code or declaring completion, review your own work for:

## Correctness

- Does it satisfy the exact task?
- Are edge cases handled?

## Simplicity

- Is there unnecessary abstraction?
- Can a helper/service be removed?

## Performance

- N+1?
- unnecessary query/data transfer?
- avoidable rerender/client computation?

## Security

- Auth/authz?
- validation?
- sensitive data?

## Maintainability

- clear names?
- cohesive responsibilities?
- tests?

## Compatibility

- Docker?
- CI?
- cPanel?
- existing data?

---

# 34. Forbidden Shortcuts

Do not:

- fake a successful integration with mock data when real flow is required.
- disable validation to make a test pass.
- remove authorization because UI hides the button.
- hard-code production credentials.
- use `any` broadly to silence TypeScript.
- suppress static-analysis issues without understanding them.
- swallow exceptions silently.
- catch every exception and return success.
- add `@ts-ignore`/analysis ignores as default solution.
- execute destructive migration automatically without review.
- store user-uploaded executable content in public paths.

---

# 35. Communication Rule

When proposing implementation, state:

- what changes.
- where it changes.
- why it changes.
- how to test it.

When alternatives exist, explain the practical trade-off.

Prefer the simpler approach unless the more complex approach provides a meaningful performance, security, maintainability, or deployment advantage.

---

# 36. Final Principle

The expected engineering loop is:

```text
Understand before editing.
Specify before implementing.
Implement the smallest complete solution.
Test behavior, not assumptions.
Document changes that affect the system contract.
```

Or, in the project shorthand:

```text
PLAN â†’ SPECIFY â†’ BUILD â†’ TEST
```

# 37. Kontrak v4 yang wajib diperiksa

- Runtime PHP 8.3.x pada Docker/CI/hosting; tidak menaikkan platform minimum ke 8.4. Composer lock/install/platform checks diuji pada seri ini.
- Data-model/permission/transition/retention rules mengikuti dokumen 06; acceptance IDs dan evidence mengikuti dokumen 08.
- Product/Activity public reads hanya PUBLISHED revision; draft save tidak boleh mengubah live relation/metadata/search.
- Read/list/dashboard/export/download memerlukan authorization, bukan hanya mutations. Race terakhir Super Admin, duplicate applications dan media references diuji secara integrasi.
- Hosting yang belum diverifikasi tetap ditulis demikian. Tidak mengubah placeholder menjadi klaim capability atau hasil test PASS.
- Deployment menggunakan artifact staging yang sama, persistent APP_KEY/storage, dan smoke setelah aktivasi sesuai dokumen 09.
- Repository ini berkembang bertahap; perintah contoh dokumentasi bukan bukti file script/config sudah tersedia. Buat implementasi nyata dan perbarui handbook ketika tersedia.
- Runtime acceptance pada paket dokumentasi berstatus NOT_RUN. Verification dokumen tidak boleh dilaporkan sebagai aplikasi production-ready.

