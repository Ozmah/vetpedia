<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->role === UserRole::User;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function createAdmin(User $user): bool
    {
        return $user->isSuperadmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->role === UserRole::User;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->role === UserRole::User;
    }

    public function updateRole(User $user, User $model, UserRole $role): bool
    {
        if ($model->isSuperadmin() || $role === UserRole::Superadmin) {
            return false;
        }

        return $user->isSuperadmin();
    }

    public function suspend(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->role === UserRole::User;
    }

    public function unsuspend(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->role === UserRole::User;
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
