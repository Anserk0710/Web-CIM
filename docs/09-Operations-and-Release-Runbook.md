# Operations, Release & Recovery Runbook

**Versi:** 4.0 · **Tanggal:** 5 Oktober 2026  
**Status:** prosedur target; rehearsal belum dijalankan. Perintah contoh baru digunakan setelah repository aplikasi dan capability hosting tersedia.  
**Acuan:** [Matriks Hosting](00-Decisions-and-Hosting-Matrix.md), [Architecture](05-Architecture.md), [Acceptance](08-Acceptance-and-Traceability.md).

## 1. Batas lingkungan

| Environment | Runtime / data | Email / akses |
|---|---|---|
| Local Docker | PHP 8.3, DB target lokal, data sintetis, storage lokal persistent | Mailpit; development accounts terpisah |
| CI | PHP 8.3, ephemeral DB engine/versi target, fixture deterministik | Fake/Mailpit; tidak ada production secret |
| kind local | Image release PHP 8.3, non-production Secret, DB PVC | Mailpit/local mail sink; hanya latihan |
| Staging | Mendekati PHP/DB/web-server/capability produksi; DB/storage/APP_KEY sendiri | Batasi akses + noindex; mail sink/recipient allowlist; tidak memakai data pelamar produksi |
| Production | cPanel PHP **8.3.x**, actual DB/capability terverifikasi, private storage persistent | HTTPS, SMTP nyata, APP_DEBUG=false |

Non-production menampilkan environment badge di CMS. Isolasi menggunakan direktori/storage/cookie/session domain yang berbeda; bukan hanya nama DB. Robots/noindex bukan kontrol akses; staging juga dibatasi dengan mekanisme autentikasi/akses server yang teruji.

## 2. Release artifact dan identitas

Setiap release memetakan commit SHA → tag → artifact checksum SHA-256 → container image digest. `latest` bukan identitas deployment.

Artifact cPanel berisi source aplikasi yang diperlukan, `vendor` produksi dari lockfile, `public/build` beserta manifest, migration dan script release yang disetujui. Exclude `.env`, Git history, node_modules, development fixtures/passwords, private/public user uploads, log, runtime sessions/cache dan file `public/hot`.

Baseline membundel vendor dalam artifact agar hosting tanpa Composer tetap dapat memakai output yang sama. Builder memakai PHP 8.3 dan kebutuhan platform tujuan terverifikasi. Composer install `--no-dev --prefer-dist --optimize-autoloader` tidak menggantikan platform check pada target. Jangan memakai `--ignore-platform-reqs` untuk meloloskan release.

Assets dan vendor tidak dibangun ulang setelah staging acceptance. Runtime config/route/view cache yang membutuhkan environment dibuat pada target; jangan mengirim cache config dengan APP_KEY/DB/mail CI. Image GHCR adalah output terpisah dari commit yang sama untuk belajar/portabilitas; image tidak dianggap sebagai artifact cPanel yang identik byte.

Release manifest mencatat versi runtime/package manager yang diuji, hashes, migration list, release notes dan known operational limitations. Tidak mencantumkan secret.

## 3. Prasyarat deployment

- [ ] PHP web/CLI 8.3, ekstensi dan DB sesuai matriks.
- [ ] Cara menjalankan migration, cache command dan smoke test sudah berhasil di staging.
- [ ] Root web mengarah ke `public`; project root/private storage tidak exposed.
- [ ] Upload melalui SSH/SFTP/metode provider yang aman dan teruji.
- [ ] APP_KEY tetap milik environment; tidak dibuat ulang setiap deploy.
- [ ] Storage persistent/.env berada di luar release yang akan diganti.
- [ ] Cron/worker/email executor diketahui dan diuji.
- [ ] Backup terbaru dapat dipulihkan; recovery secrets tersedia bagi operator berwenang.
- [ ] E2E critical/release checks dan content approval tercatat untuk commit yang sama.
- [ ] Approval production dan branch/tag protection sesuai kemampuan GitHub aktual; jika tidak tersedia, release manual terkontrol dicatat sebagai fallback, tidak disebut enforced approval otomatis.

SFTP saja tidak membuktikan artisan bisa dijalankan. Jika tidak ada metode CLI privat yang teruji, deployment diblokir sampai provider memberi metode yang sesuai. Jangan membuat route publik `/migrate`, `/artisan`, `/key-generate` sebagai jalan pintas.

## 4. Urutan deployment yang berlaku untuk seluruh dokumen

