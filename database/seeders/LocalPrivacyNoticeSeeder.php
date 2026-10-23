<?php

// path: database/seeders/LocalPrivacyNoticeSeeder.php

namespace Database\Seeders;

use App\Models\PageSection;
use App\Models\SitePage;
use Illuminate\Database\Seeder;

class LocalPrivacyNoticeSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment([
            'local',
            'testing',
        ])) {
            return;
        }

        $page = SitePage::query()
            ->firstOrCreate(
                ['key' => 'PRIVACY'],
                [
                    'title' => 'Privacy Notice',
                    'lock_version' => 1,
                ],
            );

        PageSection::query()
            ->firstOrCreate(
                [
                    'page_id' => $page->id,
                    'key' => 'policy',
                ],
                [
                    'data' => [
                        'version' => 'DEV-PRIVACY-V1',

                        'body' => 'DEVELOPMENT ONLY — Data yang Anda kirim melalui formulir digunakan untuk menindaklanjuti inquiry Anda. Ganti teks ini dengan privacy notice yang sudah disetujui sebelum production.',
                    ],

                    'is_enabled' => true,
                    'sort_order' => 0,
                ],
            );
    }
}
