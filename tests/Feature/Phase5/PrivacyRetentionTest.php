<?php // path: tests/Feature/Phase5/PrivacyRetentionTest.php

namespace Tests\Feature\Phase5;

use App\Enums\ApplicationStatus;
use App\Enums\CareerStatus;
use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\EmploymentType;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivacyRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_rejected_application_is_anonymized_and_cv_is_deleted(): void
    {
        Storage::fake('private');

        $application = $this->createCareerApplication();

        Storage::disk('private')->put(
            'cv/synthetic-cv.pdf',
            '%PDF-1.4 synthetic test data',
        );

        DB::table('application_notes')->insert([
            'application_id' => $application->id,
            'author_id' => Career::query()
                ->findOrFail($application->career_id)
                ->created_by,
            'body' => 'Sensitive note for retention testing.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('application_status_events')->insert([
            'application_id' => $application->id,
            'actor_id' => null,
            'from_status' => 'INTERVIEW',
            'to_status' => 'REJECTED',
            'reason' => 'Sensitive reason for retention testing.',
            'created_at' => now(),
        ]);

        $this->artisan('privacy:purge')
            ->assertExitCode(0);

        $application->refresh();

        $this->assertNotNull(
            $application->anonymized_at,
        );

        $this->assertNull(
            $application->full_name,
        );

        $this->assertNull(
            $application->email,
        );

        $this->assertNull(
            $application->email_normalized,
        );

        $this->assertNull(
            $application->phone,
        );

        $this->assertNull(
            $application->introduction,
        );

        $this->assertNull(
            $application->cv_path,
        );

        $this->assertNull(
            $application->cv_sha256,
        );

        Storage::disk('private')->assertMissing(
            'cv/synthetic-cv.pdf',
        );

        $this->assertDatabaseMissing(
            'application_notes',
            [
                'application_id' => $application->id,
            ],
        );

        $this->assertDatabaseHas(
            'application_status_events',
            [
                'application_id' => $application->id,
                'to_status' => 'REJECTED',
                'reason' => null,
            ],
        );

        $this->assertDatabaseHas(
            'privacy_deletion_ledger',
            [
                'entity_type' => 'CareerApplication',
                'entity_id' => $application->id,
                'category' => 'CAREER_APPLICATION_PII',
            ],
        );
    }

    public function test_resolved_inquiry_older_than_twelve_months_is_anonymized(): void
    {
        $author = User::factory()->create();

        $inquiry = Inquiry::query()->create([
            'type' => InquiryType::General,

            'name' => 'Old Contact',

            'email' => 'old-contact@example.test',

            'phone' => '+628123456789',

            'organization' => 'Synthetic Organization',

            'message' => 'Old sensitive inquiry message.',

            'status' => InquiryStatus::Resolved,

            'resolved_at' =>
                now()->subMonthsNoOverflow(13),

            'privacy_notice_version' => 'TEST-PRIVACY-V1',

            'lock_version' => 1,
        ]);

        DB::table('inquiry_notes')->insert([
            'inquiry_id' => $inquiry->id,
            'author_id' => $author->id,
            'body' => 'Old sensitive internal note.',
            'created_at' => now()->subMonthsNoOverflow(13),
            'updated_at' => now()->subMonthsNoOverflow(13),
        ]);

        $this->artisan('privacy:purge')
            ->assertExitCode(0);

        $inquiry->refresh();

        $this->assertNotNull(
            $inquiry->anonymized_at,
        );

        $this->assertNull(
            $inquiry->name,
        );

        $this->assertNull(
            $inquiry->email,
        );

        $this->assertNull(
            $inquiry->phone,
        );

        $this->assertNull(
            $inquiry->organization,
        );

        $this->assertNull(
            $inquiry->message,
        );

        $this->assertSame(
            'TEST-PRIVACY-V1',
            $inquiry->privacy_notice_version,
        );

        $this->assertDatabaseMissing(
            'inquiry_notes',
            [
                'inquiry_id' => $inquiry->id,
            ],
        );

        $this->assertDatabaseHas(
            'privacy_deletion_ledger',
            [
                'entity_type' => 'Inquiry',
                'entity_id' => $inquiry->id,
                'category' => 'INQUIRY_PII',
            ],
        );
    }

    public function test_recent_inquiry_is_not_anonymized(): void
    {
        $inquiry = Inquiry::query()->create([
            'type' => InquiryType::General,

            'name' => 'Recent Contact',

            'email' => 'recent@example.test',

            'message' => 'Recent inquiry message.',

            'status' => InquiryStatus::Resolved,

            'resolved_at' => now()->subMonths(2),

            'privacy_notice_version' => 'TEST-PRIVACY-V1',

            'lock_version' => 1,
        ]);

        $this->artisan('privacy:purge')
            ->assertExitCode(0);

        $this->assertNull(
            $inquiry->refresh()->anonymized_at,
        );

        $this->assertSame(
            'recent@example.test',
            $inquiry->email,
        );
    }

    private function createCareerApplication(): CareerApplication
    {
        $user = User::factory()->create();

        $career = Career::query()->create([
            'slug' => 'retention-test-vacancy',

            'title' => 'Retention Test Vacancy',

            'department' => 'Testing',

            'location' => 'Denpasar',

            'employment_type' =>
                EmploymentType::FullTime,

            'summary' => 'Synthetic test vacancy.',

            'responsibilities' => 'Synthetic test responsibilities.',

            'requirements' => 'Synthetic test requirements.',

            'status' => CareerStatus::Closed,

            'created_by' => $user->id,

            'updated_by' => $user->id,

            'lock_version' => 1,
        ]);

        return CareerApplication::query()->create([
            'career_id' => $career->id,

            'full_name' => 'Synthetic Applicant',

            'email' => 'applicant@example.test',

            'email_normalized' => 'applicant@example.test',

            'phone' => '+628123456789',

            'introduction' => 'Synthetic applicant introduction.',

            'portfolio_url' => 'https://example.test/portfolio',

            'linkedin_url' => 'https://example.test/profile',

            'cv_disk' => 'private',

            'cv_path' => 'cv/synthetic-cv.pdf',

            'cv_original_name' => 'synthetic-cv.pdf',

            'cv_mime' => 'application/pdf',

            'cv_size' => 32,

            'cv_sha256' => hash(
                'sha256',
                'synthetic-test-cv',
            ),

            'status' => ApplicationStatus::Rejected,

            'selection_completed_at' => now()->subMonthsNoOverflow(7),

            'retention_due_at' => now()->subDay(),

            'lock_version' => 1,
        ]);
    }
}
