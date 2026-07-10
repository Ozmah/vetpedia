<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Ability;
use App\Exceptions\InvalidEntryTransition;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class DeleteArchivedEntry
{
    public function __construct(private LogAuditEvent $logAuditEvent)
    {
        //
    }

    public function handle(User $actor, Entry $entry, bool $confirmed): void
    {
        Gate::forUser($actor)->authorize(Ability::ArchiveEntries->value);

        throw_unless($confirmed, InvalidEntryTransition::class, 'Permanent entry deletion requires explicit confirmation.');

        DB::transaction(function () use ($actor, $entry): void {
            $entry = Entry::query()->lockForUpdate()->findOrFail($entry->id);

            throw_unless($entry->isArchived(), InvalidEntryTransition::class, 'Only archived entries can be permanently deleted.');

            $this->logAuditEvent->handle(
                actor: $actor,
                action: 'entry.deleted',
                summary: 'Archived entry permanently deleted.',
                subject: $entry,
                before: [
                    'status' => $entry->status->value,
                    'title' => $entry->title,
                    'slug' => $entry->slug,
                    'archived_at' => $entry->archived_at?->toISOString(),
                ],
            );

            $entry->delete();
        });
    }
}
