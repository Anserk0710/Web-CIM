<?php

namespace App\Enums;

enum ActivityBlockType: string
{
    case Paragraph = 'paragraph';
    case Heading = 'heading';
    case Image = 'image';
    case Gallery = 'gallery';
    case Quote = 'quote';
    case Video = 'video';
    case Cta = 'cta';
    case RelatedProduct = 'related_product';
    case Divider = 'divider';
}
