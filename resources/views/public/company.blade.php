@extends('layouts.public')

@section('title', ($page?->seo_title ?: 'Company') . ' | PT Cahya Intan Medika')

@section(
    'meta_description',
    $page?->seo_description ?: 'Informasi perusahaan PT Cahya Intan Medika.'
)

@if($page?->ogMedia)
    @section('og_image', $page->ogMedia->url())
@endif

@section('content')
    {{-- COMPANY HERO --}}
    <section class="relative overflow-hidden bg-[#070a0f] pt-28 pb-16 sm:pt-36 sm:pb-20 lg:pt-40 lg:pb-24 bg-tech-grid border-b border-white/10">
        <div class="absolute inset-0 pointer-events-none tech-glow-radial" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-3.5 py-1 text-xs font-bold tracking-wider text-[#00f0ff] uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span>
                    <span>{{ $hero['eyebrow'] ?: 'PROFIL PERUSAHAAN' }}</span>
                </div>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.08]">
                    {{ $hero['title'] ?? $page?->title ?? 'Profil PT Cahya Intan Medika' }}
                </h1>

                @if(!empty($hero['description']))
                    <p class="mt-6 text-lg sm:text-xl leading-relaxed text-slate-400">
                        {{ $hero['description'] }}
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- OUR STORY & MEDIA PLACEMENT --}}
    @if(!empty($story['body']))
        <section class="mx-auto grid max-w-7xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-28 items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                    REKAM JEJAK & DEDIKASI
                </p>

                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    {{ $story['heading'] ?? 'Membangun Ekosistem Alat Kesehatan Terpercaya' }}
                </h2>

                <div class="mt-6 whitespace-pre-line text-base leading-relaxed text-slate-300">
                    {{ $story['body'] }}
                </div>
            </div>

            <div class="relative aspect-[4/3] w-full overflow-hidden rounded-3xl border border-white/10 bg-[#0c121e]/90 shadow-2xl">
                @if($storyImage !== null)
                    <img
                        src="{{ $storyImage->url() }}"
                        alt="{{ $story['image_alt'] ?? '' }}"
                        width="{{ $storyImage->width ?: 1200 }}"
                        height="{{ $storyImage->height ?: 900 }}"
                        loading="lazy"
                        class="h-full w-full object-cover"
                    >
                @else
                    {{-- Media Placement for Company Story --}}
                    <div class="media-placeholder-container h-full w-full flex flex-col justify-between p-8">
                        <div class="media-placeholder-grid" aria-hidden="true"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <span class="rounded bg-[#00f0ff]/10 border border-[#00f0ff]/30 px-3 py-1 text-[11px] font-mono text-[#00f0ff]">CIM-FACILITY</span>
                            <span class="text-[11px] font-mono text-slate-500">EST. 2011</span>
                        </div>
                        <div class="relative z-10 flex flex-col items-center justify-center text-center">
                            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl border border-white/10 bg-white/5">
                                <svg class="h-10 w-10 text-[#00f0ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <p class="text-xs font-mono font-bold text-slate-300">MEDIA PLACEMENT: STORY IMAGE</p>
                            <p class="mt-1 text-[11px] text-slate-500">Dapat diunggah langsung melalui menu CMS</p>
                        </div>
                        <div class="relative z-10 flex items-center justify-between text-[11px] font-mono text-slate-500 border-t border-white/10 pt-3">
                            <span>FASILITAS & GUDANG RESMI</span>
                            <span class="text-[#00f0ff]">TERSERTIFIKASI CDAKB</span>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- METRICS SECTION --}}
    @if($metrics->isNotEmpty())
        <section class="border-y border-white/10 bg-[#05070a] py-16">
            <div class="mx-auto grid max-w-7xl grid-cols-2 lg:grid-cols-4 gap-6 px-4 sm:px-6 lg:px-8">
                @foreach($metrics as $metric)
                    <article class="tech-glow-card rounded-2xl border border-white/10 bg-[#0c121e]/80 p-6">
                        <p class="text-3xl sm:text-4xl font-extrabold text-[#00f0ff]">
                            {{ $metric['value'] }} {{ $metric['unit'] ?? '' }}
                        </p>

                        <h2 class="mt-2 text-sm font-semibold uppercase tracking-wider text-slate-200">
                            {{ $metric['label'] }}
                        </h2>

                        @if(!empty($metric['source_note']))
                            <p class="mt-2 text-xs leading-relaxed text-slate-500">
                                {{ $metric['source_note'] }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- VISION & MISSION (Bento Cards) --}}
    @if(!empty($visionMission['vision']) || !empty($visionMission['mission']))
        <section class="mx-auto grid max-w-7xl gap-6 px-4 py-20 sm:px-6 md:grid-cols-2 lg:px-8">
            @if(!empty($visionMission['vision']))
                <article class="tech-glow-card rounded-2xl border border-white/10 bg-[#0c121e]/90 p-8 sm:p-10 backdrop-blur-md">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-3 py-1 text-xs font-bold text-[#00f0ff] uppercase">
                        VISI PERUSAHAAN
                    </span>

                    <h2 class="mt-6 text-2xl font-bold text-white">
                        Visi Masa Depan
                    </h2>

                    <p class="mt-4 whitespace-pre-line text-base leading-relaxed text-slate-300">
                        {{ $visionMission['vision'] }}
                    </p>
                </article>
            @endif

            @if(!empty($visionMission['mission']))
                <article class="tech-glow-card rounded-2xl border border-white/10 bg-[#0c121e]/90 p-8 sm:p-10 backdrop-blur-md">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-3 py-1 text-xs font-bold text-slate-300 uppercase">
                        MISI UTAMA
                    </span>

                    <h2 class="mt-6 text-2xl font-bold text-white">
                        Misi Berkelanjutan
                    </h2>

                    <p class="mt-4 whitespace-pre-line text-base leading-relaxed text-slate-300">
                        {{ $visionMission['mission'] }}
                    </p>
                </article>
            @endif
        </section>
    @endif

    {{-- VALUES --}}
    @if($values !== [])
        <section class="border-y border-white/10 bg-[#05070a] py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                        NILAI & PRINSIP
                    </p>

                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        {{ $valuesHeading ?: 'Nilai Fundamental Kami' }}
                    </h2>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($values as $value)
                        @if(is_array($value) && !empty($value['title']))
                            <article class="tech-glow-card rounded-2xl border border-white/10 bg-[#0c121e]/80 p-8">
                                <h3 class="text-lg font-bold text-white">
                                    {{ $value['title'] }}
                                </h3>

                                @if(!empty($value['body']))
                                    <p class="mt-3 text-sm leading-relaxed text-slate-400">
                                        {{ $value['body'] }}
                                    </p>
                                @endif
                            </article>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- MILESTONES TIMELINE --}}
    @if($milestones !== [])
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                    PERJALANAN KAMI
                </p>

                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    {{ $milestonesHeading ?: 'Tonggak Sejarah Perusahaan' }}
                </h2>
            </div>

            <ol class="mt-12 space-y-8 border-l border-[#00f0ff]/30 pl-6 sm:pl-8">
                @foreach($milestones as $milestone)
                    @if(is_array($milestone) && !empty($milestone['title']))
                        <li class="relative">
                            <span class="absolute -left-[1.95rem] sm:-left-[2.45rem] top-1.5 h-3.5 w-3.5 rounded-full border-2 border-[#070a0f] bg-[#00f0ff] shadow-[0_0_10px_#00f0ff]" aria-hidden="true"></span>

                            @if(!empty($milestone['date']))
                                <p class="text-xs font-mono font-bold tracking-wider text-[#00f0ff]">
                                    {{ $milestone['date'] }}
                                </p>
                            @endif

                            <h3 class="mt-1 text-xl font-bold text-white">
                                {{ $milestone['title'] }}
                            </h3>

                            @if(!empty($milestone['body']))
                                <p class="mt-2 text-sm sm:text-base leading-relaxed text-slate-400">
                                    {{ $milestone['body'] }}
                                </p>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ol>
        </section>
    @endif

    {{-- OFFICES NETWORK --}}
    @if($publicSite['offices']->isNotEmpty())
        <section class="border-y border-white/10 bg-[#05070a] py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    {{ $coverageData['heading'] ?? 'Jaringan Kantor & Operasional' }}
                </h2>

                @if(!empty($coverageData['intro']))
                    <p class="mt-4 max-w-3xl leading-relaxed text-slate-400">
                        {{ $coverageData['intro'] }}
                    </p>
                @endif

                <div class="mt-10 grid gap-6 md:grid-cols-2">
                    @foreach($publicSite['offices'] as $office)
                        <article class="tech-glow-card rounded-2xl border border-white/10 bg-[#0c121e]/80 p-8">
                            <span class="rounded bg-[#00f0ff]/10 border border-[#00f0ff]/30 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#00f0ff]">
                                {{ $office->is_head_office ? 'KANTOR PUSAT' : 'KANTOR OPERASIONAL' }}
                            </span>

                            <h3 class="mt-4 text-xl font-bold text-white">
                                {{ $office->name }}
                            </h3>

                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-400">
                                {{ $office->address }}
                            </p>

                            @if($office->phone)
                                <p class="mt-4 text-sm">
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $office->phone) }}" class="text-[#00f0ff] hover:underline">
                                        {{ $office->phone }}
                                    </a>
                                </p>
                            @endif

                            @if($office->email)
                                <p class="mt-2 text-sm">
                                    <a href="mailto:{{ $office->email }}" class="break-all text-slate-400 hover:text-[#00f0ff]">
                                        {{ $office->email }}
                                    </a>
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA BANNER --}}
    <section class="relative overflow-hidden bg-[#070a0f] py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-r from-[#03045e]/80 to-[#0c121e] px-8 py-14 sm:px-16 text-center sm:text-left sm:flex sm:items-center sm:justify-between gap-8">
                <div>
                    <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                        {{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}
                    </h2>

                    <p class="mt-3 max-w-2xl text-base text-slate-300">
                        Mitra strategis fasilitas kesehatan dan rumah sakit di seluruh Indonesia untuk pengadaan alat medis terpercaya.
                    </p>
                </div>

                <a
                    href="{{ route('contact') }}"
                    class="mt-6 sm:mt-0 shrink-0 inline-flex items-center justify-center gap-2 rounded-full bg-[#00f0ff] px-8 py-4 text-sm font-bold text-[#070a0f] transition-all hover:bg-white hover:shadow-[0_0_30px_rgba(0,240,255,0.5)]"
                >
                    <span>Hubungi Kami</span>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>
    </section>
@endsection