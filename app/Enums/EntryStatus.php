<?php

declare(strict_types=1);

namespace App\Enums;

enum EntryStatus: string
{
    case Raw = 'raw';
    case Parsed = 'parsed';
    case NeedsReview = 'needs_review';
    case SoftApproved = 'soft_approved';
    case VetApproved = 'vet_approved';
    case Rejected = 'rejected';
}
