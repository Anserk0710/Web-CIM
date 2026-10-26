@extends('layouts.public')

@section('title', 'Katalog Produk Alat Kesehatan | PT Cahya Intan Medika')

@section(
    'meta_description',
    'Daftar instrumen medis, elektromedik, dan alat kesehatan bersertifikasi resmi PT Cahya Intan Medika.'
)

@section('canonical', route('products.index'))

@section('structured_data')
    @php
        $productListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',

            'itemListElement' => $products
                ->getCollection()
                ->values()
                ->map(
                    function ($product, $index) use ($products) {
                        $revision = $product->publishedRevision;

                        if (
                            $revision === null
                            || !$product->public_slug
                        ) {
                            return null;
                        }

                        return [
                            '@type' => 'ListItem',

                            'position' => (
                                ($products->currentPage() - 1)
                                * $products->perPage()
                            ) + $index + 1,

                            'name' => $revision->name,

                            'url' => route(
                                'products.show',
                                $product->public_slug,
                            ),
                        ];
                    }
                )
                ->filter()
                ->values()
                ->all(),
        ];
    @endphp

    <script type="application/ld+json">
        @json($productListSchema)
    </script>
@endsection

@section('content')
    {{-- PRODUCTS HERO HEADER --}}
    <section class="relative overflow-hidden bg-[#070a0f] pt-28 pb-14 sm:pt-36 sm:pb-16 lg:pt-40 lg:pb-20 bg-tech-grid border-b border-white/10">
        <div class="absolute inset-0 pointer-events-none tech-glow-radial" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-3.5 py-1 text-xs font-bold tracking-wider text-[#00f0ff] uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span>
                    <span>PORTFOLIO INSTRUMEN & ALKES</span>
                </div>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.08]">
                    Daftar Produk Resmi
                </h1>

                <p class="mt-5 text-base sm:text-lg leading-relaxed text-slate-400">
                    Eksplorasi portofolio alat kesehatan rumah sakit, elektromedik, dan instrumen laboratorium terstandarisasi Kemenkes RI dan CDAKB.
                </p>
            </div>
        </div>
    </section>

    {{-- FILTER CONSOLE & GRID --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Search & Filters --}}
        <form method="GET" action="{{ route('products.index') }}"
            class="grid gap-4 rounded-2xl border border-white/10 bg-[#0c121e]/90 p-6 sm:p-8 backdrop-blur-md md:grid-cols-4">
            <div>
                <label for="q" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">
                    Cari Produk
                </label>
                <input id="q" name="q" value="{{ $filters['q'] }}" class="w-full rounded-xl border border-white/15 bg-[#070a0f] px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-[#00f0ff] focus:outline-none focus:ring-1 focus:ring-[#00f0ff]"
                    placeholder="Nama produk / kata kunci...">
            </div>

            <div>
                <label for="category" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">
                    Kategori
                </label>
                <select id="category" name="category" class="w-full rounded-xl border border-white/15 bg-[#070a0f] px-3.5 py-2.5 text-sm text-white focus:border-[#00f0ff] focus:outline-none focus:ring-1 focus:ring-[#00f0ff]">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="brand" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">
                    Brand / Manufaktur
                </label>
                <select id="brand" name="brand" class="w-full rounded-xl border border-white/15 bg-[#070a0f] px-3.5 py-2.5 text-sm text-white focus:border-[#00f0ff] focus:outline-none focus:ring-1 focus:ring-[#00f0ff]">
                    <option value="">Semua Brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->slug }}" @selected($filters['brand'] === $brand->slug)>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="type" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">
                    Tipe Produk
                </label>
                <select id="type" name="type" class="w-full rounded-xl border border-white/15 bg-[#070a0f] px-3.5 py-2.5 text-sm text-white focus:border-[#00f0ff] focus:outline-none focus:ring-1 focus:ring-[#00f0ff]">
                    <option value="">Semua Tipe</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->slug }}" @selected($filters['type'] === $type->slug)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4 flex items-center justify-between border-t border-white/10 pt-4">
                <span class="text-xs text-slate-400 font-mono">DITEMUKAN {{ $products->total() }} PRODUK</span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('products.index') }}" class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white">
                        Reset
                    </a>
                    <button type="submit" class="rounded-full bg-[#00f0ff] px-6 py-2 text-xs font-bold text-[#070a0f] hover:bg-white transition-all shadow-[0_0_15px_rgba(0,240,255,0.3)]">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>

        {{-- Product Grid --}}
        @if ($products->isEmpty())
            <div class="mt-12 rounded-2xl border border-dashed border-white/15 bg-[#0c121e]/50 p-12 text-center">
                <p class="text-sm font-mono text-[#00f0ff]">HASIL PENCARIAN KOSONG</p>
                <h2 class="mt-2 text-lg font-bold text-white">
                    Produk tidak ditemukan
                </h2>
                <p class="mt-2 text-sm text-slate-400">
                    Coba atur ulang kata kunci atau filter pencarian Anda.
                </p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-white/20 px-4 py-2 text-xs text-white hover:border-[#00f0ff]">
                    Lihat Seluruh Produk
                </a>
            </div>
        @else
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $revision = $product->publishedRevision;
                        $cover = $revision
                            ->media
                            ->firstWhere(
                                'role',
                                $galleryRole,
                            );
                    @endphp

                    <article class="tech-glow-card overflow-hidden rounded-2xl border border-white/10 bg-[#0c121e]/80 flex flex-col justify-between">
                        <div>
                            <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#070a0f] border-b border-white/10">
                                @if ($cover)
                                    <img src="{{ $cover->media->url() }}" alt="{{ $cover->alt_text ?: $revision->name }}"
                                        class="h-full w-full object-cover transition-transform duration-500 hover:scale-105" loading="lazy">
                                @else
                                    <div class="media-placeholder-container h-full w-full flex flex-col justify-center items-center p-6 text-center">
                                        <div class="media-placeholder-grid" aria-hidden="true"></div>
                                        <svg class="h-10 w-10 text-[#00f0ff]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <p class="mt-2 text-[10px] font-mono text-slate-500">MEDIA PLACEMENT: READY</p>
                                    </div>
                                @endif

                                @if($revision?->category?->name)
                                    <span class="absolute top-3 left-3 rounded bg-[#070a0f]/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#00f0ff] backdrop-blur-md border border-white/10">
                                        {{ $revision->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-6">
                                <p class="text-xs font-mono font-medium text-slate-400">
                                    {{ $revision->brand?->name ?? 'PT CIM' }}
                                </p>

                                <h2 class="mt-2 text-xl font-bold text-white">
                                    <a href="{{ route('products.show', $product->public_slug) }}" class="hover:text-[#00f0ff] transition-colors">
                                        {{ $revision->name }}
                                    </a>
                                </h2>

                                @if ($revision->summary)
                                    <p class="mt-3 text-sm leading-relaxed text-slate-400 line-clamp-2">
                                        {{ $revision->summary }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="p-6 pt-0 border-t border-white/10 mt-4 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-mono">DOKUMEN RESMI</span>
                            <a href="{{ route('products.show', $product->public_slug) }}" class="inline-flex items-center gap-1.5 font-bold text-[#00f0ff] hover:text-white transition-colors">
                                <span>Detail Spesifikasi</span>
                                <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($products->hasPages())
                <nav class="mt-12 flex items-center justify-between border-t border-white/10 pt-6" aria-label="Pagination">
                    @if ($products->onFirstPage())
                        <span class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold text-slate-600">
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="rounded-full border border-white/20 bg-white/5 px-4 py-2 text-xs font-semibold text-white hover:border-[#00f0ff] hover:text-[#00f0ff]">
                            Sebelumnya
                        </a>
                    @endif

                    <span class="text-xs font-mono text-slate-400">
                        Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}
                    </span>

                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="rounded-full border border-white/20 bg-white/5 px-4 py-2 text-xs font-semibold text-white hover:border-[#00f0ff] hover:text-[#00f0ff]">
                            Selanjutnya
                        </a>
                    @else
                        <span class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold text-slate-600">
                            Selanjutnya
                        </span>
                    @endif
                </nav>
            @endif
        @endif
    </section>
@endsection