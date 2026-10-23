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
    <article class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-600">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-cim-blue">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('activities.index') }}" class="hover:text-cim-blue">Activity</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-medium text-cim-navy">
                    {{ $revision->title }}
                </li>
            </ol>
        </nav>

        <header class="mt-8">
            @if($revision->category)
                <p class="text-sm font-bold uppercase tracking-wide text-cim-blue">
                    {{ $revision->category->name }}
                </p>
            @endif

            <h1 class="mt-3 text-4xl font-bold tracking-tight text-cim-navy sm:text-5xl">
                {{ $revision->title }}
            </h1>

            @if($revision->excerpt)
                <p class="mt-5 text-lg leading-8 text-slate-600">
                    {{ $revision->excerpt }}
                </p>
            @endif

            <p class="mt-5 text-sm text-slate-500">
                {{ $activity->published_at?->format('d-m-Y') }}
            </p>
        </header>

        @if($revision->featureMedia)
            <figure class="mt-8">
                <img src="{{ $revision->featureMedia->url() }}" alt="{{ $revision->title }}"
                    width="{{ $revision->featureMedia->width ?: 1200 }}" height="{{ $revision->featureMedia->height ?: 900 }}"
                    fetchpriority="high" class="aspect-[16/9] w-full rounded-2xl border object-cover">
            </figure>
        @endif

        <div class="mt-10 space-y-8">
            @foreach($revision->blocks as $block)
                @include('public.activities._block', [
                    'block' => $block,
                    'mediaById' => $mediaById,
                    'relatedProductsById' => $relatedProductsById,
                ])
            @endforeach
        </div>

        @if($revision->relatedProducts->isNotEmpty())
            <section class="mt-14 border-t pt-10">
                <h2 class="text-2xl font-bold text-cim-navy">
                    Related Products
                </h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach($revision->relatedProducts as $product)
                        @if($product->publishedRevision && $product->public_slug)
                            <a href="{{ route('products.show', $product->public_slug) }}"
                                class="rounded-xl border p-5 hover:border-cim-blue">
                                <h3 class="font-semibold text-cim-navy">
                                    {{ $product->publishedRevision->name }}
                                </h3>

                                @if($product->publishedRevision->summary)
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ $product->publishedRevision->summary }}
                                    </p>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-14 border-t pt-8">
            <a href="{{ route('activities.index') }}" class="font-semibold text-cim-blue hover:text-cim-navy">
                ← Back to Activity
            </a>
        </div>
    </article>
@endsection