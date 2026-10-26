@extends('layouts.public')

@section('title', ($revision->seo_title ?: $revision->title) . ' | PT Cahya Intan Medika')

@section(
    'meta_description',
    $revision->seo_description ?: ($revision->excerpt ?: $revision->title)
)

@section('canonical', route('activities.show', $activity->public_slug))

@if($revision->ogMedia)
    @section('og_image', $revision->ogMedia->url())
@elseif($revision->featureMedia)
    @section('og_image', $revision->featureMedia->url())
@endif

@section('structured_data')
    @json([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $revision->title,
        'description' => $revision->seo_description ?: $revision->excerpt,
        'datePublished' => $activity->published_at?->toIso8601String(),
        'dateModified' => $activity->updated_at?->toIso8601String(),
        'mainEntityOfPage' => route('activities.show', $activity->public_slug),
        'image' => $revision->featureMedia?->url(),
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
@endsection

@section('content')
    <article class="mx-auto max-w-4xl px-4 pt-28 pb-16 sm:pt-36 sm:pb-24 lg:px-8">
        {{-- BREADCRUMB --}}
        <nav aria-label="Breadcrumb" class="text-xs font-mono text-slate-400">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('activities.index') }}" class="hover:text-white">Kegiatan</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-[#00f0ff] truncate max-w-md">
                    {{ $revision->title }}
                </li>
            </ol>
        </nav>

        {{-- ARTICLE HEADER --}}
        <header class="mt-8 border-b border-white/10 pb-8">
            @if($revision->category)
                <span class="inline-flex items-center gap-2 rounded-full border border-[#00f0ff]/30 bg-[#00f0ff]/10 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-[#00f0ff]">
                    {{ $revision->category->name }}
                </span>
            @endif

            <h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-[1.12]">
                {{ $revision->title }}
            </h1>

            @if($revision->excerpt)
                <p class="mt-6 text-lg sm:text-xl leading-relaxed text-slate-300">
                    {{ $revision->excerpt }}
                </p>
            @endif

            <div class="mt-6 flex items-center gap-4 text-xs font-mono text-slate-500">
                <time datetime="{{ $activity->published_at?->toIso8601String() }}">
                    Diterbitkan: {{ $activity->published_at?->translatedFormat('d F Y') }}
                </time>
                <span>·</span>
                <span>PT CAHYA INTAN MEDIKA</span>
            </div>
        </header>

        {{-- FEATURE IMAGE / MEDIA PLACEMENT --}}
        @if($revision->featureMedia)
            <figure class="mt-10 overflow-hidden rounded-3xl border border-white/10 shadow-2xl">
                <img
                    src="{{ $revision->featureMedia->url() }}"
                    alt="{{ $revision->title }}"
                    width="{{ $revision->featureMedia->width ?: 1200 }}"
                    height="{{ $revision->featureMedia->height ?: 900 }}"
                    fetchpriority="high"
                    class="aspect-[16/9] w-full object-cover"
                >
            </figure>
        @endif

        {{-- CONTENT BLOCKS --}}
        <div class="mt-12 space-y-8 rounded-2xl border border-white/10 bg-[#0c121e]/60 p-6 sm:p-10 backdrop-blur-md">
            @foreach($revision->blocks as $block)
                @include('public.activities._block', [
                    'block' => $block,
                    'mediaById' => $mediaById,
                    'relatedProductsById' => $relatedProductsById,
                ])
            @endforeach
        </div>

        {{-- RELATED PRODUCTS --}}
        @if($revision->relatedProducts->isNotEmpty())
            <section class="mt-16 border-t border-white/10 pt-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#00f0ff]">
                    PRODUK TERKAIT DOKUMENTASI INI
                </p>
                <h2 class="mt-2 text-2xl font-bold text-white">
                    Instrumen yang Digunakan
                </h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach($revision->relatedProducts as $product)
                        @if($product->publishedRevision && $product->public_slug)
                            <a
                                href="{{ route('products.show', $product->public_slug) }}"
                                class="tech-glow-card rounded-2xl border border-white/10 bg-[#0c121e]/80 p-6 block"
                            >
                                <span class="text-xs font-mono text-[#00f0ff]">PRODUK MEDIS</span>
                                <h3 class="mt-2 text-lg font-bold text-white">
                                    {{ $product->publishedRevision->name }}
                                </h3>

                                @if($product->publishedRevision->summary)
                                    <p class="mt-2 text-xs leading-relaxed text-slate-400 line-clamp-2">
                                        {{ $product->publishedRevision->summary }}
                                    </p>
                                @endif
                                <p class="mt-4 text-xs font-semibold text-[#00f0ff] flex items-center justify-between">
                                    <span>Lihat Spesifikasi</span>
                                    <span aria-hidden="true">↗</span>
                                </p>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection