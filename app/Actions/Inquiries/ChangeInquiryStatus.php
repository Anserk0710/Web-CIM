<?php

// path: app/Actions/Inquiries/ChangeInquiryStatus.php

namespace App\Actions\Inquiries;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\InquiryNote;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Inquiry\InquiryStatusWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ChangeInquiryStatus
{
    public function __construct(
        private readonly InquiryStatusWorkflow $workflow,
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Inquiry $inquiry,
        InquiryStatus $targetStatus,
        ?string $reason,
        int $expectedLockVersion,
    ): Inquiry {
        Gate::forUser($actor)->authorize(
            'update',
            $inquiry,
        );

        return DB::transaction(
            function () use (
                $actor,
                $inquiry,
                $targetStatus,
                $reason,
                $expectedLockVersion,
            ): Inquiry {
                $locked = Inquiry::query()
                    ->whereKey($inquiry->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Inquiry telah berubah. Muat ulang halaman sebelum menyimpan.',
                    ]);
                }

                if ($locked->isAnonymized()) {
                    throw ValidationException::withMessages([
                        'inquiry' => 'Inquiry yang sudah dianonimkan tidak dapat diubah.',
                    ]);
                }

                $fromStatus = $locked->status;

                if (
                    ! $this->workflow->canTransition(
                        $fromStatus,
                        $targetStatus,
                    )
                ) {
                    throw ValidationException::withMessages([
                        'target_status' => 'Transisi status tidak diizinkan.',
                    ]);
                }

                $reason = trim(
                    (string) $reason,
                );

                if (
                    $this->workflow->requiresReason(
                        $fromStatus,
                        $targetStatus,
                    )
                    && $reason === ''
                ) {
                    throw ValidationException::withMessages([
                        'reason' => 'Alasan wajib diisi untuk membuka kembali inquiry.',
                    ]);
                }

                $reasonNote = null;

                if ($reason !== '') {
                    $reasonNote = InquiryNote::query()
                        ->create([
                            'inquiry_id' => $locked->id,
                            'author_id' => $actor->id,

                            'body' => sprintf(
                                'Alasan perubahan status %s → %s: %s',
                                $fromStatus->value,
                                $targetStatus->value,
                                $reason,
                            ),
                        ]);
                }

                $locked->forceFill([
                    'status' => $targetStatus,

                    'resolved_at' => $targetStatus === InquiryStatus::Resolved
                            ? now()
                            : null,

                    'spam_marked_at' => $targetStatus === InquiryStatus::Spam
                            ? now()
                            : null,

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'inquiry.status_changed',
                    entity: $locked,
                    changes: [
                        'status' => [
                            'before' => $fromStatus->value,

                            'after' => $targetStatus->value,
                        ],

                        'reason_note_id' => $reasonNote?->id,
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
