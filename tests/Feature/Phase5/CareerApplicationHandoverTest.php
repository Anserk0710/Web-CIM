<?php // path: tests/Feature/Phase5/CareerApplicationHandoverTest.php

namespace Tests\Feature\Phase5;

use App\Actions\CareerApplications\MarkCareerApplicationHandover;
use App\Enums\ApplicationStatus;
use App\Enums\CareerStatus;
use App\Enums\EmploymentType;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerApplicationHandoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_handover_sets_retention_due_date_and_audit(): void
    {
        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $hr = User::factory()->create();

        $hrRole = Role::query()
            ->where('name', 'HR')
            ->firstOrFail();

        $hr->roles()->attach($hrRole);

        $career = Career::query()->create([
            'slug' => 'handover-test-vacancy',

            'title' => 'Handover Test Vacancy',

            'department' => 'Testing',

            'location' => 'Denpasar',

            'employment_type' =>
                EmploymentType::FullTime,

            'summary' => 'Synthetic vacancy.',

            'responsibilities' => 'Synthetic responsibilities.',

            'requirements' => 'Synthetic requirements.',

            'status' => CareerStatus::Closed,

            'created_by' => $hr->id,

            'updated_by' => $hr->id,

            'lock_version' => 1,
        ]);

        $application = CareerApplication::query()->create([
            'career_id' => $career->id,

            'full_name' => 'Synthetic Hired Applicant',

            'email' => 'hired@example.test',

            'email_normalized' => 'hired@example.test',

            'status' => ApplicationStatus::Hired,

            'selection_completed_at' => now(),

            'retention_due_at' => null,

            'hr_handover_at' => null,

            'lock_version' => 1,
        ]);

        $result = app(
            MarkCareerApplicationHandover::class,
        )->handle(
            actor: $hr,
            application: $application,
            expectedLockVersion: 1,
        );

        $this->assertNotNull(
            $result->hr_handover_at,
        );

        $this->assertTrue(
            $result->retention_due_at->equalTo(
                $result->hr_handover_at->copy()->addDays(30),
            ),
        );

        $this->assertSame(
            2,
            $result->lock_version,
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' =>
                    'application.hr_handover_recorded',

                'entity_type' => 'CareerApplication',

                'entity_id' => $application->id,

                'actor_id' => $hr->id,
            ],
        );
    }
}
