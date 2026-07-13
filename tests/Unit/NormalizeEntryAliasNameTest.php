<?php

declare(strict_types=1);

use App\Actions\NormalizeEntryAliasName;

it('normalizes aliases for search and deduplication', function (): void {
    $normalized = resolve(NormalizeEntryAliasName::class)->handle('  Ácido   Acetilsalicílico  ');

    expect($normalized)->toBe('acido acetilsalicilico');
});

it('preserves meaningful punctuation while normalizing casing accents and spacing', function (): void {
    $normalized = resolve(NormalizeEntryAliasName::class)->handle('  A.S.A. / ÁSA  ');

    expect($normalized)->toBe('a.s.a. / asa');
});
