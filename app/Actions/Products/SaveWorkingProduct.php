<?php

namespace App\Actions\Products;

use App\Enums\ContentStatus;
use App\Enums\ProductMediaRole;
use App\Enums\RevisionKind;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductRevision;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveWorkingProduct
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $actor, array $data, ?Product $product = null): Product
    {
        Gate::forUser($actor)->authorize(
            $product === null ? 'create' : 'update',
            $product ?? Product::class,
        );

        return DB::transaction(function () use ($actor, $data, $product): Product {
            $locked = $product === null
                ? Product::query()->create([
                    'status' => ContentStatus::Draft,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                    'lock_version' => 1,
                ])
                : Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($product !== null) {
                if ($locked->lock_version !== (int) ($data['expected_lock_version'] ?? 0)) {
                    throw ValidationException::withMessages([
                        'expected_lock_version' => 'Product telah berubah. Muat ulang halaman sebelum menyimpan.',
                    ]);
                }

                $locked->forceFill([
                    'updated_by' => $actor->id,
                    'lock_version' => $locked->lock_version + 1,
                ])->save();
            }

            $this->validateMedia($data);

            $relatedIds = $data['related_product_ids'] ?? [];

            if (in_array($locked->id, array_map('intval', $relatedIds), true)) {
                throw ValidationException::withMessages([
                    'related_product_ids' => 'Product tidak dapat berelasi dengan dirinya sendiri.',
                ]);
            }

            $revision = ProductRevision::query()->updateOrCreate([
                'product_id' => $locked->id,
                'kind' => RevisionKind::Working->value,
            ], [
                ...Arr::only($data, [
                    'requested_slug', 'name', 'summary', 'description', 'category_id',
                    'brand_id', 'type_id', 'is_featured', 'seo_title', 'seo_description', 'og_media_id',
                ]),
                'updated_by' => $actor->id,
            ]);

            $this->replaceRelations($revision, $data);

            $this->audit->record(
                action: $product === null ? 'product.created' : 'product.updated',
                entity: $locked,
                actor: $actor,
            );

            return $locked->refresh();
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateMedia(array $data): void
    {
        $references = [];

        foreach ($data['gallery'] ?? [] as $index => $item) {
            $references["gallery.{$index}.media_id"] = [(int) $item['media_id'], 'image/'];
        }

        if (! empty($data['og_media_id'])) {
            $references['og_media_id'] = [(int) $data['og_media_id'], 'image/'];
        }

        if (! empty($data['brochure_media_id'])) {
            $references['brochure_media_id'] = [(int) $data['brochure_media_id'], 'application/pdf'];
        }

        if ($references === []) {
            return;
        }

        // Lock in ID order, using the same media lock as deletion to prevent reference races.
        $media = Media::query()
            ->whereIn('id', array_column($references, 0))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($references as $field => [$id, $mime]) {
            $item = $media->get($id);

            if ($item === null || $item->disk !== 'public' || ! str_starts_with($item->mime, $mime)) {
                throw ValidationException::withMessages([
                    $field => 'Media tidak tersedia atau jenis file tidak sesuai.',
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function replaceRelations(ProductRevision $revision, array $data): void
    {
        $revision->media()->delete();

        foreach ($data['gallery'] ?? [] as $index => $item) {
            $revision->media()->create([
                'media_id' => $item['media_id'],
                'role' => ProductMediaRole::Gallery,
                'sort_order' => $index,
                'alt_text' => $item['alt_text'] ?? null,
                'caption' => $item['caption'] ?? null,
            ]);
        }

        if (! empty($data['brochure_media_id'])) {
            $revision->media()->create([
                'media_id' => $data['brochure_media_id'],
                'role' => ProductMediaRole::Brochure,
                'sort_order' => 0,
            ]);
        }

        $revision->highlights()->delete();

        foreach ($data['highlights'] ?? [] as $index => $text) {
            if ($text !== null && trim($text) !== '') {
                $revision->highlights()->create(['text' => $text, 'sort_order' => $index]);
            }
        }

        $revision->specificationGroups()->delete();

        foreach ($data['spec_groups'] ?? [] as $index => $group) {
            $saved = $revision->specificationGroups()->create([
                'name' => $group['name'],
                'sort_order' => $index,
            ]);

            foreach ($group['items'] ?? [] as $itemIndex => $item) {
                $saved->items()->create([
                    'label' => $item['label'],
                    'value' => $item['value'],
                    'unit' => $item['unit'] ?? null,
                    'sort_order' => $itemIndex,
                ]);
            }
        }

        $related = [];

        foreach ($data['related_product_ids'] ?? [] as $index => $id) {
            $related[$id] = ['sort_order' => $index];
        }

        $revision->relatedProducts()->sync($related);
    }
}
