<!-- path: docs/evidence/phase-4.md -->

# Phase 4 Evidence

## Status

**NOT_RUN**

Phase:

**4 — Remaining CMS Modules and Content Administration**

Applications and Inquiries remain Phase 5 acceptance.

## CMS Shell

Admin layout:

`NOT_RUN`

Sidebar:

`NOT_RUN`

Breadcrumb:

`NOT_RUN`

Permission-aware navigation:

`NOT_RUN`

Environment badge:

`NOT_RUN`

Data table:

`NOT_RUN`

Form primitives:

`NOT_RUN`

Dialog:

`NOT_RUN`

Toast:

`NOT_RUN`

Loading:

`NOT_RUN`

Error:

`NOT_RUN`

Empty state:

`NOT_RUN`

Responsive baseline:

`NOT_RUN`

## Product

Existing Phase 3 Product regression:

`NOT_RUN`

CRUD:

`NOT_RUN`

Search/filter:

`NOT_RUN`

Draft/publish/archive:

`NOT_RUN`

Taxonomy:

`NOT_RUN`

Media/gallery/brochure:

`NOT_RUN`

Specs/highlights:

`NOT_RUN`

Related Product:

`NOT_RUN`

E-Catalog relationship:

`NOT_RUN`

SEO:

`NOT_RUN`

Preview:

`NOT_RUN`

## E-Catalog

Create/read/update:

`NOT_RUN`

Product relation:

`NOT_RUN`

Procurement fields:

`NOT_RUN`

Availability:

`NOT_RUN`

Publish:

`NOT_RUN`

Archive:

`NOT_RUN`

Validation:

`NOT_RUN`

Authorization:

`NOT_RUN`

Audit:

`NOT_RUN`

## Activity

CRUD:

`NOT_RUN`

Category:

`NOT_RUN`

WORKING/PUBLISHED:

`NOT_RUN`

Structured block schema:

`NOT_RUN`

Server sanitization:

`NOT_RUN`

Media usage registry:

`NOT_RUN`

Reorder:

`NOT_RUN`

Publish/archive:

`NOT_RUN`

Related Product:

`NOT_RUN`

SEO:

`NOT_RUN`

Optimistic locking:

`NOT_RUN`

## Career

CRUD:

`NOT_RUN`

Draft/open/close/archive:

`NOT_RUN`

UTC storage:

`NOT_RUN`

WITA display/input:

`NOT_RUN`

Live edit requires publish permission:

`NOT_RUN`

SEO:

`NOT_RUN`

## Media

Upload:

`NOT_RUN`

Reuse:

`NOT_RUN`

Alt text:

`NOT_RUN`

Caption:

`NOT_RUN`

Immutable binary-path strategy:

`NOT_RUN`

Safe delete:

`NOT_RUN`

Reference protection:

`NOT_RUN`

Mounted storage persistence:

`NOT_RUN`

## User / Role

Create User:

`NOT_RUN`

Reset-password invitation:

`NOT_RUN`

Activate/deactivate:

`NOT_RUN`

Role assignment:

`NOT_RUN`

Permission matrix:

`NOT_RUN`

Last Super Admin protections:

`NOT_RUN`

## Settings / Pages

Company:

`NOT_RUN`

Contact:

`NOT_RUN`

Coverage:

`NOT_RUN`

SEO defaults:

`NOT_RUN`

Branding:

`NOT_RUN`

Footer:

`NOT_RUN`

Offices:

`NOT_RUN`

One active Head Office invariant:

`NOT_RUN`

Structured page sections:

`NOT_RUN`

No arbitrary HTML/script:

`NOT_RUN`

## Audit UI

List:

`NOT_RUN`

Search/filter:

`NOT_RUN`

Detail:

`NOT_RUN`

Permission:

`NOT_RUN`

## Phase 4 Exit Criteria

- [ ] All Phase 4 modules use real database workflows.
- [ ] Applications/Inquiries remain Phase 5.
- [ ] No primary workflow uses mock data.
- [ ] Data persists across restart.
- [ ] Authorization is authoritative server-side.
- [ ] Mutations create audit events.
- [ ] Media persists through mounted storage.
- [ ] Responsive CMS baseline works.
- [ ] Existing Product regression remains green.
- [ ] CI remains green.

## Verification

```text
powershell -ExecutionPolicy Bypass -File .\scripts\phase4-check.ps1