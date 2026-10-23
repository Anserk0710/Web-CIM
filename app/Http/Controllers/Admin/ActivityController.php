<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Activities\ArchiveActivity;
use App\Actions\Activities\PublishActivity;
use App\Actions\Activities\SaveWorkingActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivityWorkingRequest;
use App\Http\Requests\Admin\ProductLockRequest;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        Gate::authorize(
            'viewAny',
            Activity::class,
        );

        $search = trim(
            (string) $request->query(
                'q',
                '',
            ),
        );

        $status = trim(
            (string) $request->query(
                'status',
                '',
            ),
        );

        $query = Activity::query()
            ->with([
                'workingRevision:id,activity_id,title,requested_slug',
                'publishedRevision:id,activity_id,title,requested_slug',
            ]);

        if ($search !== '') {
            $query->whereHas(
                'workingRevision',
                fn ($revision) => $revision->where(
                    'title',
                    'like',
                    '%'.$search.'%',
                ),
            );
        }

        if ($status !== '') {
            $query->where(
                'status',
                $status,
            );
        }

        return Inertia::render(
            'Admin/Activities/Index',
            [
                'activities' => $query
                    ->latest('updated_at')
                    ->paginate(20)
                    ->withQueryString(),

                'filters' => [
                    'q' => $search,
                    'status' => $status,
                ],

                'canCreate' => request()
                    ->user()
                    ->can(
                        'create',
                        Activity::class,
                    ),
            ],
        );
    }

    public function create(): Response
    {
        Gate::authorize(
            'create',
            Activity::class,
        );

        return Inertia::render(
            'Admin/Activities/Form',
            [
                'activity' => null,
                'form' => $this->emptyForm(),
                'options' => $this->options(),
            ],
        );
    }

    public function store(
        ActivityWorkingRequest $request,
        SaveWorkingActivity $action,
    ): RedirectResponse {
        Gate::authorize(
            'create',
            Activity::class,
        );

        $activity = $action->handle(
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route(
                'admin.activities.edit',
                $activity,
            )
            ->with(
                'success',
                'Draf berita kegiatan berhasil dibuat.',
            );
    }

    public function edit(
        Activity $activity,
    ): Response {
        Gate::authorize(
            'update',
            $activity,
        );

        $activity->load([
            'workingRevision.blocks',
            'workingRevision.relatedProducts',
        ]);

        $revision =
            $activity->workingRevision;

        abort_if(
            $revision === null,
            404,
        );

        return Inertia::render(
            'Admin/Activities/Form',
            [
                'activity' => [
                    'id' => $activity->id,

                    'status' => $activity->status->value,

                    'lock_version' => $activity->lock_version,
                ],

                'form' => [
                    'expected_lock_version' => $activity->lock_version,

                    'requested_slug' => $revision
                        ->requested_slug,

                    'title' => $revision->title,

                    'excerpt' => $revision->excerpt
                        ?? '',

                    'category_id' => $revision->category_id,

                    'featured_media_id' => $revision
                        ->featured_media_id,

                    'is_featured' => $revision->is_featured,

                    'seo_title' => $revision->seo_title
                        ?? '',

                    'seo_description' => $revision
                        ->seo_description
                        ?? '',

                    'og_media_id' => $revision->og_media_id,

                    'blocks' => $revision
                        ->blocks
                        ->map(
                            fn ($block): array => [
                                'block_key' => $block
                                    ->block_key,

                                'type' => $block->type,

                                'data' => $block->data,
                            ],
                        )
                        ->values(),

                    'related_product_ids' => $revision
                        ->relatedProducts
                        ->pluck('id')
                        ->values(),
                ],

                'options' => $this->options(),

                'can' => [
                    'publish' => request()
                        ->user()
                        ->can(
                            'publish',
                            $activity,
                        ),

                    'archive' => request()
                        ->user()
                        ->can(
                            'archive',
                            $activity,
                        ),
                ],
            ],
        );
    }

    public function update(
        ActivityWorkingRequest $request,
        Activity $activity,
        SaveWorkingActivity $action,
    ): RedirectResponse {
        Gate::authorize(
            'update',
            $activity,
        );

        $action->handle(
            $request->user(),
            $request->validated(),
            $activity,
        );

        return back()->with(
            'success',
            'Perubahan draf berita kegiatan berhasil disimpan.',
        );
    }

    public function publish(
        ProductLockRequest $request,
        Activity $activity,
        PublishActivity $action,
    ): RedirectResponse {
        Gate::authorize(
            'publish',
            $activity,
        );

        $action->handle(
            $request->user(),
            $activity,
            $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Berita kegiatan berhasil diterbitkan ke situs web.',
        );
    }

    public function archive(
        ProductLockRequest $request,
        Activity $activity,
        ArchiveActivity $action,
    ): RedirectResponse {
        Gate::authorize(
            'archive',
            $activity,
        );

        $action->handle(
            $request->user(),
            $activity,
            $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Berita kegiatan berhasil diarsipkan.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyForm(): array
    {
        return [
            'expected_lock_version' => null,
            'requested_slug' => '',
            'title' => '',
            'excerpt' => '',
            'category_id' => null,
            'featured_media_id' => null,
            'is_featured' => false,
            'seo_title' => '',
            'seo_description' => '',
            'og_media_id' => null,
            'blocks' => [],
            'related_product_ids' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'categories' => ActivityCategory::query()
                ->where(
                    'is_active',
                    true,
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'media' => Media::query()
                ->where(
                    'mime',
                    'like',
                    'image/%',
                )
                ->latest()
                ->limit(200)
                ->get()
                ->map(
                    fn (Media $media): array => [
                        'id' => $media->id,

                        'name' => $media
                            ->original_name,

                        'url' => $media->url(),
                    ],
                ),

            'products' => Product::query()
                ->with(
                    'workingRevision:id,product_id,name',
                )
                ->latest('updated_at')
                ->limit(200)
                ->get()
                ->map(
                    fn (Product $product): array => [
                        'id' => $product->id,

                        'name' => $product
                            ->workingRevision
                            ?->name
                            ?? '#'.$product->id,
                    ],
                ),
        ];
    }
}
