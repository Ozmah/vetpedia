<?php

declare(strict_types=1);

namespace App\Enums;

enum EntryType: string
{
    case Drug = 'drug';
    case Procedure = 'procedure';
    case Maneuver = 'maneuver';
    case Protocol = 'protocol';
    case Toxicity = 'toxicity';
    case Formula = 'formula';
}
