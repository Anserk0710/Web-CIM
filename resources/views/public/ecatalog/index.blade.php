{{-- path: resources/views/public/ecatalog/index.blade.php --}}

@extends('layouts.public')

@section(
    'title',
    $page?->seo_title
        ?: ($page?->title ?: 'E-Catalog | '.config('app.name'))
)

@section(
    'meta_description',
    $page?->seo_description
        ?: 'Daftar E-Catalog dan informasi pengadaan produk.'
)

@section('canonical', route('ecatalog.public.index'))

@section('og_type', 'website')

@if ($page?->ogMedia)
    @section('og_image', $page->ogMedia->url())
@endif

@section('structured_data')
    <script type="application/ld+json">
        @json($structuredData)
    </script>
@endsection

@section('content')
    <section class="border-b bg-cim-ice/40">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-cim-blue">
                Procurement
            </p>

            <h1 class="mt-3 text-4xl font-bold tracking-tight text-cim-navy">
                {{ $page?->title ?: 'E-Catalog' }}
            </h1>

            <p class="mt-3 max-w-2xl leading-7 text-muted-foreground">
                {{ $page?->seo_description
                    ?: 'Temukan informasi pengadaan dan produk yang terhubung dengan E-Catalog.' }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($entries->isEmpty())
            <div class="rounded-2xl border border-dashed p-10 text-center">
                <h2 class="font-semibold text-cim-navy">
                    E-Catalog belum tersedia
                </h2>

                <p class="mt-2 text-sm text-muted-foreground">
                    Belum ada entri pengadaan yang dapat ditampilkan.
                </p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($entries as $entry)
                    <article
                        id="catalog-{{ $entry->id }}"
                        class="flex flex-col rounded-2xl border bg-background p-5"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-cim-blue">
                            {{ $entry->catalog_code }}
                        </p>

                        <h2 class="mt-2 text-xl font-semibold text-cim-navy">
                            {{ $entry->procurement_title }}
                        </h2>

                        @if ($entry->procurement_category)
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{ $entry->procurement_category }}
                            </p>
                        @endif

                        <dl class="mt-5 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Related product
                                </dt>

                                <dd class="mt-1">
                                    @if ($entry->product->public_slug)
                                        <a
                                            href="{{ route(
                                                'products.show',
                                                $entry->product->public_slug,
                                            ) }}"
                                            class="font-medium text-cim-blue hover:text-cim-navy"
                                        >
                                            {{ $entry->product->publishedRevision->name }}
                                        </a>
                                    @else
                                        {{ $entry->product->publishedRevision->name }}
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Availability
                                </dt>

                                <dd class="mt-1">
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        $entry->availability->value,
                                    ) }}
                                </dd>
                            </div>

                            @if ($entry->unit_package)
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Unit / package
                                    </dt>

                                    <dd class="mt-1">
                                        {{ $entry->unit_package }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        @if ($entry->notes)
                            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-muted-foreground">
                                {{ $entry->notes }}
                            </p>
                        @endif

                        <div class="mt-auto flex flex-wrap gap-3 pt-6">
                            <a
                                href="{{ route(
                                    'contact',
                                    [
                                        'type' => 'ECATALOG',
                                        'ecatalog_entry_id' => $entry->id,
                                    ],
                                ) }}"
                                class="inline-flex rounded-lg bg-cim-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-cim-navy"
                            >
                                Ask about this catalog
                            </a>

                            @if ($entry->reference_url)
                                <a
                                    href="{{ $entry->reference_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex rounded-lg border px-4 py-2.5 text-sm font-semibold hover:bg-muted"
                                >
                                    Reference
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($entries->hasPages())
                <nav
                    aria-label="E-Catalog pagination"
                    class="mt-10 flex items-center justify-between gap-4"
                >
                    @if ($entries->onFirstPage())
                        <span class="text-sm text-muted-foreground">
                            Previous
                        </span>
                    @else
                        <a
                            href="{{ $entries->previousPageUrl() }}"
                            class="text-sm font-semibold text-cim-blue"
                        >
                            Previous
                        </a>
                    @endif

                    <span class="text-sm text-muted-foreground">
                        Page {{ $entries->currentPage() }}
                        of {{ $entries->lastPage() }}
                    </span>

                    @if ($entries->hasMorePages())
                        <a
                            href="{{ $entries->nextPageUrl() }}"
                            class="text-sm font-semibold text-cim-blue"
                        >
                            Next
                        </a>
                    @else
                        <span class="text-sm text-muted-foreground">
                            Next
                        </span>
                    @endif
                </nav>
            @endif
        @endif
    </section>
@endsection