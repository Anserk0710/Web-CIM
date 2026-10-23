@extends('layouts.public')

@section(
    'title',
    $revision->seo_title ?: $revision->name
)

@section(
    'meta_description',
    $revision->seo_description
    ?: ($revision->summary ?: $revision->name)
)

@if ($preview)
@section('robots', 'noindex,nofollow')
@else
@section(
    'canonical',
    route(
        'products.show',
        $product->public_slug
    )
)
@endif

@if ($revision->ogMedia)
@section(
    'og_image',
    $revision->ogMedia->url()
)
@endif

@if (!$preview)
    @section('og_type', 'product')

    @php
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => route(
                'products.show',
                $product->public_slug,
            ).'#product',
            'name' => $revision->name,
            'description' => trim(
                strip_tags(
                    $revision->summary
                    ?: ($revision->description ?: $revision->name)
                )
            ),
            'url' => route(
                'products.show',
                $product->public_slug,
            ),
        ];

        $schemaImages = $revision->media
            ->where('role', $galleryRole)
            ->map(
                fn ($item) => $item->media?->url()
            )
            ->filter()
            ->values()
            ->all();

        if ($schemaImages !== []) {
            $productSchema['image'] = $schemaImages;
        }

        if ($revision->brand !== null) {
            $productSchema['brand'] = [
                '@type' => 'Brand',
                'name' => $revision->brand->name,
            ];
        }

        if ($revision->category !== null) {
            $productSchema['category'] =
                $revision->category->name;
        }

        $schemaProperties = $revision
            ->specificationGroups
            ->flatMap(
                fn ($group) => $group->items->map(
                    fn ($item) => [
                        '@type' => 'PropertyValue',
                        'name' => $group->name
                            .' - '.$item->label,
                        'value' => trim(
                            $item->value
                            .(
                                $item->unit
                                    ? ' '.$item->unit
                                    : ''
                            )
                        ),
                    ]
                )
            )
            ->values()
            ->all();

        if ($schemaProperties !== []) {
            $productSchema['additionalProperty'] =
                $schemaProperties;
        }
    @endphp

    @section('structured_data')
        <script type="application/ld+json">
            @json($productSchema)
        </script>
    @endsection
@endif

@section('content')
    @php
        $gallery = $revision
            ->media
            ->where(
                'role',
                $galleryRole
            )
            ->values();

        $brochure = $revision
            ->media
            ->firstWhere(
                'role',
                $brochureRole
            );
    @endphp

    @if ($preview)
        <div class="bg-amber-100 px-4 py-3 text-center text-sm font-semibold text-amber-900">
            Authenticated preview — WORKING revision. Halaman ini bukan konten live.
        </div>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                @if ($gallery->isNotEmpty())
                    <img src="{{ $gallery->first()->media->url() }}" alt="{{ $gallery->first()->alt_text ?: $revision->name }}"
                        class="aspect-[4/3] w-full rounded-2xl border object-cover">

                    @if ($gallery->count() > 1)
                        <div class="mt-4 grid grid-cols-4 gap-3">
                            @foreach ($gallery->skip(1) as $item)
                                <img src="{{ $item->media->url() }}" alt="{{ $item->alt_text ?: $revision->name }}"
                                    class="aspect-square rounded-lg border object-cover" loading="lazy">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-muted text-muted-foreground">
                        No product image
                    </div>
                @endif
            </div>

            <div>
                <p class="text-sm font-semibold text-cim-blue">
                    {{ $revision->category->name }}
                </p>

                <h1 class="mt-2 text-4xl font-bold tracking-tight text-cim-navy">
                    {{ $revision->name }}
                </h1>

                @if ($revision->brand || $revision->type)
                    <p class="mt-3 text-sm text-muted-foreground">
                        @if ($revision->brand)
                            {{ $revision->brand->name }}
                        @endif

                        @if ($revision->brand && $revision->type)
                            ·
                        @endif

                        @if ($revision->type)
                            {{ $revision->type->name }}
                        @endif
                    </p>
                @endif

                @if ($revision->summary)
                    <p class="mt-6 text-lg leading-8 text-slate-600">
                        {{ $revision->summary }}
                    </p>
                @endif

                @if ($revision->highlights->isNotEmpty())
                    <ul class="mt-6 space-y-2">
                        @foreach ($revision->highlights as $highlight)
                            <li class="flex gap-3 text-sm">
                                <span class="text-cim-blue">
                                    ✓
                                </span>

                                <span>
                                    {{ $highlight->text }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($brochure)
                    <a href="{{ $brochure->media->url() }}" target="_blank" rel="noopener"
                        class="mt-8 inline-flex rounded-lg bg-cim-blue px-5 py-3 text-sm font-semibold text-white">
                        Download brochure
                    </a>
                @endif
            </div>
        </div>

        @if ($revision->description)
            <section class="mt-14 border-t pt-10">
                <h2 class="text-2xl font-bold text-cim-navy">
                    Description
                </h2>

                <div class="mt-5 whitespace-pre-line leading-7 text-slate-700">
                    {{ $revision->description }}
                </div>
            </section>
        @endif

        @if ($revision->specificationGroups->isNotEmpty())
            <section class="mt-14 border-t pt-10">
                <h2 class="text-2xl font-bold text-cim-navy">
                    Specifications
                </h2>

                <div class="mt-6 space-y-8">
                    @foreach ($revision->specificationGroups as $group)
                        <div>
                            <h3 class="font-semibold text-cim-navy">
                                {{ $group->name }}
                            </h3>

                            <dl class="mt-3 divide-y rounded-xl border">
                                @foreach ($group->items as $item)
                                    <div class="grid grid-cols-2 gap-4 p-4">
                                        <dt class="font-medium">
                                            {{ $item->label }}
                                        </dt>

                                        <dd>
                                            {{ $item->value }}

                                            @if ($item->unit)
                                                {{ $item->unit }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($revision->relatedProducts->isNotEmpty())
            <section class="mt-14 border-t pt-10">
                <h2 class="text-2xl font-bold text-cim-navy">
                    Related Products
                </h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($revision->relatedProducts as $related)
                        @if (
                                $related->publishedRevision
                                && $related->public_slug
                            )
                            <a href="{{ route('products.show', $related->public_slug) }}" class="rounded-xl border p-5">
                                <strong class="text-cim-navy">
                                    {{ $related->publishedRevision->name }}
                                </strong>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    </section>
@endsection