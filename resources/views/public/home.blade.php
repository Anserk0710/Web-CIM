@extends('layouts.public')

@section('title', 'PT Cahya Intan Medika')

@section(
    'meta_description',
    'Application foundation PT Cahya Intan Medika.'
)

@section('content')
    <section class="bg-gradient-to-b from-cim-ice to-background">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cim-blue">
                    Phase 1
                </p>

                <h1 class="mt-4 text-4xl font-bold tracking-tight text-cim-navy sm:text-5xl">
                    PT Cahya Intan Medika
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                    Laravel 13 public website foundation berjalan melalui
                    Nginx, PHP-FPM, MySQL, Vite, dan Docker Compose.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-lg bg-cim-blue px-5 py-3 text-sm font-semibold text-white hover:bg-cim-navy">
                        Open CMS Shell
                    </a>

                    <a href="{{ route('health.ready') }}"
                        class="rounded-lg border border-cim-blue px-5 py-3 text-sm font-semibold text-cim-blue hover:bg-cim-ice">
                        Database Readiness
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-border bg-card p-6">
                <p class="text-sm font-semibold text-cim-blue">
                    Public
                </p>

                <h2 class="mt-2 text-xl font-semibold text-cim-navy">
                    Blade
                </h2>

                <p class="mt-3 text-sm leading-6 text-muted-foreground">
                    Public pages menggunakan server-rendered Blade dan
                    tidak memuat React CMS bundle.
                </p>
            </article>

            <article class="rounded-2xl border border-border bg-card p-6">
                <p class="text-sm font-semibold text-cim-blue">
                    CMS
                </p>

                <h2 class="mt-2 text-xl font-semibold text-cim-navy">
                    React + Inertia
                </h2>

                <p class="mt-3 text-sm leading-6 text-muted-foreground">
                    CMS menggunakan React, TypeScript, Inertia, Tailwind,
                    dan shadcn-style components.
                </p>
            </article>

            <article class="rounded-2xl border border-border bg-card p-6">
                <p class="text-sm font-semibold text-cim-blue">
                    Runtime
                </p>

                <h2 class="mt-2 text-xl font-semibold text-cim-navy">
                    Docker Compose
                </h2>

                <p class="mt-3 text-sm leading-6 text-muted-foreground">
                    PHP, Node, MySQL, Nginx, dan SMTP development tidak
                    bergantung pada instalasi host Windows.
                </p>
            </article>
        </div>
    </section>
@endsection