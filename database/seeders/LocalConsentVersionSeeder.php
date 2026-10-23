<?php

namespace Database\Seeders;

use App\Models\ConsentVersion;
use Illuminate\Database\Seeder;

class LocalConsentVersionSeeder extends Seeder
{
    public function run(): void
    {
        if (
            ! app()->environment([
                'local',
                'testing',
            ])
        ) {
            return;
        }

        ConsentVersion::query()
            ->firstOrCreate(
                [
                    'version' => 'DEV-RECRUITMENT-CONSENT-V1',
                ],
                [
                    'text' => 'DEV ONLY  Saya menyetujui pemrosesan data pribadi yang saya kirimkan untuk proses rekrutmen dan evaluasi lamaran. Teks ini hanya untuk local/testing dan wajib diganti dengan wording legal yang telah disetujui sebelum production.',

                    'purpose' => 'Recruitment application processing',

                    'effective_at' => now()->subDay(),

                    'created_at' => now(),
                ],
            );
    }
}
