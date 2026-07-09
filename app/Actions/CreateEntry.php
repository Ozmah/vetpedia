<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EntryStatus;
use App\Enums\EntryType;
use App\Models\Entry;
use App\Models\EntryAlias;
use App\Models\EntrySection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateEntry
{
    public function __construct(
        private GenerateUniqueEntrySlug $generateUniqueEntrySlug,
        private NormalizeEntryAliasName $normalizeEntryAliasName,
        private LogAuditEvent $logAuditEvent,
    ) {
        //
    }

    /**
     * @param  array{type: EntryType|string, title: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    public function handle(User $actor, array $attributes): Entry
    {
        return DB::transaction(function () use ($actor, $attributes): Entry {
            $entry = new Entry();
            $entry->setAttribute('type', $this->entryType($attributes['type']));
            $entry->setAttribute('status', $this->statusFor($attributes));
            $entry->setAttribute('title', $attributes['title']);
            $entry->setAttribute('slug', $this->generateUniqueEntrySlug->handle((string) $attributes['title']));
            $entry->setAttribute('summary', $attributes['summary'] ?? null);
            $entry->setAttribute('warnings', $attributes['warnings'] ?? null);
            $entry->setAttribute('created_by', $actor->id);
            $entry->save();

            $this->syncAliases($entry, $attributes['aliases'] ?? []);
            $this->syncSpecies($entry, $attributes['species'] ?? []);
            $this->syncEntrySources($entry, $actor, $attributes['sources'] ?? []);
            $this->syncSections($entry, $actor, $attributes['sections'] ?? []);

            $entry->refresh()->load(['aliases', 'sections.sources', 'sources', 'species']);

            $this->logAuditEvent->handle(
                actor: $actor,
                action: 'entry.created',
                summary: 'Entry created.',
                subject: $entry,
                after: $this->auditSnapshot($entry),
            );

            return $entry;
        });
    }

    private function entryType(EntryType|string $type): EntryType
    {
        return $type instanceof EntryType ? $type : EntryType::from($type);
    }

    /**
     * @param  array{type: EntryType|string, title: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    private function statusFor(array $attributes): EntryStatus
    {
        return $this->hasSources($attributes) ? EntryStatus::Documented : EntryStatus::Draft;
    }

    /**
     * @param  array{type: EntryType|string, title: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    private function hasSources(array $attributes): bool
    {
        if (($attributes['sources'] ?? []) !== []) {
            return true;
        }

        foreach (($attributes['sections'] ?? []) as $section) {
            if (($section['sources'] ?? []) !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array{name: string}|string>  $aliases
     */
    private function syncAliases(Entry $entry, array $aliases): void
    {
        foreach ($aliases as $alias) {
            $name = is_string($alias) ? $alias : $alias['name'];

            $entryAlias = new EntryAlias();
            $entryAlias->setAttribute('name', $name);
            $entryAlias->setAttribute('normalized_name', $this->normalizeEntryAliasName->handle($name));

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
        foreach ($sections as $index => $section) {
            $entrySection = new EntrySection();
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
