<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\ContentStatus;
use App\Enums\ECatalogAvailability;
use App\Http\Controllers\Controller;
use App\Models\EcatalogEntry;
use App\Support\PublicSite\PageContent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EcatalogController extends Controller
{
    public function index(
        Request $request,
        PageContent $content,
    ): View {
        $now = now();

        $pageData = $content->forKey('ECATALOG');
        $page = $pageData['page'];
        $hero = $content->data(
            $pageData['sections'],
            'hero',
        );

        $search = mb_substr(
            trim((string) $request->query('q', '')),
            0,
            100,
        );

        $category = mb_substr(
            trim((string) $request->query('category', '')),
            0,
            120,
        );

        $requestedAvailability = strtoupper(
            trim(
                (string) $request->query(
                    'availability',
                    '',
                ),
            ),
        );

        $availability = ECatalogAvailability::tryFrom(
            $requestedAvailability,
        );

        $baseQuery = EcatalogEntry::query()
            ->where(
                'status',
                ContentStatus::Published->value,
            )
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
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
            );

        $query = clone $baseQuery;

        $query->with([
            'product.publishedRevision.category',
            'product.publishedRevision.media.media',
        ]);

        if ($search !== '') {
            $query->where(
                function (Builder $inner) use ($search): void {
                    $inner
                        ->where(
                            'catalog_code',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'procurement_title',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'procurement_category',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhereHas(
                            'product.publishedRevision',
                            fn (Builder $revision) => $revision
                                ->where(
                                    'name',
                                    'like',
                                    '%'.$search.'%',
                                ),
                        );
                },
            );
        }

        if ($category !== '') {
            $query->where(
                'procurement_category',
                $category,
            );
        }

        if ($availability !== null) {
            $query->where(
                'availability',
                $availability->value,
            );
        }

        $entries = $query
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = (clone $baseQuery)
            ->whereNotNull('procurement_category')
            ->where('procurement_category', '<>', '')
            ->distinct()
            ->orderBy('procurement_category')
            ->pluck('procurement_category');

        return view('public.ecatalog.index', [
            'page' => $page,
            'hero' => $hero,
            'entries' => $entries,
            'categories' => $categories,
            'filters' => [
                'q' => $search,
                'category' => $category,
                'availability' => $availability?->value ?? '',
            ],
            'availabilityOptions' => [
                ECatalogAvailability::Available->value => 'Tersedia',
                ECatalogAvailability::Unavailable->value => 'Tidak tersedia',
                ECatalogAvailability::ContactUs->value => 'Hubungi kami',
            ],
        ]);
    }
}
