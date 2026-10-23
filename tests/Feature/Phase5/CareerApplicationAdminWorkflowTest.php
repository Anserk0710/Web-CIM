<?php

namespace Tests\Feature\Phase5;

use App\Enums\ApplicationStatus;
use App\Enums\CareerStatus;
use App\Enums\EmploymentType;
use App\Models\ApplicationStatusEvent;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\ConsentVersion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationAdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $hr;

    private CareerApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->hr =
            User::factory()->create();

        $hrRole = Role::query()
            ->where(
                'name',
                'HR',
            )
            ->firstOrFail();

        $this->hr
            ->roles()
            ->attach($hrRole);

        $creator =
            User::factory()->create();

        $career = Career::query()
            ->create([
                'slug' => 'qa-engineer',

                'title' => 'QA Engineer',

                'department' => 'Technology',

                'location' => 'Bali',

                'employment_type' => EmploymentType::FullTime,

                'summary' => 'Quality.',

                'responsibilities' => 'Testing.',

                'requirements' => 'Testing experience.',

                'status' => CareerStatus::Open,

                'opens_at' => now()->subDay(),

                'closes_at' => now()->addWeek(),

                'created_by' => $creator->id,

                'updated_by' => $creator->id,

                'lock_version' => 1,
            ]);

        $consent =
            ConsentVersion::query()
                ->create([
                    'version' => 'TEST-CONSENT-V1',

                    'text' => 'Recruitment consent.',

                    'purpose' => 'Recruitment application processing',

                    'effective_at' => now()->subDay(),

                    'created_at' => now(),
                ]);

        $path =
            'career-applications/test/candidate.pdf';

        Storage::disk('private')
            ->put(
                $path,
                "%PDF-1.4\n%%EOF\n",
            );

        $this->application =
            CareerApplication::query()
                ->create([
                    'career_id' => $career->id,

                    'full_name' => 'Candidate Test',

                    'email' => 'candidate@example.test',

                    'email_normalized' => 'candidate@example.test',

                    'phone' => '+628123456789',

                    'introduction' => 'Introduction',

                    'portfolio_url' => null,

                    'linkedin_url' => null,

                    'cv_disk' => 'private',

                    'cv_path' => $path,

                    'cv_original_name' => 'candidate.pdf',

                    'cv_mime' => 'application/pdf',

                    'cv_size' => strlen(
                        "%PDF-1.4\n%%EOF\n",
                    ),

                    'cv_sha256' => hash(
                        'sha256',
                        "%PDF-1.4\n%%EOF\n",
                    ),

                    'status' => ApplicationStatus::New,

                    'consent_version_id' => $consent->id,

                    'consented_at' => now(),

                    'lock_version' => 1,
                ]);

        ApplicationStatusEvent::query()
            ->create([
                'application_id' => $this->application->id,

                'actor_id' => null,

                'from_status' => null,

                'to_status' => ApplicationStatus::New,

                'reason' => 'Public application submitted.',

                'created_at' => now(),
            ]);
    }

    public function test_hr_can_read_application_add_note_and_move_status(): void
    {
        $this->withoutVite();

        $this->actingAs($this->hr)
            ->get('/admin/applications')
            ->assertOk();

        $this->actingAs($this->hr)
            ->get(
                "/admin/applications/{$this->application->id}",
            )
            ->assertOk();

        $this->actingAs($this->hr)
            ->post(
                "/admin/applications/{$this->application->id}/notes",
                [
                    'body' => 'Strong PHP background.',

                    'lock_version' => $this->application
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->application->refresh();

        $this->assertDatabaseHas(
            'application_notes',
            [
                'application_id' => $this->application->id,

                'author_id' => $this->hr->id,

                'body' => 'Strong PHP background.',
            ],
        );

        $this->actingAs($this->hr)
            ->post(
                "/admin/applications/{$this->application->id}/status",
                [
                    'target_status' => 'REVIEWING',

                    'reason' => '',

                    'lock_version' => $this->application
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            ApplicationStatus::Reviewing,

            $this->application
                ->refresh()
                ->status,
        );

        $this->assertDatabaseHas(
            'application_status_events',
            [
                'application_id' => $this->application->id,

                'actor_id' => $this->hr->id,

                'from_status' => 'NEW',

                'to_status' => 'REVIEWING',
            ],
        );
    }

    public function test_backward_transition_requires_reason(): void
    {
        $this->application
            ->forceFill([
                'status' => ApplicationStatus::Reviewing,
            ])
            ->save();

        $this->actingAs($this->hr)
            ->post(
                "/admin/applications/{$this->application->id}/status",
                [
                    'target_status' => 'NEW',

                    'reason' => '',

                    'lock_version' => $this->application
                        ->lock_version,
                ],
            )
            ->assertSessionHasErrors(
                'reason',
            );

        $this->application->refresh();

        $this->actingAs($this->hr)
            ->post(
                "/admin/applications/{$this->application->id}/status",
                [
                    'target_status' => 'NEW',

                    'reason' => 'Needs initial screening again.',

                    'lock_version' => $this->application
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $this->assertSame(
            ApplicationStatus::New,

            $this->application
                ->refresh()
                ->status,
        );
    }

    public function test_rejection_sets_six_calendar_month_retention_due_date(): void
    {
        $before = now();

        $this->actingAs($this->hr)
            ->post(
                "/admin/applications/{$this->application->id}/status",
                [
                    'target_status' => 'REJECTED',

                    'reason' => 'Not selected for current vacancy.',

                    'lock_version' => $this->application
                        ->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $application =
            $this->application
                ->refresh();

        $this->assertSame(
            ApplicationStatus::Rejected,
            $application->status,
        );

        $this->assertNotNull(
            $application
                ->selection_completed_at,
        );

        $this->assertNotNull(
            $application
                ->retention_due_at,
        );

        $this->assertTrue(
            $application
                ->retention_due_at
                ->betweenIncluded(
                    $before
                        ->copy()
                        ->addMonthsNoOverflow(6)
                        ->subSeconds(5),

                    now()
                        ->addMonthsNoOverflow(6)
                        ->addSeconds(5),
                ),
        );
    }

    public function test_cv_download_requires_read_and_download_permissions(): void
    {
        $reader =
            User::factory()->create();

        $readerRole =
            Role::query()->create([
                'name' => 'Applicant Reader',

                'description' => 'Can read applications but cannot download CV.',

                'is_system' => false,

                'lock_version' => 1,
            ]);

        $readPermission =
            Permission::query()
                ->where(
                    'name',
                    'application.read',
                )
                ->firstOrFail();

        $readerRole
            ->permissions()
            ->attach(
                $readPermission,
            );

        $reader
            ->roles()
            ->attach(
                $readerRole,
            );

        $this->withoutVite();

        $this->actingAs($reader)
            ->get(
                "/admin/applications/{$this->application->id}",
            )
            ->assertOk();

        $this->actingAs($reader)
            ->get(
                "/admin/applications/{$this->application->id}/cv",
            )
            ->assertForbidden();

        $response =
            $this->actingAs($this->hr)
                ->get(
                    "/admin/applications/{$this->application->id}/cv",
                );

        $response->assertOk();

        $response->assertHeader(
            'content-type',
            'application/pdf',
        );

        $response->assertHeader(
            'x-content-type-options',
            'nosniff',
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'application.cv_downloaded',

                'entity_id' => $this->application->id,

                'actor_id' => $this->hr->id,
            ],
        );
    }

    public function test_user_without_application_read_is_denied(): void
    {
        $readOnly =
            User::factory()->create();

        $role = Role::query()
            ->where(
                'name',
                'Read Only',
            )
            ->firstOrFail();

        $readOnly
            ->roles()
            ->attach($role);

        $this->actingAs($readOnly)
            ->get('/admin/applications')
            ->assertForbidden();
    }
}
