<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocalSuperAdminSeeder extends Seeder
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

        $email = Str::lower(
            trim(
                (string) env(
                    'DEV_SUPER_ADMIN_EMAIL',
                    'cim@cim.local',
                ),
            ),
        );

        $name = (string) env(
            'DEV_SUPER_ADMIN_NAME',
            'Local Super Admin',
        );

        $configuredPassword = trim(
            (string) env(
                'DEV_SUPER_ADMIN_PASSWORD',
                '',
            ),
        );

        $existing = User::query()
            ->where('email', $email)
            ->first();

        $generatedPassword = null;

        if ($existing === null) {
            $password = $configuredPassword;

            if ($password === '') {
                $generatedPassword = Str::password(
                    length: 24,
                    letters: true,
                    numbers: true,
                    symbols: true,
                    spaces: false,
                );

                $password = $generatedPassword;
            }

            $existing = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
                'email_verified_at' => now(),
                'lock_version' => 1,
            ]);
        }

        $superAdmin = Role::query()
            ->where(
                'name',
                Role::SUPER_ADMIN,
            )
            ->firstOrFail();

        $existing->roles()->syncWithoutDetaching([
            $superAdmin->id,
        ]);

        if (
            $generatedPassword !== null
            && $this->command !== null
        ) {
            $this->command->warn(
                'Local Super Admin created.',
            );

            $this->command->line(
                "Email: {$email}",
            );

            $this->command->line(
                "Password: {$generatedPassword}",
            );

            $this->command->warn(
                'Simpan password tersebut. Password tidak disimpan di source control.',
            );
        }
    }
}
