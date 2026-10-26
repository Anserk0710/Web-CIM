@extends('layouts.public')

@section('title', ($page?->seo_title ?: 'Kegiatan & Berita') . ' | PT Cahya Intan Medika')

@section(
    'meta_description',
    $page?->seo_description ?: 'Aktivitas, berita resmi, dan informasi kegiatan PT Cahya Intan Medika.'
)

@section('content')
    {{-- ACTIVITIES HERO HEADER --}}
    <section class="relative overflow-hidden bg-[#070a0f] pt-28 pb-14 sm:pt-36 sm:pb-16 lg:pt-40 lg:pb-20 bg-tech-grid border-b border-white/10">
        <div class="absolute inset-0 pointer-events-none tech-glow-radial" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-3.5 py-1 text-xs font-bold tracking-wider text-[#00f0ff] uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#00f0ff]"></span>
                    <span>DOKUMENTASI & MEDIA</span>
                </div>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.08]">
                    {{ $hero['title'] ?? $page?->title ?? 'Kegiatan & Berita' }}
                </h1>

                @if(!empty($hero['description']))
                    <p class="mt-5 text-base sm:text-lg leading-relaxed text-slate-400">
                        {{ $hero['description'] }}
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- ACTIVITIES SEARCH & EDITORIAL GRID --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('activities.index') }}"
            class="grid gap-4 rounded-2xl border border-white/10 bg-[#0c121e]/90 p-6 sm:p-8 backdrop-blur-md md:grid-cols-3">
            <div>
                <label for="q" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">
                    Cari Kegiatan
                </label>
                <input id="q" name="q" value="{{ $filters['q'] }}" maxlength="100"
                    class="w-full rounded-xl border border-white/15 bg-[#070a0f] px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-[#00f0ff] focus:outline-none focus:ring-1 focus:ring-[#00f0ff]"
                    placeholder="Kata kunci berita...">
            </div>

            <div>
                <label for="category" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">
                    Kategori
                </label>
                <select id="category" name="category" class="w-full rounded-xl border border-white/15 bg-[#070a0f] px-3.5 py-2.5 text-sm text-white focus:border-[#00f0ff] focus:outline-none focus:ring-1 focus:ring-[#00f0ff]">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                    class="w-full rounded-xl bg-[#00f0ff] px-6 py-2.5 text-xs font-bold text-[#070a0f] hover:bg-white transition-all shadow-[0_0_15px_rgba(0,240,255,0.3)]">
                    Cari Kegiatan ↗
                </button>
            </div>
        </form>

        @if($activities->isEmpty())
            <div class="mt-12 rounded-2xl border border-dashed border-white/15 bg-[#0c121e]/50 p-12 text-center">
                <p class="text-sm font-mono text-[#00f0ff]">HASIL KOSONG</p>
                <h2 class="mt-2 text-xl font-bold text-white">
                    Belum ada aktivitas yang sesuai
                </h2>
                <p class="mt-2 text-sm text-slate-400">
                    Coba sesuaikan kata kunci pencarian Anda.
                </p>
            </div>
        @else
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($activities as $activity)
                    @php($revision = $activity->publishedRevision)

                    <article class="tech-glow-card overflow-hidden rounded-2xl border border-white/10 bg-[#0c121e]/80 flex flex-col justify-between">
                        <div>
                            <div class="relative aspect-[16/10] w-full overflow-hidden bg-[#070a0f] border-b border-white/10">
                                @if($revision?->featureMedia)
                                    <img src="{{ $revision->featureMedia->url() }}" alt="{{ $revision->title }}"
                                        width="{{ $revision->featureMedia->width ?: 800 }}"
                                        height="{{ $revision->featureMedia->height ?: 600 }}" loading="lazy"
                                        class="h-full w-full object-cover transition-transform duration-500 hover:scale-105">
                                @else
                                    <div class="media-placeholder-container h-full w-full flex items-center justify-center p-6 text-center">
                                        <div class="media-placeholder-grid" aria-hidden="true"></div>
                                        <span class="text-[10px] font-mono text-slate-500">MEDIA PLACEMENT: READY</span>
                                    </div>
                                @endif

                                @if($revision?->category?->name)
                                    <span class="absolute top-3 left-3 rounded bg-[#070a0f]/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[#00f0ff] backdrop-blur-md border border-white/10">
                                        {{ $revision->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-6">
                                <time class="text-xs font-mono text-slate-500" datetime="{{ $activity->published_at?->toIso8601String() }}">
                                    {{ $activity->published_at?->translatedFormat('d F Y') }}
                                </time>

                                <h2 class="mt-2 text-xl font-bold text-white leading-snug">
                                    <a href="{{ route('activities.show', $activity->public_slug) }}" class="hover:text-[#00f0ff] transition-colors">
                                        {{ $revision?->title }}
                                    </a>
                                </h2>

                                @if($revision?->excerpt)
                                    <p class="mt-3 text-sm leading-relaxed text-slate-400 line-clamp-3">
                                        {{ $revision->excerpt }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="p-6 pt-0 border-t border-white/10 mt-4 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-mono">DOKUMENTASI</span>
                            <a href="{{ route('activities.show', $activity->public_slug) }}"
                                class="inline-flex items-center gap-1.5 font-bold text-[#00f0ff] hover:text-white transition-colors">
                                <span>Baca Rilis</span>
                                <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $activities->links() }}
            </div>
        @endif
    </section>
@endsection