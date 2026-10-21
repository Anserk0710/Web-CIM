<?php

namespace App\Actions\Ecatalog;

use App\Enums\ContentStatus;
use App\Models\EcatalogEntry;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArchiveEcatalogEntry
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

                $locked->forceFill([
                    'status' => ContentStatus::Archived,

                    'published_at' => null,

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'ecatalog.archived',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
