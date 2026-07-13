<?php

declare(strict_types=1);

namespace App\Enums;

enum EntryTransition: string
{
    case Approve = 'approve';
    case Archive = 'archive';
    case Restore = 'restore';
    case Delete = 'delete';
}
