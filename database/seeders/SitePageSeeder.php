<?php

namespace Database\Seeders;

use App\Models\SitePage;
use App\Support\Content\SiteContentRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SitePageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (
            SiteContentRegistry::pageKeys() as $key
        ) {
            SitePage::query()->firstOrCreate(
                [
                    'key' => $key,
                ],
                [
                    'title' => Str::headline(
                        strtolower(
                            $key,
                        ),
                    ),

                    'lock_version' => 1,
                ],
            );
        }
    }
}
