<?php

declare(strict_types=1);

namespace App\Enums;

enum SourceType: string
{
    case Book = 'book';
    case Article = 'article';
    case Guideline = 'guideline';
    case Website = 'website';
    case InternalNote = 'internal_note';
    case Other = 'other';
}
