{{-- path: resources/views/public/privacy.blade.php --}}

@extends('layouts.public')

@section(
    'title',
    $page?->seo_title ?: ($page?->title ?? 'Privacy Notice')
)

@section(
    'meta_description',
    $page?->seo_description ?: 'Privacy notice PT Cahya Intan Medika.'
)

@section('canonical', route('privacy'))

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-cim-blue">
            Privacy
        </p>

        <h1 class="mt-3 text-4xl font-bold tracking-tight text-cim-navy">
            {{ $page?->title ?? 'Privacy Notice' }}
        </h1>

        @if(
            !empty($policy['version'])
            && !empty($policy['body'])
        )
            <p class="mt-4 text-xs text-muted-foreground">
                Version: {{ $policy['version'] }}
            </p>

            <div class="mt-8 whitespace-pre-line leading-7 text-slate-700">
                {{ $policy['body'] }}
            </div>
        @else
            <div class="mt-8 rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-950">
                Privacy notice belum dikonfigurasi.
            </div>
        @endif

        <a
            href="{{ route('contact') }}"
            class="mt-8 inline-flex rounded-lg bg-cim-blue px-5 py-3 font-semibold text-white hover:bg-cim-navy"
        >
            Back to Contact
        </a>
    </section>
@endsection