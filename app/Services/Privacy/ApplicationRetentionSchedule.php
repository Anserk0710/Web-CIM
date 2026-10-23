<?php // path: app/Services/Privacy/ApplicationRetentionSchedule.php

namespace App\Services\Privacy;

use App\Enums\ApplicationStatus;
use Carbon\Carbon;

class ApplicationRetentionSchedule
{
    /**
     * @return array<string, mixed>
     */
    public function fieldsForTransition(
        ApplicationStatus $from,
        ApplicationStatus $to,
        Carbon $at,
    ): array {
        if ($to === ApplicationStatus::Rejected) {
            return [
                'selection_completed_at' => $at->copy(),
                'retention_due_at' => $at
                    ->copy()
                    ->addMonthsNoOverflow(6),
                'hr_handover_at' => null,
            ];
        }

        if ($to === ApplicationStatus::Hired) {
            return [
                'selection_completed_at' => $at->copy(),
                'retention_due_at' => null,
                'hr_handover_at' => null,
            ];
        }

        if (
            $from === ApplicationStatus::Rejected
            && $to === ApplicationStatus::Reviewing
        ) {
            return [
                'selection_completed_at' => null,
                'retention_due_at' => null,
                'hr_handover_at' => null,
            ];
        }

        return [];
    }
}
