<?php

return [
    // Initial navigation copy only. Saved CMS sections, including disabled ones,
    // take precedence. Never supply sample products, metrics, or testimonials.
    'sections' => [
        'capabilities' => [
            'heading' => 'Informasi yang tepat. Langkah yang lebih jelas.',
            'intro' => 'Kenali Cahya Intan Medika. Jelajahi produk, temukan informasi pengadaan, dan terhubung dengan tim kami dalam satu tempat.',
            'items' => [
                ['title' => 'Jelajahi produk', 'body' => 'Temukan kategori, spesifikasi, dan informasi produk yang Anda butuhkan.'],
                ['title' => 'Informasi pengadaan', 'body' => 'Akses E-Katalog untuk menelusuri informasi produk dan pengadaan.'],
                ['title' => 'Terhubung dengan CIM', 'body' => 'Kenali perusahaan kami dan sampaikan kebutuhan Anda melalui halaman kontak.'],
            ],
        ],
        'lifecycle' => [
            'heading' => 'Mulai dari kebutuhan Anda.',
            'items' => [
                ['title' => 'Jelajahi', 'body' => 'Mulai dengan menelusuri portofolio dan kategori produk kami.'],
                ['title' => 'Pelajari', 'body' => 'Baca informasi produk dan dokumen yang tersedia untuk memahami pilihan Anda.'],
                ['title' => 'Diskusikan', 'body' => 'Sampaikan kebutuhan dan pertanyaan Anda kepada tim Cahya Intan Medika.'],
            ],
        ],
        'featured_products' => ['heading' => 'Temukan pilihan Anda.', 'product_ids' => []],
        'activity_preview' => ['heading' => 'Dari keseharian kami.', 'activity_ids' => []],
        'cta' => [
            'heading' => 'Mari memulai percakapan.',
            'description' => 'Punya pertanyaan tentang produk atau perusahaan kami? Tim CIM siap menerima pesan Anda.',
            'primary_cta_label' => 'Hubungi Kami',
            'primary_cta_url' => '/contact',
        ],
    ],
];
