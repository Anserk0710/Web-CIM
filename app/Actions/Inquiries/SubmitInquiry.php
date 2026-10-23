<?php

// path: app/Actions/Inquiries/SubmitInquiry.php

namespace App\Actions\Inquiries;

use App\Enums\ContentStatus;
use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\EcatalogEntry;
use App\Models\Inquiry;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\SitePage;
use App\Services\Audit\AuditService;
use App\Services\Notifications\NotificationOutboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitInquiry
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly NotificationOutboxService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        array $data,
    ): Inquiry {
        return DB::transaction(
            function () use ($data): Inquiry {
                $now = now();

                $privacyPage = SitePage::query()
                    ->where('key', 'PRIVACY')
                    ->lockForUpdate()
                    ->first();

                $policy = $privacyPage === null
                    ? null
                    : PageSection::query()
                        ->where(
                            'page_id',
                            $privacyPage->id,
                        )
                        ->where('key', 'policy')
                        ->where('is_enabled', true)
                        ->lockForUpdate()
                        ->first();

                $policyData = $policy?->data ?? [];

                $currentVersion = trim(
                    (string) (
                        $policyData['version'] ?? ''
                    ),
                );

                $currentBody = trim(
                    (string) (
                        $policyData['body'] ?? ''
                    ),
                );

                if (
                    $currentVersion === ''
                    || $currentBody === ''
                ) {
                    throw ValidationException::withMessages([
                        'privacy_notice_version' => 'Privacy notice belum dikonfigurasi. Inquiry belum dapat dikirim.',
                    ]);
                }

                if (
                    ! hash_equals(
                        $currentVersion,
                        trim(
                            (string) $data[
                                'privacy_notice_version'
                            ],
                        ),
                    )
                ) {
                    throw ValidationException::withMessages([
                        'privacy_notice_version' => 'Privacy notice telah berubah. Muat ulang halaman dan periksa pemberitahuan terbaru.',
                    ]);
                }

                $type = InquiryType::from(
                    (string) $data['type'],
                );

                $productId = isset($data['product_id'])
                    ? (int) $data['product_id']
                    : null;

                $catalogId = isset(
                    $data['ecatalog_entry_id'],
                )
                    ? (int) $data[
                        'ecatalog_entry_id'
                    ]
                    : null;

                if ($type === InquiryType::Product) {
                    $product = $productId === null
                        ? null
                        : Product::query()
                            ->published()
                            ->whereKey($productId)
                            ->lockForUpdate()
                            ->first();

                    if ($product === null) {
                        throw ValidationException::withMessages([
                            'product_id' => 'Produk tidak tersedia untuk inquiry.',
                        ]);
                    }

                    if ($catalogId !== null) {
                        throw ValidationException::withMessages([
                            'ecatalog_entry_id' => 'Inquiry Product tidak boleh menyertakan E-Catalog.',
                        ]);
                    }
                } elseif (
                    $type === InquiryType::ECatalog
                ) {
                    $catalog = $catalogId === null
                        ? null
                        : EcatalogEntry::query()
                            ->whereKey($catalogId)
                            ->lockForUpdate()
                            ->first();

                    if (
                        $catalog === null
                        || $catalog->status
                            !== ContentStatus::Published
                        || $catalog->published_at === null
                        || (
                            $catalog->effective_at !== null
                            && $catalog->effective_at
                                ->greaterThan($now)
                        )
                        || (
                            $catalog->expires_at !== null
                            && ! $now->lessThan(
                                $catalog->expires_at,
                            )
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'ecatalog_entry_id' => 'E-Catalog tidak tersedia untuk inquiry.',
                        ]);
                    }

                    $product = Product::query()
                        ->published()
                        ->whereKey(
                            $catalog->product_id,
                        )
                        ->lockForUpdate()
                        ->first();

                    if ($product === null) {
                        throw ValidationException::withMessages([
                            'ecatalog_entry_id' => 'Produk terkait tidak lagi tersedia.',
                        ]);
                    }

                    if (
                        $productId !== $catalog->product_id
                    ) {
                        throw ValidationException::withMessages([
                            'product_id' => 'Produk tidak sesuai dengan E-Catalog yang dipilih.',
                        ]);
                    }
                } elseif (
                    $productId !== null
                    || $catalogId !== null
                ) {
                    throw ValidationException::withMessages([
                        'type' => 'Inquiry tipe ini tidak boleh mempunyai relasi Product atau E-Catalog.',
                    ]);
                }

                $inquiry = Inquiry::query()->create([
                    'type' => $type,

                    'name' => trim(
                        (string) $data['name'],
                    ),

                    'email' => mb_strtolower(
                        trim(
                            (string) $data['email'],
                        ),
                    ),

                    'phone' => $this->nullableString(
                        $data['phone'] ?? null,
                    ),

                    'organization' => $this->nullableString(
                        $data['organization'] ?? null,
                    ),

                    'message' => trim(
                        (string) $data['message'],
                    ),

                    'product_id' => $productId,

                    'ecatalog_entry_id' => $catalogId,

                    'status' => InquiryStatus::New,

                    'resolved_at' => null,

                    'spam_marked_at' => null,

                    'privacy_notice_version' => $currentVersion,

                    'anonymized_at' => null,

                    'lock_version' => 1,
                ]);

                $this->audit->record(
                    action: 'inquiry.submitted',
                    entity: $inquiry,
                    changes: [
                        'type' => $type->value,
                        'status' => InquiryStatus::New->value,
                        'product_id' => $productId,
                        'ecatalog_entry_id' => $catalogId,
                        'privacy_notice_version' => $currentVersion,
                    ],
                    actor: null,
                );

                $this->notifications->inquirySubmitted($inquiry);

                return $inquiry->refresh();
            },
            attempts: 3,
        );
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}
