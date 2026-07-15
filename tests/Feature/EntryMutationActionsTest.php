<?php

declare(strict_types=1);

use App\Actions\CreateEntry;
use App\Actions\UpdateEntry;
use App\Enums\EntryStatus;
use App\Enums\EntryType;
use App\Models\AuditEvent;
use App\Models\Entry;
use App\Models\Source;
use App\Models\Species;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('creates draft entries without sources and writes an audit event', function (): void {
    $actor = User::factory()->admin()->create();
    $species = Species::factory()->create();

    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => EntryType::Drug,
        'title' => 'Meloxicam',
        'summary' => 'NSAID analgesic.',
        'warnings' => null,
        'aliases' => ['Metacam'],
        'species' => [$species->id],
        'sections' => [
            [
                'key' => 'description',
                'title' => 'Description',
                'body' => 'Anti-inflammatory drug.',
                'sort_order' => 1,
            ],
        ],
        'sources' => [],
    ]);

    expect($entry->status)->toBe(EntryStatus::Draft)
        ->and($entry->createdBy->is($actor))->toBeTrue()
        ->and($entry->slug)->toBe('meloxicam')
        ->and($entry->aliases)->toHaveCount(1)
        ->and($entry->aliases->first()?->normalized_name)->toBe('metacam')
        ->and($entry->species)->toHaveCount(1)
        ->and($entry->sections)->toHaveCount(1);

    $auditEvent = AuditEvent::query()->sole();

    expect($auditEvent->actor_id)->toBe($actor->id)
        ->and($auditEvent->action)->toBe('entry.created')
        ->and($auditEvent->subject_id)->toBe($entry->id)
        ->and($auditEvent->after['status'])->toBe('draft');
});

it('creates documented entries when direct or section sources are present', function (): void {
    $actor = User::factory()->admin()->create();
    $source = Source::factory()->create(['created_by' => $actor->id]);

    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => 'procedure',
        'title' => 'Fluid therapy protocol',
        'summary' => null,
        'warnings' => null,
        'sources' => [
            ['id' => $source->id, 'locator' => 'p. 10', 'note' => 'Primary reference.'],
        ],
        'sections' => [],
    ]);

    expect($entry->status)->toBe(EntryStatus::Documented)
        ->and($entry->sources)->toHaveCount(1)
        ->and($entry->sources->first()?->citation->locator)->toBe('p. 10')
        ->and($entry->sources->first()?->citation->created_by)->toBe($actor->id);
});

it('rejects normalized alias collisions before creating an entry', function (): void {
    $actor = User::factory()->admin()->create();
    $errors = null;

    try {
        resolve(CreateEntry::class)->handle($actor, [
            'type' => EntryType::Drug,
            'title' => 'Acetylsalicylic acid',
            'aliases' => ['Ácido acetilsalicílico', 'Acido acetilsalicilico'],
        ]);
    } catch (ValidationException $validationException) {
        $errors = $validationException->errors();
    }

    expect($errors)->toHaveKey('aliases')
        ->and(Entry::query()->doesntExist())->toBeTrue();
});

it('updates entries by syncing relations status and audit snapshots', function (): void {
    $actor = User::factory()->admin()->create();
    $firstSpecies = Species::factory()->create();
    $secondSpecies = Species::factory()->create();
    $firstSource = Source::factory()->create(['created_by' => $actor->id]);
    $secondSource = Source::factory()->create(['created_by' => $actor->id]);

    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => EntryType::Drug,
        'title' => 'Old title',
        'summary' => 'Old summary.',
        'aliases' => ['Old alias'],
        'species' => [$firstSpecies->id],
        'sources' => [
            ['id' => $firstSource->id],
        ],
        'sections' => [
            [
                'key' => 'description',
                'title' => 'Description',
                'body' => 'Old body.',
                'sources' => [],
            ],
        ],
    ]);

    $updated = resolve(UpdateEntry::class)->handle($actor, $entry, [
        'type' => EntryType::Formula,
        'title' => 'New title',
        'summary' => 'New summary.',
        'warnings' => 'Use carefully.',
        'aliases' => ['New alias'],
        'species' => [$secondSpecies->id],
        'sources' => [],
        'sections' => [
            [
                'key' => 'formula',
                'title' => 'Formula',
                'body' => 'New body.',
                'sources' => [
                    ['id' => $secondSource->id, 'locator' => 'table 1'],
                ],
            ],
        ],
    ]);

    expect($updated->type)->toBe(EntryType::Formula)
        ->and($updated->status)->toBe(EntryStatus::Documented)
        ->and($updated->title)->toBe('New title')
        ->and($updated->slug)->toBe('new-title')
        ->and($updated->updated_by)->toBe($actor->id)
        ->and($updated->aliases)->toHaveCount(1)
        ->and($updated->aliases->first()?->name)->toBe('New alias')
        ->and($updated->species)->toHaveCount(1)
        ->and($updated->species->first()?->id)->toBe($secondSpecies->id)
        ->and($updated->sources)->toHaveCount(0)
        ->and($updated->sections)->toHaveCount(1)
        ->and($updated->sections->first()?->sources)->toHaveCount(1)
        ->and($updated->sections->first()?->sources->first()?->citation->locator)->toBe('table 1');

    $updateAuditEvent = AuditEvent::query()
        ->where('action', 'entry.updated')
        ->sole();

    expect($updateAuditEvent->before['title'])->toBe('Old title')
        ->and($updateAuditEvent->before['status'])->toBe('documented')
        ->and($updateAuditEvent->after['title'])->toBe('New title')
        ->and($updateAuditEvent->after['status'])->toBe('documented');
});

