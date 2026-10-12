<?php

namespace App\Actions\Products;

use App\Enums\ContentStatus;
use App\Enums\RevisionKind;
use App\Models\Product;
use App\Models\ProductRevision;
use App\Models\UrlRedirect;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishProduct
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Product $product,
        int $expectedLockVersion,
    ): Product {
        try {
            return DB::transaction(
                function () use ($actor, $product, $expectedLockVersion): Product {
                    /** @var Product $locked */
                    $locked = Product::query()
                        ->whereKey($product->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $locked->lock_version
                        !== $expectedLockVersion
                    ) {
                        throw ValidationException::withMessages([
                            'lock_version' => 'Product telah berubah. Muat ulang halaman sebelum publish.',
                        ]);
                    }

                    $working = $this->lockWorkingRevision($locked);

                    $this->validatePublishability(
                        $locked,
                        $working,
                    );

                    $oldSlug = $locked->public_slug;
                    $newSlug = $working->requested_slug;

                    $this->prepareRedirects(
                        $locked,
                        $oldSlug,
                        $newSlug,
                    );

                    $existingPublished = ProductRevision::query()
                        ->where(
                            'product_id',
                            $locked->id,
                        )
                        ->where(
                            'kind',
                            RevisionKind::Published->value,
                        )
                        ->lockForUpdate()
                        ->first();

                    $existingPublished?->delete();

                    $published = $this->copyRevision(
                        $actor,
                        $working,
                    );

                    $locked->forceFill([
                        'public_slug' => $newSlug,
                        'status' => ContentStatus::Published,
                        'published_at' => now(),
                        'archived_at' => null,
                        'updated_by' => $actor->id,
                        'lock_version' => $locked->lock_version + 1,
                    ])->save();

                    if (
                        $oldSlug !== null
                        && $oldSlug !== $newSlug
                    ) {
                        UrlRedirect::query()->updateOrCreate(
                            [
                                'old_path' => '/products/'.$oldSlug,
                            ],
                            [
                                'entity_type' => Product::REDIRECT_TYPE,
                                'entity_id' => $locked->id,
                                'created_at' => now(),
                            ],
                        );
                    }

                    $this->audit->record(
                        action: 'product.published',
                        entity: $locked,
                        changes: [
                            'published_revision_id' => $published->id,
                            'old_slug' => $oldSlug,
                            'new_slug' => $newSlug,
                        ],
                        actor: $actor,
                    );

                    return $locked->refresh();
                },
                attempts: 3,
            );
        } catch (QueryException $exception) {
            if (
                (int) (
                    $exception->errorInfo[1]
                    ?? 0
                ) === 1062
            ) {
                throw ValidationException::withMessages([
                    'requested_slug' => 'Slug sudah digunakan oleh product lain.',
                ]);
            }

            throw $exception;
        }
    }

    private function lockWorkingRevision(Product $product): ProductRevision
    {
        /** @var ProductRevision $revision */
        $revision = ProductRevision::query()
            ->where('product_id', $product->id)
            ->where('kind', RevisionKind::Working->value)
            ->with([
                'category',
                'brand',
                'type',
                'media',
                'highlights',
                'specificationGroups.items',
                'relatedProducts',
            ])
            ->lockForUpdate()
            ->firstOrFail();

        return $revision;
    }

    private function validatePublishability(
        Product $product,
        ProductRevision $working,
    ): void {
        if (! $working->category->is_active) {
            throw ValidationException::withMessages([
                'category_id' => 'Category tidak aktif dan tidak dapat dipublish.',
            ]);
        }

        if (
            $working->brand !== null
            && ! $working->brand->is_active
        ) {
            throw ValidationException::withMessages([
                'brand_id' => 'Brand tidak aktif dan tidak dapat dipublish.',
            ]);
        }

        if (
            $working->type !== null
            && ! $working->type->is_active
        ) {
            throw ValidationException::withMessages([
                'type_id' => 'Product type tidak aktif dan tidak dapat dipublish.',
            ]);
        }

        $owner = Product::query()
            ->where(
                'public_slug',
                $working->requested_slug,
            )
            ->whereKeyNot($product->id)
            ->lockForUpdate()
            ->first();

        if ($owner !== null) {
            throw ValidationException::withMessages([
                'requested_slug' => 'Slug sudah digunakan oleh product lain.',
            ]);
        }
    }

    private function prepareRedirects(
        Product $product,
        ?string $oldSlug,
        string $newSlug,
    ): void {
        $newPath = '/products/'.$newSlug;

        $redirect = UrlRedirect::query()
            ->where('old_path', $newPath)
            ->lockForUpdate()
            ->first();

        if (
            $redirect !== null
            && (
                $redirect->entity_type
                !== Product::REDIRECT_TYPE
                || $redirect->entity_id
                !== $product->id
            )
        ) {
            throw ValidationException::withMessages([
                'requested_slug' => 'Slug pernah digunakan oleh resource lain.',
            ]);
        }

        if ($redirect !== null) {
            $redirect->delete();
        }

        if (
            $oldSlug === null
            || $oldSlug === $newSlug
        ) {
            return;
        }

        $oldPath = '/products/'.$oldSlug;

        $existing = UrlRedirect::query()
            ->where('old_path', $oldPath)
            ->lockForUpdate()
            ->first();

        if (
            $existing !== null
            && (
                $existing->entity_type
                !== Product::REDIRECT_TYPE
                || $existing->entity_id
                !== $product->id
            )
        ) {
            throw ValidationException::withMessages([
                'requested_slug' => 'Redirect slug lama bertabrakan dengan resource lain.',
            ]);
        }
    }

    private function copyRevision(
        User $actor,
        ProductRevision $working,
    ): ProductRevision {
        $published = ProductRevision::query()->create([
            'product_id' => $working->product_id,
            'kind' => RevisionKind::Published,
            'requested_slug' => $working->requested_slug,
            'name' => $working->name,
            'summary' => $working->summary,
            'description' => $working->description,
            'category_id' => $working->category_id,
            'brand_id' => $working->brand_id,
            'type_id' => $working->type_id,
            'is_featured' => $working->is_featured,
            'seo_title' => $working->seo_title,
            'seo_description' => $working->seo_description,
            'og_media_id' => $working->og_media_id,
            'updated_by' => $actor->id,
        ]);

        foreach ($working->highlights as $highlight) {
            $published->highlights()->create([
                'text' => $highlight->text,
                'sort_order' => $highlight->sort_order,
            ]);
        }

        foreach ($working->media as $media) {
            $published->media()->create([
                'media_id' => $media->media_id,
                'role' => $media->role,
                'sort_order' => $media->sort_order,
                'alt_text' => $media->alt_text,
                'caption' => $media->caption,
            ]);
        }

        foreach (
            $working->specificationGroups as $group
        ) {
            $publishedGroup = $published
                ->specificationGroups()
                ->create([
                    'name' => $group->name,
                    'sort_order' => $group->sort_order,
                ]);

            foreach ($group->items as $item) {
                $publishedGroup->items()->create([
                    'label' => $item->label,
                    'value' => $item->value,
                    'unit' => $item->unit,
                    'sort_order' => $item->sort_order,
                ]);
            }
        }

        $sync = [];

        foreach (
            $working->relatedProducts as $related
        ) {
            $sync[$related->id] = [
                'sort_order' => $related
                    ->pivot
                    ->sort_order,
            ];
        }

        $published
            ->relatedProducts()
            ->sync($sync);

        return $published;
    }
}
