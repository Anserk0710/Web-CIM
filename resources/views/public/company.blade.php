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
    <section class="bg-gradient-to-br from-cim-ice/60 to-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            @if(!empty($hero['eyebrow']))
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-blue">
                    {{ $hero['eyebrow'] }}
                </p>
            @endif

            <h1 class="mt-3 text-4xl font-bold tracking-tight text-cim-navy sm:text-5xl">
                {{ $hero['title'] ?? $page?->title ?? 'Company' }}
            </h1>

            @if(!empty($hero['description']))
                <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                    {{ $hero['description'] }}
                </p>
            @endif
        </div>
    </section>

    @if(!empty($story['body']))
        <section class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-blue">
                    Our Story
                </p>

                <h2 class="mt-3 text-3xl font-bold text-cim-navy">
                    {{ $story['heading'] ?? 'Company Story' }}
                </h2>

                <div class="mt-6 whitespace-pre-line leading-8 text-slate-600">
                    {{ $story['body'] }}
                </div>
            </div>

            @if($storyImage !== null)
                <img src="{{ $storyImage->url() }}" alt="{{ $story['image_alt'] ?? '' }}" width="{{ $storyImage->width ?: 1200 }}"
                    height="{{ $storyImage->height ?: 900 }}" loading="lazy"
                    class="aspect-[4/3] w-full rounded-3xl border object-cover">
            @endif
        </section>
    @endif

    @if($metrics->isNotEmpty())
        <section class="border-y bg-slate-50">
            <div class="mx-auto grid max-w-7xl gap-4 px-4 py-8 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                @foreach($metrics as $metric)
                    <article class="rounded-2xl border bg-white p-5">
                        <p class="text-3xl font-bold text-cim-navy">
                            {{ $metric['value'] }} {{ $metric['unit'] ?? '' }}
                        </p>

                        <h2 class="mt-2 font-semibold">
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

    @if(!empty($visionMission['vision']) || !empty($visionMission['mission']))
        <section class="mx-auto grid max-w-7xl gap-5 px-4 py-16 sm:px-6 md:grid-cols-2 lg:px-8">
            @if(!empty($visionMission['vision']))
                <article class="rounded-2xl border p-7">
                    <h2 class="text-2xl font-bold text-cim-navy">
                        Vision
                    </h2>

                    <p class="mt-4 whitespace-pre-line leading-7 text-slate-600">
                        {{ $visionMission['vision'] }}
                    </p>
                </article>
            @endif

            @if(!empty($visionMission['mission']))
                <article class="rounded-2xl border p-7">
                    <h2 class="text-2xl font-bold text-cim-navy">
                        Mission
                    </h2>

                    <p class="mt-4 whitespace-pre-line leading-7 text-slate-600">
                        {{ $visionMission['mission'] }}
                    </p>
                </article>
            @endif
        </section>
    @endif

    @if($values !== [])
        <section class="bg-cim-ice/30">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-cim-navy">
                    {{ $valuesHeading ?: 'Our Values' }}
                </h2>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($values as $value)
                        @if(is_array($value) && !empty($value['title']))
                            <article class="rounded-2xl border bg-white p-6">
                                <h3 class="font-semibold text-cim-navy">
                                    {{ $value['title'] }}
                                </h3>

                                @if(!empty($value['body']))
                                    <p class="mt-3 text-sm leading-6 text-slate-600">
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

    @if($milestones !== [])
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-cim-navy">
                {{ $milestonesHeading ?: 'Milestones' }}
            </h2>

            <ol class="mt-8 space-y-5 border-l-2 border-cim-sky pl-6">
                @foreach($milestones as $milestone)
                    @if(is_array($milestone) && !empty($milestone['title']))
                        <li class="relative">
                            <span class="absolute -left-[1.95rem] top-1 size-3 rounded-full bg-cim-blue" aria-hidden="true"></span>

                            @if(!empty($milestone['date']))
                                <p class="text-sm font-bold text-cim-blue">
                                    {{ $milestone['date'] }}
                                </p>
                            @endif

                            <h3 class="mt-1 font-semibold text-cim-navy">
                                {{ $milestone['title'] }}
                            </h3>

                            @if(!empty($milestone['body']))
                                <p class="mt-2 leading-7 text-slate-600">
                                    {{ $milestone['body'] }}
                                </p>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ol>
        </section>
    @endif

    @if($publicSite['offices']->isNotEmpty())
        <section class="bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-cim-navy">
                    {{ $coverageData['heading'] ?? 'Our Offices' }}
                </h2>

                @if(!empty($coverageData['intro']))
                    <p class="mt-4 max-w-3xl leading-7 text-slate-600">
                        {{ $coverageData['intro'] }}
                    </p>
                @endif

                <div class="mt-8 grid gap-5 md:grid-cols-2">
                    @foreach($publicSite['offices'] as $office)
                        <article class="rounded-2xl border bg-white p-6">
                            <h3 class="font-semibold text-cim-navy">
                                {{ $office->name }}
                            </h3>

                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                                {{ $office->address }}
                            </p>

                            @if($office->phone)
                                <p class="mt-3 text-sm">
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $office->phone) }}"
                                        class="text-cim-blue hover:text-cim-navy">
                                        {{ $office->phone }}
                                    </a>
                                </p>
                            @endif

                            @if($office->email)
                                <p class="mt-2 text-sm">
                                    <a href="mailto:{{ $office->email }}" class="break-all text-cim-blue hover:text-cim-navy">
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

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-cim-navy px-6 py-10 text-white sm:px-10">
            <h2 class="text-2xl font-bold">
                {{ data_get($publicSite, 'company.display_name', 'PT Cahya Intan Medika') }}
            </h2>

            <p class="mt-3 max-w-2xl leading-7 text-slate-200">
                Hubungi kami untuk informasi lebih lanjut.
            </p>

            <a href="{{ route('contact') }}"
                class="mt-6 inline-flex rounded-lg bg-white px-5 py-3 font-semibold text-cim-navy hover:bg-cim-ice">
                Contact Us
            </a>
        </div>
    </section>
@endsection