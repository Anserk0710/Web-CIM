<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'NEW';
    case Reviewing = 'REVIEWING';
    case Shortlisted = 'SHORTLISTED';
    case Interview = 'INTERVIEW';
    case Rejected = 'REJECTED';
    case Hired = 'HIRED';
}
