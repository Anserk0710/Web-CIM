<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use App\Models\SitePage;
use App\Services\Audit\AuditService;
use App\Support\Content\SiteContentRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PageContentController extends Controller
{
    public function edit(
        SitePage $page,
    ): Response {
        Gate::authorize('page.read');

        $allowed =
            SiteContentRegistry::pageSections()[
                $page->key
            ] ?? [];

        $page->load('sections');

        return Inertia::render(
            'Admin/Settings/PageContent',
            [
                'page' => [
                    'id' => $page->id,
                    'key' => $page->key,
                    'title' => $page->title,

                    'lock_version' => $page->lock_version,
                ],

                'sections' => collect(
                    $allowed,
                )
                    ->map(
                        function (string $key) use ($page): array {
                            $section =
                                $page
                                    ->sections
                                    ->firstWhere(
                                        'key',
                                        $key,
                                    );

                            return [
                                'key' => $key,

                                'is_enabled' => $section
                                    ?->is_enabled
                                    ?? true,

                                'data' => $section
                                    ?->data
                                    ?? [],
                            ];
                        },
                    )
                    ->values(),

                'canUpdate' => request()
                    ->user()
                    ->can('page.update'),
            ],
        );
    }

    public function update(
        Request $request,
        SitePage $page,
        AuditService $audit,
    ): RedirectResponse {
        Gate::authorize('page.update');

        $allowed =
            SiteContentRegistry::pageSections()[
                $page->key
            ] ?? [];

        $rules = [
            'lock_version' => [
                'required',
                'integer',
                'min:1',
            ],

            'sections' => [
                'required',
                'array',
            ],

            'sections.*.key' => [
                'required',
                'string',
            ],

            'sections.*.is_enabled' => [
                'required',
                'boolean',
            ],

            'sections.*.data' => [
                'required',
                'array',
            ],
        ];

        if ($page->key === 'PRIVACY') {
            $rules['sections.*.data.version'] = [
                'required',
                'string',
                'max:40',
            ];

            $rules['sections.*.data.body'] = [
                'required',
                'string',
                'max:5000',
            ];
        }

        $validated = $request->validate($rules);

        DB::transaction(
            function () use ($request, $page, $allowed, $validated, $audit): void {
                $locked = SitePage::query()
                    ->whereKey($page->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== (int) $validated[
                        'lock_version'
                    ]
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Page telah berubah.',
                    ]);
                }

                foreach (
                    $validated['sections'] as $index => $section
                ) {
                    if (
                        ! in_array(
                            $section['key'],
                            $allowed,
                            true,
                        )
                    ) {
                        throw ValidationException::withMessages([
                            "sections.{$index}.key" => 'Section is not allowed for this page.',
                        ]);
                    }

                    $data =
                        $this->sanitizeSectionData(
                            $section['data'],
                        );

                    PageSection::query()
                        ->updateOrCreate(
                            [
                                'page_id' => $locked->id,

                                'key' => $section['key'],
                            ],
                            [
                                'data' => $data,

                                'is_enabled' => (bool) $section[
                                        'is_enabled'
                                    ],

                                'sort_order' => $index,
                            ],
                        );
                }

                $locked->forceFill([
                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $audit->record(
                    action: 'page_content.updated',
                    entity: $locked,
                    actor: $request->user(),
                );
            },
            attempts: 3,
        );

        return back()->with(
            'success',
            'Page content diperbarui.',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitizeSectionData(
        array $data,
    ): array {
        $result = [];

        foreach (
            $data as $key => $value
        ) {
            if (is_array($value)) {
                $result[$key] =
                    $this->sanitizeSectionData(
                        $value,
                    );

                continue;
            }

            if (
                is_string($value)
            ) {
                $result[$key] =
                    mb_substr(
                        trim(
                            strip_tags(
                                $value,
                            ),
                        ),
                        0,
                        5000,
                    );

                continue;
            }

            if (
                is_bool($value)
                || is_numeric($value)
                || $value === null
            ) {
                $result[$key] =
                    $value;
            }
        }

        return $result;
    }
}
