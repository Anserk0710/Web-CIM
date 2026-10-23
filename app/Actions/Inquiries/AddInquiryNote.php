<?php

// path: app/Actions/Inquiries/AddInquiryNote.php

namespace App\Actions\Inquiries;

use App\Models\Inquiry;
use App\Models\InquiryNote;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AddInquiryNote
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Inquiry $inquiry,
        string $body,
        int $expectedLockVersion,
    ): InquiryNote {
        Gate::forUser($actor)->authorize(
            'update',
            $inquiry,
        );

        return DB::transaction(
            function () use (
                $actor,
                $inquiry,
                $body,
                $expectedLockVersion,
            ): InquiryNote {
                $locked = Inquiry::query()
                    ->whereKey($inquiry->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Inquiry telah berubah. Muat ulang halaman sebelum menambahkan note.',
                    ]);
                }

                if ($locked->isAnonymized()) {
                    throw ValidationException::withMessages([
                        'inquiry' => 'Inquiry yang sudah dianonimkan tidak dapat diberi note.',
                    ]);
                }

                $body = trim($body);

                if ($body === '') {
                    throw ValidationException::withMessages([
                        'body' => 'Note wajib diisi.',
                    ]);
                }

                $note = InquiryNote::query()->create([
                    'inquiry_id' => $locked->id,
                    'author_id' => $actor->id,
                    'body' => $body,
                ]);

                $locked->forceFill([
                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'inquiry.note_added',
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
