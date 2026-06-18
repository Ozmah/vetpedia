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
    public function handle(User $user, UserRole $role): void
    {
        throw_if($user->isSuperadmin() && $role !== UserRole::Superadmin, AuthorizationException::class, 'Superadmin users cannot be demoted.');

        $user->setAttribute('role', $role);
        $user->save();
    }
}
