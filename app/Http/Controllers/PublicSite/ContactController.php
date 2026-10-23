<?php

// path: app/Http/Controllers/PublicSite/ContactController.php

namespace App\Http\Controllers\PublicSite;

use App\Enums\ContentStatus;
use App\Enums\InquiryType;
use App\Http\Controllers\Controller;
use App\Models\EcatalogEntry;
use App\Models\Office;
use App\Models\Product;
use App\Models\SitePage;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show(
        Request $request,
    ): View {
        $page = SitePage::query()
            ->where('key', 'CONTACT')
            ->with('sections')
            ->first();

        $hero = [];

        if ($page !== null) {
            $heroSection = $page
                ->sections
                ->firstWhere('key', 'hero');

            if (
                $heroSection !== null
                && $heroSection->is_enabled
            ) {
                $hero = $heroSection->data ?? [];
            }
        }

        $privacyPage = SitePage::query()
            ->where('key', 'PRIVACY')
            ->with('sections')
            ->first();

        $privacySection = $privacyPage === null
            ? null
            : $privacyPage
                ->sections
                ->firstWhere('key', 'policy');

        $privacyPolicy = (
            $privacySection !== null
            && $privacySection->is_enabled
        )
            ? ($privacySection->data ?? [])
            : [];

        $privacyVersion = trim(
            (string) (
                $privacyPolicy['version'] ?? ''
            ),
        );

        $privacyBody = trim(
            (string) (
                $privacyPolicy['body'] ?? ''
            ),
        );

        $privacyReady = $privacyVersion !== ''
            && mb_strlen($privacyVersion) <= 40
            && $privacyBody !== '';

        $products = Product::query()
            ->published()
            ->with(
                'publishedRevision:id,product_id,name',
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get([
                'id',
                'public_slug',
                'published_at',
            ])
            ->filter(
                fn (Product $product): bool => $product->publishedRevision !== null,
            )
            ->values();

        $catalogs = EcatalogEntry::query()
            ->where(
                'status',
                ContentStatus::Published->value,
            )
            ->whereNotNull('published_at')
            ->where(
                fn ($query) => $query
                    ->whereNull('effective_at')
                    ->orWhere(
                        'effective_at',
                        '<=',
                        now(),
                    ),
            )
            ->where(
                fn ($query) => $query
                    ->whereNull('expires_at')
                    ->orWhere(
                        'expires_at',
                        '>',
                        now(),
                    ),
            )
            ->whereHas(
                'product',
                fn ($query) => $query->published(),
            )
            ->with(
                'product.publishedRevision:id,product_id,name',
            )
            ->orderBy('procurement_title')
            ->orderBy('id')
            ->get()
            ->filter(
                fn (EcatalogEntry $entry): bool => $entry->product?->publishedRevision
                    !== null,
            )
            ->values();

        $selectedType = InquiryType::tryFrom(
            mb_strtoupper(
                trim(
                    (string) $request->query(
                        'type',
                        'GENERAL',
                    ),
                ),
            ),
        ) ?? InquiryType::General;

        $prefill = [
            'type' => $selectedType->value,
            'product_id' => '',
            'ecatalog_entry_id' => '',
        ];

        if (
            $selectedType === InquiryType::Product
        ) {
            $product = $products->firstWhere(
                'id',
                $request->integer('product_id'),
            );

            if ($product !== null) {
                $prefill['product_id'] =
                    (string) $product->id;
            }
        }

        if (
            $selectedType === InquiryType::ECatalog
        ) {
            $catalog = $catalogs->firstWhere(
                'id',
                $request->integer(
                    'ecatalog_entry_id',
                ),
            );

            if ($catalog !== null) {
                $prefill['product_id'] =
                    (string) $catalog->product_id;

                $prefill['ecatalog_entry_id'] =
                    (string) $catalog->id;
            }
        }

        $contactSettings = SiteSetting::query()
            ->where('key', 'contact')
            ->first()
            ?->value ?? [];

        $offices = Office::query()
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view(
            'public.contact',
            [
                'page' => $page,
                'hero' => $hero,
                'privacyPolicy' => $privacyPolicy,
                'privacyReady' => $privacyReady,
                'products' => $products,
                'catalogs' => $catalogs,
                'prefill' => $prefill,
                'contactSettings' => $contactSettings,
                'offices' => $offices,
            ],
        );
    }
}
