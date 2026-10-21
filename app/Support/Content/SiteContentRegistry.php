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
                'capabilities',
                'lifecycle',
            ],

            'COMPANY' => [
                'hero',
                'values',
                'milestones',
            ],

            'CAREER' => [
                'hero',
                'career_branding',
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
