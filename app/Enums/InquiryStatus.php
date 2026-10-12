<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case New = 'NEW';
    case InProgress = 'IN_PROGRESS';
    case Resolved = 'RESOLVED';
    case Spam = 'SPAM';
}
