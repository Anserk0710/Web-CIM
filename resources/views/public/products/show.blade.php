@extends('layouts.public')

@section(
    'title',
    $revision->seo_title ?: $revision->name . ' | PT Cahya Intan Medika'
)

@section(
    'meta_description',
    $revision->seo_description
    ?: ($revision->summary ?: $revision->name)
)

@if ($preview)
    @section('robots', 'noindex,nofollow')
@else
    @section(
        'canonical',
        route(
            'products.show',
            $product->public_slug
        )
    )
@endif

@if ($revision->ogMedia)
    @section(
        'og_image',
        $revision->ogMedia->url()
    )
@endif

@if (!$preview)
    @section('og_type', 'product')

    @php
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => route(
                'products.show',
                $product->public_slug,
            ).'#product',
            'name' => $revision->name,
            'description' => trim(
                strip_tags(
                    $revision->summary
                    ?: ($revision->description ?: $revision->name)
                )
            ),
            'url' => route(
                'products.show',
                $product->public_slug,
            ),
        ];

        $schemaImages = $revision->media
            ->where('role', $galleryRole)
            ->map(
                fn ($item) => $item->media?->url()
            )
            ->filter()
            ->values()
            ->all();

        if ($schemaImages !== []) {
            $productSchema['image'] = $schemaImages;
        }

        if ($revision->brand !== null) {
            $productSchema['brand'] = [
                '@type' => 'Brand',
                'name' => $revision->brand->name,
            ];
        }

        if ($revision->category !== null) {
            $productSchema['category'] =
                $revision->category->name;
        }

        $schemaProperties = $revision
            ->specificationGroups
            ->flatMap(
                fn ($group) => $group->items->map(
                    fn ($item) => [
                        '@type' => 'PropertyValue',
                        'name' => $group->name
                            .' - '.$item->label,
                        'value' => trim(
                            $item->value
                            .(
                                $item->unit
                                    ? ' '.$item->unit
                                    : ''
                            )
                        ),
                    ]
                )
            )
            ->values()
            ->all();

        if ($schemaProperties !== []) {
            $productSchema['additionalProperty'] =
                $schemaProperties;
        }
    @endphp

    @section('structured_data')
        <script type="application/ld+json">
            @json($productSchema)
        </script>
    @endsection
@endif

