@extends('layouts.public')

@section('title', ($page?->seo_title ?: $hero['title']) . ' | PT Cahya Intan Medika')

@section(
    'meta_description',
    $page?->seo_description
    ?: $hero['description']
)

@if($page?->ogMedia)
    @section('og_image', $page->ogMedia->url())
@endif

@section('content')
    {{-- HERO SECTION (Inspired by ON.energy Cinematic Tech Hero) --}}
    <section class="relative overflow-hidden bg-[#070a0f] pt-28 pb-16 sm:pt-36 sm:pb-24 lg:pt-40 lg:pb-28 bg-tech-grid">
        <div class="absolute inset-0 pointer-events-none tech-glow-radial" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-8">
                {{-- Left Column: Hero Content & CTAs --}}
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2.5 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-4 py-1.5 text-xs font-bold tracking-wider text-[#00f0ff] uppercase shadow-[0_0_15px_rgba(0,240,255,0.15)]">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00f0ff] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00f0ff]"></span>
                        </span>
                        <span>{{ ($hero['eyebrow'] ?? null) ?: 'TEKNOLOGI MEDIS & DISTRIBUSI ALAT KESEHATAN RESMI' }}</span>
                    </div>

                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-6xl lg:text-7xl leading-[1.06]">
                        {{ $hero['title'] ?? 'PT Cahya Intan Medika' }}
                    </h1>

                    <p class="mt-6 max-w-xl text-lg sm:text-xl leading-relaxed text-slate-400">
                        {{ $hero['description'] ?? 'Jelajahi produk, katalog pengadaan, aktivitas, dan informasi perusahaan.' }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a
                            href="{{ $hero['primary_cta_url'] ?? route('products.index') }}"
                            class="inline-flex items-center justify-center gap-2.5 rounded-full bg-[#00f0ff] px-8 py-4 text-base font-bold text-[#070a0f] transition-all duration-300 hover:bg-white hover:shadow-[0_0_35px_rgba(0,240,255,0.5)] hover:scale-[1.02]"
                        >
                            <span>{{ $hero['primary_cta_label'] ?? 'Jelajahi Produk' }}</span>
                            <span aria-hidden="true" class="text-lg">↗</span>
                        </a>

                        @if(!empty($hero['secondary_cta_label']))
                            <a
                                href="{{ $hero['secondary_cta_url'] ?? route('company') }}"
                                class="inline-flex items-center justify-center gap-2.5 rounded-full border border-white/20 bg-white/5 px-8 py-4 text-base font-semibold text-white transition-all duration-300 hover:border-[#00f0ff] hover:text-[#00f0ff] hover:bg-white/10"
                            >
                                <span>{{ $hero['secondary_cta_label'] }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Trust / Validation Indicators --}}
                    <div class="mt-10 grid grid-cols-3 gap-4 border-t border-white/10 pt-6">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#00f0ff]">STANDAR CDAKB</p>
                            <p class="mt-1 text-xs text-slate-400">Sertifikasi Kemenkes RI</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#00f0ff]">LKPP E-KATALOG</p>
                            <p class="mt-1 text-xs text-slate-400">Penyedia Resmi Terdaftar</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#00f0ff]">NASIONAL</p>
                            <p class="mt-1 text-xs text-slate-400">Jangkauan 38 Provinsi</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Media Placement & Spotlight Card --}}
                <div class="relative lg:col-span-5">
                    <div class="relative aspect-[4/3] sm:aspect-[16/11] w-full overflow-hidden rounded-3xl border border-white/10 bg-[#0c121e]/90 shadow-2xl">
                        @if($heroVideo !== null)
                            <video
                                src="{{ $heroVideo->url() }}"
                                autoplay
                                muted
                                loop
                                playsinline
                                class="h-full w-full object-cover"
                            ></video>
                        @elseif($heroImage !== null)
                            <img
                                src="{{ $heroImage->url() }}"
                                alt="{{ $hero['image_alt'] ?? '' }}"
                                width="{{ $heroImage->width ?: 1200 }}"
                                height="{{ $heroImage->height ?: 900 }}"
                                fetchpriority="high"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{-- Interactive High-Tech Blueprint / Media Placement Placeholder --}}
                            <div class="media-placeholder-container h-full w-full flex flex-col justify-between p-6 sm:p-8">
                                <div class="media-placeholder-grid" aria-hidden="true"></div>

                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#070a0f]/80 px-3 py-1 text-[11px] font-mono font-semibold tracking-wider text-[#00f0ff]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span> CIM-SYS-MED-01
                                    </span>
                                    <span class="text-[11px] font-mono text-slate-500">LAT -6.2088° | LON 106.8456°</span>
                                </div>

                                {{-- Stylized Medical Engineering Schematic Graphic --}}
                                <div class="relative z-10 flex flex-col items-center justify-center py-6 text-center">
                                    <div class="relative mb-4 flex h-24 w-24 items-center justify-center rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md">
                                        <div class="absolute inset-0 rounded-2xl border border-[#00f0ff]/30 animate-pulse"></div>
                                        <svg class="h-12 w-12 text-[#00f0ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-mono font-semibold tracking-wider text-slate-300">MEDIA PLACEMENT</p>
                                    <p class="mt-1 text-[11px] text-slate-500">Siap untuk upload gambar atau video MP4 melalui CMS</p>
                                </div>

                                <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-3 text-[11px] font-mono text-slate-500">
                                    <span>STATUS: OPERATIONAL</span>
                                    <span class="text-[#00f0ff]">ISO 13485 / CDAKB</span>
                                </div>
                            </div>
                        @endif

                        {{-- Floating Spotlight Card (Identical to ON.energy Explore Card) --}}
                        <div class="absolute bottom-4 right-4 max-w-[280px] rounded-2xl border border-[#00f0ff]/40 bg-[#070a0f]/95 p-4 shadow-2xl backdrop-blur-xl">
                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#00f0ff]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span> JELAJAHI PRODUK
                            </div>
                            <p class="mt-2 text-xs font-semibold leading-relaxed text-white">
                                Instrumen elektromedik, anestesi & diagnostik berstandar rumah sakit.
                            </p>
                            <a href="{{ route('products.index') }}" class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-[#00f0ff] hover:text-white transition-colors">
                                <span>Buka Katalog Lengkap</span>
                                <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FULL-BLEED HIGH-VOLTAGE STATEMENT SECTION (ON.energy Bold Contrast Banner) --}}
    <section class="relative overflow-hidden border-y border-white/10 bg-gradient-to-r from-[#03045e]/90 via-[#070a0f] to-[#0c121e] py-20 sm:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#00f0ff]">
                KEANDALAN PENGADAAN & DISTRIBUSI ALKES
            </p>
            <h2 class="mt-5 text-2xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white leading-[1.2]">
                PT Cahya Intan Medika menetapkan standar keandalan distribusi instrumen medis, elektromedik, dan alat kesehatan bersertifikasi untuk fasilitas kesehatan di seluruh Indonesia.
            </h2>
        </div>
    </section>

    {{-- IMPACT METRICS SECTION (Monumental Statistics Row) --}}
    @if($metrics->isNotEmpty())
        <section aria-label="Company metrics" class="border-b border-white/10 bg-[#070a0f]">
            <div class="mx-auto grid max-w-7xl grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-white/10 lg:grid-cols-4">
                @foreach($metrics as $metric)
                    <article class="p-6 sm:p-8 lg:p-10">
                        <p class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#00f0ff]">
                            {{ $metric['value'] }} {{ $metric['unit'] ?? '' }}
                        </p>

                        <h2 class="mt-3 text-sm font-semibold uppercase tracking-wider text-slate-200">
                            {{ $metric['label'] }}
                        </h2>

                        @if(!empty($metric['source_note']))
                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                {{ $metric['source_note'] }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CAPABILITIES & SOLUTIONS (Bento Grid) --}}
    @if($sections->has('capabilities') && $capabilities !== [])
        <section class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                    SOLUSI & KAPABILITAS
                </p>

                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    {{ $capabilitiesHeading ?: 'Solusi Lengkap Fasilitas Kesehatan' }}
                </h2>

                @if($capabilitiesIntro)
                    <p class="mt-4 text-base leading-relaxed text-slate-400">
                        {{ $capabilitiesIntro }}
                    </p>
                @endif
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($capabilities as $index => $item)
                    @if(is_array($item) && !empty($item['title']))
                        <article class="tech-glow-card flex flex-col justify-between rounded-2xl border border-white/10 bg-[#0c121e]/80 p-8 backdrop-blur-md">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#00f0ff]/30 bg-[#00f0ff]/10 text-xs font-mono font-bold text-[#00f0ff]">
                                        0{{ $index + 1 }}
                                    </span>
                                    <span class="text-xs text-slate-500">CIM·CAP</span>
                                </div>

                                <h3 class="mt-6 text-xl font-bold text-white">
                                    {{ $item['title'] }}
                                </h3>

                                @if(!empty($item['body']))
                                    <p class="mt-3 text-sm leading-relaxed text-slate-400">
                                        {{ $item['body'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-6 border-t border-white/10 pt-4 flex items-center justify-between text-xs font-semibold text-[#00f0ff]">
                                <span>Spesifikasi Layanan</span>
                                <span aria-hidden="true">↗</span>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    {{-- SERVICE LIFECYCLE ("Our Process" - Interactive Timeline) --}}
    @if($sections->has('lifecycle') && $lifecycle !== [])
        <section class="border-y border-white/10 bg-[#05070a] py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                        ALUR PROSES PENGADAAN
                    </p>

                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        {{ $lifecycleHeading ?: 'Tahapan Kerja Sama & Pengadaan' }}
                    </h2>
                </div>

                <ol class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach($lifecycle as $index => $step)
                        @if(is_array($step) && !empty($step['title']))
                            <li class="relative rounded-2xl border border-white/10 bg-[#0c121e]/90 p-7 backdrop-blur-md">
                                <p class="text-xs font-mono font-bold text-[#00f0ff]">
                                    TAHAP {{ sprintf('%02d', $index + 1) }}
                                </p>

                                <h3 class="mt-4 text-lg font-bold text-white">
                                    {{ $step['title'] }}
                                </h3>

                                @if(!empty($step['body']))
                                    <p class="mt-3 text-sm leading-relaxed text-slate-400">
                                        {{ $step['body'] }}
                                    </p>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- FEATURED PRODUCTS SECTION (Bento Product Grid) --}}
    @if($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                        KATALOG PILIHAN
                    </p>

                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        {{ $featuredData['heading'] ?? 'Produk Unggulan Medis' }}
                    </h2>
                </div>

                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-5 py-2.5 text-xs font-bold text-[#00f0ff] transition-all hover:bg-[#00f0ff] hover:text-[#070a0f]"
                >
                    <span>Lihat Semua Produk</span>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($featuredProducts as $product)
                    @php
                        $revision = $product->publishedRevision;
                        $cover = $revision?->media->first(
                            fn($item) => $item->role->value === 'GALLERY'
                        );
                    @endphp

                    <article class="tech-glow-card overflow-hidden rounded-2xl border border-white/10 bg-[#0c121e]/80 flex flex-col justify-between">
                        <div>
                            <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#070a0f] border-b border-white/10">
                                @if($cover?->media)
                                    <img
                                        src="{{ $cover->media->url() }}"
                                        alt="{{ $cover->alt_text ?: $revision->name }}"
                                        width="{{ $cover->media->width ?: 800 }}"
                                        height="{{ $cover->media->height ?: 600 }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                    >
                                @else
                                    {{-- Product Blueprint Schematic Placement --}}
                                    <div class="media-placeholder-container h-full w-full flex flex-col justify-center items-center p-6 text-center">
                                        <div class="media-placeholder-grid" aria-hidden="true"></div>
                                        <svg class="h-10 w-10 text-[#00f0ff]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <p class="mt-2 text-[10px] font-mono text-slate-500">MEDIA PLACEMENT: PRODUCT</p>
                                    </div>
                                @endif

                                @if($revision?->category?->name)
                                    <span class="absolute top-3 left-3 rounded-md bg-[#070a0f]/80 border border-white/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#00f0ff] backdrop-blur-md">
                                        {{ $revision->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-6">
                                <h3 class="text-lg font-bold text-white">
                                    <a href="{{ route('products.show', $product->public_slug) }}" class="hover:text-[#00f0ff] transition-colors">
                                        {{ $revision?->name }}
                                    </a>
                                </h3>

                                @if($revision?->summary)
                                    <p class="mt-2 text-sm leading-relaxed text-slate-400 line-clamp-2">
                                        {{ $revision->summary }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="p-6 pt-0">
                            <a
                                href="{{ route('products.show', $product->public_slug) }}"
                                class="inline-flex w-full items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-slate-200 transition-colors hover:border-[#00f0ff] hover:text-[#00f0ff]"
                            >
                                <span>Spesifikasi & Dokumen</span>
                                <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- AREA COVERAGE & DISTRIBUTION (Network Console) --}}
    @php
        $coverageAreas = app(\App\Support\PublicSite\PageContent::class)
            ->listValue($coverageData['areas'] ?? data_get($publicSite, 'coverage.regions', []));
    @endphp

    @if($coverageAreas !== [] || $publicSite['offices']->isNotEmpty())
        <section class="border-y border-white/10 bg-[#05070a] py-20 sm:py-28">
            <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                        JARINGAN DISTRIBUSI
                    </p>

                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        {{ $coverageData['heading'] ?? 'Jangkauan Wilayah & Layanan' }}
                    </h2>

                    @if(!empty($coverageData['intro']))
                        <p class="mt-4 leading-relaxed text-slate-400">
                            {{ $coverageData['intro'] }}
                        </p>
                    @endif

                    @if($coverageAreas !== [])
                        <ul class="mt-8 flex flex-wrap gap-2.5">
                            @foreach($coverageAreas as $area)
                                @if(is_string($area) && trim($area) !== '')
                                    <li class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-[#0c121e] px-4 py-1.5 text-xs font-medium text-slate-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span>
                                        {{ $area }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-6">
                        KANTOR OPERASIONAL & PERWAKILAN
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @if($publicSite['headOffice'] !== null)
                            <div class="rounded-2xl border border-[#00f0ff]/30 bg-[#0c121e] p-6 shadow-lg">
                                <span class="rounded bg-[#00f0ff]/10 px-2 py-0.5 text-[10px] font-bold text-[#00f0ff]">KANTOR PUSAT</span>
                                <h4 class="mt-2 text-base font-bold text-white">{{ $publicSite['headOffice']->name }}</h4>
                                <p class="mt-2 text-xs leading-relaxed text-slate-400">{{ $publicSite['headOffice']->address }}</p>
                            </div>
                        @endif

                        @foreach($publicSite['offices'] as $office)
                            @if(!$office->is_head_office)
                                <div class="rounded-2xl border border-white/10 bg-[#0c121e] p-6">
                                    <span class="rounded bg-white/5 px-2 py-0.5 text-[10px] font-bold text-slate-400">CABANG</span>
                                    <h4 class="mt-2 text-base font-bold text-white">{{ $office->name }}</h4>
                                    <p class="mt-2 text-xs leading-relaxed text-slate-400">{{ $office->address }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- LATEST ACTIVITIES & NEWS FEED (Editorial Layout) --}}
    @if($featuredActivities->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                        DOKUMENTASI & KABAR
                    </p>

                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        {{ $activityData['heading'] ?? 'Kegiatan & Informasi Terkini' }}
                    </h2>
                </div>

                <a
                    href="{{ route('activities.index') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-5 py-2.5 text-xs font-bold text-white transition-all hover:border-[#00f0ff] hover:text-[#00f0ff]"
                >
                    <span>Semua Kegiatan</span>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($featuredActivities as $activity)
                    @php
                        $actRev = $activity->publishedRevision;
                        $featureImage = $actRev?->featureMedia;
                    @endphp

                    <article class="tech-glow-card overflow-hidden rounded-2xl border border-white/10 bg-[#0c121e]/80 flex flex-col justify-between">
                        <div>
                            <div class="relative aspect-[16/10] w-full overflow-hidden bg-[#070a0f] border-b border-white/10">
                                @if($featureImage)
                                    <img
                                        src="{{ $featureImage->url() }}"
                                        alt="{{ $actRev?->title }}"
                                        width="{{ $featureImage->width ?: 600 }}"
                                        height="{{ $featureImage->height ?: 400 }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                    >
                                @else
                                    <div class="media-placeholder-container h-full w-full flex items-center justify-center p-4">
                                        <div class="media-placeholder-grid" aria-hidden="true"></div>
                                        <span class="text-[10px] font-mono text-slate-500">MEDIA PLACEMENT</span>
                                    </div>
                                @endif

                                @if($actRev?->category?->name)
                                    <span class="absolute top-3 left-3 rounded bg-[#070a0f]/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#00f0ff] backdrop-blur-md">
                                        {{ $actRev->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-5">
                                <time class="text-[11px] font-mono text-slate-500" datetime="{{ $activity->published_at?->toIso8601String() }}">
                                    {{ $activity->published_at?->translatedFormat('d F Y') }}
                                </time>

                                <h3 class="mt-2 text-base font-bold text-white leading-snug line-clamp-2">
                                    <a href="{{ route('activities.show', $activity->public_slug) }}" class="hover:text-[#00f0ff] transition-colors">
                                        {{ $actRev?->title }}
                                    </a>
                                </h3>
                            </div>
                        </div>

                        <div class="p-5 pt-0">
                            <a href="{{ route('activities.show', $activity->public_slug) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#00f0ff] hover:text-white transition-colors">
                                <span>Baca Rilis</span>
                                <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- INTERACTIVE PROCUREMENT & INQUIRY CTA SECTION --}}
    <section class="relative overflow-hidden border-t border-white/10 bg-[#070a0f] py-20 sm:py-28">
        <div class="absolute inset-0 pointer-events-none tech-glow-radial" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#00f0ff]">
                KONSULTASI PENGADAAN MEDIS
            </span>

            <h2 class="mt-6 text-3xl font-extrabold tracking-tight text-white sm:text-5xl">
                {{ $ctaData['heading'] ?? 'Mari Memulai Percakapan untuk Kebutuhan Alkes Anda' }}
            </h2>

            <p class="mx-auto mt-6 max-w-2xl text-base sm:text-lg leading-relaxed text-slate-400">
                {{ $ctaData['description'] ?? 'Tim spesialis PT Cahya Intan Medika siap memberikan konsultasi teknis, katalog harga resmi, dan dokumen sertifikasi yang Anda butuhkan.' }}
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a
                    href="{{ $ctaData['primary_cta_url'] ?? route('contact') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-[#00f0ff] px-8 py-4 text-base font-bold text-[#070a0f] transition-all hover:bg-white hover:shadow-[0_0_35px_rgba(0,240,255,0.5)] hover:scale-105"
                >
                    <span>{{ $ctaData['primary_cta_label'] ?? 'Hubungi Kami Sekarang' }}</span>
                    <span aria-hidden="true">↗</span>
                </a>

                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-white/20 bg-white/5 px-8 py-4 text-base font-semibold text-white transition-all hover:border-[#00f0ff] hover:text-[#00f0ff] hover:bg-white/10"
                >
                    <span>Telusuri Produk</span>
                </a>
            </div>
        </div>
    </section>
@endsection