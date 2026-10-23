// path: resources/js/lib/humanize.ts

/**
 * Humanizer utilities for converting backend, database, and system identifiers
 * into natural, polite, and professional Indonesian language terms.
 */

export const STATUS_LABELS: Record<string, string> = {
    // Content & Publication Statuses
    DRAFT: 'Draf',
    PUBLISHED: 'Diterbitkan',
    ARCHIVED: 'Diarsipkan',
    OPEN: 'Dibuka',
    CLOSED: 'Ditutup',

    // Career Application Statuses
    NEW: 'Lamaran Baru',
    REVIEWING: 'Sedang Ditinjau',
    SHORTLISTED: 'Lolos Berkas',
    INTERVIEW: 'Tahap Wawancara',
    REJECTED: 'Belum Sesuai',
    HIRED: 'Diterima Bekerja',

    // Inquiry Statuses
    IN_PROGRESS: 'Sedang Ditindaklanjuti',
    RESOLVED: 'Selesai',
    SPAM: 'Spam / Tidak Relevan',

    // Notification Outbox Statuses
    PENDING: 'Menunggu Antrean',
    PROCESSING: 'Sedang Dikirim',
    SENT: 'Berhasil Terkirim',
    FAILED: 'Gagal Dikirim',

    // Audit Log Results
    SUCCESS: 'Berhasil',
    DENIED: 'Akses Ditolak',
    FAILURE: 'Gagal',

    // E-Catalog Availability
    AVAILABLE: 'Tersedia',
    UNAVAILABLE: 'Tidak Tersedia',
    CONTACT_US: 'Hubungi Kami',

    // General
    ACTIVE: 'Aktif',
    INACTIVE: 'Nonaktif',
};

export const INQUIRY_TYPE_LABELS: Record<string, string> = {
    PRODUCT: 'Pertanyaan Produk',
    ECATALOG: 'Pertanyaan E-Katalog',
    TECHNICAL_SUPPORT: 'Dukungan Teknis',
    PARTNERSHIP: 'Kerja Sama / Kemitraan',
    GENERAL: 'Pertanyaan Umum',
};

export const EMPLOYMENT_TYPE_LABELS: Record<string, string> = {
    FULL_TIME: 'Purna Waktu (Full Time)',
    PART_TIME: 'Paruh Waktu (Part Time)',
    CONTRACT: 'Kontrak',
    INTERNSHIP: 'Magang',
};

export const NOTIFICATION_EVENT_LABELS: Record<string, string> = {
    'inquiry.submitted': 'Pesan Masuk Baru dari Situs',
    'application.submitted': 'Lamaran Pekerjaan Baru Masuk',
    'application.status_changed': 'Perubahan Status Lamaran Kerja',
};

export const PAGE_SECTION_LABELS: Record<string, string> = {
    hero: 'Banner Utama (Hero)',
    metrics: 'Statistik & Angka Kinerja',
    capabilities: 'Layanan & Keunggulan',
    lifecycle: 'Tahapan Layanan / Alur Kerja',
    featured_products: 'Produk Unggulan',
    activity_preview: 'Cuplikan Kegiatan Terbaru',
    coverage: 'Wilayah Distribusi & Jangkauan',
    cta: 'Panggilan Aksi (Hubungi Kami)',
    story: 'Kisah & Profil Perusahaan',
    vision_mission: 'Visi dan Misi',
    values: 'Nilai-Nilai Utama Perusahaan',
    milestones: 'Jejak Langkah & Sejarah',
    career_branding: 'Budaya Kerja & Manfaat Karir',
    contact_intro: 'Informasi Layanan Kontak',
    policy: 'Isi Dokumen Kebijakan Privasi',
};

