<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

final readonly class UpdateUserRole
{
    /**
     * @throws AuthorizationException
     */
    public function handle(User $actor, User $user, UserRole $role): void
    {
        Gate::forUser($actor)->authorize('updateRole', [$user, $role]);

        $user->setAttribute('role', $role);
        $user->save();

        // TODO: Write an audit log entry when audit logging infrastructure is available.
    }
}
