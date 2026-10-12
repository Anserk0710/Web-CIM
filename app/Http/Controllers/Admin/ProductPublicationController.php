<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\ArchiveProduct;
use App\Actions\Products\PublishProduct;
use App\Actions\Products\UnpublishProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductLockRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ProductPublicationController extends Controller
{
    public function publish(
        ProductLockRequest $request,
        Product $product,
        PublishProduct $action,
    ): RedirectResponse {
        Gate::authorize(
            'publish',
            $product,
        );

        $action->handle(
            actor: $request->user(),
            product: $product,
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Product berhasil dipublish.',
        );
    }

    public function unpublish(
        ProductLockRequest $request,
        Product $product,
        UnpublishProduct $action,
    ): RedirectResponse {
        Gate::authorize(
            'publish',
            $product,
        );

        $action->handle(
            actor: $request->user(),
            product: $product,
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Product berhasil di-unpublish.',
        );
    }

    public function archive(
        ProductLockRequest $request,
        Product $product,
        ArchiveProduct $action,
    ): RedirectResponse {
        Gate::authorize(
            'archive',
            $product,
        );

        $action->handle(
            actor: $request->user(),
            product: $product,
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Product berhasil diarsipkan.',
        );
    }
}
