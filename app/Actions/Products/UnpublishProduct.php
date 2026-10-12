<?php

namespace App\Actions\Products;

use App\Enums\ContentStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UnpublishProduct
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Product $product,
        int $expectedLockVersion,
    ): Product {
        return DB::transaction(
            function () use ($actor, $product, $expectedLockVersion): Product {
                $locked = Product::query()
                    ->whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Product telah berubah.',
                    ]);
                }

                $locked->forceFill([
                    'status' => ContentStatus::Draft,
                    'published_at' => null,
                    'archived_at' => null,
                    'updated_by' => $actor->id,
                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'product.unpublished',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
