<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Services\Career\ApplicationStatusWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CareerApplicationController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        Gate::authorize(
            'viewAny',
            CareerApplication::class,
        );

        $search = mb_substr(
            trim(
                (string) $request->query(
                    'q',
                    '',
                ),
            ),
            0,
            100,
        );

        $status = ApplicationStatus::tryFrom(
            strtoupper(
                trim(
                    (string) $request->query(
                        'status',
                        '',
                    ),
                ),
            ),
        );

        $careerId = $request->integer(
            'career_id',
        );

        $query = CareerApplication::query()
            ->with(
                'career:id,title,slug',
            )
            ->latest('created_at')
            ->latest('id');

        if ($search !== '') {
            $query->where(
                function (
                    Builder $inner,
                ) use ($search): void {
                    $inner
                        ->where(
                            'full_name',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%'.$search.'%',
                        );
                },
            );
        }

        if ($status !== null) {
            $query->where(
                'status',
                $status->value,
            );
        }

        if ($careerId > 0) {
            $query->where(
                'career_id',
                $careerId,
            );
        }

        $applications = $query
            ->paginate(20)
            ->withQueryString()
            ->through(
                fn (
                    CareerApplication $application,
                ): array => [
                    'id' => $application->id,

                    'full_name' => $application->full_name,

                    'email' => $application->email,

                    'status' => $application
                        ->status
                        ->value,

                    'created_at' => $application
                        ->created_at
                        ?->toIso8601String(),

                    'anonymized_at' => $application
                        ->anonymized_at
                        ?->toIso8601String(),

                    'career' => [
                        'id' => $application
                            ->career
                            ->id,

                        'title' => $application
                            ->career
                            ->title,

                        'slug' => $application
                            ->career
                            ->slug,
                    ],
                ],
            );

        $careers = Career::query()
            ->whereHas('applications')
            ->orderBy('title')
            ->get([
                'id',
                'title',
            ]);

        return Inertia::render(
            'Admin/Applications/Index',
            [
                'applications' => $applications,

                'careers' => $careers,

                'statuses' => array_map(
                    fn (
                        ApplicationStatus $item,
                    ): string => $item->value,
                    ApplicationStatus::cases(),
                ),

                'filters' => [
                    'q' => $search,

                    'status' => $status?->value ?? '',

                    'career_id' => $careerId > 0
                            ? $careerId
                            : null,
                ],
            ],
        );
    }

    public function show(
        CareerApplication $application,
        ApplicationStatusWorkflow $workflow,
    ): Response {
        Gate::authorize(
            'view',
            $application,
        );

        $application->load([
            'career:id,title,slug,department,location',

            'consentVersion:id,version,purpose,effective_at',

            'notes.author:id,name,email',

            'statusEvents.actor:id,name,email',
        ]);

        $transitions = collect(
            $workflow->allowedTargets(
                $application->status,
            ),
        )
            ->map(
                fn (
                    ApplicationStatus $target,
                ): array => [
                    'value' => $target->value,

                    'label' => $workflow->label(
                        $target,
                    ),

                    'requires_reason' => $workflow
                        ->requiresReason(
                            $application->status,
                            $target,
                        ),
                ],
            )
            ->values();

        return Inertia::render(
            'Admin/Applications/Show',
            [
                'application' => [
                    'id' => $application->id,

                    'full_name' => $application->full_name,

                    'email' => $application->email,

                    'phone' => $application->phone,

                    'introduction' => $application->introduction,

                    'portfolio_url' => $application->portfolio_url,

                    'linkedin_url' => $application->linkedin_url,

                    'status' => $application
                        ->status
                        ->value,

                    'cv_original_name' => $application
                        ->cv_original_name,

                    'cv_size' => $application->cv_size,

                    'has_cv' => $application->cv_disk
                            === 'private'
                        && $application->cv_path
                            !== null
                        && $application->anonymized_at
                            === null,

                    'consented_at' => $application
                        ->consented_at
                        ?->toIso8601String(),

                    'selection_completed_at' => $application
                        ->selection_completed_at
                        ?->toIso8601String(),

                    'retention_due_at' => $application
                        ->retention_due_at
                        ?->toIso8601String(),

                    'hr_handover_at' => $application
                        ->hr_handover_at
                        ?->toIso8601String(),

                    'anonymized_at' => $application
                        ->anonymized_at
                        ?->toIso8601String(),

                    'created_at' => $application
                        ->created_at
                        ?->toIso8601String(),

                    'updated_at' => $application
                        ->updated_at
                        ?->toIso8601String(),

                    'lock_version' => $application
                        ->lock_version,

                    'career' => [
                        'id' => $application
                            ->career
                            ->id,

                        'title' => $application
                            ->career
                            ->title,

                        'slug' => $application
                            ->career
                            ->slug,

                        'department' => $application
                            ->career
                            ->department,

                        'location' => $application
                            ->career
                            ->location,
                    ],

                    'consent_version' => $application
                        ->consentVersion
                            === null
                                ? null
                                : [
                                    'version' => $application
                                        ->consentVersion
                                        ->version,

                                    'purpose' => $application
                                        ->consentVersion
                                        ->purpose,

                                    'effective_at' => $application
                                        ->consentVersion
                                        ->effective_at
                                        ?->toIso8601String(),
                                ],

                    'notes' => $application
                        ->notes
                        ->map(
                            fn ($note): array => [
                                'id' => $note->id,

                                'body' => $note->body,

                                'created_at' => $note
                                    ->created_at
                                    ?->toIso8601String(),

                                'author' => [
                                    'id' => $note
                                        ->author
                                        ->id,

                                    'name' => $note
                                        ->author
                                        ->name,

                                    'email' => $note
                                        ->author
                                        ->email,
                                ],
                            ],
                        )
                        ->values(),

                    'status_events' => $application
                        ->statusEvents
                        ->map(
                            fn ($event): array => [
                                'id' => $event->id,

                                'from_status' => $event
                                    ->from_status
                                    ?->value,

                                'to_status' => $event
                                    ->to_status
                                    ->value,

                                'reason' => $event->reason,

                                'created_at' => $event
                                    ->created_at
                                    ?->toIso8601String(),

                                'actor' => $event->actor
                                        === null
                                            ? null
                                            : [
                                                'id' => $event
                                                    ->actor
                                                    ->id,

                                                'name' => $event
                                                    ->actor
                                                    ->name,

                                                'email' => $event
                                                    ->actor
                                                    ->email,
                                            ],
                            ],
                        )
                        ->values(),
                ],

                'transitions' => $transitions,

                'can' => [
                    'update' => request()
                        ->user()
                        ->can(
                            'update',
                            $application,
                        ),

                    'download' => request()
                        ->user()
                        ->can(
                            'download',
                            $application,
                        ),
                ],
            ],
        );
    }
}
