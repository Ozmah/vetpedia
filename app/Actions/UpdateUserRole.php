<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final readonly class UpdateUserRole
{
    /**
     * @throws AuthorizationException
     */
    public function handle(User $actor, User $user, UserRole $role): void
    {
        throw_unless($actor->isAdmin() || $actor->isSuperadmin(), AuthorizationException::class, 'Only administrators can update user roles.');
        throw_if($user->isSuperadmin(), AuthorizationException::class, 'Superadmin users cannot have their role changed.');

        $user->setAttribute('role', $role);
        $user->save();

        // TODO: Write an audit log entry when audit logging infrastructure is available.
    }
}
