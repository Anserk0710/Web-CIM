<?php

// path: app/Http/Controllers/Admin/InquiryWorkflowController.php

namespace App\Http\Controllers\Admin;

use App\Actions\Inquiries\AddInquiryNote;
use App\Actions\Inquiries\ChangeInquiryStatus;
use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInquiryNoteRequest;
use App\Http\Requests\Admin\UpdateInquiryStatusRequest;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;

class InquiryWorkflowController extends Controller
{
    public function status(
        UpdateInquiryStatusRequest $request,
        Inquiry $inquiry,
        ChangeInquiryStatus $action,
    ): RedirectResponse {
        $data = $request->validated();

        $action->handle(
            actor: $request->user(),

            inquiry: $inquiry,

            targetStatus: InquiryStatus::from(
                $data['target_status'],
            ),

            reason: $data['reason'] ?? null,

            expectedLockVersion: (int) $data[
                'lock_version'
            ],
        );

        return back()->with(
            'success',
            'Status inquiry berhasil diperbarui.',
        );
    }

    public function note(
        StoreInquiryNoteRequest $request,
        Inquiry $inquiry,
        AddInquiryNote $action,
    ): RedirectResponse {
        $data = $request->validated();

        $action->handle(
            actor: $request->user(),

            inquiry: $inquiry,

            body: $data['body'],

            expectedLockVersion: (int) $data[
                'lock_version'
            ],
        );

        return back()->with(
            'success',
            'Internal note berhasil ditambahkan.',
        );
    }
}
