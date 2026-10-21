<?php

namespace App\Actions\Settings;

use App\Models\Media;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Media\MediaUsageRegistry;
use App\Support\Content\SiteContentRegistry;
use Illuminate\Support\Facades\DB;

class UpdateSiteSettings
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly MediaUsageRegistry $mediaRegistry,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        User $actor,
        array $data,
    ): void {
        DB::transaction(
            function () use ($actor, $data): void {
                foreach (
                    SiteContentRegistry::settingKeys() as $key
                ) {
                    if (
                        ! array_key_exists(
                            $key,
                            $data,
                        )
                    ) {
                        continue;
                    }

                    $setting =
                        SiteSetting::query()
                            ->where(
                                'key',
                                $key,
                            )
                            ->lockForUpdate()
                            ->first();

                    if ($setting === null) {
                        $setting =
                            SiteSetting::query()
                                ->create([
                                    'key' => $key,

                                    'value' => $data[$key],

                                    'updated_by' => $actor->id,

                                    'lock_version' => 1,
                                ]);
                    } else {
                        $setting->forceFill([
                            'value' => $data[$key],

                            'updated_by' => $actor->id,

                            'lock_version' => $setting
                                ->lock_version
                                + 1,
                        ])->save();
                    }

                    if (
                        $key === 'branding'
                    ) {
                        DB::table('media_usages')
                            ->where(
                                'owner_type',
                                'site_setting',
                            )
                            ->where(
                                'owner_id',
                                $setting->id,
                            )
                            ->delete();

                        foreach (
                            [
                                'logo_media_id',
                                'favicon_media_id',
                            ] as $slot
                        ) {
                            $mediaId = $data[
                                'branding'
                            ][$slot] ?? null;

                            if ($mediaId === null) {
                                continue;
                            }

                            $this->mediaRegistry
                                ->register(
                                    media: Media::query()
                                        ->findOrFail(
                                            $mediaId,
                                        ),

                                    ownerType: 'site_setting',

                                    ownerId: $setting->id,

                                    slot: $slot,
                                );
                        }
                    }

                    $this->audit->record(
                        action: 'settings.updated',

                        entity: $setting,

                        changes: [
                            'key' => $key,
                        ],

                        actor: $actor,
                    );
                }
            },
            attempts: 3,
        );
    }
}
