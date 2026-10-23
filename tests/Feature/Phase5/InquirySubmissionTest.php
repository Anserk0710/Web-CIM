<?php

// path: tests/Feature/Phase5/InquirySubmissionTest.php

namespace Tests\Feature\Phase5;

use App\Enums\ContentStatus;
use App\Enums\ECatalogAvailability;
use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\RevisionKind;
use App\Models\AuditLog;
use App\Models\EcatalogEntry;
use App\Models\Inquiry;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRevision;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InquirySubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurePrivacyNotice();
    }

    public function test_contact_page_loads(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Send an inquiry')
            ->assertSee('TEST-PRIVACY-V1');
    }

    public function test_general_inquiry_persists_and_creates_audit(): void
    {
        $response = $this->post(
            route('contact.inquiries.store'),
            $this->payload(),
        );

        $response->assertRedirect(
            route('contact').'#contact-form',
        );

        $inquiry = Inquiry::query()
            ->firstOrFail();

        $this->assertSame(
            InquiryType::General,
            $inquiry->type,
        );

        $this->assertSame(
            InquiryStatus::New,
            $inquiry->status,
        );

        $this->assertSame(
            'candidate@example.test',
            $inquiry->email,
        );

        $this->assertSame(
            'TEST-PRIVACY-V1',
            $inquiry->privacy_notice_version,
        );

        $this->assertSame(
            1,
            $inquiry->lock_version,
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'action' => 'inquiry.submitted',
                'entity_type' => 'Inquiry',
                'entity_id' => $inquiry->id,
            ],
        );

        $audit = AuditLog::query()
            ->where(
                'action',
                'inquiry.submitted',
            )
            ->firstOrFail();

        $this->assertArrayNotHasKey(
            'email',
            $audit->changes ?? [],
        );

        $this->assertArrayNotHasKey(
            'message',
            $audit->changes ?? [],
        );
    }

    public function test_product_inquiry_rejects_unpublished_product(): void
    {
        $product = $this->createProduct(
            published: false,
        );

        $this->post(
            route('contact.inquiries.store'),
            [
                ...$this->payload(),

                'type' => InquiryType::Product->value,

                'product_id' => $product->id,
            ],
        )
            ->assertSessionHasErrors(
                'product_id',
            );

        $this->assertDatabaseCount(
            'inquiries',
            0,
        );
    }

    public function test_ecatalog_inquiry_rejects_mismatched_product(): void
    {
        $catalogProduct = $this->createProduct(
            published: true,
        );

        $otherProduct = $this->createProduct(
            published: true,
        );

        $catalog = $this->createCatalog(
            $catalogProduct,
        );

        $this->post(
            route('contact.inquiries.store'),
            [
                ...$this->payload(),

                'type' => InquiryType::ECatalog->value,

                'product_id' => $otherProduct->id,

                'ecatalog_entry_id' => $catalog->id,
            ],
        )
            ->assertSessionHasErrors(
                'product_id',
            );

        $this->assertDatabaseCount(
            'inquiries',
            0,
        );
    }

    public function test_valid_ecatalog_inquiry_persists_both_relations(): void
    {
        $product = $this->createProduct(
            published: true,
        );

        $catalog = $this->createCatalog(
            $product,
        );

        $this->post(
            route('contact.inquiries.store'),
            [
                ...$this->payload(),

                'type' => InquiryType::ECatalog->value,

                'product_id' => $product->id,

                'ecatalog_entry_id' => $catalog->id,
            ],
        )->assertRedirect(
            route('contact').'#contact-form',
        );

        $this->assertDatabaseHas(
            'inquiries',
            [
                'type' => InquiryType::ECatalog->value,

                'product_id' => $product->id,

                'ecatalog_entry_id' => $catalog->id,

                'status' => InquiryStatus::New->value,
            ],
        );
    }

    public function test_stale_privacy_notice_version_is_rejected(): void
    {
        $this->post(
            route('contact.inquiries.store'),
            [
                ...$this->payload(),

                'privacy_notice_version' => 'OLD-PRIVACY-VERSION',
            ],
        )->assertSessionHasErrors(
            'privacy_notice_version',
        );

        $this->assertDatabaseCount(
            'inquiries',
            0,
        );
    }

    public function test_honeypot_submission_is_not_saved(): void
    {
        $this->post(
            route('contact.inquiries.store'),
            [
                ...$this->payload(),

                'website' => 'https://spam.example.test',
            ],
        )->assertSessionHasErrors(
            'website',
        );

        $this->assertDatabaseCount(
            'inquiries',
            0,
        );
    }

    public function test_public_inquiry_rate_limit_is_enforced(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(
                route('contact.inquiries.store'),
                [
                    ...$this->payload(),

                    'email' => "candidate{$i}@example.test",
                ],
            )->assertRedirect(
                route('contact').'#contact-form',
            );
        }

        $this->post(
            route('contact.inquiries.store'),
            $this->payload(),
        )->assertStatus(429);

        $this->assertDatabaseCount(
            'inquiries',
            5,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'type' => InquiryType::General->value,

            'name' => 'Candidate Test',

            'email' => 'candidate@example.test',

            'phone' => '+628123456789',

            'organization' => 'Example Organization',

            'message' => 'I would like to request more information.',

            'privacy_notice_version' => 'TEST-PRIVACY-V1',

            'website' => '',
        ];
    }

    private function configurePrivacyNotice(): void
    {
        $page = SitePage::query()
            ->firstOrCreate(
                ['key' => 'PRIVACY'],
                [
                    'title' => 'Privacy Notice',
                    'lock_version' => 1,
                ],
            );

        PageSection::query()
            ->updateOrCreate(
                [
                    'page_id' => $page->id,
                    'key' => 'policy',
                ],
                [
                    'data' => [
                        'version' => 'TEST-PRIVACY-V1',

                        'body' => 'Test privacy notice for automated tests.',
                    ],

                    'is_enabled' => true,
                    'sort_order' => 0,
                ],
            );
    }

    private function createProduct(
        bool $published,
    ): Product {
        $user = User::factory()->create();

        $slug = 'test-product-'.Str::lower(
            Str::random(12),
        );

        $product = Product::query()->create([
            'public_slug' => $published
                ? $slug
                : null,

            'status' => $published
                ? ContentStatus::Published
                : ContentStatus::Draft,

            'published_at' => $published
                ? now()->subDay()
                : null,

            'created_by' => $user->id,
            'updated_by' => $user->id,
            'lock_version' => 1,
        ]);

        $category = ProductCategory::query()->firstOrCreate(
            ['slug' => 'test-category'],
            [
                'name' => 'Test Category',
                'is_active' => true,
                'sort_order' => 0,
                'lock_version' => 1,
            ],
        );

        ProductRevision::query()->create([
            'product_id' => $product->id,

            'category_id' => $category->id,

            'kind' => $published
                ? RevisionKind::Published
                : RevisionKind::Working,

            'requested_slug' => $slug,

            'name' => 'Test Product '.$slug,

            'summary' => 'Synthetic test product.',

            'updated_by' => $user->id,
        ]);

        return $product;
    }

    private function createCatalog(
        Product $product,
    ): EcatalogEntry {
        return EcatalogEntry::query()->create([
            'product_id' => $product->id,

            'catalog_code' => 'CAT-'.Str::upper(
                Str::random(12),
            ),

            'procurement_title' => 'Test procurement entry',

            'availability' => ECatalogAvailability::ContactUs,

            'effective_at' => now()->subDay(),

            'expires_at' => now()->addDay(),

            'status' => ContentStatus::Published,

            'published_at' => now()->subDay(),

            'sort_order' => 0,

            'lock_version' => 1,
        ]);
    }
}
