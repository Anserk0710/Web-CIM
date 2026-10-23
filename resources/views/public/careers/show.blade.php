@extends('layouts.public')

@section('title', ($career->seo_title ?: $career->title) . ' | Career | PT Cahya Intan Medika')

@section(
    'meta_description',
    $career->seo_description ?: ($career->summary ?: $career->title)
)

@section('canonical', route('careers.show', $career->slug))

@if($jobPosting)
    @section('structured_data')
        @json(
            $jobPosting,
            JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        )
    @endsection
@endif

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-600">
            <a href="{{ route('careers.index') }}" class="hover:text-cim-blue">
                Karir
            </a>
            <span aria-hidden="true"> / </span>
            <span aria-current="page" class="font-medium text-cim-navy">
                {{ $career->title }}
            </span>
        </nav>

        <header class="mt-8">
            @php
                $employmentTypeLabel = match($career->employment_type?->value ?? '') {
                    'FULL_TIME' => 'Penuh Waktu (Full Time)',
                    'PART_TIME' => 'Paruh Waktu (Part Time)',
                    'CONTRACT' => 'Kontrak (Contract)',
                    'INTERNSHIP' => 'Magang (Internship)',
                    default => str_replace('_', ' ', $career->employment_type?->value ?? ''),
                };
            @endphp
            <span class="rounded-full bg-cim-ice px-3 py-1 text-xs font-bold text-cim-navy">
                {{ $employmentTypeLabel }}
            </span>

            <h1 class="mt-4 text-4xl font-bold tracking-tight text-cim-navy sm:text-5xl">
                {{ $career->title }}
            </h1>

            <dl class="mt-6 grid gap-4 rounded-2xl border bg-slate-50 p-5 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500">Departemen</dt>
                    <dd class="mt-1 font-semibold text-cim-navy">
                        {{ $career->department }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Lokasi Penempatan</dt>
                    <dd class="mt-1 font-semibold text-cim-navy">
                        {{ $career->location }}
                    </dd>
                </div>

                @if($career->closes_at)
                    <div>
                        <dt class="text-sm text-slate-500">Batas Waktu Lamaran</dt>
                        <dd class="mt-1 font-semibold text-cim-navy">
                            {{ $career->closes_at->timezone('Asia/Makassar')->format('d-m-Y H:i') }} WITA
                        </dd>
                    </div>
                @endif
            </dl>
        </header>

        @if($career->summary)
            <section class="mt-10">
                <h2 class="text-2xl font-bold text-cim-navy">
                    Gambaran Pekerjaan
                </h2>

                <p class="mt-4 whitespace-pre-line leading-8 text-slate-600">
                    {{ $career->summary }}
                </p>
            </section>
        @endif

        <section class="mt-10">
            <h2 class="text-2xl font-bold text-cim-navy">
                Tanggung Jawab Utama
            </h2>

            <div class="mt-4 whitespace-pre-line leading-8 text-slate-600">
                {{ $career->responsibilities }}
            </div>
        </section>

        <section class="mt-10">
            <h2 class="text-2xl font-bold text-cim-navy">
                Kualifikasi & Persyaratan
            </h2>

            <div class="mt-4 whitespace-pre-line leading-8 text-slate-600">
                {{ $career->requirements }}
            </div>
        </section>

        @include('public.careers._application-panel')
    </article>
@endsection
