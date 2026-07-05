<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Str;

final readonly class NormalizeEntryAliasName
{
    public function handle(string $name): string
    {
        return Str::squish(Str::lower(Str::ascii($name)));
    }
}
