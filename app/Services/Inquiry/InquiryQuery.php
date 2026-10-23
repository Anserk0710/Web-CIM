<?php

// path: app/Services/Inquiry/InquiryQuery.php

namespace App\Services\Inquiry;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InquiryQuery
{
    /**
     * @return array{
     *   q: string,
     *   type: string,
     *   status: string,
     *   sort: string,
     *   direction: string
     * }
     */
    public function filters(
        Request $request,
    ): array {
        $type = InquiryType::tryFrom(
            mb_strtoupper(
                trim(
                    (string) $request->query(
                        'type',
                        '',
                    ),
                ),
            ),
        );

        $status = InquiryStatus::tryFrom(
            mb_strtoupper(
                trim(
                    (string) $request->query(
                        'status',
                        '',
                    ),
                ),
            ),
        );

        $requestedSort = trim(
            (string) $request->query(
                'sort',
                'created_at',
            ),
        );

        $sort = in_array(
            $requestedSort,
            [
                'created_at',
                'name',
                'status',
                'type',
            ],
            true,
        )
            ? $requestedSort
            : 'created_at';

        $direction = mb_strtolower(
            (string) $request->query(
                'direction',
                'desc',
            ),
        ) === 'asc'
            ? 'asc'
            : 'desc';

        return [
            'q' => mb_substr(
                trim(
                    (string) $request->query(
                        'q',
                        '',
                    ),
                ),
                0,
                100,
            ),

            'type' => $type?->value ?? '',

            'status' => $status?->value ?? '',

            'sort' => $sort,

            'direction' => $direction,
        ];
    }

    /**
     * @param array{
     *   q: string,
     *   type: string,
     *   status: string,
     *   sort: string,
     *   direction: string
     * } $filters
     */
    public function build(
        array $filters,
    ): Builder {
        $query = Inquiry::query()
            ->with([
                'product.publishedRevision:id,product_id,name',

                'ecatalogEntry.product.publishedRevision:id,product_id,name',
            ]);

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $query->where(
                function (
                    Builder $inner,
                ) use ($search): void {
                    $inner
                        ->where(
                            'name',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'organization',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'message',
                            'like',
                            '%'.$search.'%',
                        );
                },
            );
        }

        if ($filters['type'] !== '') {
            $query->where(
                'type',
                $filters['type'],
            );
        }

        if ($filters['status'] !== '') {
            $query->where(
                'status',
                $filters['status'],
            );
        }

        return $query
            ->orderBy(
                $filters['sort'],
                $filters['direction'],
            )
            ->orderByDesc('id');
    }
}
