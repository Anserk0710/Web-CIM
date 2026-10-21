<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityCategory;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ActivityCategoryController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(
            'taxonomy.read',
        );

        return Inertia::render(
            'Admin/Activities/Categories',
            [
                'categories' => ActivityCategory::query()
                    ->orderBy('name')
                    ->get(),

                'canManage' => request()
                    ->user()
                    ->can('taxonomy.manage'),
            ],
        );
    }

    public function store(
        Request $request,
        AuditService $audit,
    ): RedirectResponse {
        Gate::authorize(
            'taxonomy.manage',
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'slug' => [
                'required',
                'string',
                'max:160',
                Rule::unique(
                    'activity_categories',
                    'slug',
                ),
            ],
        ]);

        $category =
            ActivityCategory::query()
                ->create([
                    'name' => trim(
                        $data['name'],
                    ),

                    'slug' => Str::slug(
                        $data['slug'],
                    ),

                    'is_active' => true,
                    'lock_version' => 1,
                ]);

        $audit->record(
            action: 'activity_category.created',
            entity: $category,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'Activity category dibuat.',
        );
    }

    public function update(
        Request $request,
        ActivityCategory $category,
        AuditService $audit,
    ): RedirectResponse {
        Gate::authorize(
            'taxonomy.manage',
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'slug' => [
                'required',
                'string',
                'max:160',

                Rule::unique(
                    'activity_categories',
                    'slug',
                )->ignore(
                    $category->id,
                ),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'lock_version' => [
                'required',
                'integer',
            ],
        ]);

        DB::transaction(
            function () use ($request, $category, $data, $audit): void {
                $locked =
                    ActivityCategory::query()
                        ->whereKey(
                            $category->id,
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $locked->lock_version
                    !== (int) $data[
                        'lock_version'
                    ]
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Category telah berubah.',
                    ]);
                }

                $locked->forceFill([
                    'name' => trim($data['name']),

                    'slug' => Str::slug(
                        $data['slug'],
                    ),

                    'is_active' => (bool) $data[
                            'is_active'
                        ],

                    'lock_version' => $locked
                        ->lock_version + 1,
                ])->save();

                $audit->record(
                    action: 'activity_category.updated',
                    entity: $locked,
                    actor: $request->user(),
                );
            },
            attempts: 3,
        );

        return back()->with(
            'success',
            'Activity category diperbarui.',
        );
    }
}
