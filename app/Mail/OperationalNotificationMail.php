<?php // path: app/Mail/OperationalNotificationMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OperationalNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array<string, string|int> $details
     */
    public function __construct(
        public readonly int $outboxId,
        public readonly string $headline,
        public readonly array $details,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[CIM] '.$this->headline,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.operational-notification',
            with: [
                'outboxId' => $this->outboxId,
                'headline' => $this->headline,
                'details' => $this->details,
            ],
        );
    }

    public function attachments(): array
    {
        // Tidak pernah melampirkan CV atau file privat.
        return [];
    }
}
