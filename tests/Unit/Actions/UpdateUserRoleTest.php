<?php

declare(strict_types=1);

use App\Actions\UpdateUserRole;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

it('may update a user role', function (): void {
    $actor = User::factory()->admin()->create();
    $user = User::factory()->create();

    $action = resolve(UpdateUserRole::class);

    $action->handle($actor, $user, UserRole::Admin);

    expect($user->refresh()->role)->toBe(UserRole::Admin);
});

it('allows superadmin users to update non-superadmin user roles', function (): void {
    $actor = User::factory()->superadmin()->create();
    $user = User::factory()->create();

    $action = resolve(UpdateUserRole::class);

    $action->handle($actor, $user, UserRole::Admin);

    expect($user->refresh()->role)->toBe(UserRole::Admin);
});

it('does not allow non-admin users to update roles', function (): void {
    $actor = User::factory()->create();
    $user = User::factory()->create();

    $action = resolve(UpdateUserRole::class);

    expect(fn () => $action->handle($actor, $user, UserRole::Admin))->toThrow(AuthorizationException::class);

    expect($user->refresh()->role)->toBe(UserRole::User);
});

it('cannot change a superadmin user role', function (): void {
    $actor = User::factory()->superadmin()->create();
    $user = User::factory()->superadmin()->create();

    $action = resolve(UpdateUserRole::class);

    expect(fn () => $action->handle($actor, $user, UserRole::Admin))->toThrow(AuthorizationException::class);

    expect($user->refresh()->role)->toBe(UserRole::Superadmin);
});
