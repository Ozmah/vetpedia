<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final readonly class DeleteUser
{
    /**
     * @throws AuthorizationException
     */
    public function handle(User $user): void
    {
        throw_if($user->isSuperadmin(), AuthorizationException::class, 'Superadmin users cannot be deleted.');

        $user->delete();
    }
}
