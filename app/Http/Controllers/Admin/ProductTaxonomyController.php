<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductTaxonomyController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('taxonomy.read');

        return Inertia::render(
            'Admin/Taxonomies/Index',
            [
                'categories' => ProductCategory::query()
                    ->orderBy('name')
                    ->get(),

                'brands' => Brand::query()
                    ->orderBy('name')
                    ->get(),

                'types' => ProductType::query()
                    ->orderBy('name')
                    ->get(),

                'canManage' => request()
                    ->user()
                    ->can('taxonomy.manage'),
            ],
        );
    }

    public function store(
        Request $request,
        string $kind,
        AuditService $audit,
    ): RedirectResponse {
        Gate::authorize('taxonomy.manage');

        [$modelClass, $table] = $this
            ->resolveKind($kind);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'slug' => [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(
                    $table,
                    'slug',
                ),
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        /** @var Model $model */
        $model = $modelClass::query()->create(
            $this->attributes(
                kind: $kind,
                data: $data,
            ),
        );

        $audit->record(
            action: 'taxonomy.created',
            entity: $model,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'Taxonomy berhasil dibuat.',
        );
    }

    public function update(
        Request $request,
        string $kind,
        int $id,
        AuditService $audit,
    ): RedirectResponse {
        Gate::authorize('taxonomy.manage');

        [$modelClass, $table] = $this
            ->resolveKind($kind);

        /** @var Model $model */
        $model = $modelClass::query()
            ->findOrFail($id);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'slug' => [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(
                    $table,
                    'slug',
                )->ignore($id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'lock_version' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(
            function () use ($request, $kind, $modelClass, $id, $data, $audit): void {
                /** @var Model $locked */
                $locked = $modelClass::query()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== (int) $data['lock_version']
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Taxonomy telah diubah oleh user lain.',
                    ]);
                }

                $before = $locked->toArray();

                $locked->fill(
                    $this->attributes(
                        kind: $kind,
                        data: $data,
                    ),
                );

                $locked->is_active = (bool) $data['is_active'];
                $locked->lock_version++;

                $locked->save();

                $audit->record(
                    action: 'taxonomy.updated',
                    entity: $locked,
                    changes: [
                        'before' => $before,
                        'after' => $locked->toArray(),
                    ],
                    actor: $request->user(),
                );
            },
            attempts: 3,
        );

        return back()->with(
            'success',
            'Taxonomy berhasil diperbarui.',
        );
    }

    /**
     * @return array{0: class-string<Model>, 1: string}
     */
    private function resolveKind(
        string $kind,
    ): array {
        return match ($kind) {
            'category' => [
                ProductCategory::class,
                'product_categories',
            ],

            'brand' => [
                Brand::class,
                'brands',
            ],

            'type' => [
                ProductType::class,
                'product_types',
            ],

            default => abort(404),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(
        string $kind,
        array $data,
    ): array {
        $attributes = [
            'name' => trim($data['name']),
            'slug' => Str::slug(
                $data['slug'],
            ),
        ];

        if ($kind === 'category') {
            $attributes['description'] = $data[
                'description'
            ] ?? null;
        }

        return $attributes;
    }
}
