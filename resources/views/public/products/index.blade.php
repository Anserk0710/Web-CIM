@extends('layouts.public')

@section('title', 'Products | PT Cahya Intan Medika')

@section(
    'meta_description',
    'Daftar produk PT Cahya Intan Medika.'
)

@section('canonical', route('products.index'))

@section('content')
    <section class="border-b bg-cim-ice/40">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-cim-navy">
                Products
            </h1>

            <p class="mt-3 max-w-2xl text-muted-foreground">
                Temukan produk berdasarkan kategori, brand, atau tipe.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('products.index') }}"
            class="grid gap-4 rounded-2xl border bg-background p-5 md:grid-cols-4">
            <div>
                <label for="q" class="mb-2 block text-sm font-medium">
                    Search
                </label>

                <input id="q" name="q" value="{{ $filters['q'] }}" class="w-full rounded-lg border border-input px-3 py-2"
                    placeholder="Nama produk...">
            </div>

            <div>
                <label for="category" class="mb-2 block text-sm font-medium">
                    Category
                </label>

                <select id="category" name="category" class="w-full rounded-lg border border-input px-3 py-2">
                    <option value="">
                        Semua
                    </option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(
                            $filters['category']
                            === $category->slug
                        )>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="brand" class="mb-2 block text-sm font-medium">
                    Brand
                </label>

                <select id="brand" name="brand" class="w-full rounded-lg border border-input px-3 py-2">
                    <option value="">
                        Semua
                    </option>

                    @foreach ($brands as $brand)
                        <option value="{{ $brand->slug }}" @selected(
                            $filters['brand']
                            === $brand->slug
                        )>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="type" class="mb-2 block text-sm font-medium">
                    Type
                </label>

                <select id="type" name="type" class="w-full rounded-lg border border-input px-3 py-2">
                    <option value="">
                        Semua
                    </option>

                    @foreach ($types as $type)
                        <option value="{{ $type->slug }}" @selected(
                            $filters['type']
                            === $type->slug
                        )>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <button type="submit" class="rounded-lg bg-cim-blue px-5 py-2.5 text-sm font-semibold text-white">
                    Apply filter
                </button>
            </div>
        </form>

        @if ($products->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed p-10 text-center">
                <h2 class="font-semibold text-cim-navy">
                    Product tidak ditemukan
                </h2>
            </div>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $revision = $product->publishedRevision;

                        $cover = $revision
                            ->media
                            ->firstWhere(
                                'role',
                                $galleryRole,
                            );
                    @endphp

                    <article class="overflow-hidden rounded-2xl border bg-background">
                        @if ($cover)
                            <img src="{{ $cover->media->url() }}" alt="{{ $cover->alt_text ?: $revision->name }}"
                                class="aspect-[4/3] w-full object-cover" loading="lazy">
                        @endif

                        <div class="p-5">
                            <p class="text-sm font-medium text-cim-blue">
                                {{ $revision->category->name }}
                            </p>

                            <h2 class="mt-2 text-xl font-semibold text-cim-navy">
                                <a href="{{ route('products.show', $product->public_slug) }}">
                                    {{ $revision->name }}
                                </a>
                            </h2>

                            @if ($revision->summary)
                                <p class="mt-3 text-sm leading-6 text-muted-foreground">
                                    {{ $revision->summary }}
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($products->hasPages())
                <nav class="mt-10 flex items-center justify-between" aria-label="Pagination">
                    @if ($products->onFirstPage())
                        <span class="text-sm text-muted-foreground">
                            Previous
                        </span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="text-sm font-semibold text-cim-blue">
                            Previous
                        </a>
                    @endif

                    <span class="text-sm text-muted-foreground">
                        Page {{ $products->currentPage() }}
                        of {{ $products->lastPage() }}
                    </span>

                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="text-sm font-semibold text-cim-blue">
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