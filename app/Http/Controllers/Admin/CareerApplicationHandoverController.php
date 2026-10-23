<?php // path: app/Http/Controllers/Admin/CareerApplicationHandoverController.php

namespace App\Http\Controllers\Admin;

use App\Actions\CareerApplications\MarkCareerApplicationHandover;
use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CareerApplicationHandoverController extends Controller
{
    public function __invoke(
        Request $request,
        CareerApplication $application,
        MarkCareerApplicationHandover $action,
    ): RedirectResponse {
        $data = $request->validate([
            'lock_version' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $action->handle(
            actor: $request->user(),
            application: $application,
            expectedLockVersion: (int) $data['lock_version'],
        );

        return back()->with(
            'success',
            'HR handover tercatat. Data pribadi dijadwalkan untuk dibersihkan 30 hari setelah handover.',
        );
    }
}
