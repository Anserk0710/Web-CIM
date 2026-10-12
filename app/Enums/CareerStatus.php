<?php

namespace App\Enums;

enum CareerStatus: string
{
    case Draft = 'DRAFT';
    case Open = 'OPEN';
    case Closed = 'CLOSED';
    case Archived = 'ARCHIVED';
}
