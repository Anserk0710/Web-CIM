<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\SaveWorkingProduct;
use App\Enums\ContentStatus;
use App\Enums\ProductMediaRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductWorkingRequest;
use App\Models\Brand;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRevision;
use App\Models\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(
            'viewAny',
            Product::class,
        );

        $products = Product::query()
            ->with([
                'workingRevision:id,product_id,name,requested_slug',
                'publishedRevision:id,product_id,name,requested_slug',
            ])
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString()
            ->through(
                fn (Product $product): array => [
                    'id' => $product->id,
                    'status' => $product->status->value,
                    'public_slug' => $product->public_slug,
                    'lock_version' => $product->lock_version,
                    'name' => $product
                        ->workingRevision
                        ?->name,
                    'requested_slug' => $product
                        ->workingRevision
                        ?->requested_slug,
                    'published_at' => $product
                        ->published_at
                        ?->toIso8601String(),
                ],
            );

        return Inertia::render(
            'Admin/Products/Index',
            [
                'products' => $products,

                'can' => [
                    'create' => request()
                        ->user()
                        ->can(
                            'create',
                            Product::class,
                        ),
                ],
            ],
        );
    }

    public function create(): Response
    {
        Gate::authorize(
            'create',
            Product::class,
        );

        return Inertia::render(
            'Admin/Products/Create',
            [
                'form' => $this->emptyForm(),
                'options' => $this->options(),
            ],
        );
    }

    public function store(
        ProductWorkingRequest $request,
        SaveWorkingProduct $action,
    ): RedirectResponse {
        Gate::authorize(
            'create',
            Product::class,
        );

        $product = $action->handle(
            actor: $request->user(),
            data: $request->validated(),
        );

        return redirect()
            ->route(
                'admin.products.edit',
                $product,
            )
            ->with(
                'success',
                'Draf produk berhasil dibuat.',
            );
    }

    public function edit(
        Product $product,
    ): Response {
        Gate::authorize(
            'update',
            $product,
        );

        $product->load([
            'workingRevision.media.media',
            'workingRevision.highlights',
            'workingRevision.specificationGroups.items',
            'workingRevision.relatedProducts',
        ]);

        $working = $product->workingRevision;

        abort_if(
            $working === null,
            404,
        );

        return Inertia::render(
            'Admin/Products/Edit',
            [
                'product' => [
                    'id' => $product->id,
                    'status' => $product->status->value,
                    'public_slug' => $product->public_slug,
                    'lock_version' => $product->lock_version,
                    'published_at' => $product
                        ->published_at
                        ?->toIso8601String(),

                    'public_url' => (
                        $product->status
                        === ContentStatus::Published
                        && $product->public_slug !== null
                    )
                        ? route(
                            'products.show',
                            $product->public_slug,
                        )
                        : null,
                ],

                'form' => $this->revisionForm(
                    $product,
                    $working,
                ),

                'options' => $this->options(
                    $product,
                ),

                'can' => [
                    'publish' => request()
                        ->user()
                        ->can(
                            'publish',
                            $product,
                        ),

                    'archive' => request()
                        ->user()
                        ->can(
                            'archive',
                            $product,
                        ),
                ],
            ],
        );
    }

    public function update(
        ProductWorkingRequest $request,
        Product $product,
        SaveWorkingProduct $action,
    ): RedirectResponse {
        Gate::authorize(
            'update',
            $product,
        );

        $action->handle(
            actor: $request->user(),
            data: $request->validated(),
            product: $product,
        );

        return back()->with(
            'success',
            'Perubahan draf produk berhasil disimpan.',
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
            'name' => '',
            'summary' => '',
            'description' => '',
            'category_id' => null,
            'brand_id' => null,
            'type_id' => null,
            'is_featured' => false,
            'seo_title' => '',
            'seo_description' => '',
            'og_media_id' => null,
            'gallery' => [],
            'brochure_media_id' => null,
            'highlights' => [],
            'spec_groups' => [],
            'related_product_ids' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function revisionForm(
        Product $product,
        ProductRevision $revision,
    ): array {
        return [
            'expected_lock_version' => $product->lock_version,
            'requested_slug' => $revision->requested_slug,
            'name' => $revision->name,
            'summary' => $revision->summary ?? '',
            'description' => $revision->description ?? '',
            'category_id' => $revision->category_id,
            'brand_id' => $revision->brand_id,
            'type_id' => $revision->type_id,
            'is_featured' => $revision->is_featured,
            'seo_title' => $revision->seo_title ?? '',
            'seo_description' => $revision->seo_description ?? '',
            'og_media_id' => $revision->og_media_id,

            'gallery' => $revision->media
                ->where(
                    'role',
                    ProductMediaRole::Gallery,
                )
                ->values()
                ->map(
                    fn ($item): array => [
                        'media_id' => $item->media_id,
                        'alt_text' => $item->alt_text ?? '',
                        'caption' => $item->caption ?? '',
                    ],
                ),

            'brochure_media_id' => $revision->media
                ->firstWhere(
                    'role',
                    ProductMediaRole::Brochure,
                )
                ?->media_id,

            'highlights' => $revision
                ->highlights
                ->pluck('text')
                ->values(),

            'spec_groups' => $revision
                ->specificationGroups
                ->map(
                    fn ($group): array => [
                        'name' => $group->name,

                        'items' => $group
                            ->items
                            ->map(
                                fn ($item): array => [
                                    'label' => $item->label,
                                    'value' => $item->value,
                                    'unit' => $item->unit ?? '',
                                ],
                            )
                            ->values(),
                    ],
                )
                ->values(),

            'related_product_ids' => $revision
                ->relatedProducts
                ->pluck('id')
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function options(
        ?Product $except = null,
    ): array {
        $relatedQuery = Product::query()
            ->with(
                'workingRevision:id,product_id,name',
            )
            ->latest('updated_at');

        if ($except !== null) {
            $relatedQuery->whereKeyNot(
                $except->id,
            );
        }

        return [
            'categories' => ProductCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'brands' => Brand::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'types' => ProductType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'media' => Media::query()
                ->latest()
                ->limit(200)
                ->get()
                ->map(
                    fn (Media $media): array => [
                        'id' => $media->id,
                        'name' => $media->original_name,
                        'mime' => $media->mime,
                        'url' => $media->url(),
                    ],
                ),

            'products' => $relatedQuery
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
