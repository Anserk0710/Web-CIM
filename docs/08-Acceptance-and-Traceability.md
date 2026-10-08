# Acceptance Criteria & Traceability

**Versi:** 4.0 · **Tanggal:** 5 Oktober 2026  
**Status eksekusi aplikasi:** seluruh skenario di bawah **BELUM DIJALANKAN**. Paket ini berisi dokumentasi, bukan source aplikasi atau hasil CI.  
**Tujuan:** menghubungkan kebutuhan → fase implementasi → bukti yang harus disediakan.

## 1. Aturan bukti

Setiap hasil test mencatat commit, environment, data fixture, command/metode, waktu, expected/actual dan tautan log/screenshot bila relevan. Checklist tidak boleh dicentang hanya karena kode telah ditulis. Test yang tidak berlaku dijelaskan dengan alasan; tidak disamarkan sebagai lulus.

- FT: feature/integration test Laravel pada database target, isolated dari data developer.
- CT: component test untuk perilaku frontend yang kompleks.
- E2E: Playwright pada stack yang menjalankan aplikasi lengkap.
- OP: rehearsal operasional pada environment yang ditentukan.
- MAN: pemeriksaan manual konten, keyboard, responsive atau hosting.
- Endpoint forbidden menghasilkan 403 untuk pengguna authenticated tanpa izin; guest mengikuti login redirect pada CMS. Public content yang tidak terlihat menghasilkan 404. Error validasi mengikuti response web/Inertia yang sesuai dan tidak mengubah state.

## 2. Matriks requirement dan acceptance

