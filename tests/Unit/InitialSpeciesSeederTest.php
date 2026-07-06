<?php

declare(strict_types=1);

use App\Models\Species;
use Database\Seeders\InitialSpeciesSeeder;

it('seeds the initial species idempotently', function (): void {
    $this->seed(InitialSpeciesSeeder::class);
    $this->seed(InitialSpeciesSeeder::class);

    expect(Species::query()->orderBy('slug')->pluck('name', 'slug')->all())->toBe([
        'gatos' => 'Gatos',
        'perros' => 'Perros',
    ]);
});

it('restores canonical initial species names by slug', function (): void {
    Species::factory()->create([
        'name' => 'Cats',
        'slug' => 'gatos',
    ]);

    $this->seed(InitialSpeciesSeeder::class);

    expect(Species::query()->where('slug', 'gatos')->value('name'))->toBe('Gatos');
});
