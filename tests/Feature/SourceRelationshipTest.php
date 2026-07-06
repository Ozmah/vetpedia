<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\EntrySection;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\QueryException;

it('allows entries to cite multiple sources with citation metadata', function (): void {
    $entry = Entry::factory()->create();
    $creator = User::factory()->admin()->create();
    $firstSource = Source::factory()->book()->create();
    $secondSource = Source::factory()->website()->create();

    $entry->sources()->attach($firstSource->id, [
        'locator' => 'p. 245',
        'note' => 'Dose reference.',
        'created_by' => $creator->id,
    ]);
    $entry->sources()->attach($secondSource->id, [
        'locator' => 'tabla 4.3',
        'note' => null,
        'created_by' => $creator->id,
    ]);

    $sources = $entry->sources()->orderBy('title')->get();

    expect($sources)->toHaveCount(2)
        ->and($sources->pluck('citation.locator')->all())->toContain('p. 245', 'tabla 4.3')
        ->and($sources->firstWhere('id', $firstSource->id)?->citation->note)->toBe('Dose reference.')
        ->and($sources->firstWhere('id', $firstSource->id)?->citation->created_by)->toBe($creator->id);
});

it('allows sections to cite granular sources with citation metadata', function (): void {
    $section = EntrySection::factory()->create();
    $source = Source::factory()->book()->create();
    $creator = User::factory()->admin()->create();

    $section->sources()->attach($source->id, [
        'locator' => 'cap. 12',
        'note' => 'Section-specific citation.',
        'created_by' => $creator->id,
    ]);

    $citation = $section->sources()->firstOrFail()->citation;

    expect($citation->locator)->toBe('cap. 12')
        ->and($citation->note)->toBe('Section-specific citation.')
        ->and($citation->created_by)->toBe($creator->id);
});

it('exposes inverse source relationships for admin and public rendering', function (): void {
    $entry = Entry::factory()->create();
    $section = EntrySection::factory()->for($entry)->create();
    $source = Source::factory()->book()->create();
    $creator = User::factory()->admin()->create();

    $entry->sources()->attach($source->id, [
        'locator' => 'pp. 245-247',
        'note' => null,
        'created_by' => $creator->id,
    ]);
    $section->sources()->attach($source->id, [
        'locator' => 'tabla 4.3',
        'note' => null,
        'created_by' => $creator->id,
    ]);

    expect($source->entries()->firstOrFail()->is($entry))->toBeTrue()
        ->and($source->entrySections()->firstOrFail()->is($section))->toBeTrue();
});

it('prevents duplicate source citations for the same entry or section', function (): void {
    $entry = Entry::factory()->create();
    $section = EntrySection::factory()->for($entry)->create();
    $source = Source::factory()->book()->create();
    $creator = User::factory()->admin()->create();
    $citation = [
        'locator' => 'p. 245',
        'note' => null,
        'created_by' => $creator->id,
    ];

    $entry->sources()->attach($source->id, $citation);
    $section->sources()->attach($source->id, $citation);

    expect(fn () => $entry->sources()->attach($source->id, $citation))->toThrow(QueryException::class)
        ->and(fn () => $section->sources()->attach($source->id, $citation))->toThrow(QueryException::class);
});
