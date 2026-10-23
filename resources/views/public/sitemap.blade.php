@if (isset($urls))
{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $item)
    <url>
        <loc>{{ $item['loc'] }}</loc>
@if (!empty($item['lastmod']))
        <lastmod>{{ substr($item['lastmod'], 0, 10) }}</lastmod>
@endif
    </url>
@endforeach
</urlset>
@else
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
    </url>

    <url>
        <loc>{{ route('products.index') }}</loc>
    </url>

    <url>
        <loc>{{ route('ecatalog.public.index') }}</loc>
    </url>

    @foreach ($products ?? [] as $product)
        <url>
            <loc>{{ route('products.show', $product->public_slug) }}</loc>

            @if ($product->publishedRevision?->updated_at)
                <lastmod>{{ $product->publishedRevision->updated_at->toAtomString() }}</lastmod>
            @endif
        </url>
    @endforeach
</urlset>
@endif
