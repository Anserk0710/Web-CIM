@extends('layouts.public')

@section('title', '404 — Page Not Found | PT Cahya Intan Medika')

@section('robots', 'noindex,nofollow')

@section('content')
    <section class="mx-auto flex min-h-[55vh] max-w-3xl flex-col items-center justify-center px-4 py-16 text-center">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-cim-blue">
            Error 404
        </p>

        <h1 class="mt-4 text-4xl font-bold tracking-tight text-cim-navy">
            Halaman tidak ditemukan
        </h1>

        <p class="mt-4 max-w-xl leading-7 text-slate-600">
            Halaman yang Anda cari tidak tersedia, mungkin sudah dipindahkan atau tidak lagi dipublikasikan.
        </p>

        <a href="{{ route('home') }}"
            class="mt-8 rounded-lg bg-cim-blue px-5 py-3 font-semibold text-white hover:bg-cim-navy">
            Kembali ke Home
        </a>
    </section>
@endsection