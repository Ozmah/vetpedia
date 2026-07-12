<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Ability;
use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Global authorization rules
        Gate::before(function (User $user, string $ability, mixed $arguments = null): ?bool {
            if ($user->isSuspended()) {
                return false;
            }

            $target = is_array($arguments) ? ($arguments[0] ?? null) : $arguments;
            $requestedRole = is_array($arguments) ? ($arguments[1] ?? null) : null;

            if ($target instanceof User && $this->isProtectedUserAbility($ability, $target, $requestedRole)) {
                return false;
            }

            if ($user->isSuperadmin()) {
                return true;
            }

            return null;
        });

        // Global access
        Gate::define(Ability::AccessInternal->value, fn (User $user): bool => true);

        // Entries
        Gate::define(Ability::CreateEntries->value, fn (User $user): bool => true);
        Gate::define(Ability::ManageUnapprovedEntries->value, fn (User $user): bool => true);
        Gate::define(Ability::ManageApprovedEntries->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::ApproveEntries->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::ArchiveEntries->value, fn (User $user): bool => $user->isAdmin());

        // Catalogs
        Gate::define(Ability::ManageSources->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::ManageSpecies->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::ManageCatalogs->value, fn (User $user): bool => $user->isAdmin());

        // Users
        Gate::define(Ability::ViewUsers->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::CreateUsers->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::CreateAdmins->value, [UserPolicy::class, 'createAdmin']);
        Gate::define(Ability::ManageUsers->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::ManageAdmins->value, fn (User $user): bool => false);
        Gate::define(Ability::SuspendUsers->value, [UserPolicy::class, 'suspend']);
        Gate::define(Ability::UnsuspendUsers->value, [UserPolicy::class, 'unsuspend']);

        // Operations
        Gate::define(Ability::RunSearchMaintenance->value, fn (User $user): bool => $user->isAdmin());
        Gate::define(Ability::RunBackups->value, fn (User $user): bool => false);

        // Local development
        Gate::define(Ability::ViewLocalDatabase->value, fn (User $user): bool => $user->isSuperadmin());
        Gate::define(Ability::ViewLocalUi->value, fn (User $user): bool => $user->isSuperadmin());
    }

    private function isProtectedUserAbility(string $ability, User $target, mixed $requestedRole): bool
    {
        if ($ability === 'forceDelete') {
            return true;
        }

        if (in_array($ability, ['delete', 'updateRole'], true) && $target->isSuperadmin()) {
            return true;
        }

        if ($ability === 'updateRole' && $requestedRole === UserRole::Superadmin) {
            return true;
        }

        return in_array($ability, ['suspend', 'unsuspend', Ability::SuspendUsers->value, Ability::UnsuspendUsers->value], true)
            && $target->role !== UserRole::User;
    }
}
