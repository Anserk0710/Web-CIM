<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
    </url>
    <url>
        <loc>{{ route('products.index') }}</loc>
    </url>
    @foreach ($products as $product)
        <url>
            <loc>{{ route('products.show', $product->public_slug) }}</loc>
        </url>
    @endforeach
</urlset>
