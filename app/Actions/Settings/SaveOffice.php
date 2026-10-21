<?php

namespace App\Actions\Settings;

use App\Models\Office;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveOffice
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
        ?Office $office = null,
    ): Office {
        return DB::transaction(
            function () use ($actor, $data, $office): Office {
                Office::query()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($office === null) {
                    $locked = new Office;
                    $locked->lock_version = 1;
                } else {
                    $locked = Office::query()
                        ->whereKey($office->id)
                        ->firstOrFail();

                    if (
                        $locked->lock_version
                        !== (int) (
                            $data[
                                'lock_version'
                            ] ?? 0
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'lock_version' => 'Office telah berubah.',
                        ]);
                    }
                }

                if (
                    (bool) $data[
                        'is_head_office'
                    ]
                    && (bool) $data[
                        'is_active'
                    ]
                ) {
                    $query = Office::query()
                        ->where(
                            'is_head_office',
                            true,
                        )
                        ->where(
                            'is_active',
                            true,
                        );

                    if ($office !== null) {
                        $query->whereKeyNot(
                            $office->id,
                        );
                    }

                    if ($query->exists()) {
                        throw ValidationException::withMessages([
                            'is_head_office' => 'Only one active Head Office is allowed.',
                        ]);
                    }
                }

                $locked->fill([
                    'name' => $data['name'],
                    'address' => $data['address'],

                    'phone' => $data['phone']
                        ?? null,

                    'email' => $data['email']
                        ?? null,

                    'latitude' => $data['latitude']
                        ?? null,

                    'longitude' => $data['longitude']
                        ?? null,

                    'hours' => $data['hours']
                        ?? null,

                    'is_head_office' => (bool) $data[
                            'is_head_office'
                        ],

                    'sort_order' => $data['sort_order'],

                    'is_active' => (bool) $data[
                            'is_active'
                        ],
                ]);

                if ($locked->exists) {
                    $locked->lock_version++;
                }

                $locked->save();

                $this->audit->record(
                    action: $office === null
                    ? 'office.created'
                    : 'office.updated',
                    entity: $locked,
                    actor: $actor,
                );

                return $locked->refresh();
            },
            attempts: 3,
        );
    }
}
