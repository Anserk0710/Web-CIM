<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditResult;
use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use App\Services\Audit\AuditService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareerApplicationCvController extends Controller
{
    public function __invoke(
        CareerApplication $application,
        AuditService $audit,
    ): StreamedResponse|Response {
        $authorization = Gate::inspect(
            'download',
            $application,
        );

        if ($authorization->denied()) {
            $audit->record(
                action: 'application.cv_download_denied',

                entity: $application,

                result: AuditResult::Denied,

                actor: request()->user(),
            );

            abort(403);
        }

        if (
            $application->anonymized_at !== null
            || $application->cv_disk !== 'private'
            || $application->cv_path === null
        ) {
            abort(404);
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('private');

        if (
            ! $disk->exists(
                $application->cv_path,
            )
        ) {
            abort(404);
        }

        $audit->record(
            action: 'application.cv_downloaded',

            entity: $application,

            changes: [
                'career_id' => $application->career_id,
            ],

            actor: request()->user(),
        );

        return $disk->download(
            $application->cv_path,

            $application->cv_original_name
                ?: 'cv.pdf',

            [
                'Content-Type' => 'application/pdf',

                'Cache-Control' => 'private, no-store, max-age=0',

                'Pragma' => 'no-cache',

                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
