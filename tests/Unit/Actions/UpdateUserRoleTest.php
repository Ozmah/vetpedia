<?php

declare(strict_types=1);

use App\Actions\UpdateUserRole;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

it('may update a user role', function (): void {
    $user = User::factory()->create();

    $action = resolve(UpdateUserRole::class);

    $action->handle($user, UserRole::Admin);

    expect($user->refresh()->role)->toBe(UserRole::Admin);
});

it('cannot demote a superadmin user', function (): void {
    $user = User::factory()->superadmin()->create();

    $action = resolve(UpdateUserRole::class);

    expect(fn () => $action->handle($user, UserRole::Admin))->toThrow(AuthorizationException::class);

    expect($user->refresh()->role)->toBe(UserRole::Superadmin);
});
