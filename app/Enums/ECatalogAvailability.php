<?php

namespace App\Enums;

enum ECatalogAvailability: string
{
    case Available = 'AVAILABLE';
    case Unavailable = 'UNAVAILABLE';
    case ContactUs = 'CONTACT_US';
}
