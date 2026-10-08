# Changelog

## 4.0 — 5 Oktober 2026

Dasar revisi: pengguna menerima rekomendasi review dan mengoreksi kandidat PHP 8.4 menjadi batas hosting maksimum PHP 8.3. Seri PHP 8.3 sekarang menjadi kontrak runtime lokal/CI/produksi; Laravel 13 tetap sesuai kompatibilitas resmi.

### Keputusan dan kompatibilitas

- Menambahkan matriks hosting dengan status fakta dikonfirmasi pengguna vs capability belum diverifikasi; pemeriksaan dipindah ke awal roadmap.
- Memisahkan local application, production acceptance dan Kubernetes learning completion.
- Menetapkan lini versi dan aturan pin/lock/platform checks tanpa mengarang nomor patch atau versi database hosting.
- Mempertahankan seluruh primary modules dan pola Laravel monolith/Blade/Inertia dari v3.

### Data dan perilaku

- Menambahkan ERD/kamus data, normalized relations, foreign-key/delete policy, revision slots dan optimistic locking.
- Menambahkan state/transition/visibility/deadline, slug redirects dan validation cross-field.
- Menambahkan permission registry, role defaults, read/download/export guards, last Super Admin dan race handling.
- Menambahkan consent version/time, private PDF CV limits, duplicate application constraint, HR workflow dan retention.
- Menambahkan media-reference registry, replace semantics dan file compensation; memisahkan CV dari Media Library.
- Memetakan section halaman publik ke field CMS, source data, permission dan empty behavior.

### Delivery dan operasi

- CI dasar sejak Phase 1; Product vertical slice pada Phase 3; Phase 6 memperkuat tests yang sudah tumbuh bersama feature.
- Phase 10 kind tidak memblokir staging/production; HTTP runtime Nginx/FPM diperjelas.
- Entry assets publik/CMS terpisah dengan shared tokens.
- Artifact/vendor/assets tetap sama dari staging ke produksi, dengan hash/digest/commit traceability.
- Menyatukan deployment sequence, smoke setelah activation, rollback yang mempertimbangkan DB compatibility, persistent APP_KEY/storage.
- Menambahkan queue/cron fallback, backup/restore drill, retention audit/log/backup, deletion ledger dan content cutover.
- Menambahkan requirement IDs, phase/method mapping dan evidence template; seluruh runtime acceptance masih NOT_RUN.

### UI dan dokumentasi

- Memperbaiki focus/control-border/text-on-tint contract berdasarkan contrast tokens, dengan batas bahwa composed screens belum diuji.
- Memperjelas draft/live, stale edit, Career closed state, export/download permissions dan zero-vacancy behavior.
- Menambah reflow QA 320 CSS px dan keyboard focus di sekitar sticky headers.
- Memperjelas peran dokumen pada AGENTS, menormalkan referensi, dan menambahkan dokumen 00/06/08/09 serta changelog ini.

### Default implementasi tambahan

Nilai awal teknis seperti page size, upload pixel count, rate limits, CSV row limit, inquiry/audit/log retention dan detail handover merupakan pilihan implementasi yang melengkapi rekomendasi yang diterima. Ini bukan hasil pengukuran trafik/kapasitas hosting atau pernyataan kepatuhan hukum. Perubahan nilai dicatat lewat keputusan proyek dan test terkait.

### Pemeriksaan paket

- Seluruh 8 nama dokumen asal tetap tersedia, dengan perubahan terarah pada bagian yang terdampak.
- Lima dokumen baru melengkapi keputusan, data, acceptance, operasi dan histori perubahan.
- Relative Markdown links, fenced blocks, ID acceptance, permission references, kontrak PHP, jumlah file dan archive integrity diperiksa sebelum penyerahan.
- Tidak ada source aplikasi/test/hosting execution dalam pekerjaan ini; checklist runtime tidak dicentang sebagai PASS.
