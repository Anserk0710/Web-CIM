<?php // path: config/notifications.php

return [
    'inquiry_recipient' => env('INQUIRY_NOTIFICATION_TO'),

    'career_application_recipient' => env(
        'CAREER_NOTIFICATION_TO',
    ),

    'max_attempts' => 3,

    'processing_timeout_minutes' => 15,

    'queued_timeout_minutes' => 10,

    'dispatch_batch_size' => 100,
];
