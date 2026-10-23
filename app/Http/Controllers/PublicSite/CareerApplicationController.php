<?php

namespace App\Http\Controllers\PublicSite;

use App\Actions\CareerApplications\SubmitCareerApplication;
use App\Enums\CareerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSite\CareerApplicationRequest;
use App\Models\Career;
use Illuminate\Http\RedirectResponse;

class CareerApplicationController extends Controller
{
    public function store(
        CareerApplicationRequest $request,
        string $slug,
        SubmitCareerApplication $action,
    ): RedirectResponse {
        $career = Career::query()
            ->where('slug', $slug)
            ->whereIn(
                'status',
                [
                    CareerStatus::Open->value,
                    CareerStatus::Closed->value,
                ],
            )
            ->firstOrFail();

        $action->handle(
            career: $career,
            data: $request->validated(),
            cv: $request->file('cv'),
        );

        return redirect()
            ->to(
                route(
                    'careers.show',
                    $career->slug,
                ).'#application-form',
            )
            ->with(
                'success',
                'Terima kasih, berkas lamaran Anda berhasil dikirim. Tim rekrutmen kami akan segera meninjau profil Anda.',
            );
    }
}
