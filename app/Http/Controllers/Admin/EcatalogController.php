<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Ecatalog\ArchiveEcatalogEntry;
use App\Actions\Ecatalog\DeleteEcatalogEntry;
use App\Actions\Ecatalog\PublishEcatalogEntry;
use App\Actions\Ecatalog\SaveEcatalogEntry;
use App\Enums\ECatalogAvailability;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EcatalogRequest;
use App\Http\Requests\Admin\ProductLockRequest;
use App\Models\EcatalogEntry;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EcatalogController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        Gate::authorize(
            'viewAny',
            EcatalogEntry::class,
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

        $query = EcatalogEntry::query()
            ->with(
                'product.publishedRevision:id,product_id,name',
            );

        if ($search !== '') {
            $query->where(
                function ($inner) use ($search): void {
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
                        );
                },
            );
        }

        if ($status !== '') {
            $query->where(
                'status',
                $status,
            );
        }

        $entries = $query
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render(
            'Admin/Ecatalog/Index',
            [
                'entries' => $entries,

                'filters' => [
                    'q' => $search,
                    'status' => $status,
                ],

                'can' => [
                    'create' => request()
                        ->user()
                        ->can(
                            'create',
                            EcatalogEntry::class,
                        ),
                ],
            ],
        );
    }

    public function create(): Response
    {
        Gate::authorize(
            'create',
            EcatalogEntry::class,
        );

        return Inertia::render(
            'Admin/Ecatalog/Form',
            [
                'entry' => null,
                'form' => $this->emptyForm(),
                'products' => $this->products(),
                'availabilities' => array_map(
                    fn (
                        ECatalogAvailability $value,
                    ): string => $value->value,
                    ECatalogAvailability::cases(),
                ),
            ],
        );
    }

    public function store(
        EcatalogRequest $request,
        SaveEcatalogEntry $action,
    ): RedirectResponse {
        Gate::authorize(
            'create',
            EcatalogEntry::class,
        );

        $entry = $action->handle(
            actor: $request->user(),
            data: $request->validated(),
        );

        return redirect()
            ->route(
                'admin.ecatalog.edit',
                $entry,
            )
            ->with(
                'success',
                'Data produk E-Katalog berhasil dibuat.',
            );
    }

    public function edit(
        EcatalogEntry $ecatalog,
    ): Response {
        Gate::authorize(
            'update',
            $ecatalog,
        );

        return Inertia::render(
            'Admin/Ecatalog/Form',
            [
                'entry' => [
                    'id' => $ecatalog->id,
                    'status' => $ecatalog->status->value,

                    'lock_version' => $ecatalog->lock_version,
                ],

                'form' => [
                    'expected_lock_version' => $ecatalog->lock_version,

                    'product_id' => $ecatalog->product_id,

                    'catalog_code' => $ecatalog->catalog_code,

                    'procurement_title' => $ecatalog
                        ->procurement_title,

                    'procurement_category' => $ecatalog
                        ->procurement_category
                        ?? '',

                    'notes' => $ecatalog->notes ?? '',

                    'unit_package' => $ecatalog->unit_package
                        ?? '',

                    'availability' => $ecatalog
                        ->availability
                        ->value,

                    'reference_url' => $ecatalog->reference_url
                        ?? '',

                    'effective_at' => $ecatalog
                        ->effective_at
                        ?->format(
                            'Y-m-d\TH:i',
                        ),

                    'expires_at' => $ecatalog
                        ->expires_at
                        ?->format(
                            'Y-m-d\TH:i',
                        ),

                    'sort_order' => $ecatalog->sort_order,

                    'seo_title' => $ecatalog->seo_title
                        ?? '',

                    'seo_description' => $ecatalog
                        ->seo_description
                        ?? '',
                ],

                'products' => $this->products(),

                'availabilities' => array_map(
                    fn (
                        ECatalogAvailability $value,
                    ): string => $value->value,
                    ECatalogAvailability::cases(),
                ),

                'can' => [
                    'publish' => request()
                        ->user()
                        ->can(
                            'publish',
                            $ecatalog,
                        ),

                    'archive' => request()
                        ->user()
                        ->can(
                            'archive',
                            $ecatalog,
                        ),

                    'delete' => request()
                        ->user()
                        ->can(
                            'delete',
                            $ecatalog,
                        ),
                ],
            ],
        );
    }

    public function update(
        EcatalogRequest $request,
        EcatalogEntry $ecatalog,
        SaveEcatalogEntry $action,
    ): RedirectResponse {
        Gate::authorize(
            'update',
            $ecatalog,
        );

        $action->handle(
            actor: $request->user(),
            data: $request->validated(),
            entry: $ecatalog,
        );

        return back()->with(
            'success',
            'Perubahan data E-Katalog berhasil disimpan.',
        );
    }

    public function publish(
        ProductLockRequest $request,
        EcatalogEntry $ecatalog,
        PublishEcatalogEntry $action,
    ): RedirectResponse {
        Gate::authorize(
            'publish',
            $ecatalog,
        );

        $action->handle(
            actor: $request->user(),
            entry: $ecatalog,
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Produk E-Katalog berhasil diterbitkan.',
        );
    }

    public function archive(
        ProductLockRequest $request,
        EcatalogEntry $ecatalog,
        ArchiveEcatalogEntry $action,
    ): RedirectResponse {
        Gate::authorize(
            'archive',
            $ecatalog,
        );

        $action->handle(
            actor: $request->user(),
            entry: $ecatalog,
            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Produk E-Katalog berhasil diarsipkan.',
        );
    }

    public function destroy(
        EcatalogEntry $ecatalog,
        DeleteEcatalogEntry $action,
    ): RedirectResponse {
        Gate::authorize(
            'delete',
            $ecatalog,
        );

        $action->handle(
            request()->user(),
            $ecatalog,
        );

        return redirect()
            ->route('admin.ecatalog.index')
            ->with(
                'success',
                'Draf produk E-Katalog berhasil dihapus.',
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyForm(): array
    {
        return [
            'expected_lock_version' => null,
            'product_id' => null,
            'catalog_code' => '',
            'procurement_title' => '',
            'procurement_category' => '',
            'notes' => '',
            'unit_package' => '',
            'availability' => ECatalogAvailability::ContactUs
                ->value,
            'reference_url' => '',
            'effective_at' => null,
            'expires_at' => null,
            'sort_order' => 0,
            'seo_title' => '',
            'seo_description' => '',
        ];
    }

    private function products()
    {
        return Product::query()
            ->with(
                'workingRevision:id,product_id,name',
            )
            ->orderByDesc('updated_at')
            ->get()
            ->map(
                fn (Product $product): array => [
                    'id' => $product->id,

                    'name' => $product
                        ->workingRevision
                        ?->name
                        ?? '#'.$product->id,
                ],
            );
    }
}
