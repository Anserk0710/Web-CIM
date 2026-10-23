<?php

namespace App\View\Composers;

use App\Models\Media;
use App\Models\Office;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class PublicSiteLayoutComposer
{
    public function compose(View $view): void
    {
        if (array_key_exists('publicSite', $view->getData())) {
            return;
        }

        /** @var Collection<int, SiteSetting> $siteSettings */
        $siteSettings = SiteSetting::query()->get();

        $settings = $siteSettings
            ->mapWithKeys(
                fn (SiteSetting $setting): array => [
                    $setting->key => (array) $setting->value,
                ],
            )
            ->all();

        $company = (array) ($settings['company'] ?? []);
        $contact = (array) ($settings['contact'] ?? []);
        $coverage = (array) ($settings['coverage'] ?? []);
        $seoDefaults = (array) ($settings['seo_defaults'] ?? []);
        $branding = (array) ($settings['branding'] ?? []);
        $footer = (array) ($settings['footer'] ?? []);

        $logo = isset($branding['logo_media_id'])
            ? Media::query()
                ->whereKey((int) $branding['logo_media_id'])
                ->where('mime', 'like', 'image/%')
                ->first()
            : null;

        $favicon = isset($branding['favicon_media_id'])
            ? Media::query()
                ->whereKey((int) $branding['favicon_media_id'])
                ->where('mime', 'like', 'image/%')
                ->first()
            : null;

        $offices = Office::query()
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $headOffice = $offices->firstWhere(
            'is_head_office',
            true,
        ) ?? $offices->first();

        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $company['display_name']
                ?? $company['legal_name']
                ?? 'PT Cahya Intan Medika',
            'url' => url('/'),
        ];

        if ($logo !== null) {
            $organizationSchema['logo'] = $logo->url();
        }

        if (! empty($contact['email'])) {
            $organizationSchema['email'] = $contact['email'];
        }

        if (! empty($contact['phone'])) {
            $organizationSchema['telephone'] = $contact['phone'];
        }

        if ($headOffice !== null) {
            $organizationSchema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $headOffice->address,
            ];
        }

        $view->with('publicSite', [
            'company' => $company,
            'contact' => $contact,
            'coverage' => $coverage,
            'seo_defaults' => $seoDefaults,
            'branding' => $branding,
            'footer' => $footer,
            'logo' => $logo,
            'favicon' => $favicon,
            'offices' => $offices,
            'headOffice' => $headOffice,
            'organization_schema' => $organizationSchema,
        ]);
    }
}
