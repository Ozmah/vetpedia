<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'two_factor_confirmed_at',
            'created_at',
            'updated_at',
            'role',
            'suspended_at',
            'deleted_at',
        ]);
});

it('casts role values to the user role enum', function (): void {
    $user = User::factory()->admin()->create()->refresh();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->isAdmin())->toBeTrue()
        ->and($user->isSuperadmin())->toBeFalse();
});

it('may query users by role', function (): void {
    User::factory()->create();
    $admin = User::factory()->admin()->create();
    $superadmin = User::factory()->superadmin()->create();

    expect(User::query()->role(UserRole::Admin)->pluck('id')->all())->toBe([$admin->id])
        ->and(User::query()->admins()->pluck('id')->all())->toEqualCanonicalizing([$admin->id, $superadmin->id]);
});

it('may query users that are not suspended', function (): void {
    $active = User::factory()->create();
    User::factory()->suspended()->create();

    expect(User::query()->notSuspended()->pluck('id')->all())->toBe([$active->id]);
});

it('excludes soft deleted users from normal queries', function (): void {
    $user = User::factory()->create();

    $user->delete();

    expect(User::query()->find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id)?->trashed())->toBeTrue();
});
