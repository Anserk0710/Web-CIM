<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use App\Models\Media;
use App\Models\SitePage;
use App\Services\Audit\AuditService;
use App\Services\Media\PageSectionMedia;
use App\Support\Content\SiteContentRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
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
                'canReadMedia' => request()->user()->can('media.read'),
                'selectedMedia' => request()->user()->can('media.read')
                    ? Media::query()->whereIn('id', $page->sections->flatMap(
                        fn (PageSection $section): array => array_filter([
                            $section->data['image_id'] ?? null,
                            $section->data['video_id'] ?? null,
                        ]),
                    ))->get()->map(fn (Media $media): array => [
                        'id' => $media->id,
                        'name' => $media->original_name,
                        'mime' => $media->mime,
                        'url' => $media->url(),
                    ])
                    : [],
            ],
        );
    }

    public function update(
        Request $request,
        SitePage $page,
        AuditService $audit,
        PageSectionMedia $sectionMedia,
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

        foreach ($request->input('sections', []) as $index => $section) {
            $rules["sections.{$index}.data.image_id"] = [
                'nullable', 'integer', Rule::exists('media', 'id')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp']),
            ];
            $rules["sections.{$index}.data.video_id"] = $page->key === 'HOME' && ($section['key'] ?? null) === 'hero'
                ? ['nullable', 'integer', Rule::exists('media', 'id')->where('mime', 'video/mp4')]
                : ['prohibited'];
            $rules["sections.{$index}.data.image_alt"] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        DB::transaction(
            function () use ($request, $page, $allowed, $validated, $audit, $sectionMedia): void {
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

                    $savedSection = PageSection::query()
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

                    $sectionMedia->sync($savedSection, $data, "sections.{$index}.data");
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
