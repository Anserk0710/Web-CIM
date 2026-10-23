<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\RevisionKind;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\ActivityRevision;
use App\Models\Media;
use App\Models\Product;
use App\Models\UrlRedirect;
use App\Support\PublicSite\PageContent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(
        Request $request,
        PageContent $content,
    ): View {
        $pageData = $content->forKey('ACTIVITY');
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
            160,
        );

        $query = Activity::query()
            ->published()
            ->whereHas('publishedRevision')
            ->with([
                'publishedRevision.category',
                'publishedRevision.featureMedia',
            ]);

        if ($search !== '') {
            $query->whereHas(
                'publishedRevision',
                fn (Builder $revision) => $revision
                    ->where(
                        fn (Builder $inner) => $inner
                            ->where(
                                'title',
                                'like',
                                '%'.$search.'%',
                            )
                            ->orWhere(
                                'excerpt',
                                'like',
                                '%'.$search.'%',
                            ),
                    ),
            );
        }

        if ($category !== '') {
            $query->whereHas(
                'publishedRevision.category',
                fn (Builder $taxonomy) => $taxonomy->where(
                    'slug',
                    $category,
                ),
            );
        }

        $activities = $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = ActivityCategory::query()
            ->where('is_active', true)
            ->whereIn(
                'id',
                ActivityRevision::query()
                    ->where(
                        'kind',
                        RevisionKind::Published->value,
                    )
                    ->whereNotNull('category_id')
                    ->whereHas(
                        'activity',
                        fn (Builder $activity) => $activity
                            ->published(),
                    )
                    ->select('category_id'),
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);

        return view('public.activities.index', [
            'page' => $page,
            'hero' => $hero,
            'activities' => $activities,
            'categories' => $categories,
            'filters' => [
                'q' => $search,
                'category' => $category,
            ],
        ]);
    }

    public function show(
        string $slug,
    ): View|RedirectResponse {
        $activity = Activity::query()
            ->published()
            ->whereHas('publishedRevision')
            ->where('public_slug', $slug)
            ->with([
                'publishedRevision.category',
                'publishedRevision.featureMedia',
                'publishedRevision.ogMedia',
                'publishedRevision.blocks' => fn (Builder $query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'publishedRevision.relatedProducts' => fn (Builder $query) => $query
                    ->published()
                    ->whereHas('publishedRevision')
                    ->with(
                        'publishedRevision:id,product_id,name,summary',
                    ),
            ])
            ->first();

        if ($activity === null) {
            $redirect = UrlRedirect::query()
                ->where('old_path', '/activities/'.$slug)
                ->where(
                    'entity_type',
                    Activity::REDIRECT_TYPE,
                )
                ->first();

            if ($redirect !== null) {
                $target = Activity::query()
                    ->published()
                    ->whereHas('publishedRevision')
                    ->find($redirect->entity_id);

                if (
                    $target !== null
                    && $target->public_slug !== null
                ) {
                    return redirect()->route(
                        'activities.show',
                        $target->public_slug,
                        301,
                    );
                }
            }

            abort(404);
        }

        $revision = $activity->publishedRevision;
        $blocks = $revision->blocks;

        $mediaIds = $blocks
            ->flatMap(
                function ($block): array {
                    $data = (array) $block->data;

                    return match ($block->type) {
                        'image' => [
                            $data['media_id'] ?? null,
                        ],
                        'gallery' => $data['media_ids'] ?? [],
                        default => [],
                    };
                },
            )
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $mediaById = $mediaIds->isEmpty()
            ? collect()
            : Media::query()
                ->whereIn('id', $mediaIds)
                ->where('mime', 'like', 'image/%')
                ->get()
                ->keyBy('id');

        $relatedProductIds = $blocks
            ->filter(
                fn ($block): bool => $block->type === 'related_product',
            )
            ->map(
                fn ($block): mixed => $block->data['product_id'] ?? null,
            )
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $relatedProductsById = $relatedProductIds->isEmpty()
            ? collect()
            : Product::query()
                ->published()
                ->whereHas('publishedRevision')
                ->whereIn('id', $relatedProductIds)
                ->with(
                    'publishedRevision:id,product_id,name,summary',
                )
                ->get()
                ->keyBy('id');

        return view('public.activities.show', [
            'activity' => $activity,
            'revision' => $revision,
            'mediaById' => $mediaById,
            'relatedProductsById' => $relatedProductsById,
        ]);
    }
}
