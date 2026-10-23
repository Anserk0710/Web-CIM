<?php

namespace App\Support\Content;

final class SiteContentRegistry
{
    /**
     * @return array<int, string>
     */
    public static function pageKeys(): array
    {
        return [
            'HOME',
            'COMPANY',
            'PRODUCTS',
            'ECATALOG',
            'ACTIVITY',
            'CAREER',
            'CONTACT',
            'PRIVACY',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function pageSections(): array
    {
        return [
            'HOME' => [
                'hero',
                'metrics',
                'capabilities',
                'lifecycle',
                'featured_products',
                'coverage',
                'activity_preview',
                'cta',
            ],

            'COMPANY' => [
                'hero',
                'story',
                'metrics',
                'vision_mission',
                'values',
                'milestones',
                'coverage',
                'cta',
            ],

            'PRODUCTS' => [
                'hero',
            ],

            'ECATALOG' => [
                'hero',
            ],

            'ACTIVITY' => [
                'hero',
            ],

            'CAREER' => [
                'hero',
                'career_branding',
            ],

            'CONTACT' => [
                'hero',
                'contact_intro',
            ],

            'PRIVACY' => [
                'policy',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function settingKeys(): array
    {
        return [
            'company',
            'contact',
            'coverage',
            'seo_defaults',
            'branding',
            'footer',
        ];
    }
}
