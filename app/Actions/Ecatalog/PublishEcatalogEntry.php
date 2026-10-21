<?php

namespace App\Actions\Ecatalog;

use App\Enums\ContentStatus;
use App\Models\EcatalogEntry;
use App\Models\Product;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishEcatalogEntry
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        EcatalogEntry $entry,
        int $expectedLockVersion,
    ): EcatalogEntry {
        return DB::transaction(
            function () use ($actor, $entry, $expectedLockVersion): EcatalogEntry {
                $locked = EcatalogEntry::query()
                    ->whereKey($entry->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'E-Catalog telah berubah.',
                    ]);
                }

                $product = Product::query()
                    ->whereKey($locked->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $product->status
                    !== ContentStatus::Published
                ) {
                    throw ValidationException::withMessages([
                        'product_id' => 'Product harus PUBLISHED sebelum E-Catalog dapat dipublish.',
                    ]);
                }

                if (
                    $locked->effective_at !== null
                    && $locked->expires_at !== null
                    && $locked->expires_at
                        ->lessThanOrEqualTo(
                            $locked->effective_at,
                        )
                ) {
                    throw ValidationException::withMessages([
                        'expires_at' => 'Expiry harus setelah effective date.',
                    ]);
                }

                $locked->forceFill([
                    'status' => ContentStatus::Published,

                    'published_at' => now(),

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'ecatalog.published',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
