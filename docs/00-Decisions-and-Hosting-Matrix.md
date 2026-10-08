# Keputusan Proyek & Matriks Hosting

**Versi:** 4.0 · **Tanggal:** 5 Oktober 2026  
**Status:** baseline dokumentasi yang disetujui untuk implementasi; aplikasi belum diimplementasikan/diverifikasi dalam paket ini.  
**Dasar:** pengguna menerima rekomendasi review, dengan koreksi bahwa PHP maksimum yang tersedia di cPanel adalah **8.3**.

## 1. Cara membaca status

- **DISETUJUI:** keputusan desain yang menjadi acuan pembangunan.
- **DIKONFIRMASI PENGGUNA:** informasi dari pengguna; belum berarti pemeriksaan server telah dilakukan.
- **PERLU VERIFIKASI:** fakta lingkungan yang belum memiliki bukti.
- **TARGET:** hasil yang wajib dibuktikan melalui pengujian; bukan hasil pengujian yang sudah lulus.

Pemilik verifikasi teknis adalah pelaksana IT/developer proyek. Pemilik konten dan proses rekrutmen adalah pengelola bisnis/HR. Nama individu ditetapkan saat kickoff, tanpa mengarang penanggung jawab. Semua batas penyelesaian menggunakan milestone, bukan tanggal proyek yang belum diberikan.

## 2. Keputusan yang berlaku

| ID | Keputusan | Status |
|---|---|---|
| D-01 | Satu Laravel modular monolith; publik Blade, CMS React/TypeScript/Inertia | DISETUJUI |
| D-02 | Target PHP **8.3.x** untuk Docker, CI, dan produksi; tidak memerlukan PHP 8.4/8.5 | DISETUJUI |
| D-03 | Laravel 13.x; dependency harus lolos instalasi dan test pada PHP 8.3 | DISETUJUI |
| D-04 | Node.js 24 LTS untuk build; tidak membutuhkan Node server di produksi | DISETUJUI |
| D-05 | Target awal lokal MySQL 8.4 LTS; engine/versi produksi masih perlu diverifikasi | DISETUJUI / PERLU VERIFIKASI |
| D-06 | Docker Compose adalah lingkungan pengembangan utama; CI dasar sejak Phase 1 | DISETUJUI |
| D-07 | kind adalah milestone belajar mandiri, tidak memblokir staging/produksi cPanel | DISETUJUI |
| D-08 | Seluruh modul inti v3 tetap dalam release awal; revisi tidak mengurangi cakupan | DISETUJUI |
| D-09 | Product/Activity memiliki satu working revision dan satu published revision; publik membaca published revision | DISETUJUI |
| D-10 | Satu kategori utama per Product; Brand dan Type sebagai master terkontrol | DISETUJUI |
| D-11 | Satu Product dapat memiliki banyak E-Catalog; satu E-Catalog menunjuk satu Product | DISETUJUI |
| D-12 | Career memiliki DRAFT, OPEN, CLOSED, ARCHIVED; tidak menerima lamaran umum pada release awal | DISETUJUI |
| D-13 | Satu lamaran per normalized email per lowongan selama data identitas masih disimpan | DISETUJUI |
| D-14 | CV PDF saja, maksimal 5 MiB (5.242.880 byte); download privat dan berizin | DISETUJUI |
| D-15 | CV/identitas kandidat tidak diterima dibersihkan 6 bulan kalender setelah keputusan akhir seleksi | DISETUJUI |
| D-16 | Data kandidat HIRED ditangani melalui serah-terima HR dan aturan pada dokumen 09 | DISETUJUI |
| D-17 | Timestamp disimpan UTC; zona bisnis Asia/Makassar (WITA); UI menampilkan zona waktu | DISETUJUI |
| D-18 | Default antarmuka/copy Indonesia; nama produk dan istilah teknis resmi dipertahankan | DISETUJUI |
| D-19 | Backup harian DB dan upload, retensi 30 hari, salinan terpisah; target RPO 24 jam/RTO 4 jam | TARGET |
| D-20 | Role adalah template izin; read, download, export, publish, dan pengelolaan akses dipisahkan | DISETUJUI |
| D-21 | Konten halaman dikelola melalui form terstruktur; block editor hanya untuk Activity | DISETUJUI |
| D-22 | Production memakai artifact persis yang lolos staging, dengan checksum yang sama | DISETUJUI |

## 3. Matriks kemampuan hosting

