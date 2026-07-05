<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\EntryAlias;
use Illuminate\Database\QueryException;

it('belongs to an entry', function (): void {
    $entry = Entry::factory()->create();

    $alias = EntryAlias::factory()->for($entry)->create();

    expect($alias->entry->is($entry))->toBeTrue();
});

it('normalizes factory alias names', function (): void {
    $alias = EntryAlias::factory()->name('  Ácido   Acetilsalicílico  ')->create();

    expect($alias->normalized_name)->toBe('acido acetilsalicilico');
});

it('prevents duplicate normalized aliases within the same entry', function (): void {
    $entry = Entry::factory()->create();

    EntryAlias::factory()->for($entry)->name('Ácido Acetilsalicílico')->create();

    expect(fn () => EntryAlias::factory()->for($entry)->name('acido acetilsalicilico')->create())
        ->toThrow(QueryException::class);
});

it('allows the same normalized alias across different entries', function (): void {
    EntryAlias::factory()->name('Ácido Acetilsalicílico')->create();
    EntryAlias::factory()->name('acido acetilsalicilico')->create();

    expect(EntryAlias::query()->where('normalized_name', 'acido acetilsalicilico')->count())->toBe(2);
});
