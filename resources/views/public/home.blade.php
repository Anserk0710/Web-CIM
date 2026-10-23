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
    <section class="overflow-hidden bg-gradient-to-br from-cim-ice/70 via-white to-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 md:py-24 lg:grid-cols-2 lg:px-8">
            <div>
                @if(!empty($hero['eyebrow']))
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-cim-blue">
                        {{ $hero['eyebrow'] }}
                    </p>
                @endif

                <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight text-cim-navy sm:text-5xl lg:text-6xl">
                    {{ $hero['title'] }}
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                    {{ $hero['description'] }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="{{ $hero['primary_cta_url'] }}"
                        class="inline-flex items-center justify-center rounded-lg bg-cim-blue px-5 py-3 font-semibold text-white transition-colors hover:bg-cim-navy"
                    >
                        {{ $hero['primary_cta_label'] }}
                    </a>

                    @if(!empty($hero['secondary_cta_label']))
                        <a
                            href="{{ $hero['secondary_cta_url'] }}"
                            class="inline-flex items-center justify-center rounded-lg border border-cim-blue px-5 py-3 font-semibold text-cim-blue transition-colors hover:bg-cim-ice/50"
                        >
                            {{ $hero['secondary_cta_label'] }}
                        </a>
                    @endif
                </div>
            </div>

            @if($heroImage !== null)
                <figure class="relative">
                    <div class="absolute -inset-4 rounded-[2rem] bg-cim-sky/30 blur-2xl" aria-hidden="true"></div>

                    <img
                        src="{{ $heroImage->url() }}"
                        alt="{{ $hero['image_alt'] ?? '' }}"
                        width="{{ $heroImage->width ?: 1200 }}"
                        height="{{ $heroImage->height ?: 900 }}"
                        fetchpriority="high"
                        class="relative aspect-[4/3] w-full rounded-3xl border border-white object-cover shadow-xl"
                    >
                </figure>
            @endif
        </div>
    </section>

    @if($metrics->isNotEmpty())
        <section aria-label="Company metrics" class="border-y border-border bg-white">
            <div class="mx-auto grid max-w-7xl gap-px px-4 py-8 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                @foreach($metrics as $metric)
                    <article class="p-5">
                        <p class="text-3xl font-bold tracking-tight text-cim-navy">
                            {{ $metric['value'] }} {{ $metric['unit'] ?? '' }}
                        </p>

                        <h2 class="mt-2 text-sm font-semibold text-slate-800">
                            {{ $metric['label'] }}
                        </h2>

                        @if(!empty($metric['source_note']))
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                {{ $metric['source_note'] }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($sections->has('capabilities') && $capabilities !== [])
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-blue">
                    Capabilities
                </p>

                <h2 class="mt-3 text-3xl font-bold tracking-tight text-cim-navy sm:text-4xl">
                    {{ $capabilitiesHeading ?: 'Capabilities' }}
                </h2>

                @if($capabilitiesIntro)
                    <p class="mt-4 leading-7 text-slate-600">
                        {{ $capabilitiesIntro }}
                    </p>
                @endif
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($capabilities as $item)
                    @if(is_array($item) && !empty($item['title']))
                        <article class="rounded-2xl border bg-white p-6 transition-colors hover:border-cim-sky">
                            <h3 class="text-lg font-semibold text-cim-navy">
                                {{ $item['title'] }}
                            </h3>

                            @if(!empty($item['body']))
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    {{ $item['body'] }}
                                </p>
                            @endif
                        </article>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    @if($sections->has('lifecycle') && $lifecycle !== [])
        <section class="bg-cim-navy text-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-sky">
                        Service lifecycle
                    </p>

                    <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">
                        {{ $lifecycleHeading ?: 'Our Process' }}
                    </h2>
                </div>

                <ol class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    @foreach($lifecycle as $index => $step)
                        @if(is_array($step) && !empty($step['title']))
                            <li class="rounded-2xl border border-white/20 bg-white/5 p-6">
                                <p class="text-sm font-bold text-cim-sky">
                                    STEP {{ $index + 1 }}
                                </p>

                                <h3 class="mt-4 text-lg font-semibold">
                                    {{ $step['title'] }}
                                </h3>

                                @if(!empty($step['body']))
                                    <p class="mt-3 text-sm leading-6 text-slate-200">
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

    @if($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-blue">
                        Product portfolio
                    </p>

                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-cim-navy">
                        {{ $featuredData['heading'] ?? 'Featured Products' }}
                    </h2>
                </div>

                <a
                    href="{{ route('products.index') }}"
                    class="font-semibold text-cim-blue hover:text-cim-navy"
                >
                    View all products
                </a>
            </div>

            <div class="mt-8 flex snap-x gap-5 overflow-x-auto pb-4">
                @foreach($featuredProducts as $product)
                    @php
                        $revision = $product->publishedRevision;
                        $cover = $revision?->media->first(
                            fn($item) => $item->role->value === 'GALLERY'
                        );
                    @endphp

                    <article class="w-[min(82vw,320px)] shrink-0 snap-start overflow-hidden rounded-2xl border bg-white">
                        @if($cover?->media)
                            <img
                                src="{{ $cover->media->url() }}"
                                alt="{{ $cover->alt_text ?: $revision->name }}"
                                width="{{ $cover->media->width ?: 800 }}"
                                height="{{ $cover->media->height ?: 600 }}"
                                loading="lazy"
                                class="aspect-[4/3] w-full object-cover"
                            >
                        @endif

                        <div class="p-5">
                            <p class="text-sm font-medium text-cim-blue">
                                {{ $revision?->category?->name }}
                            </p>

                            <h3 class="mt-2 text-lg font-semibold text-cim-navy">
                                <a href="{{ route('products.show', $product->public_slug) }}">
                                    {{ $revision?->name }}
                                </a>
                            </h3>

                            @if($revision?->summary)
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    {{ $revision->summary }}
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $coverageAreas = app(\App\Support\PublicSite\PageContent::class)
            ->listValue($coverageData['areas'] ?? data_get($publicSite, 'coverage.regions', []));
    @endphp

    @if($coverageAreas !== [] || $publicSite['offices']->isNotEmpty())
        <section class="bg-slate-50">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-blue">
                        Coverage
                    </p>

                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-cim-navy">
                        {{ $coverageData['heading'] ?? 'Area Coverage' }}
                    </h2>

                    @if(!empty($coverageData['intro']))
                        <p class="mt-4 leading-7 text-slate-600">
                            {{ $coverageData['intro'] }}
                        </p>
                    @endif

                    @if($coverageAreas !== [])
                        <ul class="mt-6 flex flex-wrap gap-2">
                            @foreach($coverageAreas as $area)
                                @if(is_string($area) && trim($area) !== '')
                                    <li class="rounded-full border bg-white px-4 py-2 text-sm text-slate-700">
                                        {{ $area }}
                                    </li>
                                @elseif(is_array($area) && !empty($area['name']))
                                    <li class="rounded-full border bg-white px-4 py-2 text-sm text-slate-700">
                                        {{ $area['name'] }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if($publicSite['offices']->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($publicSite['offices'] as $office)
                            <article class="rounded-2xl border bg-white p-5">
                                <h3 class="font-semibold text-cim-navy">
                                    {{ $office->name }}

                                    @if($office->is_head_office)
                                        <span class="ml-2 rounded-full bg-cim-ice px-2 py-1 text-xs text-cim-navy">
                                            Head Office
                                        </span>
                                    @endif
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $office->address }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($featuredActivities->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-blue">
                        Latest activities
                    </p>

                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-cim-navy">
                        {{ $activityData['heading'] ?? 'Activity' }}
                    </h2>
                </div>

                <a href="{{ route('activities.index') }}" class="font-semibold text-cim-blue hover:text-cim-navy">
                    View all activities
                </a>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @foreach($featuredActivities as $activity)
                    @php($revision = $activity->publishedRevision)

                    <article class="overflow-hidden rounded-2xl border bg-white">
                        @if($revision?->featureMedia)
                            <img
                                src="{{ $revision->featureMedia->url() }}"
                                alt="{{ $revision->title }}"
                                width="{{ $revision->featureMedia->width ?: 800 }}"
                                height="{{ $revision->featureMedia->height ?: 600 }}"
                                loading="lazy"
                                class="aspect-[4/3] w-full object-cover"
                            >
                        @endif

                        <div class="p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-cim-blue">
                                {{ $revision?->category?->name }}
                            </p>

                            <h3 class="mt-2 font-semibold text-cim-navy">
                                <a href="{{ route('activities.show', $activity->public_slug) }}">
                                    {{ $revision?->title }}
                                </a>
                            </h3>

                            @if($revision?->excerpt)
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    {{ $revision->excerpt }}
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($ctaData['heading']))
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-gradient-to-r from-cim-navy to-cim-blue px-6 py-10 text-white sm:px-10 lg:flex lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-bold sm:text-3xl">
                        {{ $ctaData['heading'] }}
                    </h2>

                    @if(!empty($ctaData['description']))
                        <p class="mt-3 leading-7 text-slate-100">
                            {{ $ctaData['description'] }}
                        </p>
                    @endif
                </div>

                <a
                    href="{{ app(\App\Support\PublicSite\PageContent::class)->safeUrl($ctaData['primary_cta_url'] ?? null) ?: route('contact') }}"
                    class="mt-6 inline-flex rounded-lg bg-white px-5 py-3 font-semibold text-cim-navy hover:bg-cim-ice lg:mt-0"
                >
                    {{ $ctaData['primary_cta_label'] ?? 'Contact Us' }}
                </a>
            </div>
        </section>
    @endif
@endsection