<?php

namespace App\Enums;

enum AuditResult: string
{
    case Success = 'SUCCESS';
    case Denied = 'DENIED';
    case Failure = 'FAILURE';
}