export const ACTION_LABELS: Record<string, string> = {
    'product.create': 'Membuat Produk Baru',
    'product.update': 'Memperbarui Data Produk',
    'product.delete': 'Menghapus Produk',
    'product.publish': 'Menerbitkan Produk ke Situs',
    'product.archive': 'Mengarsipkan Produk',
    'product.restore': 'Memulihkan Produk',
    'taxonomy.create': 'Menambahkan Kategori / Merek Baru',
    'taxonomy.update': 'Memperbarui Kategori / Merek',
    'taxonomy.delete': 'Menghapus Kategori / Merek',
    'career.create': 'Membuka Lowongan Kerja Baru',
    'career.update': 'Memperbarui Data Lowongan Kerja',
    'career.delete': 'Menghapus Lowongan Kerja',
    'career.publish': 'Menerbitkan Lowongan Kerja',
    'career.archive': 'Menutup Lowongan Kerja',
    'application.status_update': 'Memperbarui Status Lamaran Kerja',
    'application.cv_download': 'Mengunduh Dokumen CV Pelamar',
    'application.handover': 'Menyerahkan Lamaran ke Tim Terkait',
    'inquiry.status_update': 'Memperbarui Status Pesan Masuk',
    'inquiry.note_add': 'Menambahkan Catatan Internal',
    'inquiry.assign': 'Menugaskan Penanganan Pesan',
    'activity.create': 'Membuat Berita / Kegiatan Baru',
    'activity.update': 'Memperbarui Berita / Kegiatan',
    'activity.delete': 'Menghapus Berita / Kegiatan',
    'activity.publish': 'Menerbitkan Berita / Kegiatan',
    'activity.archive': 'Mengarsipkan Berita / Kegiatan',
    'media.upload': 'Mengunggah Berkas Media',
    'media.delete': 'Menghapus Berkas Media',
    'role.update_permissions': 'Memperbarui Hak Akses Peran',
    'role.assign': 'Menetapkan Peran Pengguna',
    'user.create': 'Menambahkan Pengguna Baru',
    'user.update': 'Memperbarui Data Pengguna',
    'user.toggle_active': 'Mengubah Status Aktif Pengguna',
    'content.update': 'Memperbarui Konten Halaman',
    'settings.update': 'Memperbarui Pengaturan Sistem',
    'notification.retry': 'Mengirim Ulang Notifikasi Email',
    'auth.login': 'Masuk ke Sistem (Login)',
    'auth.logout': 'Keluar dari Sistem (Logout)',
};

export const ENTITY_LABELS: Record<string, string> = {
    Product: 'Produk',
    ProductCategory: 'Kategori Produk',
    ProductBrand: 'Merek Produk',
    ProductType: 'Tipe Produk',
    Career: 'Lowongan Kerja',
    CareerApplication: 'Lamaran Pekerjaan',
    Inquiry: 'Pesan / Pertanyaan Masuk',
    Activity: 'Kegiatan / Berita',
    ActivityCategory: 'Kategori Kegiatan',
    User: 'Pengguna',
    Role: 'Peran Hak Akses',
    Media: 'Berkas Media',
    PageContent: 'Konten Halaman',
    Setting: 'Pengaturan Sistem',
    NotificationOutbox: 'Antrean Notifikasi',
};

export const SECTION_FIELD_LABELS: Record<string, string> = {
    eyebrow: 'Teks Pengantar Kecil (Eyebrow)',
    heading: 'Judul Utama Bagian',
    title: 'Judul Bagian',
    description: 'Deskripsi / Penjelasan',
    intro: 'Kalimat Pengantar',
    body: 'Isi Konten / Teks',
    image_id: 'ID Berkas Gambar',
    image_alt: 'Teks Alternatif Gambar (Alt)',
    primary_cta_label: 'Teks Tombol Utama',
    primary_cta_url: 'Tautan Tombol Utama',
    secondary_cta_label: 'Teks Tombol Kedua',
    secondary_cta_url: 'Tautan Tombol Kedua',
    items: 'Daftar Butir Data (Format JSON)',
    product_ids: 'ID Produk Pilihan (Format JSON)',
    activity_ids: 'ID Kegiatan Pilihan (Format JSON)',
    areas: 'Daftar Wilayah (Format JSON)',
    benefits: 'Daftar Fasilitas / Manfaat (Format JSON)',
    culture: 'Budaya Lingkungan Kerja',
    growth: 'Peluang Pengembangan Diri',
    vision: 'Pernyataan Visi',
    mission: 'Pernyataan Misi',
    version: 'Nomor Versi Dokumen',
};

/**
 * Returns a human-friendly label for any status string.
 */
