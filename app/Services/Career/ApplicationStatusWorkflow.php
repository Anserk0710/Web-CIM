<?php

namespace App\Services\Career;

use App\Enums\ApplicationStatus;

class ApplicationStatusWorkflow
{
    /**
     * @return array<int, ApplicationStatus>
     */
    public function allowedTargets(
        ApplicationStatus $from,
    ): array {
        return match ($from) {
            ApplicationStatus::New => [
                ApplicationStatus::Reviewing,
                ApplicationStatus::Rejected,
            ],

            ApplicationStatus::Reviewing => [
                ApplicationStatus::New,
                ApplicationStatus::Shortlisted,
                ApplicationStatus::Rejected,
            ],

            ApplicationStatus::Shortlisted => [
                ApplicationStatus::Reviewing,
                ApplicationStatus::Interview,
                ApplicationStatus::Rejected,
            ],

            ApplicationStatus::Interview => [
                ApplicationStatus::Shortlisted,
                ApplicationStatus::Hired,
                ApplicationStatus::Rejected,
            ],

            ApplicationStatus::Rejected => [
                ApplicationStatus::Reviewing,
            ],

            ApplicationStatus::Hired => [],
        };
    }

    public function canTransition(
        ApplicationStatus $from,
        ApplicationStatus $to,
    ): bool {
        return in_array(
            $to,
            $this->allowedTargets($from),
            true,
        );
    }

    public function requiresReason(
        ApplicationStatus $from,
        ApplicationStatus $to,
    ): bool {
        if ($to === ApplicationStatus::Rejected) {
            return true;
        }

        if (
            $from === ApplicationStatus::Rejected
            && $to === ApplicationStatus::Reviewing
        ) {
            return true;
        }

        $order = [
            ApplicationStatus::New->value => 0,
            ApplicationStatus::Reviewing->value => 1,
            ApplicationStatus::Shortlisted->value => 2,
            ApplicationStatus::Interview->value => 3,
        ];

        return isset(
            $order[$from->value],
            $order[$to->value],
        )
            && $order[$to->value]
            < $order[$from->value];
    }

    public function label(
        ApplicationStatus $status,
    ): string {
        return match ($status) {
            ApplicationStatus::New => 'New',
            ApplicationStatus::Reviewing => 'Reviewing',
            ApplicationStatus::Shortlisted => 'Shortlisted',
            ApplicationStatus::Interview => 'Interview',
            ApplicationStatus::Rejected => 'Rejected',
            ApplicationStatus::Hired => 'Hired',
        };
    }
}
