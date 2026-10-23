@php
    $data = (array) $block->data;
@endphp

@if($block->type === 'paragraph')
        <p class="whitespace-pre-line text-base leading-8 text-slate-700">
            {{ $data['text'] ?? '' }}
        </p>

@elseif($block->type === 'heading')
        @php
            $headingLevel = max(2, min(4, (int) ($data['level'] ?? 2)));
        @endphp

        @if($headingLevel === 2)
            <h2 class="text-2xl font-bold text-cim-navy">
                {{ $data['text'] ?? '' }}
            </h2>
        @elseif($headingLevel === 3)
            <h3 class="text-xl font-bold text-cim-navy">
                {{ $data['text'] ?? '' }}
            </h3>
        @else
            <h4 class="text-lg font-bold text-cim-navy">
                {{ $data['text'] ?? '' }}
            </h4>
        @endif

@elseif($block->type === 'image')
        @php
            $image = $mediaById->get((int) ($data['media_id'] ?? 0));
        @endphp

        @if($image)
            <figure>
                <img
                    src="{{ $image->url() }}"
                    alt="{{ $data['alt'] ?? '' }}"
                    width="{{ $image->width ?: 1200 }}"
                    height="{{ $image->height ?: 900 }}"
                    loading="lazy"
                    class="w-full rounded-2xl border object-cover"
                >

                @if(!empty($data['caption']))
                    <figcaption class="mt-2 text-sm text-slate-500">
                        {{ $data['caption'] }}
                    </figcaption>
                @endif
            </figure>
        @endif

@elseif($block->type === 'gallery')
        @php
            $galleryIds = $data['media_ids'] ?? [];
        @endphp

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($galleryIds as $mediaId)
                @php
                    $image = $mediaById->get((int) $mediaId);
                @endphp

                @if($image)
                    <figure>
                        <img
                            src="{{ $image->url() }}"
                            alt=""
                            width="{{ $image->width ?: 800 }}"
                            height="{{ $image->height ?: 600 }}"
                            loading="lazy"
                            class="aspect-[4/3] w-full rounded-xl border object-cover"
                        >
                    </figure>
                @endif
            @endforeach
        </div>

        @if(!empty($data['caption']))
            <p class="mt-2 text-sm text-slate-500">
                {{ $data['caption'] }}
            </p>
        @endif

@elseif($block->type === 'quote')
        <blockquote class="border-l-4 border-cim-blue bg-cim-ice/30 px-6 py-5">
            <p class="text-lg italic leading-8 text-cim-navy">
                “{{ $data['text'] ?? '' }}”
            </p>

            @if(!empty($data['attribution']))
                <footer class="mt-3 text-sm font-semibold text-slate-600">
                    {{ $data['attribution'] }}
                </footer>
            @endif
        </blockquote>

@elseif($block->type === 'video')
        @php
            $videoUrl = (string) ($data['url'] ?? '');
            $host = strtolower((string) parse_url($videoUrl, PHP_URL_HOST));
            $path = trim((string) parse_url($videoUrl, PHP_URL_PATH), '/');
            $videoId = null;
            $embedUrl = null;

            if (in_array($host, ['youtube.com', 'www.youtube.com'], true)) {
                parse_str((string) parse_url($videoUrl, PHP_URL_QUERY), $query);
                $candidate = (string) ($query['v'] ?? '');

                if (preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate)) {
                    $videoId = $candidate;
                    $embedUrl = 'https://www.youtube-nocookie.com/embed/'.$videoId;
                }
            } elseif ($host === 'youtu.be') {
                $candidate = explode('/', $path)[0] ?? '';

                if (preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate)) {
                    $videoId = $candidate;
                    $embedUrl = 'https://www.youtube-nocookie.com/embed/'.$videoId;
                }
            } elseif (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
                $candidate = explode('/', $path)[0] ?? '';

                if (preg_match('/^[0-9]+$/', $candidate)) {
                    $videoId = $candidate;
                    $embedUrl = 'https://player.vimeo.com/video/'.$videoId;
                }
            }
        @endphp

        @if($embedUrl)
            <figure>
                <div class="aspect-video overflow-hidden rounded-2xl bg-slate-100">
                    <iframe
                        src="{{ $embedUrl }}"
                        title="{{ $data['title'] ?? 'Activity video' }}"
                        loading="lazy"
                        sandbox="allow-scripts allow-same-origin allow-presentation"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; encrypted-media; picture-in-picture"
                        allowfullscreen
                        class="size-full"
                    ></iframe>
                </div>

                @if(!empty($data['title']))
                    <figcaption class="mt-2 text-sm text-slate-500">
                        {{ $data['title'] }}
                    </figcaption>
                @endif
            </figure>
        @endif

@elseif($block->type === 'cta')
        @php
            $ctaUrl = app(\App\Support\PublicSite\PageContent::class)->safeUrl(
                $data['url'] ?? null
            );
        @endphp

        @if($ctaUrl && !empty($data['label']))
            <div>
                <a href="{{ $ctaUrl }}" class="inline-flex rounded-lg bg-cim-blue px-5 py-3 font-semibold text-white hover:bg-cim-navy">
                    {{ $data['label'] }}
                </a>
            </div>
        @endif

@elseif($block->type === 'related_product')
        @php
            $relatedProduct = $relatedProductsById->get((int) ($data['product_id'] ?? 0));
        @endphp

        @if($relatedProduct && $relatedProduct->publishedRevision && $relatedProduct->public_slug)
            <article class="rounded-2xl border bg-white p-6">
                <p class="text-sm font-semibold text-cim-blue">
                    Related Product
                </p>

                <h3 class="mt-2 text-xl font-bold text-cim-navy">
                    <a href="{{ route('products.show', $relatedProduct->public_slug) }}">
                        {{ $relatedProduct->publishedRevision->name }}
                    </a>
                </h3>

                @if($relatedProduct->publishedRevision->summary)
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $relatedProduct->publishedRevision->summary }}
                    </p>
                @endif
            </article>
        @endif

@elseif($block->type === 'divider')
        <hr class="border-border">
@endif
