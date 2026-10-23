<?php

namespace App\Actions\CareerApplications;

use App\Enums\ApplicationStatus;
use App\Models\ApplicationStatusEvent;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\ConsentVersion;
use App\Services\Audit\AuditService;
use App\Services\Notifications\NotificationOutboxService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SubmitCareerApplication
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly NotificationOutboxService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        Career $career,
        array $data,
        UploadedFile $cv,
    ): CareerApplication {
        $this->assertValidPdf($cv);

        $emailNormalized = Str::lower(
            trim(
                (string) $data['email'],
            ),
        );

        $directory = 'career-applications/'
            .now()->format('Y/m');

        $filename = (string) Str::uuid()
            .'.pdf';

        $storedPath = Storage::disk(
            'private',
        )->putFileAs(
            $directory,
            $cv,
            $filename,
        );

        if ($storedPath === false) {
            throw new RuntimeException(
                'CV could not be stored.',
            );
        }

        try {
            return DB::transaction(
                function () use ($career, $data, $cv, $storedPath, $emailNormalized): CareerApplication {
                    $lockedCareer = Career::query()
                        ->whereKey($career->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        ! $lockedCareer
                            ->isAcceptingApplicationsAt(
                                now(),
                            )
                    ) {
                        throw ValidationException::withMessages([
                            'career' => 'Lowongan ini tidak sedang menerima lamaran.',
                        ]);
                    }

                    $duplicateExists = CareerApplication::query()
                        ->where(
                            'career_id',
                            $lockedCareer->id,
                        )
                        ->where(
                            'email_normalized',
                            $emailNormalized,
                        )
                        ->exists();

                    if ($duplicateExists) {
                        throw ValidationException::withMessages([
                            'email' => 'Email ini sudah pernah digunakan untuk melamar lowongan yang sama.',
                        ]);
                    }

                    $consentVersion = ConsentVersion::query()
                        ->effective()
                        ->first();

                    if ($consentVersion === null) {
                        throw ValidationException::withMessages([
                            'consent' => 'Versi persetujuan rekrutmen aktif belum tersedia.',
                        ]);
                    }

                    if (
                        $consentVersion->id
                        !== (int) $data[
                            'consent_version_id'
                        ]
                    ) {
                        throw ValidationException::withMessages([
                            'consent' => 'Versi persetujuan telah berubah. Muat ulang halaman dan baca persetujuan terbaru.',
                        ]);
                    }

                    $sha256 = hash_file(
                        'sha256',
                        $cv->getRealPath(),
                    );

                    if ($sha256 === false) {
                        throw new RuntimeException(
                            'CV checksum could not be calculated.',
                        );
                    }

                    $application = CareerApplication::query()
                        ->create([
                            'career_id' => $lockedCareer->id,

                            'full_name' => trim(
                                (string) $data[
                                    'full_name'
                                ],
                            ),

                            'email' => trim(
                                (string) $data['email'],
                            ),

                            'email_normalized' => $emailNormalized,

                            'phone' => $this->nullableString(
                                $data['phone'] ?? null,
                            ),

                            'introduction' => $this->nullableString(
                                $data['introduction'] ?? null,
                            ),

                            'portfolio_url' => $this->nullableString(
                                $data['portfolio_url'] ?? null,
                            ),

                            'linkedin_url' => $this->nullableString(
                                $data['linkedin_url'] ?? null,
                            ),

                            'cv_disk' => 'private',
                            'cv_path' => $storedPath,

                            'cv_original_name' => $this->safeOriginalName(
                                $cv->getClientOriginalName(),
                            ),

                            'cv_mime' => 'application/pdf',
                            'cv_size' => (int) $cv->getSize(),
                            'cv_sha256' => $sha256,

                            'status' => ApplicationStatus::New,

                            'consent_version_id' => $consentVersion->id,
                            'consented_at' => now(),

                            'selection_completed_at' => null,
                            'retention_due_at' => null,
                            'hr_handover_at' => null,
                            'anonymized_at' => null,

                            'lock_version' => 1,
                        ]);

                    ApplicationStatusEvent::query()
                        ->create([
                            'application_id' => $application->id,
                            'actor_id' => null,
                            'from_status' => null,
                            'to_status' => ApplicationStatus::New,
                            'reason' => 'Public application submitted.',
                            'created_at' => now(),
                        ]);

                    $this->audit->record(
                        action: 'application.submitted',
                        entity: $application,
                        changes: [
                            'career_id' => $lockedCareer->id,
                            'status' => ApplicationStatus::New->value,
                            'consent_version_id' => $consentVersion->id,
                        ],
                        actor: null,
                    );

                    $this->notifications->applicationSubmitted($application);

                    return $application->refresh();
                },
                attempts: 3,
            );
        } catch (Throwable $exception) {
            Storage::disk('private')
                ->delete($storedPath);

            throw $exception;
        }
    }

    private function assertValidPdf(
        UploadedFile $cv,
    ): void {
        if (
            (int) $cv->getSize()
            > 5 * 1024 * 1024
        ) {
            throw ValidationException::withMessages([
                'cv' => 'CV maksimal 5 MiB.',
            ]);
        }

        $mime = (string) $cv->getMimeType();

        if (
            ! in_array(
                $mime,
                [
                    'application/pdf',
                    'application/x-pdf',
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'cv' => 'CV harus berupa file PDF yang valid.',
            ]);
        }

        $signature = file_get_contents(
            $cv->getRealPath(),
            false,
            null,
            0,
            5,
        );

        if ($signature !== '%PDF-') {
            throw ValidationException::withMessages([
                'cv' => 'Signature file CV tidak valid.',
            ]);
        }
    }

    private function safeOriginalName(
        string $name,
    ): string {
        $safe = Str::of(
            basename($name),
        )
            ->replaceMatches(
                '/[^\pL\pN._ -]+/u',
                '_',
            )
            ->trim()
            ->toString();

        if ($safe === '') {
            return 'cv.pdf';
        }

        return mb_substr(
            $safe,
            0,
            255,
        );
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim(
            (string) $value,
        );

        return $value === ''
            ? null
            : $value;
    }
}
