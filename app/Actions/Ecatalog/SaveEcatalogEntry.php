<?php

namespace App\Actions\Ecatalog;

use App\Enums\ContentStatus;
use App\Models\EcatalogEntry;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveEcatalogEntry
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        User $actor,
        array $data,
        ?EcatalogEntry $entry = null,
    ): EcatalogEntry {
        return DB::transaction(
            function () use ($actor, $data, $entry): EcatalogEntry {
                if ($entry === null) {
                    $locked = new EcatalogEntry;

                    $locked->status =
                        ContentStatus::Draft;

                    $locked->lock_version = 1;
                } else {
                    $locked = EcatalogEntry::query()
                        ->whereKey($entry->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $locked->lock_version
                        !== (int) (
                            $data[
                                'expected_lock_version'
                            ] ?? 0
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'expected_lock_version' => 'E-Catalog telah diubah oleh user lain.',
                        ]);
                    }

                    if (
                        $locked->status
                        === ContentStatus::Published
                        && ! $actor->hasPermission(
                            'ecatalog.publish',
                        )
                    ) {
                        throw new AuthorizationException(
                            'Editing published E-Catalog requires ecatalog.publish.',
                        );
                    }
                }

                $locked->fill([
                    'product_id' => $data[
                        'product_id'
                    ],

                    'catalog_code' => Str::upper(
                        trim(
                            $data['catalog_code'],
                        ),
                    ),

                    'procurement_title' => $data[
                        'procurement_title'
                    ],

                    'procurement_category' => $data[
                            'procurement_category'
                        ] ?? null,

                    'notes' => $data['notes']
                        ?? null,

                    'unit_package' => $data[
                        'unit_package'
                    ] ?? null,

                    'availability' => $data[
                        'availability'
                    ],

                    'reference_url' => $data[
                        'reference_url'
                    ] ?? null,

                    'effective_at' => $data[
                        'effective_at'
                    ] ?? null,

                    'expires_at' => $data[
                        'expires_at'
                    ] ?? null,

                    'sort_order' => $data[
                        'sort_order'
                    ],

                    'seo_title' => $data[
                        'seo_title'
                    ] ?? null,

                    'seo_description' => $data[
                        'seo_description'
                    ] ?? null,
                ]);

                if ($locked->exists) {
                    $locked->lock_version++;
                }

                $locked->save();

                $this->audit->record(
                    action: $entry === null
                    ? 'ecatalog.created'
                    : 'ecatalog.updated',
                    entity: $locked,
                    changes: [
                        'catalog_code' => $locked->catalog_code,
                        'product_id' => $locked->product_id,
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