| ID | Kebutuhan / hasil yang diharapkan | Metode | Fase |
|---|---|---|---|
| HOST-01 | PHP maksimum 8.3 tercatat; versi web/CLI, extensions dan patch aktual dibuktikan | MAN | 0–1 |
| HOST-02 | Engine/version database produksi, cron, metode CLI/deploy, root public dan storage terverifikasi; setiap unsupported capability mempunyai fallback teruji | MAN/OP | 0–2, 9–11 |
| BUILD-01 | Dependency install, static checks, production build dan backend tests berhasil pada PHP 8.3.x; dependency yang memerlukan PHP 8.4 ditolak | CI/FT | 1, 8 |
| LOCAL-01 | Fresh clone tanpa vendor/node_modules/DB lama dapat start mengikuti handbook; web, CMS, Mailpit dan persistent storage tersedia | OP | 1, 7 |
| AUTH-01 | Login/logout/reset/change password berhasil; registration publik tidak tersedia; reset mail terlihat di Mailpit | FT/E2E | 2 |
| AUTH-02 | Deaktivasi memblokir request dari sesi yang sudah login dan remember token; logout mencabut sesi terkait | FT | 2 |
| AUTH-03 | Login/reset dibatasi, pesan tidak mengungkap keberadaan akun; credentials tidak tercatat di log | FT/MAN | 2, 12 |
| RBAC-01 | Editor tanpa product.publish tidak dapat publish lewat tombol maupun request langsung; entity/revision tidak berubah | FT/E2E | 2–3 |
| RBAC-02 | List/detail/dashboard/download/export memeriksa izin; domain pribadi tidak bocor melalui props, counts atau URL yang ditebak | FT | 2–5 |
| RBAC-03 | Menonaktifkan/mencabut Super Admin terakhir ditolak, termasuk dua request bersamaan; user tidak menaikkan hak sendiri | FT integrasi concurrency | 2 |
| RBAC-04 | Mengubah izin berpengaruh pada request berikutnya; permission cache bila ada diinvalidasi | FT | 2 |
| PROD-01 | Create/edit working Product menyimpan name, category, brand/type, gallery, highlights, specs, related, brochure dan SEO; restart tidak menghilangkan data | FT/E2E | 3 |
| PROD-02 | Publish melakukan validasi lengkap dan atomik; slug/media invalid menggagalkan publish tanpa perubahan parsial | FT | 3 |
| REV-01 | Edit draft Product/Activity yang sudah terbit tidak mengubah HTML, gallery, filter, metadata atau sitemap publik sampai publish | FT/E2E | 3–4 |
| REV-02 | Dua editor dengan lock_version sama tidak saling menimpa; editor kedua mendapat pesan konflik dan data terbaru tetap utuh | FT/CT | 3–4 |
| PROD-03 | Archive/unpublish membuat detail 404 dan menghapus item dari listing/sitemap; referensi tidak menghilangkan data operasional | FT/E2E | 3 |
| TAX-01 | Kategori/brand/type terpakai tidak dapat dihapus; kategori nonaktif tidak dapat dipilih saat publish baru; label change menampilkan dampak dan audit | FT/MAN | 3 |
| ECAT-01 | Satu produk bisa memiliki beberapa entri dengan kode unik; link menunjuk produk benar | FT | 4 |
| ECAT-02 | Visibility mengikuti status entry, status Product, effective_at dan batas eksklusif expires_at; uji tepat sebelum/pada/sesudah waktu batas | FT | 4 |
| ECAT-03 | Editing entry live memerlukan update+publish; editor tanpa publish tidak bisa mengubahnya melalui crafted request | FT | 4 |
| ACT-01 | Semua tipe blok dapat dibuat, diurutkan, disimpan dan dirender; referensi media/product valid | FT/CT/E2E | 4 |
| ACT-02 | HTML/script/URL protocol berbahaya ditolak atau disanitasi; embed hanya allowlist; payload tidak dieksekusi pada preview maupun publik | FT/E2E | 4 |
| CAREER-01 | DRAFT/OPEN/CLOSED/ARCHIVED mengikuti state machine; CLOSED tetap memberi informasi tanpa form/JobPosting aktif | FT/E2E | 4–5 |
| APP-01 | Submit setelah batas deadline ditolak walaupun cron belum berjalan; tidak ada record/file/notification baru | FT clock-controlled | 5 |
| APP-02 | Submit valid menyimpan data, consent version/time dan CV privat; satu notification setelah commit | FT/E2E | 5 |
| APP-03 | Submit ganda/paralel pada email+vacancy yang sama menghasilkan satu application dan satu CV; race unique constraint ditangani sebagai hasil yang jelas | FT integrasi concurrency | 5 |
| APP-04 | Transition seleksi di luar aturan ditolak; reopen/backstep membutuhkan alasan; status event dan audit sesuai | FT/CT | 5 |
| CV-01 | Guest atau pengguna tanpa application.read+download tidak menerima byte CV; direct public path tidak tersedia | FT/E2E | 5 |
| CV-02 | Non-PDF, PDF tidak valid, file >5 MiB dan nama berbahaya ditolak; file tepat pada batas yang valid diterima | FT | 5 |
| PRIV-01 | Setelah retensi kandidat ditolak jatuh tempo, CV/identitas/notes/reason pribadi hilang; agregat anonim tersisa; job dapat diulang dengan aman | FT/OP | 5–6 |
| PRIV-02 | Kandidat HIRED mengikuti handover HR; purge tidak dilakukan sebelum handover; backlog tindak lanjut terlihat | FT/MAN | 5–6 |
| INQ-01 | Semua jenis inquiry tersimpan dengan validasi field/relasi sesuai tipe; honeypot/rate limit tidak menampilkan sukses palsu | FT/E2E | 5 |
| INQ-02 | State NEW/IN_PROGRESS/RESOLVED/SPAM, notes, reopen reason, filter dan retensi bekerja sesuai kontrak | FT | 5 |
| MAIL-01 | Kegagalan SMTP setelah submission tidak membatalkan data; retry tidak menggandakan application/inquiry; failed job diketahui operator | FT/OP | 5, 11 |
| MEDIA-01 | Upload images/PDF memeriksa jenis, ukuran dan dimensi; storage tetap ada setelah recreate container | FT/OP | 3, 5 |
| MEDIA-02 | Media yang dipakai draft/live/page/block tidak dapat dihapus, termasuk race penghapusan vs penggunaan baru | FT integrasi | 3–5 |
| MEDIA-03 | Replace satu penggunaan tidak mengganti file di penggunaan lain; failed transaction membersihkan file sementara | FT | 3–5 |
| EXPORT-01 | Read tanpa export ditolak; CSV hanya berisi filter/scope yang diizinkan, maksimal 5.000 baris; private paths tidak ikut | FT/E2E | 5 |
| EXPORT-02 | Cell untrusted dengan formula prefix/whitespace/separator ditangani; CSV dibuka sebagai data aman pada aplikasi spreadsheet target | FT/MAN | 5, 12 |
| PAGE-01 | Seluruh section Home/Company/Career/Contact dapat dikelola dari form yang ditetapkan; section kosong tidak meninggalkan layout rusak | FT/E2E/MAN | 5 |
| PAGE-02 | User tanpa page/settings permission tidak mengubah live content; update sah menginvalidasi cache dan menghasilkan audit | FT | 4–5 |
| AUDIT-01 | Mutation sensitif, publish, role, settings, CV download/export menghasilkan audit; tidak ada password/token/CV body dalam payload | FT/MAN | 2–5 |
| SEO-01 | HTML awal memiliki title/description/canonical/OG/schema sesuai halaman; sitemap hanya URL publik yang benar | FT/MAN | 3–5 |
| SEO-02 | Slug lama 301 ke entity terbit yang sama; target draft/archive tidak bocor; canonical dan redirect tidak berulang | FT/E2E | 3–5 |
| SEO-03 | Staging dibatasi akses dan noindex; reset/inquiry staging tidak mengirim ke penerima produksi | OP | 11 |
| UI-01 | Keyboard login, editor, menu, dialog, filter, form publik berfungsi dengan focus terlihat dan focus return | E2E/MAN | 3–5, 12 |
| UI-02 | Kontras text/control/focus sesuai target WCAG; 320/390/430/768/820/1366/1440 px dan zoom diuji; overflow hanya pada rail/table yang disengaja | MAN | 5, 12 |
| UI-03 | Reduced motion dan JS dekoratif nonaktif tidak menyembunyikan konten; mobile sticky menjadi flow biasa | E2E/MAN | 5 |
| PERF-01 | List dipaginasi, filter/sort server allowlist, tidak ada N+1 yang bertambah per baris; publik tidak memuat CMS React bundle | FT/build inspection | 3–5, 12 |
| PERF-02 | Baseline response/image/asset diukur dengan dataset dan environment tercatat; budget performance ditetapkan sebelum Phase 12 sign-off | MAN/OP | 7, 11–12 |
| CI-01 | PR menjalankan required checks dan gagal check menghalangi merge bila fitur repo tersedia; fallback enforcement dijelaskan jika tidak tersedia | OP | 1, 8 |
| CI-02 | Backend, lint/types, component tests yang relevan, asset/container build dan audit policy berjalan; security exception memiliki owner/expiry | CI | 8 |
| REL-01 | Tag/release berasal dari commit yang lolos checks; artifact hash/image digest/commit tercatat; tidak ada .env/private uploads/runtime cache | CI/OP | 9 |
| REL-02 | Checksum artifact staging sama dengan produksi; deploy tidak membangun ulang dependency/assets setelah acceptance staging | OP | 9, 11, 13 |
| OPS-01 | Backup DB+upload+recovery secrets dilakukan sesuai kebijakan dan restore berhasil pada environment terisolasi | OP | 11 |
| OPS-02 | RPO <=24 jam dan RTO <=4 jam dibuktikan saat rehearsal atau dicatat sebagai gap release yang harus diselesaikan | OP | 11–12 |
| DEP-01 | PHP/runtime/platform check, migration, activation, smoke dan rollback rehearsal berhasil; APP_KEY tidak berubah antar-release | OP | 11–13 |
| DEP-02 | Storage dan .env tidak tertimpa deploy; root public teruji; .env/private file tidak dapat diambil dari internet | OP | 11–13 |
| K8S-01 | kind menjalankan HTTP Nginx + PHP-FPM, DB PVC, migration Job, probes, logs dan data bertahan saat pod dibuat ulang | OP belajar | 10 |
| K8S-02 | Rolling update/rollback dan scaling dipelajari dengan batas session/cache/media dijelaskan; tidak dianggap validasi produksi cPanel | OP belajar | 10 |

