<?php

namespace App\Enums;

enum InquiryType: string
{
    case Product = 'PRODUCT';
    case ECatalog = 'ECATALOG';
    case TechnicalSupport = 'TECHNICAL_SUPPORT';
    case Partnership = 'PARTNERSHIP';
    case General = 'GENERAL';
}
