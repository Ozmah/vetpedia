<?php

declare(strict_types=1);

namespace App\Enums;

enum EntryStatus: string
{
    case Draft = 'draft';
    case Documented = 'documented';
    case VetApproved = 'vet_approved';
}
