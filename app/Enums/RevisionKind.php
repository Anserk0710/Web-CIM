<?php

namespace App\Enums;

enum RevisionKind: string
{
    case Working = 'WORKING';
    case Published = 'PUBLISHED';
}
