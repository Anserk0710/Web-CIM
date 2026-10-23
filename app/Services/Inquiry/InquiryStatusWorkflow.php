<?php

// path: app/Services/Inquiry/InquiryStatusWorkflow.php

namespace App\Services\Inquiry;

use App\Enums\InquiryStatus;

class InquiryStatusWorkflow
{
    /**
     * @return array<int, InquiryStatus>
     */
    public function allowedTargets(
        InquiryStatus $from,
    ): array {
        return match ($from) {
            InquiryStatus::New => [
                InquiryStatus::InProgress,
                InquiryStatus::Spam,
            ],

            InquiryStatus::InProgress => [
                InquiryStatus::Resolved,
                InquiryStatus::Spam,
            ],

            InquiryStatus::Resolved => [
                InquiryStatus::InProgress,
            ],

            InquiryStatus::Spam => [
                InquiryStatus::InProgress,
            ],
        };
    }

    public function canTransition(
        InquiryStatus $from,
        InquiryStatus $to,
    ): bool {
        return in_array(
            $to,
            $this->allowedTargets($from),
            true,
        );
    }

    public function requiresReason(
        InquiryStatus $from,
        InquiryStatus $to,
    ): bool {
        return in_array(
            $from,
            [
                InquiryStatus::Resolved,
                InquiryStatus::Spam,
            ],
            true,
        )
            && $to === InquiryStatus::InProgress;
    }

    public function label(
        InquiryStatus $status,
    ): string {
        return match ($status) {
            InquiryStatus::New => 'New',
            InquiryStatus::InProgress => 'In Progress',
            InquiryStatus::Resolved => 'Resolved',
            InquiryStatus::Spam => 'Spam',
        };
    }
}
