<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\Species;
use Illuminate\Database\QueryException;

it('enforces unique species names and slugs', function (): void {
    Species::factory()->create([
        'name' => 'Gatos',
        'slug' => 'gatos',
    ]);

    expect(fn () => Species::factory()->create([
        'name' => 'Gatos',
        'slug' => 'felinos',
    ]))->toThrow(QueryException::class)
        ->and(fn () => Species::factory()->create([
            'name' => 'Felinos',
            'slug' => 'gatos',
        ]))->toThrow(QueryException::class);
});

it('allows entries to target multiple species', function (): void {
    $entry = Entry::factory()->create();
    $cat = Species::factory()->named('Gatos')->create();
    $dog = Species::factory()->named('Perros')->create();

    $entry->species()->attach([$cat->id, $dog->id]);

    expect($entry->species()->pluck('slug')->all())->toBe(['gatos', 'perros']);
});

it('allows species to belong to multiple entries', function (): void {
    $species = Species::factory()->named('Gatos')->create();
    $firstEntry = Entry::factory()->create();
    $secondEntry = Entry::factory()->create();

    $species->entries()->attach([$firstEntry->id, $secondEntry->id]);

    expect($species->entries()->count())->toBe(2);
});