export function humanizeStatus(status: string | null | undefined): string {
    if (!status) {
        return '—';
    }
    const upper = status.trim().toUpperCase();
    if (STATUS_LABELS[upper]) {
        return STATUS_LABELS[upper];
    }
    return status.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/**
 * Returns a human-friendly label for inquiry types.
 */
export function humanizeInquiryType(type: string | null | undefined): string {
    if (!type) {
        return '—';
    }
    const upper = type.trim().toUpperCase();
    return INQUIRY_TYPE_LABELS[upper] ?? humanizeStatus(type);
}

/**
 * Returns a human-friendly label for employment types.
 */
export function humanizeEmploymentType(type: string | null | undefined): string {
    if (!type) {
        return '—';
    }
    const upper = type.trim().toUpperCase();
    return EMPLOYMENT_TYPE_LABELS[upper] ?? humanizeStatus(type);
}

/**
 * Returns a human-friendly label for availability.
 */
export function humanizeAvailability(val: string | null | undefined): string {
    if (!val) {
        return '—';
    }
    const upper = val.trim().toUpperCase();
    return STATUS_LABELS[upper] ?? humanizeStatus(val);
}

/**
 * Returns a human-friendly label for notification event types.
 */
export function humanizeEvent(event: string | null | undefined): string {
    if (!event) {
        return '—';
    }
    return NOTIFICATION_EVENT_LABELS[event] ?? event.replace(/[._]/g, ' ');
}

/**
 * Returns a human-friendly label for page section keys.
 */
export function humanizeSectionKey(key: string): string {
    return PAGE_SECTION_LABELS[key] ?? key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/**
 * Returns a human-friendly label for section data fields.
 */
export function humanizeFieldKey(key: string): string {
    return SECTION_FIELD_LABELS[key] ?? key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/**
 * Returns a human-friendly label for audit actions.
 */
export function humanizeAction(action: string | null | undefined): string {
    if (!action) {
        return '—';
    }
    return ACTION_LABELS[action] ?? action.replace(/[._]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/**
 * Returns a human-friendly label for entity names.
 */
export function humanizeEntity(entity: string | null | undefined): string {
    if (!entity) {
        return '—';
    }
    // Handle namespaced model names like App\Models\Product or simple Product
    const simple = entity.split('\\').pop() || entity;
    return ENTITY_LABELS[simple] ?? simple;
}

export const PERMISSION_LABELS: Record<string, string> = {
    'product.create': 'Membuat Produk Baru',
    'product.update': 'Mengubah Data Produk',
    'product.delete': 'Menghapus Produk',
    'product.publish': 'Menerbitkan / Mengarsipkan Produk',
    'taxonomy.manage': 'Mengelola Kategori & Merek Produk',
    'career.create': 'Membuka Lowongan Kerja Baru',
    'career.update': 'Mengubah Lowongan Kerja',
    'career.delete': 'Menghapus Lowongan Kerja',
    'career.publish': 'Menerbitkan / Menutup Lowongan',
    'application.read': 'Melihat Berkas Lamaran Masuk',
    'application.status': 'Mengubah Status Seleksi Lamaran',
    'application.download_cv': 'Mengunduh Dokumen CV Pelamar',
    'application.export': 'Mengekspor Rekapitulasi Lamaran',
    'inquiry.read': 'Melihat Pesan & Pertanyaan Masuk',
    'inquiry.status': 'Mengubah Status Penanganan Pesan',
    'inquiry.note': 'Menambahkan Catatan Internal Pesan',
    'inquiry.assign': 'Menugaskan Penanganan Pesan Masuk',
    'activity.create': 'Membuat Berita / Kegiatan Baru',
    'activity.update': 'Mengubah Berita / Kegiatan',
    'activity.delete': 'Menghapus Berita / Kegiatan',
    'activity.publish': 'Menerbitkan Berita / Kegiatan',
    'media.upload': 'Mengunggah Berkas Media / Gambar',
    'media.delete': 'Menghapus Berkas Media',
    'user.manage': 'Mengelola Akun Pengguna',
    'role.manage': 'Mengatur Peran & Wewenang Akses',
    'settings.manage': 'Mengatur Konten Halaman & Sistem',
    'audit.read': 'Melihat Catatan Aktivitas Sistem (Audit)',
    'notification.manage': 'Mengelola Pengiriman Notifikasi & Email',
};

/**
 * Returns a human-friendly label for permission identifiers.
 */
export function humanizePermission(permission: string | null | undefined): string {
    if (!permission) {
        return '—';
    }
    return PERMISSION_LABELS[permission] ?? permission.replace(/[._]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/**
 * Formats date into readable Indonesian locale string.
 */
export function formatIndonesianDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }
    try {
        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: 'Asia/Makassar',
        }).format(new Date(value));
    } catch {
        return value;
    }
}

/**
 * Formats date and time into readable Indonesian locale string.
 */
export const formatDateTimeIndonesian = formatIndonesianDate;

/**
 * Returns a human-friendly label for audit results.
 */
export function humanizeAuditResult(result: string | null | undefined): string {
    return humanizeStatus(result);
}


