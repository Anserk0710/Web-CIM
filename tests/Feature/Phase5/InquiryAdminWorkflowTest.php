<?php

// path: tests/Feature/Phase5/InquiryAdminWorkflowTest.php

namespace Tests\Feature\Phase5;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InquiryAdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->admin = User::factory()->create();

        $adminRole = Role::query()
            ->where('name', 'Admin')
            ->firstOrFail();

        $this->admin->roles()->attach(
            $adminRole,
        );
    }

    public function test_admin_can_read_inquiry_list_and_detail(): void
    {
        $inquiry = $this->createInquiry();

        $this->withoutVite();

        $this->actingAs($this->admin)
            ->get('/admin/inquiries')
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(
                "/admin/inquiries/{$inquiry->id}",
            )
            ->assertOk();
    }

    public function test_user_without_inquiry_read_is_denied(): void
    {
        $reader = User::factory()->create();

        $readOnlyRole = Role::query()
            ->where('name', 'Read Only')
            ->firstOrFail();

        $reader->roles()->attach(
            $readOnlyRole,
        );

        $this->actingAs($reader)
            ->get('/admin/inquiries')
            ->assertForbidden();
    }

    public function test_reopening_requires_reason_and_is_audited(): void
    {
        $inquiry = $this->createInquiry(
            status: InquiryStatus::Resolved,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$inquiry->id}/status",
                [
                    'target_status' => InquiryStatus::InProgress->value,

                    'reason' => '',

                    'lock_version' => $inquiry->lock_version,
                ],
            )
            ->assertSessionHasErrors(
                'reason',
            );

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$inquiry->id}/status",
                [
                    'target_status' => InquiryStatus::InProgress->value,

                    'reason' => 'Customer requested a follow-up.',

                    'lock_version' => $inquiry->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $inquiry->refresh();

        $this->assertSame(
            InquiryStatus::InProgress,
            $inquiry->status,
        );

        $this->assertNull(
            $inquiry->resolved_at,
        );

        $this->assertSame(
            2,
            $inquiry->lock_version,
        );

        $this->assertDatabaseHas(
            'inquiry_notes',
            [
                'inquiry_id' => $inquiry->id,
                'author_id' => $this->admin->id,
            ],
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'inquiry.status_changed',

                'entity_type' => 'Inquiry',

                'entity_id' => $inquiry->id,

                'actor_id' => $this->admin->id,
            ],
        );
    }

    public function test_admin_can_add_internal_note(): void
    {
        $inquiry = $this->createInquiry();

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$inquiry->id}/notes",
                [
                    'body' => 'Follow up with the contact tomorrow.',

                    'lock_version' => $inquiry->lock_version,
                ],
            )
            ->assertSessionHas(
                'success',
            );

        $inquiry->refresh();

        $this->assertSame(
            2,
            $inquiry->lock_version,
        );

        $this->assertDatabaseHas(
            'inquiry_notes',
            [
                'inquiry_id' => $inquiry->id,
                'author_id' => $this->admin->id,

                'body' => 'Follow up with the contact tomorrow.',
            ],
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'inquiry.note_added',

                'entity_id' => $inquiry->id,
            ],
        );
    }

    public function test_stale_lock_version_is_rejected(): void
    {
        $inquiry = $this->createInquiry();

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$inquiry->id}/status",
                [
                    'target_status' => InquiryStatus::InProgress->value,

                    'reason' => '',

                    'lock_version' => 99,
                ],
            )
            ->assertSessionHasErrors(
                'lock_version',
            );

        $this->assertSame(
            InquiryStatus::New,
            $inquiry->refresh()->status,
        );
    }

    public function test_export_requires_inquiry_export_permission(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/inquiries/export')
            ->assertForbidden();
    }

    public function test_csv_export_neutralizes_spreadsheet_formulas_and_creates_audit(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $inquiry = $this->createInquiry(
            message: '   =HYPERLINK("https://evil.example.test","click")',
        );

        $response = $this->actingAs($superAdmin)
            ->get('/admin/inquiries/export');

        $response->assertOk();

        $response->assertHeader(
            'content-type',
            'text/csv; charset=UTF-8',
        );

        $csv = $response->streamedContent();

        $this->assertStringContainsString(
            "'   =HYPERLINK",
            $csv,
        );

        $this->assertStringNotContainsString(
            'career-applications/',
            $csv,
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'inquiry.exported',
            ],
        );

        $this->assertNotNull(
            $inquiry->refresh()->id,
        );
    }

    public function test_export_over_5000_rows_is_rejected(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $timestamp = now()->format(
            'Y-m-d H:i:s.u',
        );

        for ($offset = 0; $offset < 5001; $offset += 100) {
            $batch = [];

            $end = min(
                $offset + 100,
                5001,
            );

            for ($i = $offset; $i < $end; $i++) {
                $batch[] = [
                    'type' => InquiryType::General->value,

                    'status' => InquiryStatus::New->value,

                    'privacy_notice_version' => 'TEST-PRIVACY-V1',

                    'lock_version' => 1,

                    'created_at' => $timestamp,

                    'updated_at' => $timestamp,
                ];
            }

            DB::table('inquiries')->insert(
                $batch,
            );
        }

        $response = $this
            ->actingAs($superAdmin)
            ->withHeader(
                'referer',
                route('admin.inquiries.index'),
            )
            ->get('/admin/inquiries/export');

        $response->assertRedirect(
            route('admin.inquiries.index'),
        );

        $response->assertSessionHas(
            'status',
        );
    }

    public function test_status_timestamps_follow_workflow(): void
    {
        $inquiry = $this->createInquiry();

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$inquiry->id}/status",
                [
                    'target_status' => 'IN_PROGRESS',
                    'reason' => '',
                    'lock_version' => 1,
                ],
            )
            ->assertSessionHas('success');

        $inquiry->refresh();

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$inquiry->id}/status",
                [
                    'target_status' => 'RESOLVED',
                    'reason' => '',
                    'lock_version' => $inquiry->lock_version,
                ],
            )
            ->assertSessionHas('success');

        $inquiry->refresh();

        $this->assertSame(
            InquiryStatus::Resolved,
            $inquiry->status,
        );

        $this->assertNotNull(
            $inquiry->resolved_at,
        );

        $this->assertNull(
            $inquiry->spam_marked_at,
        );

        $spamInquiry = $this->createInquiry();

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$spamInquiry->id}/status",
                [
                    'target_status' => 'SPAM',
                    'reason' => '',
                    'lock_version' => 1,
                ],
            )
            ->assertSessionHas('success');

        $spamInquiry->refresh();

        $this->assertSame(
            InquiryStatus::Spam,
            $spamInquiry->status,
        );

        $this->assertNotNull(
            $spamInquiry->spam_marked_at,
        );

        $this->actingAs($this->admin)
            ->post(
                "/admin/inquiries/{$spamInquiry->id}/status",
                [
                    'target_status' => 'IN_PROGRESS',
                    'reason' => 'Verified as a legitimate inquiry.',
                    'lock_version' => $spamInquiry->lock_version,
                ],
            )
            ->assertSessionHas('success');

        $spamInquiry->refresh();

        $this->assertSame(
            InquiryStatus::InProgress,
            $spamInquiry->status,
        );

        $this->assertNull(
            $spamInquiry->spam_marked_at,
        );
    }

    public function test_user_with_read_but_without_update_cannot_change_status(): void
    {
        $reader = User::factory()->create();

        $role = Role::query()->create([
            'name' => 'Inquiry Read Test',
            'description' => 'Read-only inquiry access.',
            'is_system' => false,
            'lock_version' => 1,
        ]);

        $readPermission = Permission::query()
            ->where('name', 'inquiry.read')
            ->firstOrFail();

        $role->permissions()->attach(
            $readPermission,
        );

        $reader->roles()->attach($role);

        $inquiry = $this->createInquiry();

        $this->actingAs($reader)
            ->get('/admin/inquiries')
            ->assertOk();

        $this->actingAs($reader)
            ->post(
                "/admin/inquiries/{$inquiry->id}/status",
                [
                    'target_status' => 'IN_PROGRESS',
                    'reason' => '',
                    'lock_version' => 1,
                ],
            )
            ->assertForbidden();

        $this->assertSame(
            InquiryStatus::New,
            $inquiry->refresh()->status,
        );
    }

    public function test_export_requires_both_read_and_export_permissions(): void
    {
        $exportOnly = User::factory()->create();

        $role = Role::query()->create([
            'name' => 'Inquiry Export Test',
            'description' => 'Export permission without read.',
            'is_system' => false,
            'lock_version' => 1,
        ]);

        $exportPermission = Permission::query()
            ->where('name', 'inquiry.export')
            ->firstOrFail();

        $role->permissions()->attach(
            $exportPermission,
        );

        $exportOnly->roles()->attach($role);

        $this->actingAs($exportOnly)
            ->get('/admin/inquiries/export')
            ->assertForbidden();
    }

    private function createSuperAdmin(): User
    {
        $user = User::factory()->create();

        $role = Role::query()
            ->where('name', 'Super Admin')
            ->firstOrFail();

        $user->roles()->attach($role);

        return $user;
    }

    private function createInquiry(
        InquiryStatus $status = InquiryStatus::New,
        string $message = 'Please contact me.',
    ): Inquiry {
        return Inquiry::query()->create([
            'type' => InquiryType::General,

            'name' => 'Test Contact',

            'email' => 'contact@example.test',

            'phone' => '+628123456789',

            'organization' => 'Test Organization',

            'message' => $message,

            'product_id' => null,

            'ecatalog_entry_id' => null,

            'status' => $status,

            'resolved_at' => $status === InquiryStatus::Resolved
                    ? now()
                    : null,

            'spam_marked_at' => $status === InquiryStatus::Spam
                    ? now()
                    : null,

            'privacy_notice_version' => 'TEST-PRIVACY-V1',

            'anonymized_at' => null,

            'lock_version' => 1,
        ]);
    }
}
