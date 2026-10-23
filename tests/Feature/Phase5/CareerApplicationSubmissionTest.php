<?php

namespace Tests\Feature\Phase5;

use App\Enums\CareerStatus;
use App\Enums\EmploymentType;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\ConsentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
    }

    public function test_public_application_is_persisted_with_private_cv_and_consent(): void
    {
        $career = $this->openCareer();
        $consent = $this->consent();

        $response = $this->post(
            "/careers/{$career->slug}/apply",
            $this->payload(),
        );

        $response->assertRedirect(
            route(
                'careers.show',
                $career->slug,
            ).'#application-form',
        );

        $application =
            CareerApplication::query()
                ->firstOrFail();

        $this->assertSame(
            'candidate@example.test',
            $application->email_normalized,
        );

        $this->assertSame(
            'NEW',
            $application->status->value,
        );

        $this->assertSame(
            $consent->id,
            $application->consent_version_id,
        );

        $this->assertSame(
            'private',
            $application->cv_disk,
        );

        Storage::disk('private')
            ->assertExists(
                $application->cv_path,
            );

        $this->assertDatabaseCount(
            'media',
            0,
        );

        $this->assertDatabaseHas(
            'application_status_events',
            [
                'application_id' => $application->id,

                'from_status' => null,

                'to_status' => 'NEW',
            ],
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'application.submitted',

                'entity_id' => $application->id,
            ],
        );

        Storage::disk('public')
            ->assertMissing($application->cv_path);

        $this->assertContains(
            $this->get(
                '/storage/'
                .$application->cv_path,
            )->status(),
            [403, 404],
            'Private CV must not be accessible through the public storage URL.',
        );
    }

    public function test_duplicate_email_for_same_vacancy_is_rejected_without_orphan_cv(): void
    {
        $career = $this->openCareer();

        $this->consent();

        $this->post(
            "/careers/{$career->slug}/apply",
            $this->payload(),
        )->assertRedirect();

        $this->post(
            "/careers/{$career->slug}/apply",
            [
                ...$this->payload(),

                'email' => ' Candidate@Example.Test ',
            ],
        )->assertSessionHasErrors(
            'email',
        );

        $this->assertDatabaseCount(
            'career_applications',
            1,
        );

        $this->assertCount(
            1,

            Storage::disk('private')
                ->allFiles(
                    'career-applications',
                ),
        );
    }

    public function test_server_deadline_rejects_application_even_when_career_status_is_open(): void
    {
        $career = $this->openCareer();

        $career->forceFill([
            'closes_at' => now()->subSecond(),
        ])->save();

        $this->consent();

        $this->post(
            "/careers/{$career->slug}/apply",
            $this->payload(),
        )->assertSessionHasErrors(
            'career',
        );

        $this->assertDatabaseCount(
            'career_applications',
            0,
        );

        $this->assertSame(
            [],

            Storage::disk('private')
                ->allFiles(
                    'career-applications',
                ),
        );
    }

    public function test_cv_larger_than_five_mib_is_rejected(): void
    {
        $career = $this->openCareer();

        $this->consent();

        $payload = $this->payload();

        $payload['cv'] =
            UploadedFile::fake()
                ->create(
                    'oversized.pdf',
                    5121,
                    'application/pdf',
                );

        $this->post(
            "/careers/{$career->slug}/apply",
            $payload,
        )->assertSessionHasErrors(
            'cv',
        );

        $this->assertDatabaseCount(
            'career_applications',
            0,
        );
    }

    public function test_public_submission_is_rate_limited_per_ip(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/careers/example/apply', [])
                ->assertUnprocessable();
        }

        $this->postJson('/careers/example/apply', [])
            ->assertTooManyRequests()
            ->assertHeader('Retry-After');
    }

    private function openCareer(): Career
    {
        $user =
            User::factory()->create();

        return Career::query()
            ->create([
                'slug' => 'backend-engineer',

                'title' => 'Backend Engineer',

                'department' => 'Technology',

                'location' => 'Bali',

                'employment_type' => EmploymentType::FullTime,

                'summary' => 'Build reliable systems.',

                'responsibilities' => 'Build APIs.',

                'requirements' => 'PHP experience.',

                'status' => CareerStatus::Open,

                'opens_at' => now()->subDay(),

                'closes_at' => now()->addDay(),

                'created_by' => $user->id,

                'updated_by' => $user->id,

                'lock_version' => 1,
            ]);
    }

    private function consent(): ConsentVersion
    {
        return ConsentVersion::query()
            ->create([
                'version' => 'TEST-CONSENT-V1',

                'text' => 'Recruitment test consent.',

                'purpose' => 'Recruitment application processing',

                'effective_at' => now()->subDay(),

                'created_at' => now(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'full_name' => 'Candidate Test',

            'email' => 'candidate@example.test',

            'phone' => '+628123456789',

            'introduction' => 'Hello HR.',

            'portfolio_url' => 'https://example.test/portfolio',

            'linkedin_url' => 'https://example.test/linkedin',

            'cv' => $this->pdf(),

            'consent_version_id' => ConsentVersion::query()
                ->effective()
                ->firstOrFail()
                ->id,

            'consent' => '1',

            'website' => '',
        ];
    }

    private function pdf(): UploadedFile
    {
        $path = tempnam(
            sys_get_temp_dir(),
            'cim-cv-',
        );

        if ($path === false) {
            $this->fail(
                'Could not create temporary PDF.',
            );
        }

        file_put_contents(
            $path,
            "%PDF-1.4\n"
            ."1 0 obj\n"
            ."<<>>\n"
            ."endobj\n"
            ."trailer\n"
            ."<<>>\n"
            ."%%EOF\n",
        );

        return new UploadedFile(
            $path,
            'candidate.pdf',
            'application/pdf',
            null,
            true,
        );
    }
}
