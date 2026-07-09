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

final readonly class UpdateEntry
{
    public function __construct(
        private GenerateUniqueEntrySlug $generateUniqueEntrySlug,
        private NormalizeEntryAliasName $normalizeEntryAliasName,
        private LogAuditEvent $logAuditEvent,
    ) {
        //
    }

    /**
     * @param  array{type?: EntryType|string, title?: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    public function handle(User $actor, Entry $entry, array $attributes): Entry
    {
        return DB::transaction(function () use ($actor, $entry, $attributes): Entry {
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

            if (array_key_exists('aliases', $attributes)) {
                $entry->aliases()->delete();
                $this->syncAliases($entry, $attributes['aliases']);
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

    /**
     * @param  array{type?: EntryType|string, title?: string, summary?: string|null, warnings?: string|null, aliases?: array<int, array{name: string}|string>, species?: array<int, string>, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>, sections?: array<int, array{key: string, title: string, body: string, sort_order?: int, sources?: array<int, array{id: string, locator?: string|null, note?: string|null}>}>}  $attributes
     */
    private function statusFor(array $attributes, Entry $entry): EntryStatus
    {
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
            return array_any($attributes['sections'], fn ($section): bool => ($section['sources'] ?? []) !== []);
        }

        return $entry->sections->contains(fn (EntrySection $section): bool => $section->sources->isNotEmpty());
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
