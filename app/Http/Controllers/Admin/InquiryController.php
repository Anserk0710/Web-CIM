<?php

// path: app/Http/Controllers/Admin/InquiryController.php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Services\Audit\AuditService;
use App\Services\Inquiry\InquiryQuery;
use App\Services\Inquiry\InquiryStatusWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryController extends Controller
{
    public function index(
        Request $request,
        InquiryQuery $inquiryQuery,
    ): Response {
        Gate::authorize(
            'viewAny',
            Inquiry::class,
        );

        $filters = $inquiryQuery->filters(
            $request,
        );

        $inquiries = $inquiryQuery
            ->build($filters)
            ->paginate(20)
            ->withQueryString()
            ->through(
                fn (
                    Inquiry $inquiry,
                ): array => [
                    'id' => $inquiry->id,

                    'type' => $inquiry->type->value,

                    'status' => $inquiry->status->value,

                    'name' => $inquiry->isAnonymized()
                            ? null
                            : $inquiry->name,

                    'email' => $inquiry->isAnonymized()
                            ? null
                            : $inquiry->email,

                    'organization' => $inquiry->isAnonymized()
                            ? null
                            : $inquiry->organization,

                    'created_at' => $inquiry->created_at
                        ?->toIso8601String(),

                    'anonymized_at' => $inquiry->anonymized_at
                        ?->toIso8601String(),

                    'product_name' => $inquiry->product
                        ?->publishedRevision
                        ?->name,

                    'catalog_title' => $inquiry->ecatalogEntry
                        ?->procurement_title,
                ],
            );

        return Inertia::render(
            'Admin/Inquiries/Index',
            [
                'inquiries' => $inquiries,

                'types' => array_map(
                    fn (
                        InquiryType $type,
                    ): string => $type->value,
                    InquiryType::cases(),
                ),

                'statuses' => array_map(
                    fn (
                        InquiryStatus $status,
                    ): string => $status->value,
                    InquiryStatus::cases(),
                ),

                'filters' => $filters,

                'canExport' => $request
                    ->user()
                    ->can(
                        'export',
                        Inquiry::class,
                    ),
            ],
        );
    }

    public function show(
        Request $request,
        Inquiry $inquiry,
        InquiryStatusWorkflow $workflow,
    ): Response {
        Gate::authorize(
            'view',
            $inquiry,
        );

        $inquiry->load([
            'product.publishedRevision:id,product_id,name',

            'ecatalogEntry.product.publishedRevision:id,product_id,name',

            'notes.author:id,name,email',
        ]);

        $anonymized = $inquiry->isAnonymized();

        $transitions = collect(
            $workflow->allowedTargets(
                $inquiry->status,
            ),
        )
            ->map(
                fn (
                    InquiryStatus $target,
                ): array => [
                    'value' => $target->value,

                    'label' => $workflow->label(
                        $target,
                    ),

                    'requires_reason' => $workflow->requiresReason(
                        $inquiry->status,
                        $target,
                    ),
                ],
            )
            ->values();

        $history = AuditLog::query()
            ->with('actor:id,name,email')
            ->where(
                'entity_type',
                'Inquiry',
            )
            ->where(
                'entity_id',
                $inquiry->id,
            )
            ->where(
                'action',
                'inquiry.status_changed',
            )
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(
                fn (
                    AuditLog $log,
                ): array => [
                    'id' => $log->id,

                    'from_status' => data_get(
                        $log->changes,
                        'status.before',
                    ),

                    'to_status' => data_get(
                        $log->changes,
                        'status.after',
                    ),

                    'reason_note_id' => data_get(
                        $log->changes,
                        'reason_note_id',
                    ),

                    'created_at' => $log->created_at
                        ?->toIso8601String(),

                    'actor' => $log->actor === null
                        ? null
                        : [
                            'id' => $log->actor->id,
                            'name' => $log->actor->name,
                        ],
                ],
            )
            ->values();

        return Inertia::render(
            'Admin/Inquiries/Show',
            [
                'inquiry' => [
                    'id' => $inquiry->id,

                    'type' => $inquiry->type->value,

                    'status' => $inquiry->status->value,

                    'name' => $anonymized
                        ? null
                        : $inquiry->name,

                    'email' => $anonymized
                        ? null
                        : $inquiry->email,

                    'phone' => $anonymized
                        ? null
                        : $inquiry->phone,

                    'organization' => $anonymized
                        ? null
                        : $inquiry->organization,

                    'message' => $anonymized
                        ? null
                        : $inquiry->message,

                    'privacy_notice_version' => $inquiry->privacy_notice_version,

                    'resolved_at' => $inquiry->resolved_at
                        ?->toIso8601String(),

                    'spam_marked_at' => $inquiry->spam_marked_at
                        ?->toIso8601String(),

                    'anonymized_at' => $inquiry->anonymized_at
                        ?->toIso8601String(),

                    'created_at' => $inquiry->created_at
                        ?->toIso8601String(),

                    'lock_version' => $inquiry->lock_version,

                    'product' => [
                        'id' => $inquiry->product?->id,

                        'name' => $inquiry->product
                            ?->publishedRevision
                            ?->name,
                    ],

                    'ecatalog' => [
                        'id' => $inquiry->ecatalogEntry?->id,

                        'catalog_code' => $inquiry->ecatalogEntry
                            ?->catalog_code,

                        'title' => $inquiry->ecatalogEntry
                            ?->procurement_title,
                    ],

                    'notes' => $anonymized
                        ? []
                        : $inquiry->notes
                            ->map(
                                fn ($note): array => [
                                    'id' => $note->id,

                                    'body' => $note->body,

                                    'created_at' => $note->created_at
                                        ?->toIso8601String(),

                                    'author' => [
                                        'id' => $note->author->id,

                                        'name' => $note->author->name,
                                    ],
                                ],
                            )
                            ->values(),
                ],

                'history' => $history,

                'transitions' => $transitions,

                'can' => [
                    'update' => $request
                        ->user()
                        ->can(
                            'update',
                            $inquiry,
                        ),
                ],
            ],
        );
    }

    public function export(
        Request $request,
        InquiryQuery $inquiryQuery,
        AuditService $audit,
    ): StreamedResponse|RedirectResponse {
        Gate::authorize(
            'export',
            Inquiry::class,
        );

        $filters = $inquiryQuery->filters(
            $request,
        );

        $rows = $inquiryQuery
            ->build($filters)
            ->limit(5001)
            ->get();

        if ($rows->count() > 5000) {
            return back()->with(
                'status',
                'CSV tidak dibuat karena hasil melebihi 5.000 baris. Persempit filter dan coba kembali.',
            );
        }

        $audit->record(
            action: 'inquiry.exported',

            changes: [
                'row_count' => $rows->count(),

                'type' => $filters['type'],

                'status' => $filters['status'],
            ],

            actor: $request->user(),
        );

        return response()->streamDownload(
            function () use ($rows): void {
                $output = fopen(
                    'php://output',
                    'wb',
                );

                if ($output === false) {
                    return;
                }

                fwrite(
                    $output,
                    "\xEF\xBB\xBF",
                );

                fputcsv(
                    $output,
                    [
                        'ID',
                        'Type',
                        'Status',
                        'Name',
                        'Email',
                        'Phone',
                        'Organization',
                        'Message',
                        'Product',
                        'E-Catalog Code',
                        'E-Catalog Title',
                        'Privacy Notice Version',
                        'Created At',
                        'Resolved At',
                        'Spam Marked At',
                        'Anonymized At',
                    ],
                    ',',
                    '"',
                    '',
                );

                foreach ($rows as $inquiry) {
                    $anonymized =
                        $inquiry->isAnonymized();

                    $values = [
                        $inquiry->id,

                        $inquiry->type->value,

                        $inquiry->status->value,

                        $anonymized
                            ? ''
                            : $inquiry->name,

                        $anonymized
                            ? ''
                            : $inquiry->email,

                        $anonymized
                            ? ''
                            : $inquiry->phone,

                        $anonymized
                            ? ''
                            : $inquiry->organization,

                        $anonymized
                            ? ''
                            : $inquiry->message,

                        $inquiry->product
                            ?->publishedRevision
                            ?->name,

                        $inquiry->ecatalogEntry
                            ?->catalog_code,

                        $inquiry->ecatalogEntry
                            ?->procurement_title,

                        $inquiry->privacy_notice_version,

                        $inquiry->created_at
                            ?->toIso8601String(),

                        $inquiry->resolved_at
                            ?->toIso8601String(),

                        $inquiry->spam_marked_at
                            ?->toIso8601String(),

                        $inquiry->anonymized_at
                            ?->toIso8601String(),
                    ];

                    fputcsv(
                        $output,
                        array_map(
                            fn ($value): string => $this->safeCsvCell(
                                $value,
                            ),
                            $values,
                        ),
                        ',',
                        '"',
                        '',
                    );
                }

                fclose($output);
            },

            'inquiries-'.now()->format(
                'Ymd-His',
            ).'.csv',

            [
                'Content-Type' => 'text/csv; charset=UTF-8',

                'Cache-Control' => 'private, no-store, max-age=0',

                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    private function safeCsvCell(
        mixed $value,
    ): string {
        $text = (string) ($value ?? '');

        $withoutLeadingWhitespace = preg_replace(
            '/^[\p{Z}\s\x00-\x1F\x7F]+/u',
            '',
            $text,
        );

        if ($withoutLeadingWhitespace === null) {
            $withoutLeadingWhitespace = $text;
        }

        $formulaAfterWhitespace = preg_match(
            '/^[=+\-@]/u',
            $withoutLeadingWhitespace,
        ) === 1;

        $startsWithControl = preg_match(
            '/^[\x00-\x1F\x7F]/',
            $text,
        ) === 1;

        $startsWithFormula = preg_match(
            '/^[=+\-@]/u',
            $text,
        ) === 1;

        if (
            $formulaAfterWhitespace
            || $startsWithControl
            || $startsWithFormula
        ) {
            return "'".$text;
        }

        return $text;
    }
}