| Kemampuan | Informasi saat ini | Bukti yang dicatat | Batas verifikasi / dampak |
|---|---|---|---|
| PHP maksimum | **8.3**, DIKONFIRMASI PENGGUNA | Pernyataan pengguna 5 Oktober 2026 | Menentukan target kompatibilitas sekarang |
| PHP aktif web dan CLI | PERLU VERIFIKASI | Versi lengkap web/CLI, pilihan handler | Sebelum Phase 1 berakhir; jangan menganggap pilihan maksimum sudah aktif |
| Patch PHP 8.3 | PERLU VERIFIKASI | Nomor patch dan kebijakan update provider | Sebelum lock dependency runtime; sebelum deployment |
| Ekstensi PHP | PERLU VERIFIKASI | Daftar ekstensi web/CLI dibanding composer/platform requirements | Phase 0–1; termasuk PDO MySQL, mbstring, XML/DOM, cURL, fileinfo, openssl dan kebutuhan image driver |
| Database | PERLU VERIFIKASI | MySQL/MariaDB, versi, charset/collation, hak migration/backup | Sebelum Phase 2 schema; CI wajib menguji engine/versi tujuan |
| Document root / rewrite | PERLU VERIFIKASI | Domain menunjuk Laravel public; rewrite/routing teruji | Sebelum keputusan deployment final |
| SSH / Terminal / SFTP | PERLU VERIFIKASI | Metode upload dan eksekusi perintah yang benar-benar tersedia | Sebelum Phase 9; upload file saja belum membuktikan migration bisa dijalankan |
| Composer | PERLU VERIFIKASI | Versi atau keputusan vendor dibundel dalam artifact | Sebelum Phase 9 |
| Cron / worker | PERLU VERIFIKASI | Interval minimum, CLI PHP, durasi proses, larangan daemon | Sebelum Phase 5 notifikasi dan Phase 9 packaging |
| Symlink | PERLU VERIFIKASI | Dukungan current release dan public storage link | Sebelum Phase 9; fallback di dokumen 09 |
| SMTP | PERLU VERIFIKASI | Endpoint, TLS, batas kirim dan email uji tanpa membagikan password | Sebelum staging forms/reset password |
| Storage | PERLU VERIFIKASI | Kuota, inode, izin tulis, akses backup di luar webroot | Sebelum staging upload/backup |
| Batas upload | PERLU VERIFIKASI | PHP/web-server post/upload limit dan timeout | Sebelum staging; harus mendukung kebijakan file dokumen 06 |
| HTTPS/domain/subdomain | PERLU VERIFIKASI | Domain produksi, staging, sertifikat, kontrol akses | Sebelum Phase 11 |
| Backup/restore provider | PERLU VERIFIKASI | Cara dump/restore dan lokasi salinan terpisah | Sebelum Phase 11 restore drill |
| Fitur GitHub repository | PERLU VERIFIKASI | Required checks, proteksi branch/tag, environment approval sesuai paket repo | Sebelum Phase 8–9; jangan mengklaim enforcement bila hanya prosedur manual |

Jika database hosting berbeda dari target lokal, keputusan penggantian engine/versi dicatat sebelum schema stabil. PostgreSQL atau MariaDB tidak otomatis dianggap setara dengan MySQL. Tidak mengubah hosting atau membeli layanan tanpa instruksi terpisah.

## 4. Kontrak versi

- Composer root PHP constraint: `~8.3.0` untuk membatasi runtime proyek pada seri 8.3. Ini bukan perintah memasang patch 8.3.0.
- Pilih patch PHP 8.3 yang masih dipelihara provider; samakan image lokal/CI dengan target yang diverifikasi. Jangan mengunci patch lama yang rentan hanya untuk kesamaan versi.
- Bila `config.platform.php` digunakan, nilainya mengikuti patch minimum target yang **sudah diverifikasi**, bukan angka buatan. Tetap jalankan `composer check-platform-reqs --no-dev` pada runtime tujuan; simulasi platform bukan bukti lingkungan.
- Commit `composer.lock` dan `package-lock.json`. Gunakan `composer install` dan `npm ci` untuk reproduksi.
- Pin patch/image digest yang benar-benar diuji pada Phase 1, dan perbarui melalui PR. Dokumen ini mengunci lini versi; belum menyediakan lockfile atau image hasil implementasi.
- React, Inertia, Vite, Tailwind, dan UI dependencies dipilih sebagai satu kombinasi stabil yang lulus build/test. Tidak mengarang versi yang belum diuji.
- PHP 8.3 berada pada masa dukungan keamanan; siapkan review upgrade hosting sebelum dukungan keamanannya berakhir. Jangan menaikkan persyaratan PHP diam-diam dalam PR.

## 5. Sumber dan verifikasi

Diakses 5 Oktober 2026. Sumber ini memverifikasi kemampuan teknologi, bukan akun hosting pengguna.

- [Laravel release/support policy](https://laravel.com/framework/docs/releases): Laravel 13 mendukung PHP 8.3–8.5.
- [PHP supported versions](https://www.php.net/supported-versions.php): dukungan keamanan seri 8.3 hingga 31 Desember 2027.
- [Laravel deployment](https://laravel.com/framework/docs/deployment): document root public, runtime requirements, debug configuration.
- [Node.js release schedule](https://github.com/nodejs/Release): seri 24 LTS.
- [MySQL 8.4 release model](https://dev.mysql.com/doc/refman/8.4/en/mysql-releases.html): seri LTS.
- [Laravel Vite](https://laravel.com/framework/docs/vite): konfigurasi entry point dan integrasi Blade/React.
- [WCAG non-text contrast](https://www.w3.org/WAI/WCAG22/Understanding/non-text-contrast.html) dan [text contrast](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html).
- [OWASP authorization](https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html), [file upload](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html), [CSV injection](https://community.owasp.org/attacks/CSV_Injection).

## 6. Kendali perubahan

PRD mengatur perilaku produk; dokumen 06 mengatur kontrak data/domain; Architecture mengatur implementasi; AGENTS mengatur proses kontribusi. Bila berbeda, catat konflik dan perbarui seluruh dokumen terdampak, tanpa memilih aturan bisnis baru secara diam-diam. Explicit user/task instruction tetap menjadi acuan tertinggi.
