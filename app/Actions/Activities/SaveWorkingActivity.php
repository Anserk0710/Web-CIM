<?php

namespace App\Actions\Activities;

use App\Enums\ContentStatus;
use App\Enums\RevisionKind;
use App\Models\Activity;
use App\Models\ActivityRevision;
use App\Models\User;
use App\Services\Activity\ActivityBlockMediaRegistry;
use App\Services\Activity\ActivityBlockSanitizer;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveWorkingActivity
{
    public function __construct(
        private readonly ActivityBlockSanitizer $sanitizer,
        private readonly ActivityBlockMediaRegistry $mediaRegistry,
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        User $actor,
        array $data,
        ?Activity $activity = null,
    ): Activity {
        return DB::transaction(
            function () use ($actor, $data, $activity): Activity {
                if ($activity === null) {
                    $locked = Activity::query()
                        ->create([
                            'status' => ContentStatus::Draft,

                            'created_by' => $actor->id,

                            'updated_by' => $actor->id,

                            'lock_version' => 1,
                        ]);
                } else {
                    $locked = Activity::query()
                        ->whereKey($activity->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $locked->lock_version
                        !== (int) (
                            $data[
                                'expected_lock_version'
                            ] ?? 0
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'expected_lock_version' => 'Activity telah diubah oleh user lain.',
                        ]);
                    }
                }

                $blocks = $this->sanitizer
                    ->sanitize(
                        $data['blocks']
                        ?? [],
                    );

                $revision = ActivityRevision::query()
                    ->firstOrNew([
                        'activity_id' => $locked->id,

                        'kind' => RevisionKind::Working
                            ->value,
                    ]);

                $revision->fill([
                    'requested_slug' => $data['requested_slug'],

                    'title' => $data['title'],

                    'excerpt' => $data['excerpt']
                        ?? null,

                    'category_id' => $data['category_id']
                        ?? null,

                    'featured_media_id' => $data[
                            'featured_media_id'
                        ] ?? null,

                    'is_featured' => (bool) $data[
                            'is_featured'
                        ],

                    'seo_title' => $data['seo_title']
                        ?? null,

                    'seo_description' => $data[
                            'seo_description'
                        ] ?? null,

                    'og_media_id' => $data['og_media_id']
                        ?? null,

                    'updated_by' => $actor->id,
                ]);

                $revision->save();

                $this->mediaRegistry
                    ->clearRevision($revision);

                $revision->blocks()->delete();

                foreach (
                    $blocks as $index => $block
                ) {
                    $model = $revision
                        ->blocks()
                        ->create([
                            ...$block,
                            'sort_order' => $index,
                        ]);

                    $this->mediaRegistry
                        ->syncBlock($model);
                }

                $sync = [];

                foreach (
                    array_values(
                        $data[
                            'related_product_ids'
                        ] ?? [],
                    ) as $index => $productId
                ) {
                    $sync[
                        (int) $productId
                    ] = [
                        'sort_order' => $index,
                    ];
                }

                $revision
                    ->relatedProducts()
                    ->sync($sync);

                $locked->forceFill([
                    'updated_by' => $actor->id,

                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->audit->record(
                    action: 'activity.working_saved',
                    entity: $locked,
                    changes: [
                        'title' => $revision->title,

                        'requested_slug' => $revision
                            ->requested_slug,
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
