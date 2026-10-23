<?php

// path: app/Http/Controllers/PublicSite/InquirySubmissionController.php

namespace App\Http\Controllers\PublicSite;

use App\Actions\Inquiries\SubmitInquiry;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSite\InquirySubmissionRequest;
use Illuminate\Http\RedirectResponse;

class InquirySubmissionController extends Controller
{
    public function store(
        InquirySubmissionRequest $request,
        SubmitInquiry $action,
    ): RedirectResponse {
        $action->handle(
            $request->validated(),
        );

        return redirect()
            ->to(
                route('contact')
                .'#contact-form',
            )
            ->with(
                'success',
                'Inquiry berhasil dikirim. Tim kami akan menindaklanjutinya.',
            );
    }
}