1. Pilih release/tag yang lolos checks dan approval; catat hash artifact.
2. Verifikasi backup DB, upload, konfigurasi pemulihan, dan release sebelumnya. Aktifkan maintenance window jika strategi non-atomic atau migration membutuhkannya.
3. Upload/extract artifact ke direktori release baru; verifikasi checksum dan file inventory.
4. Hubungkan konfigurasi environment dan storage persistent. Pastikan izin minimum yang diperlukan pada storage/bootstrap cache; bukan chmod 777.
5. Jalankan platform check menggunakan **PHP 8.3 CLI target**; bila Composer binary tidak ada, gunakan pemeriksaan platform yang dibundel/teruji dan validasi startup sebelum aktivasi. Tidak memasang dependency baru di tahap ini.
6. Jalankan migration sekali dari release baru (`php artisan migrate --force` setelah rehearsal). Gunakan lock deployment agar dua release tidak bermigrasi bersamaan. Migration destructive tidak disisipkan tanpa rencana data recovery.
7. Buat/refresh cache environment yang sesuai. Jalankan preflight boot dan koneksi DB pada release baru.
8. Aktifkan release (atomic current symlink jika didukung, atau prosedur maintenance fallback). Reload/invalidate opcode cache jika dibutuhkan metode provider; restart worker agar tidak menjalankan kode release lama.
9. Jalankan **post-activation smoke test pada URL yang benar-benar aktif**. Preflight sebelum aktivasi tidak menggantikan langkah ini.
10. Jika lolos, buka kembali trafik/akhiri maintenance dan monitor. Jika gagal, jalankan rollback di bagian 6.

Urutan ini menjadi rujukan tunggal; roadmap/architecture/handbook harus menunjuk urutan yang sama.

## 5. Layout hosting dan fallback

Preferred: `releases/<release-id>`, `current` menunjuk release aktif, `shared/.env`, `shared/storage`. Web root adalah `current/public`; storage publik melalui link hanya ke disk public. Storage private tidak ikut link publik.

Jika symlink tidak didukung: gunakan direktori aplikasi tetap di luar web root + direktori public yang dipetakan sesuai fitur provider, lakukan maintenance, backup file aplikasi, ganti source/assets secara terkendali, preserve .env/storage, lalu smoke sebelum membuka trafik. Catat bahwa ini bukan deployment zero-downtime. Jangan memindahkan seluruh project root ke direktori publik agar routing bekerja.

Public storage link dapat diganti controlled public media route jika provider tidak mendukung symlink. Controller tidak boleh melayani disk private melalui route itu. File upload/media URLs diuji di staging karena Apache/provider berbeda dari Nginx lokal.

## 6. Rollback

Trigger: boot gagal, auth rusak, private data terpapar, request utama gagal, migration/config membuat aplikasi tidak dapat melayani kebutuhan kritis.

1. Hentikan release berikutnya; catat release/tag dan error. Aktifkan maintenance bila diperlukan untuk integritas data.
2. Jika DB masih kompatibel dengan release sebelumnya, aktifkan artifact sebelumnya; restore config binding tanpa mengganti APP_KEY/storage; restart worker/opcode cache sesuai provider.
3. Jalankan smoke test release lama sebelum membuka trafik.
4. Jika schema/data sudah tidak kompatibel, jangan otomatis menjalankan `migrate:rollback`. Tentukan restore DB+file snapshot yang konsisten atau forward fix dengan penanggung jawab teknis, dan catat dampak data baru sejak backup.
5. Reapply deletion ledger bila restore berpotensi menghidupkan kembali data pribadi yang sudah dihapus; validasi upload-record consistency.
6. Catat incident dan recovery duration. Jangan menyebut rollback teruji hanya karena backup file tersedia.

Schema memakai expand/backfill/contract: tambah field compatible, deploy pembaca/penulis baru, backfill, baru hapus struktur lama pada release berbeda bila memang diperlukan.

## 7. Smoke test release aktif

- Home, Product published, Activity dan Career terlihat benar.
- Draft/archive tidak terbuka melalui URL langsung atau sitemap.
- Login/logout CMS; akun nonaktif ditolak; satu akun berizin terbatas tidak mendapat akses admin lain.
- Inquiry dan application sintetis berhasil pada staging; production memakai test yang disepakati, diberi penanda dan dibersihkan, tanpa mengirim email ke orang yang tidak dituju.
- PDF CV hanya terunduh oleh akun berizin; private URL ditolak.
- Gambar/brochure/upload tersedia, persistent data tidak hilang.
- Reset mail/queue executor/cron diverifikasi dengan akun/penerima uji yang dikendalikan.
- HTTPS, APP_DEBUG=false, `.env`/log/private storage inaccessible.
- Release identifier aktif cocok dengan tag/hash yang diharapkan.

## 8. Queue dan scheduler

Driver database. Local Compose menyediakan worker/scheduler service jika jobs digunakan. Produksi memakai long-running worker hanya bila provider mendukung pengelolaan proses; otherwise cron menjalankan worker berumur terbatas, dengan overlap lock dan timeout yang dibuktikan.

Target awal polling cron satu menit jika diizinkan provider; interval aktual harus tertulis. Worker tidak memulai proses tak terbatas pada setiap tick. Maksimum percobaan awal 3 dengan backoff; failure tercatat dan operator punya prosedur retry. Job idempotent; jangan mengirim notification ganda karena retry atau menulis ulang application/inquiry.

