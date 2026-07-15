<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\EntryPolicy;

it('authorizes entry views through the internal access ability', function (): void {
    $user = User::factory()->create();
    $suspendedUser = User::factory()->suspended()->create();
    $policy = resolve(EntryPolicy::class);

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user))->toBeTrue()
        ->and($policy->viewAny($suspendedUser))->toBeFalse()
        ->and($policy->view($suspendedUser))->toBeFalse();
});
