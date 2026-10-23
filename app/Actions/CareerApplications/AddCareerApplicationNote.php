<?php

namespace App\Actions\CareerApplications;

use App\Models\ApplicationNote;
use App\Models\CareerApplication;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AddCareerApplicationNote
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        CareerApplication $application,
        string $body,
        int $expectedLockVersion,
    ): ApplicationNote {
        Gate::forUser($actor)->authorize(
            'update',
            $application,
        );

        return DB::transaction(
            function () use (
                $actor,
                $application,
                $body,
                $expectedLockVersion,
            ): ApplicationNote {
                $locked = CareerApplication::query()
                    ->whereKey($application->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Application telah berubah. Muat ulang halaman.',
                    ]);
                }

                if ($locked->isAnonymized()) {
                    throw ValidationException::withMessages([
                        'application' => 'Application yang telah dianonimkan tidak dapat diberi note baru.',
                    ]);
                }

                $body = trim($body);

                if ($body === '') {
                    throw ValidationException::withMessages([
                        'body' => 'Note wajib diisi.',
                    ]);
                }

                $note = ApplicationNote::query()
                    ->create([
                        'application_id' => $locked->id,

                        'author_id' => $actor->id,

                        'body' => $body,
                    ]);

                $locked->forceFill([
                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'application.note_added',
                    entity: $locked,
                    changes: [
                        'note_id' => $note->id,
                    ],
                    actor: $actor,
                );

                return $note->refresh();
            },
            attempts: 3,
        );
    }
}
