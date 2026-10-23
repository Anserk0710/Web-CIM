<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CareerApplications\AddCareerApplicationNote;
use App\Actions\CareerApplications\ChangeCareerApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCareerApplicationNoteRequest;
use App\Http\Requests\Admin\UpdateCareerApplicationStatusRequest;
use App\Models\CareerApplication;
use Illuminate\Http\RedirectResponse;

class CareerApplicationWorkflowController extends Controller
{
    public function status(
        UpdateCareerApplicationStatusRequest $request,
        CareerApplication $application,
        ChangeCareerApplicationStatus $action,
    ): RedirectResponse {
        $action->handle(
            actor: $request->user(),

            application: $application,

            targetStatus: ApplicationStatus::from(
                $request
                    ->string(
                        'target_status',
                    )
                    ->toString(),
            ),

            reason: $request
                ->string('reason')
                ->toString()
                ?: null,

            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Status seleksi lamaran kerja berhasil diperbarui.',
        );
    }

    public function note(
        StoreCareerApplicationNoteRequest $request,
        CareerApplication $application,
        AddCareerApplicationNote $action,
    ): RedirectResponse {
        $action->handle(
            actor: $request->user(),

            application: $application,

            body: $request
                ->string('body')
                ->toString(),

            expectedLockVersion: $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Catatan internal berhasil ditambahkan.',
        );
    }
}
