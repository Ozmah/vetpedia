<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\EntrySection;
use Illuminate\Database\QueryException;

it('belongs to an entry', function (): void {
    $entry = Entry::factory()->create();

    $section = EntrySection::factory()->for($entry)->create();

    expect($section->entry->is($entry))->toBeTrue();
});

it('preserves section order through the entry relationship', function (): void {
    $entry = Entry::factory()->create();

    EntrySection::factory()->for($entry)->key('steps')->sortOrder(20)->create();
    EntrySection::factory()->for($entry)->key('dosage')->sortOrder(10)->create();

    expect($entry->sections()->pluck('key')->all())->toBe(['dosage', 'steps']);
});

it('orders equal-position sections by id through the ordered scope', function (): void {
    $entry = Entry::factory()->create();
    $firstId = '00000000-0000-4000-8000-000000000001';
    $secondId = '00000000-0000-4000-8000-000000000002';

    EntrySection::factory()->for($entry)->create([
        'id' => $secondId,
        'sort_order' => 10,
    ]);
    EntrySection::factory()->for($entry)->create([
        'id' => $firstId,
        'sort_order' => 10,
    ]);

    expect(EntrySection::query()->ordered()->pluck('id')->all())->toBe([
        $firstId,
        $secondId,
    ]);
});

it('prevents duplicate section keys within the same entry', function (): void {
    $entry = Entry::factory()->create();

    EntrySection::factory()->for($entry)->key('dosage')->create();

    expect(fn () => EntrySection::factory()->for($entry)->key('dosage')->create())
        ->toThrow(QueryException::class);
});

it('allows the same section key across different entries', function (): void {
    EntrySection::factory()->key('dosage')->create();
    EntrySection::factory()->key('dosage')->create();

    expect(EntrySection::query()->where('key', 'dosage')->count())->toBe(2);
});