it('demotes updated entries to draft when all sources are removed', function (): void {
    $actor = User::factory()->admin()->create();
    $source = Source::factory()->create(['created_by' => $actor->id]);

    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => EntryType::Drug,
        'title' => 'Cerenia',
        'sources' => [
            ['id' => $source->id],
        ],
        'sections' => [],
    ]);

    $updated = resolve(UpdateEntry::class)->handle($actor, $entry, [
        'sources' => [],
        'sections' => [],
    ]);

    expect($updated->status)->toBe(EntryStatus::Draft)
        ->and($updated->sources)->toHaveCount(0)
        ->and($updated->sections)->toHaveCount(0);
});

it('documents an entry when an update adds direct sources', function (): void {
    $actor = User::factory()->admin()->create();
    $source = Source::factory()->create(['created_by' => $actor->id]);
    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => EntryType::Drug,
        'title' => 'Gabapentin',
    ]);

    $updated = resolve(UpdateEntry::class)->handle($actor, $entry, [
        'sources' => [
            ['id' => $source->id],
        ],
    ]);

    expect($updated->status)->toBe(EntryStatus::Documented)
        ->and($updated->sources)->toHaveCount(1);
});

it('retains documented status when an update omits existing direct sources', function (): void {
    $actor = User::factory()->admin()->create();
    $source = Source::factory()->create(['created_by' => $actor->id]);
    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => EntryType::Drug,
        'title' => 'Prednisolone',
        'sources' => [
            ['id' => $source->id],
        ],
    ]);

    $updated = resolve(UpdateEntry::class)->handle($actor, $entry, [
        'summary' => 'Updated summary.',
    ]);

    expect($updated->status)->toBe(EntryStatus::Documented)
        ->and($updated->sources)->toHaveCount(1);
});

it('retains documented status when an update omits existing section sources', function (): void {
    $actor = User::factory()->admin()->create();
    $source = Source::factory()->create(['created_by' => $actor->id]);
    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => EntryType::Drug,
        'title' => 'Furosemide',
        'sections' => [
            [
                'key' => 'description',
                'title' => 'Description',
                'body' => 'Loop diuretic.',
                'sources' => [
                    ['id' => $source->id],
                ],
            ],
        ],
    ]);

    $updated = resolve(UpdateEntry::class)->handle($actor, $entry, [
        'warnings' => 'Monitor hydration.',
    ]);

    expect($entry->status)->toBe(EntryStatus::Documented)
        ->and($updated->status)->toBe(EntryStatus::Documented)
        ->and($updated->sources)->toHaveCount(0)
        ->and($updated->sections->sole()->sources)->toHaveCount(1);
});

it('rejects normalized alias collisions before changing an entry', function (): void {
    $actor = User::factory()->admin()->create();
    $entry = resolve(CreateEntry::class)->handle($actor, [
        'type' => EntryType::Drug,
        'title' => 'Original title',
        'aliases' => ['Original alias'],
    ]);

    expect(fn () => resolve(UpdateEntry::class)->handle($actor, $entry, [
        'title' => 'Changed title',
        'aliases' => ['A  B', 'a b'],
    ]))->toThrow(ValidationException::class);

    $entry->refresh()->load('aliases');

    expect($entry->title)->toBe('Original title')
        ->and($entry->aliases)->toHaveCount(1)
        ->and($entry->aliases->sole()->name)->toBe('Original alias');
});

it('rejects malformed sections before changing an entry', function (mixed $sections, string $message): void {
    $actor = User::factory()->admin()->create();
    $entry = Entry::factory()->create([
        'created_by' => $actor->id,
        'title' => 'Original title',
    ]);

    expect(fn () => resolve(UpdateEntry::class)->handle($actor, $entry, [
        'title' => 'Changed title',
        'sections' => $sections,
    ]))->toThrow(InvalidArgumentException::class, $message);

    expect($entry->refresh()->title)->toBe('Original title');
})->with([
    'sections is not an array' => ['invalid', 'Entry sections must be an array.'],
    'section item is not an array' => [['invalid'], 'Entry section at index 0 must be an array.'],
]);
