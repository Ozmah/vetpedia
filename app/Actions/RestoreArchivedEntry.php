<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Ability;
use App\Exceptions\InvalidEntryTransition;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class RestoreArchivedEntry
{
    public function __construct(private LogAuditEvent $logAuditEvent)
    {
        //
    }

    public function handle(User $actor, Entry $entry): Entry
    {
        Gate::forUser($actor)->authorize(Ability::ArchiveEntries->value);

        return DB::transaction(function () use ($actor, $entry): Entry {
            $entry = Entry::query()->lockForUpdate()->findOrFail($entry->id);

            throw_unless($entry->isArchived(), InvalidEntryTransition::class, 'Only archived entries can be restored.');

            $before = $this->auditSnapshot($entry);

            $entry->setAttribute('archived_at', null);
            $entry->save();

            $this->logAuditEvent->handle(
                actor: $actor,
                action: 'entry.restored',
                summary: 'Archived entry restored.',
                subject: $entry,
                before: $before,
                after: $this->auditSnapshot($entry),
            );

            return $entry;
        });
    }

    /** @return array<string, mixed> */
    private function auditSnapshot(Entry $entry): array
    {
        return [
            'status' => $entry->status->value,
            'archived_at' => $entry->archived_at?->toISOString(),
        ];
    }
}
