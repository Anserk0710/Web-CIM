<?php // path: tests/Feature/Phase5/ApplicationRetentionScheduleTest.php

namespace Tests\Feature\Phase5;

use App\Enums\ApplicationStatus;
use App\Services\Privacy\ApplicationRetentionSchedule;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class ApplicationRetentionScheduleTest extends TestCase
{
    public function test_rejected_application_is_due_six_calendar_months_after_decision(): void
    {
        $decisionAt = Carbon::parse(
            '2026-01-31 10:00:00',
            'Asia/Makassar',
        );

        $fields = (new ApplicationRetentionSchedule())
            ->fieldsForTransition(
                ApplicationStatus::Interview,
                ApplicationStatus::Rejected,
                $decisionAt,
            );

        $this->assertSame(
            '2026-07-31 10:00:00',
            $fields['retention_due_at']->format(
                'Y-m-d H:i:s',
            ),
        );

        $this->assertSame(
            $decisionAt->toDateTimeString(),
            $fields['selection_completed_at']->toDateTimeString(),
        );
    }

    public function test_reopening_rejected_application_clears_retention_deadline(): void
    {
        $fields = (new ApplicationRetentionSchedule())
            ->fieldsForTransition(
                ApplicationStatus::Rejected,
                ApplicationStatus::Reviewing,
                Carbon::parse(
                    '2026-10-23 10:00:00',
                    'Asia/Makassar',
                ),
            );

        $this->assertNull(
            $fields['selection_completed_at'],
        );

        $this->assertNull(
            $fields['retention_due_at'],
        );

        $this->assertNull(
            $fields['hr_handover_at'],
        );
    }

    public function test_hired_application_waits_for_handover_before_retention_countdown(): void
    {
        $fields = (new ApplicationRetentionSchedule())
            ->fieldsForTransition(
                ApplicationStatus::Interview,
                ApplicationStatus::Hired,
                Carbon::parse(
                    '2026-10-23 10:00:00',
                    'Asia/Makassar',
                ),
            );

        $this->assertNull(
            $fields['retention_due_at'],
        );

        $this->assertNull(
            $fields['hr_handover_at'],
        );
    }
}
