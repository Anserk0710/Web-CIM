<?php

namespace Tests\Feature\PublicSite;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductRevision;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActivityBlockViewTest extends TestCase
{
    #[DataProvider('blocks')]
    public function test_activity_blocks_render(string $type, array $data, string $expected): void
    {
        $image = new Media([
            'disk' => 'public',
            'path' => 'activities/test.jpg',
        ]);
        $product = new Product(['public_slug' => 'test-product']);
        $product->setRelation('publishedRevision', new ProductRevision([
            'name' => 'Related monitor',
        ]));

        $html = view('public.activities._block', [
            'block' => (object) ['type' => $type, 'data' => $data],
            'mediaById' => collect([1 => $image]),
            'relatedProductsById' => collect([1 => $product]),
        ])->render();

        $this->assertStringContainsString($expected, $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public static function blocks(): array
    {
        return [
            'paragraph escapes HTML' => ['paragraph', ['text' => '<script>test</script>'], '&lt;script&gt;test&lt;/script&gt;'],
            'heading level two' => ['heading', ['text' => 'Heading', 'level' => 2], '<h2'],
            'heading level three' => ['heading', ['text' => 'Heading', 'level' => 3], '<h3'],
            'heading clamps level' => ['heading', ['text' => 'Heading', 'level' => 6], '<h4'],
            'image' => ['image', ['media_id' => 1], 'activities/test.jpg'],
            'gallery' => ['gallery', ['media_ids' => [1, 999]], 'activities/test.jpg'],
            'quote' => ['quote', ['text' => 'Quote text', 'attribution' => 'Author'], 'Quote text'],
            'youtube' => ['video', ['url' => 'https://www.youtube.com/watch?v=abcdefghijk'], 'https://www.youtube-nocookie.com/embed/abcdefghijk'],
            'vimeo' => ['video', ['url' => 'https://vimeo.com/123456'], 'https://player.vimeo.com/video/123456'],
            'cta' => ['cta', ['url' => '/products', 'label' => 'Browse products'], 'Browse products'],
            'related product' => ['related_product', ['product_id' => 1], 'Related monitor'],
            'divider' => ['divider', [], '<hr'],
        ];
    }
}