@section('content')
    @php
        $gallery = $revision
            ->media
            ->where(
                'role',
                $galleryRole
            )
            ->values();

        $brochure = $revision
            ->media
            ->firstWhere(
                'role',
                $brochureRole
            );
    @endphp

    @if ($preview)
        <div class="bg-amber-500/20 border-b border-amber-500/40 px-4 py-3 text-center text-xs font-mono font-bold tracking-wider text-amber-300">
            AUTHENTICATED PREVIEW — WORKING REVISION (BUKAN KONTEN PUBLIK LIVE)
        </div>
    @endif

    {{-- BREADCRUMB / TOP META --}}
    <section class="border-b border-white/10 bg-[#070a0f] pt-28 pb-6 sm:pt-36">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs font-mono text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-white">Produk</a>
                <span>/</span>
                <span class="text-[#00f0ff] truncate">{{ $revision->name }}</span>
            </nav>
        </div>
    </section>

    {{-- PRODUCT DOSSIER SHOWCASE --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12 lg:items-start">
            {{-- Media & Gallery Column (Col 7) --}}
            <div class="lg:col-span-7">
                <div class="relative aspect-[4/3] w-full overflow-hidden rounded-3xl border border-white/10 bg-[#0c121e]/90 shadow-2xl">
                    @if ($gallery->isNotEmpty())
                        <img
                            src="{{ $gallery->first()->media->url() }}"
                            alt="{{ $gallery->first()->alt_text ?: $revision->name }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        {{-- High-tech Blueprint Media Placement --}}
                        <div class="media-placeholder-container h-full w-full flex flex-col justify-between p-8">
                            <div class="media-placeholder-grid" aria-hidden="true"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <span class="rounded bg-[#00f0ff]/10 border border-[#00f0ff]/30 px-3 py-1 text-[11px] font-mono text-[#00f0ff]">
                                    PRODUCT-VISUAL
                                </span>
                                <span class="text-[11px] font-mono text-slate-500">ID: {{ $product->public_slug }}</span>
                            </div>
                            <div class="relative z-10 flex flex-col items-center justify-center text-center">
                                <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl border border-white/10 bg-white/5">
                                    <svg class="h-10 w-10 text-[#00f0ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-mono font-bold text-slate-300">MEDIA PLACEMENT: PRODUCT GALLERY</p>
                                <p class="mt-1 text-[11px] text-slate-500">Dapat diunggah langsung melalui CMS Media Library</p>
                            </div>
                            <div class="relative z-10 flex items-center justify-between text-[11px] font-mono text-slate-500 border-t border-white/10 pt-3">
                                <span>STANDAR MEDIS: KEMENKES RI</span>
                                <span class="text-[#00f0ff]">TERDAFTAR CDAKB</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($gallery->count() > 1)
                    <div class="mt-4 grid grid-cols-4 gap-3">
                        @foreach ($gallery as $index => $item)
                            <div class="relative aspect-square overflow-hidden rounded-xl border border-white/10 bg-[#0c121e]">
                                <img
                                    src="{{ $item->media->url() }}"
                                    alt="{{ $item->alt_text ?: $revision->name }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Specifications & Info Column (Col 5) --}}
            <div class="lg:col-span-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#00f0ff]">
                            {{ $revision->category->name }}
                        </span>

                        @if ($revision->brand)
                            <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold text-slate-300">
                                {{ $revision->brand->name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        {{ $revision->name }}
                    </h1>

                    @if ($revision->type)
                        <p class="mt-2 text-xs font-mono text-slate-400">
                            Tipe / Seri: <span class="text-slate-200">{{ $revision->type->name }}</span>
                        </p>
                    @endif

                    @if ($revision->summary)
                        <p class="mt-5 text-base leading-relaxed text-slate-300">
                            {{ $revision->summary }}
                        </p>
                    @endif

                    @if ($revision->highlights->isNotEmpty())
                        <div class="mt-6 rounded-2xl border border-white/10 bg-[#0c121e]/80 p-5">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-[#00f0ff] mb-3">
                                FITUR & KEUNGGULAN UTAMA
                            </h2>
                            <ul class="space-y-2.5">
                                @foreach ($revision->highlights as $highlight)
                                    <li class="flex items-start gap-2.5 text-sm text-slate-300">
                                        <span class="text-[#00f0ff] font-bold">✓</span>
                                        <span>{{ $highlight->text }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Action Console --}}
                <div class="mt-8 pt-6 border-t border-white/10 flex flex-col gap-3">
                    <a
                        href="{{ route('contact') }}?product={{ $product->id }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#00f0ff] px-6 py-4 text-sm font-bold text-[#070a0f] transition-all hover:bg-white hover:shadow-[0_0_25px_rgba(0,240,255,0.4)]"
                    >
                        <span>Ajukan Penawaran / Tanya Produk</span>
                        <span aria-hidden="true">↗</span>
                    </a>

                    @if ($brochure)
                        <a
                            href="{{ $brochure->media->url() }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:border-[#00f0ff] hover:text-[#00f0ff]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Unduh Brosur Teknis (PDF)</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- DESCRIPTION --}}
        @if ($revision->description)
            <section class="mt-16 border-t border-white/10 pt-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                    DESKRIPSI LENGKAP
                </p>
                <h2 class="mt-2 text-2xl font-bold text-white">
                    Gambaran Umum & Aplikasi Klinis
                </h2>

                <div class="mt-6 rounded-2xl border border-white/10 bg-[#0c121e]/80 p-8 whitespace-pre-line text-base leading-relaxed text-slate-300">
                    {{ $revision->description }}
                </div>
            </section>
        @endif

        {{-- TECHNICAL SPECIFICATIONS TABLE --}}
        @if ($revision->specificationGroups->isNotEmpty())
            <section class="mt-16 border-t border-white/10 pt-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                    PARAMETER TEKNIS
                </p>
                <h2 class="mt-2 text-2xl font-bold text-white">
                    Spesifikasi & Standar Rekayasa
                </h2>

                <div class="mt-8 space-y-6">
                    @foreach ($revision->specificationGroups as $group)
                        <div class="rounded-2xl border border-white/10 bg-[#0c121e]/80 overflow-hidden">
                            <div class="bg-white/5 px-6 py-3 border-b border-white/10">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-white">
                                    {{ $group->name }}
                                </h3>
                            </div>

                            <dl class="divide-y divide-white/10">
                                @foreach ($group->items as $item)
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4 text-sm">
                                        <dt class="font-medium text-slate-400">
                                            {{ $item->label }}
                                        </dt>
                                        <dd class="sm:col-span-2 font-mono text-white">
                                            {{ $item->value }}
                                            @if ($item->unit)
                                                <span class="text-slate-400">{{ $item->unit }}</span>
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- RELATED PRODUCTS --}}
        @if ($revision->relatedProducts->isNotEmpty())
            <section class="mt-16 border-t border-white/10 pt-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                    REKOMENDASI TERKAIT
                </p>
                <h2 class="mt-2 text-2xl font-bold text-white">
                    Produk & Perlengkapan Terintegrasi
                </h2>

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($revision->relatedProducts as $related)
                        @if ($related->publishedRevision && $related->public_slug)
                            <a
                                href="{{ route('products.show', $related->public_slug) }}"
                                class="tech-glow-card rounded-2xl border border-white/10 bg-[#0c121e]/80 p-6 block"
                            >
                                <span class="text-xs text-[#00f0ff] font-mono">PRODUK TERKAIT</span>
                                <h3 class="mt-2 text-lg font-bold text-white">
                                    {{ $related->publishedRevision->name }}
                                </h3>
                                <p class="mt-3 text-xs text-slate-400 flex items-center justify-between">
                                    <span>Lihat Spesifikasi</span>
                                    <span aria-hidden="true">↗</span>
                                </p>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    </section>
@endsection