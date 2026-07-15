<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EntryStatus;
use App\Enums\EntryType;
use App\Exceptions\InvalidEntryTransition;
use App\Models\Entry;
use App\Models\EntryAlias;
use App\Models\EntrySection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final readonly class UpdateEntry
{
    public function __construct(
        private GenerateUniqueEntrySlug $generateUniqueEntrySlug,
        private PrepareEntryAliases $prepareEntryAliases,
        private LogAuditEvent $logAuditEvent,
    ) {
        //
    }

    /**
     * @param  array{type?: EntryType|string, title?: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    public function handle(User $actor, Entry $entry, array $attributes): Entry
    {
        if (array_key_exists('sections', $attributes)) {
            $this->assertValidSections($attributes['sections']);
        }

        return DB::transaction(function () use ($actor, $entry, $attributes): Entry {
            $entry = Entry::query()->lockForUpdate()->findOrFail($entry->id);

            Gate::forUser($actor)->authorize('update', $entry);

            throw_if($entry->isArchived(), InvalidEntryTransition::class, 'Archived entries cannot be edited.');

            $aliases = array_key_exists('aliases', $attributes)
                ? $this->prepareEntryAliases->handle($attributes['aliases'])
                : null;

            $entry->load(['aliases', 'sections.sources', 'sources', 'species']);
            $before = $this->auditSnapshot($entry);

            if (array_key_exists('type', $attributes)) {
                $entry->setAttribute('type', $this->entryType($attributes['type']));
            }

            if (array_key_exists('title', $attributes)) {
                $title = $attributes['title'];
                $entry->setAttribute('title', $title);
                $entry->setAttribute('slug', $this->generateUniqueEntrySlug->handle($title, $entry));
            }

            if (array_key_exists('summary', $attributes)) {
                $entry->setAttribute('summary', $attributes['summary']);
            }

            if (array_key_exists('warnings', $attributes)) {
                $entry->setAttribute('warnings', $attributes['warnings']);
            }

            $entry->setAttribute('status', $this->statusFor($attributes, $entry));
            $entry->setAttribute('updated_by', $actor->id);
            $entry->save();

            if ($aliases !== null) {
                $entry->aliases()->delete();
                $this->syncAliases($entry, $aliases);
            }

            if (array_key_exists('species', $attributes)) {
                $this->syncSpecies($entry, $attributes['species']);
            }

            if (array_key_exists('sources', $attributes)) {
                $this->syncEntrySources($entry, $actor, $attributes['sources']);
            }

            if (array_key_exists('sections', $attributes)) {
                $this->syncSections($entry, $actor, $attributes['sections']);
            }

            $entry->refresh()->load(['aliases', 'sections.sources', 'sources', 'species']);

            $this->logAuditEvent->handle(
                actor: $actor,
                action: 'entry.updated',
                summary: 'Entry updated.',
                subject: $entry,
                before: $before,
                after: $this->auditSnapshot($entry),
            );

            return $entry;
        });
    }

    private function entryType(EntryType|string $type): EntryType
    {
        return $type instanceof EntryType ? $type : EntryType::from($type);
    }

    private function assertValidSections(mixed $sections): void
    {
        throw_unless(is_array($sections), InvalidArgumentException::class, 'Entry sections must be an array.');

        foreach ($sections as $index => $section) {
            throw_unless(is_array($section), InvalidArgumentException::class, sprintf('Entry section at index %s must be an array.', $index));
        }
    }

    /**
     * @param  array{type?: EntryType|string, title?: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    private function statusFor(array $attributes, Entry $entry): EntryStatus
    {
        if ($entry->isApproved()) {
            return EntryStatus::VetApproved;
        }

        return $this->hasSources($attributes, $entry) ? EntryStatus::Documented : EntryStatus::Draft;
    }

    /**
     * @param  array{type?: EntryType|string, title?: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    private function hasSources(array $attributes, Entry $entry): bool
    {
        if (array_key_exists('sources', $attributes) && $attributes['sources'] !== []) {
            return true;
        }

        if (! array_key_exists('sources', $attributes) && $entry->sources->isNotEmpty()) {
            return true;
        }

        if (array_key_exists('sections', $attributes)) {
            return array_any($attributes['sections'], fn (array $section): bool => ($section['sources'] ?? []) !== []);
        }

        return $entry->sections->contains(fn (EntrySection $section): bool => $section->sources->isNotEmpty());
    }

    /**
     * @param  list<array{name: string, normalized_name: string}>  $aliases
     */
    private function syncAliases(Entry $entry, array $aliases): void
    {
        foreach ($aliases as $alias) {
            $entryAlias = new EntryAlias();
            $entryAlias->setAttribute('name', $alias['name']);
            $entryAlias->setAttribute('normalized_name', $alias['normalized_name']);

            $entry->aliases()->save($entryAlias);
        }
    }

    /**
     * @param  array<int, string>  $species
     */
    private function syncSpecies(Entry $entry, array $species): void
    {
        $entry->species()->sync($species);
    }

    /**
     * @param  array<int, array{id: string, locator?: string|null, note?: string|null}>  $sources
     */
    private function syncEntrySources(Entry $entry, User $actor, array $sources): void
    {
        $entry->sources()->sync($this->sourceSyncPayload($sources, $actor));
    }

    /**
     * @param  array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>  $sections
     */
    private function syncSections(Entry $entry, User $actor, array $sections): void
    {
        $existingSections = $entry->sections()->get()->keyBy('key');
        $incomingKeys = collect($sections)->pluck('key')->all();

        $entry->sections()
            ->whereNotIn('key', $incomingKeys)
            ->delete();

        foreach ($sections as $index => $section) {
            /** @var EntrySection|null $entrySection */
            $entrySection = $existingSections->get($section['key']);
            $entrySection ??= new EntrySection();

            $entrySection->setAttribute('key', $section['key']);
            $entrySection->setAttribute('title', $section['title']);
            $entrySection->setAttribute('body', $section['body']);
            $entrySection->setAttribute('sort_order', $section['sort_order'] ?? $index + 1);

            $entry->sections()->save($entrySection);
            $entrySection->sources()->sync($this->sourceSyncPayload($section['sources'] ?? [], $actor));
        }
    }

    /**
     * @param  array<int, array{id: string, locator?: string|null, note?: string|null}>  $sources
     * @return array<string, array{locator: string|null, note: string|null, created_by: string}>
     */
    private function sourceSyncPayload(array $sources, User $actor): array
    {
        $payload = [];

        foreach ($sources as $source) {
            $payload[$source['id']] = [
                'locator' => $source['locator'] ?? null,
                'note' => $source['note'] ?? null,
                'created_by' => $actor->id,
            ];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function auditSnapshot(Entry $entry): array
    {
        return [
            'id' => $entry->id,
            'type' => $entry->type->value,
            'status' => $entry->status->value,
            'title' => $entry->title,
            'slug' => $entry->slug,
        ];
    }
}
