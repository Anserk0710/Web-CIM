<?php

namespace Tests\Feature\PublicSite;

use App\Models\Career;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PendingSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_implemented_submission_routes_are_registered(): void
    {
        $this->assertFalse(Route::has('inquiries.store'));
        $this->assertTrue(Route::has('contact.inquiries.store'));
        $this->assertTrue(Route::has('careers.apply'));

        $this->post('/contact')->assertStatus(405);
        $this->postJson(route('contact.inquiries.store'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_contact_offers_inquiry_form_when_privacy_is_configured(): void
    {
        $this->configurePrivacy();

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Submit inquiry')
            ->assertSee('action="'.route('contact.inquiries.store').'"', false)
            ->assertSee('name="privacy_notice_version"', false)
            ->assertSee('value="test-v1"', false)
            ->assertSee('<form', false);
    }

    public function test_contact_is_readable_without_privacy_but_hides_submission_form(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Formulir sementara tidak tersedia karena Privacy Notice belum dikonfigurasi.')
            ->assertDontSee('Submit inquiry')
            ->assertDontSee('<form', false);
    }

    public function test_open_career_offers_applications_with_privacy_and_consent(): void
    {
        $this->configurePrivacy();
        DB::table('consent_versions')->insert([
            'version' => 'test-v1',
            'text' => 'Persetujuan rekrutmen.',
            'purpose' => 'recruitment',
            'effective_at' => now()->subDay(),
            'created_at' => now(),
        ]);

        $user = User::factory()->create();
        $career = Career::query()->create([
            'slug' => 'test-engineer',
            'title' => 'Test Engineer',
            'department' => 'Engineering',
            'location' => 'Makassar',
            'employment_type' => 'FULL_TIME',
            'responsibilities' => 'Pengujian aplikasi.',
            'requirements' => 'Pengalaman pengujian.',
            'status' => 'OPEN',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->get('/careers/'.$career->slug)
            ->assertOk()
            ->assertSee('Test Engineer')
            ->assertSee('Submit Application')
            ->assertSee(route('careers.apply', $career->slug))
            ->assertSee('name="consent_version_id"', false)
            ->assertSee('JobPosting')
            ->assertSee('<form', false);
    }

    private function configurePrivacy(): void
    {
        $page = SitePage::query()->create([
            'key' => 'PRIVACY',
            'title' => 'Privacy',
        ]);

        $page->sections()->create([
            'key' => 'policy',
            'data' => ['version' => 'test-v1', 'body' => 'Pemberitahuan privasi.'],
            'is_enabled' => true,
        ]);
    }
}
