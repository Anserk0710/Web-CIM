<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductMediaRole;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ProductPreviewController extends Controller
{
    public function __invoke(
        Product $product,
    ): View {
        Gate::authorize(
            'preview',
            $product,
        );

        $product->load([
            'workingRevision.category',
            'workingRevision.brand',
            'workingRevision.type',
            'workingRevision.ogMedia',
            'workingRevision.media.media',
            'workingRevision.highlights',
            'workingRevision.specificationGroups.items',
            'workingRevision.relatedProducts.publishedRevision',
        ]);

        abort_if(
            $product->workingRevision === null,
            404,
        );

        return view(
            'public.products.show',
            [
                'product' => $product,
                'revision' => $product->workingRevision,
                'galleryRole' => ProductMediaRole::Gallery,
                'brochureRole' => ProductMediaRole::Brochure,
                'preview' => true,
            ],
        );
    }
}
