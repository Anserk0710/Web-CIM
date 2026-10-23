<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Media;
use App\Models\Product;
use App\Support\PublicSite\PageContent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function __invoke(PageContent $content): View
    {
        $pageData = $content->forKey('HOME');
        $page = $pageData['page'];
        $sections = $pageData['sections'];

        $hero = $content->data($sections, 'hero');
        $metricsData = $content->data($sections, 'metrics');
        $capabilitiesData = $content->data(
            $sections,
            'capabilities',
        );
        $lifecycleData = $content->data(
            $sections,
            'lifecycle',
        );
        $featuredData = $content->data(
            $sections,
            'featured_products',
        );
        $activityData = $content->data(
            $sections,
            'activity_preview',
        );
        $coverageData = $content->data(
            $sections,
            'coverage',
        );
        $ctaData = $content->data($sections, 'cta');

        $hero['title'] = trim(
            (string) ($hero['title'] ?? ''),
        ) ?: 'PT Cahya Intan Medika';

        $hero['description'] = trim(
            (string) ($hero['description'] ?? ''),
        ) ?: 'Jelajahi produk, katalog pengadaan, aktivitas, dan informasi perusahaan.';

        $hero['primary_cta_label'] = trim(
            (string) ($hero['primary_cta_label'] ?? ''),
        ) ?: 'Jelajahi Produk';

        $hero['primary_cta_url'] = $content->safeUrl(
            $hero['primary_cta_url'] ?? null,
        ) ?? route('products.index');

        $hero['secondary_cta_label'] = trim(
            (string) ($hero['secondary_cta_label'] ?? ''),
        ) ?: 'Tentang Kami';

        $hero['secondary_cta_url'] = $content->safeUrl(
            $hero['secondary_cta_url'] ?? null,
        ) ?? route('company');

        $heroImage = isset($hero['image_id'])
            ? Media::query()
                ->whereKey((int) $hero['image_id'])
                ->where('mime', 'like', 'image/%')
                ->first()
            : null;

        $metrics = collect(
            $content->listValue(
                $metricsData['items'] ?? [],
            ),
        )
            ->filter(
                function (mixed $item): bool {
                    if (
                        ! is_array($item)
                        || empty($item['label'])
                        || ! isset($item['value'])
                        || empty($item['verified_at'])
                    ) {
                        return false;
                    }

                    try {
                        return Carbon::parse(
                            $item['verified_at'],
                        )->lessThanOrEqualTo(now());
                    } catch (\Throwable) {
                        return false;
                    }
                },
            )
            ->values();

        $featuredProductIds = $content->ids(
            $featuredData['product_ids'] ?? [],
        );

        /** @var Collection<int, Product> $featuredProducts */
        $featuredProducts = Product::query()
            ->published()
            ->whereHas('publishedRevision')
            ->whereIn('id', $featuredProductIds)
            ->with([
                'publishedRevision.category',
                'publishedRevision.media.media',
            ])
            ->get();

        $featuredProducts = $featuredProducts
            ->sortBy(
                fn (Product $product): int|false => array_search(
                    $product->id,
                    $featuredProductIds,
                    true,
                ),
            )
            ->values();

        $featuredActivities = collect();

        if ($sections->has('activity_preview')) {
            $activityIds = $content->ids(
                $activityData['activity_ids'] ?? [],
            );

            $activityQuery = Activity::query()
                ->published()
                ->whereHas('publishedRevision')
                ->with([
                    'publishedRevision.category',
                    'publishedRevision.featureMedia',
                ]);

            if ($activityIds !== []) {
                $activityQuery->whereIn('id', $activityIds);
            } else {
                $activityQuery->whereHas(
                    'publishedRevision',
                    fn ($query) => $query->where(
                        'is_featured',
                        true,
                    ),
                );
            }

            /** @var Collection<int, Activity> $featuredActivities */
            $featuredActivities = $activityQuery
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(4)
                ->get();

            if ($activityIds !== []) {
                $featuredActivities = $featuredActivities
                    ->sortBy(
                        fn (Activity $activity): int|false => array_search(
                            $activity->id,
                            $activityIds,
                            true,
                        ),
                    )
                    ->values();
            }
        }

        return view('public.home', [
            'page' => $page,
            'sections' => $sections,
            'hero' => $hero,
            'heroImage' => $heroImage,
            'metrics' => $metrics,
            'capabilities' => $content->listValue(
                $capabilitiesData['items'] ?? [],
            ),
            'capabilitiesHeading' => $capabilitiesData['heading']
                ?? null,
            'capabilitiesIntro' => $capabilitiesData['intro']
                ?? null,
            'lifecycle' => $content->listValue(
                $lifecycleData['items'] ?? [],
            ),
            'lifecycleHeading' => $lifecycleData['heading']
                ?? null,
            'featuredProducts' => $featuredProducts,
            'featuredActivities' => $featuredActivities,
            'coverageData' => $coverageData,
            'ctaData' => $ctaData,
        ]);
    }
}
