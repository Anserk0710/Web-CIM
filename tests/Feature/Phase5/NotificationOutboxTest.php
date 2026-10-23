<?php // path: tests/Feature/Phase5/NotificationOutboxTest.php

namespace Tests\Feature\Phase5;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Jobs\SendOutboxNotification;
use App\Mail\OperationalNotificationMail;
use App\Models\Inquiry;
use App\Models\NotificationOutbox;
use App\Services\Notifications\NotificationOutboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class NotificationOutboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_inquiry_event_creates_one_outbox_record(): void
    {
        config([
            'notifications.inquiry_recipient' =>
                'ops@example.test',
        ]);

        $inquiry = $this->createInquiry();

        $service = app(NotificationOutboxService::class);

        $first = $service->inquirySubmitted($inquiry);
        $second = $service->inquirySubmitted($inquiry);

        $this->assertSame(
            $first->id,
            $second->id,
        );

        $this->assertDatabaseCount(
            'notification_outbox',
            1,
        );

        $this->assertDatabaseHas(
            'notification_outbox',
            [
                'event_key' =>
                    'inquiry.submitted:'.$inquiry->id,

                'entity_type' => 'Inquiry',

                'entity_id' => $inquiry->id,
            ],
        );
    }

    public function test_notification_is_sent_once_and_outbox_becomes_sent(): void
    {
        Mail::fake();

        config([
            'notifications.inquiry_recipient' =>
                'ops@example.test',
        ]);

        $inquiry = $this->createInquiry();

        $outbox = $this->createOutbox(
            $inquiry,
        );

        $job = new SendOutboxNotification(
            $outbox->id,
        );

        $job->handle();

        // Simulate a duplicate job delivery.
        $job->handle();

        $this->assertDatabaseHas(
            'notification_outbox',
            [
                'id' => $outbox->id,
                'status' => NotificationOutbox::SENT,
                'attempts' => 1,
            ],
        );

        Mail::assertSent(
            OperationalNotificationMail::class,
            1,
        );

        $this->assertDatabaseHas(
            'inquiries',
            [
                'id' => $inquiry->id,
                'email' => 'contact@example.test',
            ],
        );
    }

    public function test_smtp_failure_does_not_delete_inquiry_and_marks_outbox_failed_for_sync_queue(): void
    {
        config([
            'queue.default' => 'sync',

            'notifications.inquiry_recipient' =>
                'ops@example.test',
        ]);

        $inquiry = $this->createInquiry();

        $outbox = $this->createOutbox(
            $inquiry,
        );

        Mail::shouldReceive('to')
            ->once()
            ->with('ops@example.test')
            ->andThrow(
                new RuntimeException(
                    'SMTP unavailable',
                ),
            );

        try {
            (new SendOutboxNotification(
                $outbox->id,
            ))->handle();

            $this->fail(
                'The simulated SMTP error should be thrown.',
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'SMTP unavailable',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseHas(
            'inquiries',
            [
                'id' => $inquiry->id,
                'email' => 'contact@example.test',
            ],
        );

        $this->assertDatabaseHas(
            'notification_outbox',
            [
                'id' => $outbox->id,
                'status' => NotificationOutbox::FAILED,
                'attempts' => 1,
            ],
        );
    }

    public function test_outbox_does_not_store_message_or_applicant_email_in_payload(): void
    {
        $inquiry = $this->createInquiry();

        $outbox = app(
            NotificationOutboxService::class,
        )->inquirySubmitted($inquiry);

        $payload = json_encode(
            $outbox->payload,
            JSON_THROW_ON_ERROR,
        );

        $this->assertStringNotContainsString(
            'contact@example.test',
            $payload,
        );

        $this->assertStringNotContainsString(
            'Sensitive test message',
            $payload,
        );
    }

    public function test_database_queue_failure_can_be_retried_without_duplicate_outbox(): void
    {
        config([
            'queue.default' => 'database',

            'notifications.inquiry_recipient' =>
                'ops@example.test',
        ]);

        $inquiry = $this->createInquiry();

        $outbox = $this->createOutbox(
            $inquiry,
        );

        Mail::shouldReceive('to')
            ->once()
            ->with('ops@example.test')
            ->andThrow(
                new RuntimeException(
                    'Temporary SMTP failure',
                ),
            );

        try {
            (new SendOutboxNotification(
                $outbox->id,
            ))->handle();

            $this->fail(
                'The simulated SMTP error should be thrown.',
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Temporary SMTP failure',
                $exception->getMessage(),
            );
        }

        $outbox->refresh();

        $this->assertSame(
            NotificationOutbox::PENDING,
            $outbox->status,
        );

        $this->assertSame(
            1,
            $outbox->attempts,
        );

        // Force the scheduled retry to be due for this test.
        $outbox->forceFill([
            'next_attempt_at' => now(),
        ])->save();

        Mail::clearResolvedInstances();
        $this->app->forgetInstance('mail.manager');
        Mail::fake();

        (new SendOutboxNotification(
            $outbox->id,
        ))->handle();

        $this->assertDatabaseHas(
            'notification_outbox',
            [
                'id' => $outbox->id,
                'status' => NotificationOutbox::SENT,
                'attempts' => 2,
            ],
        );

        $this->assertDatabaseCount(
            'notification_outbox',
            1,
        );

        Mail::assertSent(
            OperationalNotificationMail::class,
            1,
        );
    }

    private function createInquiry(): Inquiry
    {
        return Inquiry::query()->create([
            'type' => InquiryType::General,

            'name' => 'Test Contact',

            'email' => 'contact@example.test',

            'phone' => '+628123456789',

            'organization' => 'Synthetic Test Company',

            'message' => 'Sensitive test message',

            'status' => InquiryStatus::New,

            'privacy_notice_version' => 'TEST-PRIVACY-V1',

            'lock_version' => 1,
        ]);
    }

    private function createOutbox(
        Inquiry $inquiry,
    ): NotificationOutbox {
        return NotificationOutbox::query()->create([
            'event_key' =>
                'inquiry.submitted:'.$inquiry->id,

            'event_type' => 'INQUIRY_SUBMITTED',

            'entity_type' => 'Inquiry',

            'entity_id' => $inquiry->id,

            'recipient' => null,

            'payload' => [
                'type' => InquiryType::General->value,
            ],

            'status' => NotificationOutbox::PENDING,

            'attempts' => 0,
        ]);
    }
}
