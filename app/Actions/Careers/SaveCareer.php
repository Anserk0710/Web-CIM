<?php

namespace App\Actions\Careers;

use App\Enums\CareerStatus;
use App\Models\Career;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveCareer
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        User $actor,
        array $data,
        ?Career $career = null,
    ): Career {
        return DB::transaction(
            function () use ($actor, $data, $career): Career {
                if ($career === null) {
                    $locked = new Career;

                    $locked->status =
                        CareerStatus::Draft;

                    $locked->created_by =
                        $actor->id;

                    $locked->lock_version = 1;
                } else {
                    $locked = Career::query()
                        ->whereKey(
                            $career->id,
                        )
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
                            'expected_lock_version' => 'Career telah diubah oleh user lain.',
                        ]);
                    }

                    if (
                        $locked->status
                        === CareerStatus::Open
                        && ! $actor->hasPermission(
                            'career.publish',
                        )
                    ) {
                        throw new AuthorizationException(
                            'Editing OPEN Career requires career.publish.',
                        );
                    }
                }

                $opensAt =
                    $this->toUtc(
                        $data[
                            'opens_at_local'
                        ] ?? null,
                    );

                $closesAt =
                    $this->toUtc(
                        $data[
                            'closes_at_local'
                        ] ?? null,
                    );

                if (
                    $opensAt !== null
                    && $closesAt !== null
                    && $closesAt
                        ->lessThanOrEqualTo(
                            $opensAt,
                        )
                ) {
                    throw ValidationException::withMessages([
                        'closes_at_local' => 'Closing date harus setelah opening date.',
                    ]);
                }

                $locked->fill([
                    'slug' => $data['slug'],
                    'title' => $data['title'],

                    'department' => $data['department'],

                    'location' => $data['location'],

                    'employment_type' => $data[
                            'employment_type'
                        ],

                    'summary' => $data['summary']
                        ?? null,

                    'responsibilities' => $data[
                            'responsibilities'
                        ],

                    'requirements' => $data[
                            'requirements'
                        ],

                    'opens_at' => $opensAt,
                    'closes_at' => $closesAt,

                    'seo_title' => $data['seo_title']
                        ?? null,

                    'seo_description' => $data[
                            'seo_description'
                        ] ?? null,

                    'updated_by' => $actor->id,
                ]);

                if ($locked->exists) {
                    $locked->lock_version++;
                }

                $locked->save();

                $this->audit->record(
                    action: $career === null
                    ? 'career.created'
                    : 'career.updated',
                    entity: $locked,
                    changes: [
                        'title' => $locked->title,

                        'status' => $locked
                            ->status
                            ->value,
                    ],
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }

    private function toUtc(
        ?string $value,
    ): ?Carbon {
        if (
            $value === null
            || trim($value) === ''
        ) {
            return null;
        }

        return Carbon::createFromFormat(
            'Y-m-d\TH:i',
            $value,
            'Asia/Makassar',
        )->utc();
    }
}