## 3. Pipeline dan kapan dijalankan

| Pemeriksaan | Waktu wajib |
|---|---|
| Pint, PHPStan/Larastan, TypeScript, ESLint, backend tests, Vite build | Setiap PR sejak infrastrukturnya tersedia; minimal pipeline sejak Phase 1 |
| Component tests | Setiap PR yang memengaruhi komponen/logic terkait; seluruh suite pada release |
| E2E smoke: login + Product draft/publish/public | Setiap PR setelah Phase 3; boleh job tersendiri |
| E2E critical lengkap | Wajib pada release candidate sebelum artifact diterima untuk produksi |
| Dependency audit + container build | PR pada baseline penuh Phase 8 dan setiap release |
| Private data, authorization, upload, deadline, duplicate regressions | Required backend suite sejak feature tersedia |
| Keyboard, responsive, contrast, real-content review | Per UI change yang relevan dan Phase 12 |
| Backup/restore + rollback | Sebelum peluncuran pertama; restore bulanan, ulang rollback bila strategi berubah |

PHP, database service, Node dan dependency lock harus sesuai [00-Decisions-and-Hosting-Matrix.md](00-Decisions-and-Hosting-Matrix.md). Jangan memakai SQLite sebagai satu-satunya pembuktian unique constraint/concurrency MySQL. Uji race memakai koneksi database terpisah dan bukan transaksi test tunggal yang menyembunyikan concurrency.

## 4. Quality gates

- **Local feature done:** acceptance terkait lulus, authorization/validation/audit ada, tidak ada mock primary flow, state UI lengkap, dokumen diperbarui.
- **Release candidate done:** seluruh acceptance non-operasional yang berlaku lulus pada commit yang sama; E2E critical lengkap; audit dependencies tanpa temuan kritis yang belum ditangani. Policy pengecualian tidak boleh melewati known critical exploitable issue.
- **Production gate:** HOST/REL/OPS/DEP lulus dengan bukti; konten/migration/redirect disetujui pemilik; metode approval benar-benar tersedia; known hosting gaps belum boleh disebut selesai.
- **Learning gate:** K8S-01/02 selesai untuk tujuan belajar. Gate ini tidak menjadi dependency production.

## 5. Template catatan hasil

```text
Acceptance ID:
Commit/tag:
Environment + runtime/database versions:
Fixture/data scope:
Method/command:
Expected:
Actual:
Status: NOT_RUN / PASS / FAIL / BLOCKED / NOT_APPLICABLE
Evidence:
Issue/action owner:
```

Dokumen tidak mengisi PASS sebelum pemeriksaan nyata. Pemeriksaan konsistensi paket Markdown dilaporkan terpisah dari pengujian aplikasi.
