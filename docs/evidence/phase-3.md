# Phase 3 Evidence

## Status

Status: **NOT_RUN**

Phase:

**3 — Product Vertical Slice**

Acceptance:

- PROD-01
- PROD-02
- PROD-03
- REV-01
- REV-02
- TAX-01
- MEDIA-01
- MEDIA-02
- MEDIA-03
- RBAC-01
- SEO-01
- SEO-02
- PERF-01

## Product CMS

Create Product:

`NOT_RUN`

Edit WORKING revision:

`NOT_RUN`

Primary Category:

`NOT_RUN`

Brand:

`NOT_RUN`

Type:

`NOT_RUN`

Highlights:

`NOT_RUN`

Specifications:

`NOT_RUN`

Gallery:

`NOT_RUN`

Brochure:

`NOT_RUN`

Related Products:

`NOT_RUN`

SEO fields:

`NOT_RUN`

## Revision Workflow

WORKING isolated from PUBLISHED:

`NOT_RUN`

Publish transaction:

`NOT_RUN`

Authenticated preview:

`NOT_RUN`

Optimistic locking:

`NOT_RUN`

Slug redirect:

`NOT_RUN`

Unpublish:

`NOT_RUN`

Archive:

`NOT_RUN`

## Authorization

Editor draft access:

`NOT_RUN`

Editor crafted publish request returns 403:

`NOT_RUN`

Publisher allowed:

`NOT_RUN`

## Media

Image upload:

`NOT_RUN`

PDF brochure upload:

`NOT_RUN`

Draft reference safety:

`NOT_RUN`

Published reference safety:

`NOT_RUN`

Referenced-media delete blocked:

`NOT_RUN`

Unused media delete succeeds:

`NOT_RUN`

Public/private storage boundary preserved:

`NOT_RUN`

## Public Website

Products listing:

`NOT_RUN`

Product detail:

`NOT_RUN`

Search:

`NOT_RUN`

Category filter:

`NOT_RUN`

Brand filter:

`NOT_RUN`

Type filter:

`NOT_RUN`

Pagination 12:

`NOT_RUN`

Draft hidden:

`NOT_RUN`

Archived hidden:

`NOT_RUN`

Unpublished hidden:

`NOT_RUN`

## SEO

Canonical:

`NOT_RUN`

Meta title/description:

`NOT_RUN`

OG image:

`NOT_RUN`

Draft excluded from sitemap:

`NOT_RUN`

Archived excluded from sitemap:

`NOT_RUN`

Published product included in sitemap:

`NOT_RUN`

## Failure Atomicity

Slug collision rollback:

`NOT_RUN`

Stale update rejection:

`NOT_RUN`

Media deletion failure does not delete database reference:

`NOT_RUN`

## E2E

Login:

`NOT_RUN`

Draft save:

`NOT_RUN`

Publish:

`NOT_RUN`

Public visibility:

`NOT_RUN`

CI E2E:

`NOT_RUN`

## Exit Criteria

- [ ] Create/edit stores complete Product state.
- [ ] Data survives container restart.
- [ ] WORKING does not modify live PUBLISHED revision.
- [ ] Editor without publish permission receives 403.
- [ ] Publish failures roll back.
- [ ] Media safety works.
- [ ] Stale edit is rejected.
- [ ] Archive/unpublish hides public Product without destructive deletion.
- [ ] CI runs regression and Playwright smoke.

## Verification

`powershell -ExecutionPolicy Bypass -File .\scripts\phase3-check.ps1`

## Final Phase Status

**NOT_RUN**