Jika worker/cron tidak tersedia, gunakan pengiriman sinkron dengan timeout terukur dan pencatatan notification failure untuk retry manual. Fallback ini harus diuji, terlihat oleh operator, dan tidak mengubah sukses persistence menjadi hilangnya data. Retention/backup tasks juga memerlukan executor; tugas manual sementara mempunyai jadwal/owner eksplisit, tidak disebut otomatis.

Scheduler dapat menutup status Career/E-Catalog dan membersihkan data, tetapi query-time deadline tetap authoritative. Observasi last successful run, pending/failed jobs, ruang disk dan hasil backup. Admin operasional yang diberi wewenang memperoleh indikator kegagalan; dashboard umum tidak membocorkan data pelamar.

## 9. Backup dan pemulihan

| Parameter | Baseline |
|---|---|
| Objek | DB, public uploads, private CV, konfigurasi pemulihan/APP_KEY secara terpisah dan aman |
| Jadwal | Harian dan sebelum deployment berisiko |
| Retensi | 30 hari; akses terbatas dan salinan terpisah dari akun/server produksi |
| Perlindungan | Backup dienkripsi saat disimpan/ditransfer; kunci dan akses dipisahkan dari archive bila memungkinkan |
| RPO | Target kehilangan data maksimal 24 jam |
| RTO | Target pemulihan maksimal 4 jam |
| Restore drill | Sebelum go-live, bulanan, dan setelah perubahan strategi besar |

Target bukan jaminan kemampuan saat ini. Catat ukuran DB/file, throughput transfer, durasi restore, timestamp backup terakhir, dan hasil verifikasi. Jika gagal memenuhi target, perbaiki metode/kapasitas atau ajukan perubahan target eksplisit sebelum go-live.

Snapshot DB dan upload harus konsisten. Gunakan snapshot provider teruji atau maintenance singkat saat backup konsisten bila tidak ada metode online yang sesuai. `mysqldump --single-transaction` tidak sendirian menjamin keselarasan file upload dengan database; dokumentasikan koordinasinya.

Restore dilakukan di environment terisolasi, mail dinonaktifkan/diarahkan ke sink, lalu verifikasi jumlah record, sample checksum file, app key, izin dan private download. Backup terenkripsi yang tidak bisa dibuka karena kunci hilang dianggap gagal.

## 10. Retensi data operasional

- Kandidat REJECTED: 6 bulan kalender setelah keputusan akhir, sesuai dokumen 06. HR menangani kandidat yang belum mendapat keputusan, bukan membiarkan data aktif tanpa evaluasi.
- Kandidat HIRED: HR mencatat handover ke sistem administrasi karyawan. Baseline salinan CV/identitas di CMS dibersihkan 30 hari setelah handover; sampai handover selesai, tampilkan tindak lanjut HR dan tinjau bulanan. Tidak mengklaim CMS menjadi sistem data karyawan.
- Inquiry RESOLVED: identitas/notes pribadi dibersihkan 12 bulan setelah resolved_at; SPAM 30 hari setelah penandaan. Usulan implementasi ini dapat diubah dengan keputusan bisnis tercatat.
- Audit: 12 bulan untuk metadata minimal; jangan menyimpan nama/email/CV body di changes. IP/user-agent dipangkas setelah 90 hari. Application log default 30 hari, tanpa request body/private data.
- Backup: data yang telah dihapus dapat tetap ada pada snapshot sampai masa 30 hari habis; batasi akses dan cegah penggunaan ulang. Catat deletion ledger minimal (entity ID, timestamp, kategori penghapusan tanpa identitas), terpisah dari snapshot restore, agar restore menerapkan kembali penghapusan.
- Consent wording bersifat versioned. Retensi di sini adalah kebijakan desain operasional yang dipilih, bukan pernyataan otomatis memenuhi seluruh kewajiban hukum organisasi.

## 11. Konten lama dan cutover

Inventaris URL/media lama, mapping source→target, cleanup/normalisasi, import idempotent dengan laporan error, checksum/media sampling, validasi jumlah dan spesifikasi oleh pemilik konten. Jangan membuat spesifikasi alat kesehatan yang tidak ada sumbernya.

Rehearsal import di staging. Pada cutover tentukan content freeze/delta import agar perubahan konten selama pembangunan tidak hilang. Buat redirect 301 hanya untuk mapping valid, periksa canonical/sitemap dan kontak aktual. Jangan mengubah domain/DNS sebelum release aktif dan rencana rollback domain siap.

## 12. Tanggung jawab dan incident

Pelaksana IT/developer: deployment, runtime, queue, backup, restore, log dan secret. Pemilik konten: validasi Product/Company/E-Catalog/Activity. HR: lowongan, seleksi, consent wording, handover dan retensi pelamar. Penanggung jawab Inquiry: ditetapkan perusahaan saat kickoff.

Catat nama/on-call contact sebelum go-live. Runbook insiden minimal: deteksi → batasi dampak → catat bukti tanpa menyebarkan data pribadi → pemulihan/rollback → verifikasi → pemberitahuan internal sesuai kewenangan → perbaikan dan regression test. Paket ini tidak mengirim pesan atau mengubah akun hosting.
