<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\ProductMediaRole;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\UrlRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(
        Request $request,
    ): View {
        $search = trim(
            (string) $request->query(
                'q',
                '',
            ),
        );

        $category = trim(
            (string) $request->query(
                'category',
                '',
            ),
        );

        $brand = trim(
            (string) $request->query(
                'brand',
                '',
            ),
        );

        $type = trim(
            (string) $request->query(
                'type',
                '',
            ),
        );

        $query = Product::query()
            ->published()
            ->with([
                'publishedRevision.category',
                'publishedRevision.brand',
                'publishedRevision.type',
                'publishedRevision.media.media',
            ]);

        if ($search !== '') {
            $query->whereHas(
                'publishedRevision',
                fn ($revision) => $revision
                    ->where(
                        fn ($inner) => $inner
                            ->where(
                                'name',
                                'like',
                                '%'.$search.'%',
                            )
                            ->orWhere(
                                'summary',
                                'like',
                                '%'.$search.'%',
                            ),
                    ),
            );
        }

        if ($category !== '') {
            $query->whereHas(
                'publishedRevision.category',
                fn ($taxonomy) => $taxonomy
                    ->where(
                        'slug',
                        $category,
                    ),
            );
        }

        if ($brand !== '') {
            $query->whereHas(
                'publishedRevision.brand',
                fn ($taxonomy) => $taxonomy
                    ->where(
                        'slug',
                        $brand,
                    ),
            );
        }

        if ($type !== '') {
            $query->whereHas(
                'publishedRevision.type',
                fn ($taxonomy) => $taxonomy
                    ->where(
                        'slug',
                        $type,
                    ),
            );
        }

        $products = $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'public.products.index',
            [
                'products' => $products,

                'filters' => [
                    'q' => $search,
                    'category' => $category,
                    'brand' => $brand,
                    'type' => $type,
                ],

                'categories' => ProductCategory::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'brands' => Brand::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'types' => ProductType::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'galleryRole' => ProductMediaRole::Gallery,
            ],
        );
    }

    public function show(
        string $slug,
    ): View|RedirectResponse {
        $product = Product::query()
            ->published()
            ->where(
                'public_slug',
                $slug,
            )
            ->with([
                'publishedRevision.category',
                'publishedRevision.brand',
                'publishedRevision.type',
                'publishedRevision.ogMedia',
                'publishedRevision.media.media',
                'publishedRevision.highlights',
                'publishedRevision.specificationGroups.items',

                'publishedRevision.relatedProducts' => fn ($query) => $query
                    ->published()
                    ->with(
                        'publishedRevision:id,product_id,name,summary',
                    ),
            ])
            ->first();

        if ($product === null) {
            $redirect = UrlRedirect::query()
                ->where(
                    'old_path',
                    '/products/'.$slug,
                )
                ->where(
                    'entity_type',
                    Product::REDIRECT_TYPE,
                )
                ->first();

            if ($redirect !== null) {
                $target = Product::query()
                    ->published()
                    ->find(
                        $redirect->entity_id,
                    );

                if (
                    $target !== null
                    && $target->public_slug !== null
                ) {
                    return redirect()
                        ->route(
                            'products.show',
                            $target->public_slug,
                            301,
                        );
                }
            }

            abort(404);
        }

        return view(
            'public.products.show',
            [
                'product' => $product,
                'revision' => $product->publishedRevision,
                'galleryRole' => ProductMediaRole::Gallery,
                'brochureRole' => ProductMediaRole::Brochure,
                'preview' => false,
            ],
        );
    }
}
