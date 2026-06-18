<?php

declare(strict_types=1);

use App\Actions\DeleteUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

it('may delete a user', function (): void {
    $user = User::factory()->create();

    $action = resolve(DeleteUser::class);

    $action->handle($user);

    expect(User::query()->find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id)?->trashed())->toBeTrue();
});

it('cannot delete a superadmin user', function (): void {
    $user = User::factory()->superadmin()->create();

    $action = resolve(DeleteUser::class);

    expect(fn () => $action->handle($user))->toThrow(AuthorizationException::class);

    expect($user->fresh())->not->toBeNull();
});
