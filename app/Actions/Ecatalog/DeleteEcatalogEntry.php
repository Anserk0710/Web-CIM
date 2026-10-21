<?php

namespace App\Actions\Ecatalog;

use App\Enums\ContentStatus;
use App\Models\EcatalogEntry;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteEcatalogEntry
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        EcatalogEntry $entry,
    ): void {
        DB::transaction(
            function () use ($actor, $entry): void {
                $locked = EcatalogEntry::query()
                    ->whereKey($entry->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->status
                    !== ContentStatus::Draft
                    || $locked->published_at !== null
                ) {
                    throw ValidationException::withMessages([
                        'ecatalog' => 'E-Catalog yang pernah/live published harus di-archive, bukan dihapus.',
                    ]);
                }

                $this->audit->record(
                    action: 'ecatalog.deleted',
                    entity: $locked,
                    actor: $actor,
                );

                $locked->delete();
            },
            attempts: 3,
        );
    }
}
