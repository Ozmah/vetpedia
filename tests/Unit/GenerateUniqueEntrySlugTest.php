<?php

declare(strict_types=1);

use App\Actions\GenerateUniqueEntrySlug;
use App\Models\Entry;

it('generates a normalized slug from a title', function (): void {
    $slug = resolve(GenerateUniqueEntrySlug::class)->handle('Ácido Acetilsalicílico 100 mg');

    expect($slug)->toBe('acido-acetilsalicilico-100-mg');
});

it('falls back for titles without sluggable content', function (): void {
    $slug = resolve(GenerateUniqueEntrySlug::class)->handle('---');

    expect($slug)->toBe('entry');
});

it('adds a suffix when a slug already exists', function (): void {
    Entry::factory()->create([
        'title' => 'Ketamine',
        'slug' => 'ketamine',
    ]);

    $slug = resolve(GenerateUniqueEntrySlug::class)->handle('Ketamine');

    expect($slug)->toBe('ketamine-2');
});

it('ignores the current entry when regenerating its own slug', function (): void {
    $entry = Entry::factory()->create([
        'title' => 'Ketamine',
        'slug' => 'ketamine',
    ]);

    $slug = resolve(GenerateUniqueEntrySlug::class)->handle('Ketamine', $entry);

    expect($slug)->toBe('ketamine');
});
