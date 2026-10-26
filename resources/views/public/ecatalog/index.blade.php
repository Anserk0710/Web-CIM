{{-- path: resources/views/public/ecatalog/index.blade.php --}}

@extends('layouts.public')

@section(
    'title',
    $page?->seo_title
        ?: ($page?->title ?: 'E-Katalog LKPP Alat Kesehatan | '.config('app.name'))
)

@section(
    'meta_description',
    $page?->seo_description
        ?: 'Daftar pengadaan resmi E-Katalog LKPP instrumen medis dan alat kesehatan PT Cahya Intan Medika.'
)

@section('canonical', route('ecatalog.public.index'))

@section('og_type', 'website')

@if ($page?->ogMedia)
    @section('og_image', $page->ogMedia->url())
@endif

@section('structured_data')
    <script type="application/ld+json">
        @json($structuredData)
    </script>
@endsection

@section('content')
    {{-- ECATALOG HERO HEADER --}}
    <section class="relative overflow-hidden bg-[#070a0f] pt-28 pb-14 sm:pt-36 sm:pb-16 lg:pt-40 lg:pb-20 bg-tech-grid border-b border-white/10">
        <div class="absolute inset-0 pointer-events-none tech-glow-radial" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-3.5 py-1 text-xs font-bold tracking-wider text-[#00f0ff] uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span>
                    <span>PENGADAAN PEMERINTAH & SWASTA</span>
                </div>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.08]">
                    {{ $page?->title ?: 'E-Katalog LKPP' }}
                </h1>

                <p class="mt-5 text-base sm:text-lg leading-relaxed text-slate-400">
                    {{ $page?->seo_description
                        ?: 'Informasi resmi komoditas alat kesehatan terdaftar di E-Katalog Lembaga Kebijakan Pengadaan Barang/Jasa Pemerintah (LKPP).' }}
                </p>
            </div>
        </div>
    </section>

    {{-- ECATALOG ENTRIES GRID --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($entries->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/15 bg-[#0c121e]/50 p-12 text-center">
                <p class="text-sm font-mono text-[#00f0ff]">STATUS INFORMASI</p>
                <h2 class="mt-2 text-xl font-bold text-white">
                    E-Catalog belum tersedia
                </h2>
                <p class="mt-2 text-sm text-slate-400">
                    Belum ada entri pengadaan yang dapat ditampilkan saat ini.
                </p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($entries as $entry)
                    <article
                        id="catalog-{{ $entry->id }}"
                        class="tech-glow-card flex flex-col justify-between rounded-2xl border border-white/10 bg-[#0c121e]/80 p-6 sm:p-7 backdrop-blur-md"
                    >
                        <div>
                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <span class="rounded bg-[#00f0ff]/10 border border-[#00f0ff]/30 px-2.5 py-0.5 text-xs font-mono font-bold text-[#00f0ff]">
                                    {{ $entry->catalog_code }}
                                </span>
                                <span class="text-[10px] font-mono text-slate-500">LKPP-VERIFIED</span>
                            </div>

                            <h2 class="mt-4 text-xl font-bold text-white leading-snug">
                                {{ $entry->procurement_title }}
                            </h2>

                            @if ($entry->procurement_category)
                                <p class="mt-2 text-xs font-mono text-slate-400">
                                    Kategori: <span class="text-slate-300">{{ $entry->procurement_category }}</span>
                                </p>
                            @endif

                            <dl class="mt-6 space-y-3 rounded-xl border border-white/10 bg-white/5 p-4 text-xs">
                                <div>
                                    <dt class="font-bold uppercase tracking-wider text-slate-400">
                                        Produk Terhubung
                                    </dt>
                                    <dd class="mt-1 font-semibold text-white">
                                        @if ($entry->product->public_slug)
                                            <a
                                                href="{{ route(
                                                    'products.show',
                                                    $entry->product->public_slug,
                                                ) }}"
                                                class="text-[#00f0ff] hover:underline"
                                            >
                                                {{ $entry->product->publishedRevision->name }} ↗
                                            </a>
                                        @else
                                            {{ $entry->product->publishedRevision->name }}
                                        @endif
                                    </dd>
                                </div>

                                <div>
                                    <dt class="font-bold uppercase tracking-wider text-slate-400">
                                        Ketersediaan
                                    </dt>
                                    <dd class="mt-1 flex items-center gap-1.5 font-mono text-[#00f0ff]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span>
                                        {{ str_replace('_', ' ', $entry->availability->value) }}
                                    </dd>
                                </div>

                                @if ($entry->unit_package)
                                    <div>
                                        <dt class="font-bold uppercase tracking-wider text-slate-400">
                                            Satuan / Kemasan
                                        </dt>
                                        <dd class="mt-1 font-mono text-slate-200">
                                            {{ $entry->unit_package }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            @if ($entry->notes)
                                <p class="mt-4 whitespace-pre-line text-xs leading-relaxed text-slate-400">
                                    {{ $entry->notes }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-8 flex flex-wrap gap-2.5 pt-4 border-t border-white/10">
                            <a
                                href="{{ route(
                                    'contact',
                                    [
                                        'type' => 'ECATALOG',
                                        'ecatalog_entry_id' => $entry->id,
                                    ],
                                ) }}"
                                class="inline-flex flex-1 items-center justify-center rounded-full bg-[#00f0ff] px-4 py-2.5 text-xs font-bold text-[#070a0f] transition-all hover:bg-white hover:shadow-[0_0_15px_rgba(0,240,255,0.4)]"
                            >
                                Tanya Pengadaan Ini ↗
                            </a>

                            @if ($entry->reference_url)
                                <a
                                    href="{{ $entry->reference_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/5 px-4 py-2.5 text-xs font-semibold text-white transition-colors hover:border-[#00f0ff] hover:text-[#00f0ff]"
                                >
                                    Tautan LKPP ↗
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($entries->hasPages())
                <nav
                    aria-label="E-Catalog pagination"
                    class="mt-12 flex items-center justify-between border-t border-white/10 pt-6"
                >
                    @if ($entries->onFirstPage())
                        <span class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold text-slate-600">
                            Sebelumnya
                        </span>
                    @else
                        <a
                            href="{{ $entries->previousPageUrl() }}"
                            class="rounded-full border border-white/20 bg-white/5 px-4 py-2 text-xs font-semibold text-white hover:border-[#00f0ff] hover:text-[#00f0ff]"
                        >
                            Sebelumnya
                        </a>
                    @endif

                    <span class="text-xs font-mono text-slate-400">
                        Halaman {{ $entries->currentPage() }} dari {{ $entries->lastPage() }}
                    </span>

                    @if ($entries->hasMorePages())
                        <a
                            href="{{ $entries->nextPageUrl() }}"
                            class="rounded-full border border-white/20 bg-white/5 px-4 py-2 text-xs font-semibold text-white hover:border-[#00f0ff] hover:text-[#00f0ff]"
                        >
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