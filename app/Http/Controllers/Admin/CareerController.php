<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Careers\ArchiveCareer;
use App\Actions\Careers\CloseCareer;
use App\Actions\Careers\OpenCareer;
use App\Actions\Careers\SaveCareer;
use App\Enums\EmploymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CareerRequest;
use App\Http\Requests\Admin\ProductLockRequest;
use App\Models\Career;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CareerController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        Gate::authorize(
            'viewAny',
            Career::class,
        );

        $search = trim(
            (string) $request->query(
                'q',
                '',
            ),
        );

        $status = trim(
            (string) $request->query(
                'status',
                '',
            ),
        );

        $query = Career::query();

        if ($search !== '') {
            $query->where(
                function ($inner) use ($search): void {
                    $inner
                        ->where(
                            'title',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'department',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'location',
                            'like',
                            '%'.$search.'%',
                        );
                },
            );
        }

        if ($status !== '') {
            $query->where(
                'status',
                $status,
            );
        }

        return Inertia::render(
            'Admin/Careers/Index',
            [
                'careers' => $query
                    ->latest('updated_at')
                    ->paginate(20)
                    ->withQueryString(),

                'filters' => [
                    'q' => $search,
                    'status' => $status,
                ],

                'canCreate' => request()
                    ->user()
                    ->can(
                        'create',
                        Career::class,
                    ),
            ],
        );
    }

    public function create(): Response
    {
        Gate::authorize(
            'create',
            Career::class,
        );

        return Inertia::render(
            'Admin/Careers/Form',
            [
                'career' => null,

                'form' => $this->emptyForm(),

                'employmentTypes' => array_map(
                    fn (
                        EmploymentType $type,
                    ): string => $type->value,
                    EmploymentType::cases(),
                ),
            ],
        );
    }

    public function store(
        CareerRequest $request,
        SaveCareer $action,
    ): RedirectResponse {
        Gate::authorize(
            'create',
            Career::class,
        );

        $career = $action->handle(
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route(
                'admin.careers.edit',
                $career,
            )
            ->with(
                'success',
                'Career draft dibuat.',
            );
    }

    public function edit(
        Career $career,
    ): Response {
        Gate::authorize(
            'update',
            $career,
        );

        return Inertia::render(
            'Admin/Careers/Form',
            [
                'career' => [
                    'id' => $career->id,

                    'status' => $career->status->value,

                    'lock_version' => $career->lock_version,
                ],

                'form' => [
                    'expected_lock_version' => $career->lock_version,

                    'slug' => $career->slug,
                    'title' => $career->title,

                    'department' => $career->department,

                    'location' => $career->location,

                    'employment_type' => $career
                        ->employment_type
                        ->value,

                    'summary' => $career->summary
                        ?? '',

                    'responsibilities' => $career
                        ->responsibilities,

                    'requirements' => $career->requirements,

                    'opens_at_local' => $career
                        ->opens_at
                        ?->copy()
                        ->timezone(
                            'Asia/Makassar',
                        )
                        ->format(
                            'Y-m-d\TH:i',
                        ),

                    'closes_at_local' => $career
                        ->closes_at
                        ?->copy()
                        ->timezone(
                            'Asia/Makassar',
                        )
                        ->format(
                            'Y-m-d\TH:i',
                        ),

                    'seo_title' => $career->seo_title
                        ?? '',

                    'seo_description' => $career
                        ->seo_description
                        ?? '',
                ],

                'employmentTypes' => array_map(
                    fn (
                        EmploymentType $type,
                    ): string => $type->value,
                    EmploymentType::cases(),
                ),

                'can' => [
                    'publish' => request()
                        ->user()
                        ->can(
                            'publish',
                            $career,
                        ),

                    'close' => request()
                        ->user()
                        ->can(
                            'close',
                            $career,
                        ),

                    'archive' => request()
                        ->user()
                        ->can(
                            'archive',
                            $career,
                        ),
                ],
            ],
        );
    }

    public function update(
        CareerRequest $request,
        Career $career,
        SaveCareer $action,
    ): RedirectResponse {
        Gate::authorize(
            'update',
            $career,
        );

        $action->handle(
            $request->user(),
            $request->validated(),
            $career,
        );

        return back()->with(
            'success',
            'Career disimpan.',
        );
    }

    public function open(
        ProductLockRequest $request,
        Career $career,
        OpenCareer $action,
    ): RedirectResponse {
        Gate::authorize(
            'publish',
            $career,
        );

        $action->handle(
            $request->user(),
            $career,
            $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Career dibuka.',
        );
    }

    public function close(
        ProductLockRequest $request,
        Career $career,
        CloseCareer $action,
    ): RedirectResponse {
        Gate::authorize(
            'close',
            $career,
        );

        $action->handle(
            $request->user(),
            $career,
            $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Career ditutup.',
        );
    }

    public function archive(
        ProductLockRequest $request,
        Career $career,
        ArchiveCareer $action,
    ): RedirectResponse {
        Gate::authorize(
            'archive',
            $career,
        );

        $action->handle(
            $request->user(),
            $career,
            $request->integer(
                'lock_version',
            ),
        );

        return back()->with(
            'success',
            'Career diarsipkan.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyForm(): array
    {
        return [
            'expected_lock_version' => null,
            'slug' => '',
            'title' => '',
            'department' => '',
            'location' => '',
            'employment_type' => EmploymentType::FullTime
                ->value,
            'summary' => '',
            'responsibilities' => '',
            'requirements' => '',
            'opens_at_local' => null,
            'closes_at_local' => null,
            'seo_title' => '',
            'seo_description' => '',
        ];
    }
}
