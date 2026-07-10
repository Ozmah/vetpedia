<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Ability;
use App\Models\Entry;
use App\Models\User;

final class EntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Ability::AccessInternal->value);
    }

    public function view(User $user): bool
    {
        return $user->can(Ability::AccessInternal->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Ability::CreateEntries->value);
    }

    public function update(User $user, Entry $entry): bool
    {
        $ability = $entry->isApproved()
            ? Ability::ManageApprovedEntries
            : Ability::ManageUnapprovedEntries;

        return $user->can($ability->value);
    }

    public function approve(User $user): bool
    {
        return $user->can(Ability::ApproveEntries->value);
    }

    public function archive(User $user): bool
    {
        return $user->can(Ability::ArchiveEntries->value);
    }

    public function restore(User $user): bool
    {
        return $user->can(Ability::ArchiveEntries->value);
    }

    public function forceDelete(User $user): bool
    {
        return $user->can(Ability::ArchiveEntries->value);
    }
}
