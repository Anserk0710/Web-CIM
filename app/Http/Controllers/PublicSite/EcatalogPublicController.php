<?php // path: app/Http/Controllers/PublicSite/EcatalogPublicController.php

namespace App\Http\Controllers\PublicSite;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\EcatalogEntry;
use App\Models\SitePage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class EcatalogPublicController extends Controller
{
    public function index(): View
    {
        $now = now();

        $page = SitePage::query()
            ->where('key', 'ECATALOG')
            ->with('ogMedia')
            ->first();

        $entries = EcatalogEntry::query()
            ->where(
                'status',
                ContentStatus::Published->value,
            )
            ->whereNotNull('published_at')
            ->where(
                fn (Builder $query) => $query
                    ->whereNull('effective_at')
                    ->orWhere('effective_at', '<=', $now),
            )
            ->where(
                fn (Builder $query) => $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now),
            )
            ->whereHas(
                'product',
                fn (Builder $query) => $query
                    ->published()
                    ->whereHas('publishedRevision'),
            )
            ->with([
                'product.publishedRevision.category',
                'product.publishedRevision.brand',
            ])
            ->orderBy('sort_order')
            ->orderBy('procurement_title')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $itemList = $entries
            ->getCollection()
            ->values()
            ->map(
                function (
                    EcatalogEntry $entry,
                    int $index,
                ) use ($entries): array {
                    return [
                        '@type' => 'ListItem',

                        'position' => (
                            ($entries->currentPage() - 1)
                            * $entries->perPage()
                        ) + $index + 1,

                        'name' => $entry->procurement_title,

                        'url' => route(
                            'ecatalog.public.index',
                        ).'#catalog-'.$entry->id,

                        'item' => [
                            '@type' => 'Product',

                            'name' =>
                                $entry->product
                                    ->publishedRevision
                                    ->name,

                            'url' => route(
                                'products.show',
                                $entry->product->public_slug,
                            ),
                        ],
                    ];
                },
            )
            ->all();

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $page?->seo_title
                ?: ($page?->title ?: 'E-Catalog'),
            'itemListElement' => $itemList,
        ];

        return view(
            'public.ecatalog.index',
            [
                'page' => $page,
                'entries' => $entries,
                'structuredData' => $structuredData,
            ],
        );
    }
}
