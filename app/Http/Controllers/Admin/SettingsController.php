<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Settings\SaveOffice;
use App\Actions\Settings\UpdateSiteSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfficeRequest;
use App\Http\Requests\Admin\SiteSettingsRequest;
use App\Models\Media;
use App\Models\Office;
use App\Models\SitePage;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(
            'settings.read',
        );

        $settings = SiteSetting::query()
            ->get()
            ->keyBy('key')
            ->map(
                fn (SiteSetting $setting) => $setting->value,
            );

        return Inertia::render(
            'Admin/Settings/Index',
            [
                'settings' => [
                    'company' => $settings->get(
                        'company',
                        [
                            'legal_name' => '',
                            'display_name' => '',
                            'about' => '',
                        ],
                    ),

                    'contact' => $settings->get(
                        'contact',
                        [
                            'email' => '',
                            'phone' => '',
                            'whatsapp' => '',
                        ],
                    ),

                    'coverage' => $settings->get(
                        'coverage',
                        [
                            'regions' => [],
                        ],
                    ),

                    'seo_defaults' => $settings->get(
                        'seo_defaults',
                        [
                            'title_suffix' => '',
                            'default_description' => '',
                        ],
                    ),

                    'branding' => $settings->get(
                        'branding',
                        [
                            'logo_media_id' => null,
                            'favicon_media_id' => null,
                        ],
                    ),

                    'footer' => $settings->get(
                        'footer',
                        [
                            'tagline' => '',
                            'copyright' => '',
                        ],
                    ),
                ],

                'offices' => Office::query()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(),

                'pages' => SitePage::query()
                    ->orderBy('id')
                    ->get([
                        'id',
                        'key',
                        'title',
                    ]),

                'media' => Media::query()
                    ->where(
                        'mime',
                        'like',
                        'image/%',
                    )
                    ->latest()
                    ->limit(200)
                    ->get()
                    ->map(
                        fn (Media $media): array => [
                            'id' => $media->id,

                            'name' => $media
                                ->original_name,
                        ],
                    ),

                'canUpdate' => request()
                    ->user()
                    ->can(
                        'settings.update',
                    ),
            ],
        );
    }

    public function update(
        SiteSettingsRequest $request,
        UpdateSiteSettings $action,
    ): RedirectResponse {
        $action->handle(
            $request->user(),
            $request->validated(),
        );

        return back()->with(
            'success',
            'Site settings diperbarui.',
        );
    }

    public function storeOffice(
        OfficeRequest $request,
        SaveOffice $action,
    ): RedirectResponse {
        $action->handle(
            $request->user(),
            $request->validated(),
        );

        return back()->with(
            'success',
            'Office dibuat.',
        );
    }

    public function updateOffice(
        OfficeRequest $request,
        Office $office,
        SaveOffice $action,
    ): RedirectResponse {
        $action->handle(
            $request->user(),
            $request->validated(),
            $office,
        );

        return back()->with(
            'success',
            'Office diperbarui.',
        );
    }
}
