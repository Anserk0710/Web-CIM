{{-- path: resources/views/public/contact.blade.php --}}

@extends('layouts.public')

@section(
    'title',
    $page?->seo_title ?: ($hero['title'] ?? 'Contact')
)

@section(
    'meta_description',
    $page?->seo_description
        ?: ($hero['description'] ?? 'Hubungi PT Cahya Intan Medika.')
)

@section('canonical', route('contact'))

@section('content')
    <section class="bg-cim-ice">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-cim-blue">
                Contact
            </p>

            <h1 class="mt-3 max-w-3xl text-4xl font-bold tracking-tight text-cim-navy sm:text-5xl">
                {{ $hero['title'] ?? 'Let’s discuss your needs.' }}
            </h1>

            <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600">
                {{ $hero['description'] ?? 'Hubungi tim kami untuk informasi produk, E-Catalog, dukungan teknis, dan kerja sama.' }}
            </p>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.8fr)] lg:px-8">
        <div>
            @if(session('success'))
                <div
                    role="status"
                    class="mb-6 rounded-xl border border-green-300 bg-green-50 p-4 text-sm text-green-950"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    role="alert"
                    class="mb-6 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-900"
                >
                    <p class="font-semibold">
                        Pesan belum berhasil dikirim.
                    </p>

                    <p class="mt-1">
                        Silakan periksa kembali data yang dimasukkan di bawah ini.
                    </p>
                </div>
            @endif

            <div id="contact-form" class="rounded-2xl border bg-background p-6 sm:p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-cim-navy">
                    Kirim Pesan & Pertanyaan <span class="sr-only">Send an inquiry</span>
                </h2>

                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Kolom bertanda wajib harus diisi. Pilih jenis kebutuhan Anda agar pesan langsung diarahkan ke tim yang tepat.
                </p>

                @if(!$privacyReady)
                    <div
                        role="alert"
                        class="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950"
                    >
                        Formulir sementara tidak tersedia karena Privacy Notice belum dikonfigurasi.
                        Silakan hubungi perusahaan melalui informasi kontak di samping.
                    </div>
                @else
                    <form
                        action="{{ route('contact.inquiries.store') }}"
                        method="POST"
                        data-inquiry-form
                        class="mt-6 space-y-5"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="privacy_notice_version"
                            value="{{ $privacyPolicy['version'] }}"
                        >

                        <div>
                            <label
                                for="type"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Jenis Pertanyaan / Kebutuhan
                            </label>

                            <select
                                id="type"
                                name="type"
                                required
                                data-inquiry-type
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                            >
                                @foreach([
                                    'GENERAL' => 'Pertanyaan Umum',
                                    'PRODUCT' => 'Konsultasi & Penawaran Produk',
                                    'ECATALOG' => 'Pengadaan Pemerintah / E-Katalog',
                                    'TECHNICAL_SUPPORT' => 'Layanan & Bantuan Teknis',
                                    'PARTNERSHIP' => 'Kemitraan & Kerja Sama Bisnis',
                                ] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old('type', $prefill['type']) === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('type')
                                <p class="mt-1 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div
                            data-product-field
                            hidden
                        >
                            <label
                                for="product_id"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Pilihan Produk Terkait
                            </label>

                            <select
                                id="product_id"
                                name="product_id"
                                data-product-select
                                disabled
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                            >
                                <option value="">
                                    Pilih produk medis...
                                </option>

                                @foreach($products as $product)
                                    <option
                                        value="{{ $product->id }}"
                                        @selected(
                                            (string) old(
                                                'product_id',
                                                $prefill['product_id']
                                            ) === (string) $product->id
                                        )
                                    >
                                        {{ $product->publishedRevision->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('product_id')
                                <p class="mt-1 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div
                            data-catalog-field
                            hidden
                        >
                            <label
                                for="ecatalog_entry_id"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Produk E-Katalog Terkait
                            </label>

                            <select
                                id="ecatalog_entry_id"
                                name="ecatalog_entry_id"
                                data-ecatalog-select
                                disabled
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                            >
                                <option value="">
                                    Pilih paket pengadaan E-Katalog...
                                </option>

                                @foreach($catalogs as $catalog)
                                    <option
                                        value="{{ $catalog->id }}"
                                        data-product-id="{{ $catalog->product_id }}"
                                        @selected(
                                            (string) old(
                                                'ecatalog_entry_id',
                                                $prefill['ecatalog_entry_id']
                                            ) === (string) $catalog->id
                                        )
                                    >
                                        {{ $catalog->catalog_code }}
                                        —
                                        {{ $catalog->procurement_title }}
                                    </option>
                                @endforeach
                            </select>

                            <input
                                type="hidden"
                                name="product_id"
                                data-ecatalog-product-id
                                disabled
                            >

                            @error('ecatalog_entry_id')
                                <p class="mt-1 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror

                            @error('product_id')
                                <p class="mt-1 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Nama Lengkap
                            </label>

                            <input
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                maxlength="150"
                                autocomplete="name"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                            >

                            @error('name')
                                <p class="mt-1 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Alamat Email
                            </label>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                maxlength="254"
                                autocomplete="email"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                            >

                            @error('email')
                                <p class="mt-1 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label
                                    for="phone"
                                    class="mb-2 block text-sm font-medium text-foreground"
                                >
                                    Nomor Telepon / WhatsApp (Opsional)
                                </label>

                                <input
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    maxlength="32"
                                    autocomplete="tel"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                                >

                                @error('phone')
                                    <p class="mt-1 text-sm text-red-700">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="organization"
                                    class="mb-2 block text-sm font-medium text-foreground"
                                >
                                    Nama Rumah Sakit / Instansi / Perusahaan (Opsional)
                                </label>

                                <input
                                    id="organization"
                                    name="organization"
                                    value="{{ old('organization') }}"
                                    maxlength="200"
                                    autocomplete="organization"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                                >

                                @error('organization')
                                    <p class="mt-1 text-sm text-red-700">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label
                                for="message"
                                class="mb-2 block text-sm font-medium text-foreground"
                            >
                                Isi Pesan atau Rincian Kebutuhan
                            </label>

                            <textarea
                                id="message"
                                name="message"
                                rows="6"
                                required
                                maxlength="5000"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm"
                            >{{ old('message') }}</textarea>

                            @error('message')
                                <p class="mt-1 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <section class="rounded-xl bg-muted/50 p-4">
                            <h3 class="text-sm font-semibold text-cim-navy">
                                Pemberitahuan Privasi Data
                            </h3>

                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                {{ $privacyPolicy['body'] }}
                            </p>

                            <p class="mt-3 text-xs text-muted-foreground">
                                Versi Dokumen: {{ $privacyPolicy['version'] }}
                            </p>

                            <a
                                href="{{ route('privacy') }}"
                                class="mt-2 inline-flex text-sm font-semibold text-cim-blue hover:text-cim-navy"
                            >
                                Baca Kebijakan Privasi Lengkap
                            </a>
                        </section>

                        @error('privacy_notice_version')
                            <p class="text-sm text-red-700">
                                {{ $message }}
                            </p>
                        @enderror

                        <div
                            class="hidden"
                            aria-hidden="true"
                        >
                            <label for="website">
                                Jangan isi kolom ini
                            </label>

                            <input
                                id="website"
                                name="website"
                                type="text"
                                tabindex="-1"
                                autocomplete="off"
                            >
                        </div>

                        <button
                            type="submit"
                            class="inline-flex rounded-lg bg-cim-blue px-5 py-3 font-semibold text-white hover:bg-cim-navy transition-colors"
                        >
                            Kirim Pesan Sekarang <span class="sr-only">Submit inquiry</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl border bg-background p-6 shadow-sm">
                <h2 class="text-xl font-bold text-cim-navy">
                    Informasi Kontak Resmi
                </h2>

                @if(!empty($contactSettings['email']))
                    <div class="mt-5">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Alamat Email
                        </p>

                        <a
                            href="mailto:{{ $contactSettings['email'] }}"
                            class="mt-1 inline-block break-all font-medium text-cim-blue hover:underline"
                        >
                            {{ $contactSettings['email'] }}
                        </a>
                    </div>
                @endif

                @if(!empty($contactSettings['phone']))
                    <div class="mt-5">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Nomor Telepon
                        </p>

                        <a
                            href="tel:{{ $contactSettings['phone'] }}"
                            class="mt-1 inline-block font-medium text-cim-blue hover:underline"
                        >
                            {{ $contactSettings['phone'] }}
                        </a>
                    </div>
                @endif

                @if(!empty($contactSettings['whatsapp']))
                    <div class="mt-5">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            WhatsApp Layanan
                        </p>

                        <a
                            href="https://wa.me/{{ preg_replace('/\D+/', '', $contactSettings['whatsapp']) }}"
                            rel="noopener noreferrer"
                            class="mt-1 inline-block font-medium text-cim-blue hover:underline"
                        >
                            Kirim Pesan via WhatsApp
                        </a>
                    </div>
                @endif
            </section>

            @if($offices->isNotEmpty())
                <section class="rounded-2xl border bg-background p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-cim-navy">
                        Kantor Operasional Kami
                    </h2>

                    <div class="mt-5 space-y-6">
                        @foreach($offices as $office)
                            <article>
                                <h3 class="font-semibold text-cim-navy">
                                    {{ $office->name }}

                                    @if($office->is_head_office)
                                        <span class="text-xs font-normal text-muted-foreground">
                                            (Kantor Pusat)
                                        </span>
                                    @endif
                                </h3>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                    {{ $office->address }}
                                </p>

                                @if($office->phone)
                                    <p class="mt-2 text-sm text-slate-700">
                                        {{ $office->phone }}
                                    </p>
                                @endif

                                @if($office->email)
                                    <a
                                        href="mailto:{{ $office->email }}"
                                        class="mt-1 inline-block break-all text-sm text-cim-blue hover:underline"
                                    >
                                        {{ $office->email }}
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </section>
@endsection

