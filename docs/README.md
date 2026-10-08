# PT Cahya Intan Medika — Documentation Pack v4

**Versi:** 4.0 · **Tanggal:** 5 Oktober 2026  
**Status:** baseline dokumentasi untuk implementasi, berdasarkan keputusan pengguna dan review v3.  
**Batas hosting yang dikonfirmasi pengguna:** PHP maksimum **8.3**.  
**Isi paket:** 13 file Markdown; bukan source aplikasi, migration, Compose config atau hasil pengujian runtime.

## Mulai membaca

1. Baca keputusan dan matriks hosting untuk membedakan keputusan tetap dari informasi yang belum diverifikasi.
2. Baca PRD dan Data Model & Business Rules sebelum menyusun task implementation.
3. Gunakan Roadmap untuk urutan pengerjaan, Architecture untuk integrasi, UI/Design System untuk layar.
4. Tautkan setiap task ke acceptance ID; ikuti AGENTS untuk Plan → Specify → Build → Test.
5. Gunakan handbook dan Operations Runbook saat menyiapkan repository, staging dan production.

## Peta dokumen

| File | Kegunaan |
|---|---|
| [00-Decisions-and-Hosting-Matrix.md](00-Decisions-and-Hosting-Matrix.md) | Keputusan v4, PHP 8.3, versi kandidat, capability yang belum diverifikasi dan sumber resmi |
| [01-PRD.md](01-PRD.md) | Scope, functional/non-functional requirements dan acceptance produk |
| [02-Roadmap.md](02-Roadmap.md) | Fase 0–13, CI sejak fondasi, Product vertical slice, kind mandiri |
| [03-UI.md](03-UI.md) | Anatomi layar, interaksi, draft/live, error/empty, mobile dan aksesibilitas |
| [04-Design-System.md](04-Design-System.md) | Token bersama Blade/React, komponen, kontras dan motion |
| [05-Architecture.md](05-Architecture.md) | Monolith, revisions, storage/mail/queue, Docker, CI, kind dan cPanel |
| [06-Data-Model-and-Business-Rules.md](06-Data-Model-and-Business-Rules.md) | ERD, kamus data, state machine, matriks role/permission, upload, retensi dan mapping konten |
| [07-Project-Documentation-and-Folder-Structure.md](07-Project-Documentation-and-Folder-Structure.md) | Target repository, setup, commands, conventions dan workflow developer |
| [08-Acceptance-and-Traceability.md](08-Acceptance-and-Traceability.md) | Requirement IDs, phase mapping, test methods dan format evidence |
| [09-Operations-and-Release-Runbook.md](09-Operations-and-Release-Runbook.md) | Artifact, deployment, smoke, rollback, queue, backup/restore, retensi dan cutover |
| [AGENTS.md](AGENTS.md) | Aturan kontribusi Plan → Specify → Build → Test |
| [CHANGELOG.md](CHANGELOG.md) | Perubahan dari v3 dan batas verifikasi paket |

## Arah teknis

- Laravel **13.x**, PHP **8.3.x** pada Docker, CI dan target cPanel.
- Publik: Blade + Tailwind + JavaScript/Alpine seperlunya.
- CMS: React + TypeScript + Inertia + Tailwind + shadcn/ui.
- Database: target awal lokal MySQL **8.4 LTS**; actual engine/version hosting belum diverifikasi.
- Build: Node.js **24 LTS**; dependency exact versions/image digests dikunci saat foundation diuji.
- Runtime lokal: Docker Compose, Nginx, PHP-FPM, database, Mailpit; worker/scheduler bila digunakan.
- CI/release: GitHub Actions, lockfiles, artifact checksum dan GHCR images. Fitur protection/approval repo perlu diverifikasi.
- Kubernetes: kind untuk belajar, tidak menjadi gate produksi cPanel.

Laravel 13 mendukung PHP 8.3. Paket tidak menaikkan PHP requirement di atas batas hosting. PHP web/CLI aktif, patch, extensions, database, cron, SSH/SFTP, root public dan backup masih membutuhkan bukti sesuai dokumen 00.

## Keputusan produk penting

- Product/Activity: working draft terpisah dari published revision; edit draft tidak mengubah publik.
- Product: satu kategori utama, master brand/type, beberapa E-Catalog.
- Career: DRAFT/OPEN/CLOSED/ARCHIVED, deadline dicek server; tidak menerima lamaran umum.
- Lamaran: satu email per vacancy selama identitas tersimpan; CV PDF privat maksimal 5 MiB.
- Read/download/export/publish dan access management merupakan izin terpisah.
- Retensi kandidat ditolak 6 bulan setelah keputusan akhir; HIRED melalui handover HR.
- Konten publik dikelola dari form CMS terstruktur; tidak mengarang spesifikasi atau fakta perusahaan.
- Backup harian, retensi 30 hari, target RPO 24 jam/RTO 4 jam; wajib dibuktikan sebelum produksi.

## Alur delivery

Local Docker → tests + CI bertahap → versioned artifact → staging → approval → produksi memakai artifact yang sama → monitor/rollback.

Kind learning dimulai setelah images siap dan dapat diselesaikan terpisah. Deployment selalu memiliki preflight sebelum aktivasi dan smoke setelah aktivasi.

## Batas klaim

Paket v4 diperiksa untuk konsistensi dokumen dan integritas archive. Seluruh acceptance aplikasi di dokumen 08 berstatus **BELUM DIJALANKAN**. Ini tidak menyatakan hosting telah diperiksa langsung, aplikasi telah selesai dibangun, atau produksi telah siap.
