@extends('layouts.public')

@section('title', ($page?->seo_title ?: 'Career') . ' | PT Cahya Intan Medika')

@section(
    'meta_description',
    $page?->seo_description ?: 'Informasi karier dan lowongan kerja PT Cahya Intan Medika.'
)

@section('content')
    <section class="bg-gradient-to-br from-cim-ice/60 to-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-cim-blue">
                Career
            </p>

            <h1 class="mt-3 text-4xl font-bold tracking-tight text-cim-navy sm:text-5xl">
                {{ $hero['title'] ?? $page?->title ?? 'Career' }}
            </h1>

            @if(!empty($hero['description']))
                <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                    {{ $hero['description'] }}
                </p>
            @endif

            @if(!empty($branding['description']))
                <div class="mt-8 max-w-3xl whitespace-pre-line leading-7 text-slate-600">
                    {{ $branding['description'] }}
                </div>
            @endif
        </div>
    </section>

    @if(!empty($branding['benefits']))
        @php
            $benefits = app(\App\Support\PublicSite\PageContent::class)
                ->listValue($branding['benefits']);
        @endphp

        @if($benefits !== [])
            <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-cim-navy">
                    {{ $branding['heading'] ?? 'Career at CIM' }}
                </h2>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach($benefits as $benefit)
                        @if(is_array($benefit) && !empty($benefit['title']))
                            <article class="rounded-2xl border p-6">
                                <h3 class="font-semibold text-cim-navy">
                                    {{ $benefit['title'] }}
                                </h3>

                                @if(!empty($benefit['body']))
                                    <p class="mt-3 text-sm leading-6 text-slate-600">
                                        {{ $benefit['body'] }}
                                    </p>
                                @endif
                            </article>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    @endif

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-cim-navy">
                    Open Positions
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Lowongan yang ditampilkan hanya yang sedang menerima lamaran.
                </p>
            </div>

            <span class="rounded-full bg-cim-ice px-4 py-2 text-sm font-semibold text-cim-navy">
                {{ $careers->total() }} posisi
            </span>
        </div>

        <form method="GET" action="{{ route('careers.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
            <label for="q" class="sr-only">Cari posisi</label>

            <input id="q" name="q" value="{{ $filters['q'] }}" maxlength="100"
                placeholder="Cari posisi, departemen, atau lokasi"
                class="min-w-0 flex-1 rounded-lg border border-input px-4 py-3">

            <button type="submit" class="rounded-lg bg-cim-blue px-5 py-3 font-semibold text-white hover:bg-cim-navy">
                Search
            </button>
        </form>

        @if($careers->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed p-10 text-center">
                <h3 class="text-xl font-semibold text-cim-navy">
                    Belum ada lowongan aktif
                </h3>

                <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600">
                    Saat ini tidak ada lowongan yang menerima lamaran. Silakan kunjungi halaman Company untuk mengetahui
                    informasi perusahaan.
                </p>

                <a href="{{ route('company') }}"
                    class="mt-5 inline-flex rounded-lg bg-cim-navy px-5 py-3 font-semibold text-white hover:bg-cim-blue">
                    About CIM
                </a>
            </div>
        @else
            <div class="mt-8 grid gap-5 md:grid-cols-2">
                @foreach($careers as $career)
                    <article class="rounded-2xl border bg-white p-6">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                            <span class="rounded-full bg-cim-ice px-3 py-1 text-cim-navy">
                                {{ str_replace('_', ' ', $career->employment_type->value) }}
                            </span>

                            <span class="text-slate-500">
                                {{ $career->department }}
                            </span>
                        </div>

                        <h3 class="mt-4 text-xl font-bold text-cim-navy">
                            <a href="{{ route('careers.show', $career->slug) }}" class="hover:text-cim-blue">
                                {{ $career->title }}
                            </a>
                        </h3>

                        <p class="mt-3 text-sm text-slate-600">
                            {{ $career->location }}
                        </p>

                        @if($career->summary)
                            <p class="mt-4 text-sm leading-6 text-slate-600">
                                {{ $career->summary }}
                            </p>
                        @endif

                        @if($career->closes_at)
                            <p class="mt-4 text-sm text-slate-500">
                                Batas waktu:
                                {{ $career->closes_at->timezone('Asia/Makassar')->format('d-m-Y H:i') }} WITA
                            </p>
                        @endif

                        <a href="{{ route('careers.show', $career->slug) }}"
                            class="mt-5 inline-flex font-semibold text-cim-blue hover:text-cim-navy">
                            View vacancy
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $careers->links() }}
            </div>
        @endif
    </section>
@endsection