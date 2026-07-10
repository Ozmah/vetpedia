<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Ability;
use App\Enums\EntryStatus;
use App\Exceptions\InvalidEntryTransition;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class ApproveEntry
{
    public function __construct(private LogAuditEvent $logAuditEvent)
    {
        //
    }

    public function handle(User $actor, Entry $entry): Entry
    {
        Gate::forUser($actor)->authorize(Ability::ApproveEntries->value);

        return DB::transaction(function () use ($actor, $entry): Entry {
            $entry = Entry::query()->lockForUpdate()->findOrFail($entry->id);

            throw_if($entry->isArchived(), InvalidEntryTransition::class, 'Archived entries cannot be approved.');
            throw_if($entry->isApproved(), InvalidEntryTransition::class, 'Entry is already approved.');
            throw_unless($entry->status === EntryStatus::Documented, InvalidEntryTransition::class, 'Only documented entries can be approved.');

            $before = $this->auditSnapshot($entry);

            $entry->setAttribute('status', EntryStatus::VetApproved);
            $entry->setAttribute('approved_by', $actor->id);
            $entry->setAttribute('approved_at', now());
            $entry->save();

            $this->logAuditEvent->handle(
                actor: $actor,
                action: 'entry.approved',
                summary: 'Entry approved.',
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
            'approved_by' => $entry->approved_by,
            'approved_at' => $entry->approved_at?->toISOString(),
            'archived_at' => $entry->archived_at?->toISOString(),
        ];
    }
}
