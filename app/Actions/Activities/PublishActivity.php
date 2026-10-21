<?php

namespace App\Actions\Activities;

use App\Enums\ContentStatus;
use App\Enums\RevisionKind;
use App\Models\Activity;
use App\Models\ActivityRevision;
use App\Models\UrlRedirect;
use App\Models\User;
use App\Services\Activity\ActivityBlockMediaRegistry;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishActivity
{
    public function __construct(
        private readonly ActivityBlockMediaRegistry $mediaRegistry,
        private readonly AuditService $audit,
    ) {}

    public function handle(
        User $actor,
        Activity $activity,
        int $expectedLockVersion,
    ): Activity {
        return DB::transaction(
            function () use ($actor, $activity, $expectedLockVersion): Activity {
                $locked = Activity::query()
                    ->whereKey($activity->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->lock_version
                    !== $expectedLockVersion
                ) {
                    throw ValidationException::withMessages([
                        'lock_version' => 'Activity telah berubah.',
                    ]);
                }

                $working = ActivityRevision::query()
                    ->where(
                        'activity_id',
                        $locked->id,
                    )
                    ->where(
                        'kind',
                        RevisionKind::Working
                            ->value,
                    )
                    ->with([
                        'blocks',
                        'relatedProducts',
                    ])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $working->category_id !== null
                    && ! $working
                        ->category
                        ?->is_active
                ) {
                    throw ValidationException::withMessages([
                        'category_id' => 'Activity category tidak aktif.',
                    ]);
                }

                $owner = Activity::query()
                    ->where(
                        'public_slug',
                        $working
                            ->requested_slug,
                    )
                    ->whereKeyNot(
                        $locked->id,
                    )
                    ->lockForUpdate()
                    ->first();

                if ($owner !== null) {
                    throw ValidationException::withMessages([
                        'requested_slug' => 'Slug sudah digunakan Activity lain.',
                    ]);
                }

                /** @var ActivityRevision|null $published */
                $published = ActivityRevision::query()
                    ->where(
                        'activity_id',
                        $locked->id,
                    )
                    ->where(
                        'kind',
                        RevisionKind::Published
                            ->value,
                    )
                    ->with('blocks')
                    ->lockForUpdate()
                    ->first();

                if ($published !== null) {
                    $this->mediaRegistry
                        ->clearRevision(
                            $published,
                        );

                    $published->delete();
                }

                $copy = ActivityRevision::query()
                    ->create([
                        'activity_id' => $locked->id,

                        'kind' => RevisionKind::Published,

                        'requested_slug' => $working
                            ->requested_slug,

                        'title' => $working->title,

                        'excerpt' => $working->excerpt,

                        'category_id' => $working
                            ->category_id,

                        'featured_media_id' => $working
                            ->featured_media_id,

                        'is_featured' => $working
                            ->is_featured,

                        'seo_title' => $working
                            ->seo_title,

                        'seo_description' => $working
                            ->seo_description,

                        'og_media_id' => $working
                            ->og_media_id,

                        'updated_by' => $actor->id,
                    ]);

                foreach (
                    $working->blocks as $block
                ) {
                    $newBlock = $copy
                        ->blocks()
                        ->create([
                            'block_key' => $block
                                ->block_key,

                            'type' => $block->type,

                            'data' => $block->data,

                            'sort_order' => $block
                                ->sort_order,
                        ]);

                    $this->mediaRegistry
                        ->syncBlock(
                            $newBlock,
                        );
                }

                $sync = [];

                foreach (
                    $working
                        ->relatedProducts as $related
                ) {
                    $sync[
                        $related->id
                    ] = [
                        'sort_order' => $related
                            ->pivot
                            ->sort_order,
                    ];
                }

                $copy
                    ->relatedProducts()
                    ->sync($sync);

                $oldSlug =
                    $locked->public_slug;

                $newSlug =
                    $working->requested_slug;

                $locked->forceFill([
                    'public_slug' => $newSlug,

                    'status' => ContentStatus::Published,

                    'published_at' => now(),

                    'archived_at' => null,

                    'updated_by' => $actor->id,

                    'lock_version' => $locked->lock_version
                        + 1,
                ])->save();

                if (
                    $oldSlug !== null
                    && $oldSlug !== $newSlug
                ) {
                    UrlRedirect::query()
                        ->updateOrCreate(
                            [
                                'old_path' => '/activities/'
                                    .$oldSlug,
                            ],
                            [
                                'entity_type' => Activity::REDIRECT_TYPE,

                                'entity_id' => $locked->id,

                                'created_at' => now(),
                            ],
                        );
                }

                $this->audit->record(
                    action: 'activity.published',
                    entity: $locked,
                    changes: [
                        'old_slug' => $oldSlug,
                        'new_slug' => $newSlug,
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
