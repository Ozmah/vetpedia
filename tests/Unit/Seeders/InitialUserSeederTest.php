<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\InitialUserSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    config([
        'vetpedia.seed_users.superadmin' => [
            'name' => 'Gabriel Alegria',
            'email' => 'gabriel@example.test',
            'password' => 'local-gabriel-password',
        ],
        'vetpedia.seed_users.admin' => [
            'name' => 'Carlos Admin',
            'email' => 'carlos@example.test',
            'password' => 'local-carlos-password',
        ],
        'vetpedia.seed_users.dummy_password' => 'local-dummy-password',
    ]);
});

it('seeds the initial real users with their configured roles', function (): void {
    $this->seed(InitialUserSeeder::class);

    $gabriel = User::query()->where('email', 'gabriel@example.test')->sole();
    $carlos = User::query()->where('email', 'carlos@example.test')->sole();

    expect($gabriel->name)->toBe('Gabriel Alegria')
        ->and($gabriel->role)->toBe(UserRole::Superadmin)
        ->and($gabriel->email_verified_at)->not->toBeNull()
        ->and(Hash::check('local-gabriel-password', $gabriel->password))->toBeTrue()
        ->and($carlos->name)->toBe('Carlos Admin')
        ->and($carlos->role)->toBe(UserRole::Admin)
        ->and($carlos->email_verified_at)->not->toBeNull()
        ->and(Hash::check('local-carlos-password', $carlos->password))->toBeTrue();
});

it('seeds clearly identifiable dummy users outside production', function (): void {
    $this->seed(InitialUserSeeder::class);

    $dummyAdmins = User::query()->where('email', 'like', 'dummy.admin.%@example.test')->get();
    $dummyUsers = User::query()->where('email', 'like', 'dummy.user.%@example.test')->get();

    expect($dummyAdmins)->toHaveCount(2)
        ->and($dummyUsers)->toHaveCount(10)
        ->and($dummyAdmins->every(fn (User $user): bool => str_starts_with($user->name, '[Dummy Admin ') && $user->role === UserRole::Admin))->toBeTrue()
        ->and($dummyUsers->every(fn (User $user): bool => str_starts_with($user->name, '[Dummy User ') && $user->role === UserRole::User))->toBeTrue()
        ->and($dummyUsers->every(fn (User $user): bool => Hash::check('local-dummy-password', $user->password)))->toBeTrue();
});

it('may run repeatedly without creating duplicate users', function (): void {
    $this->seed(InitialUserSeeder::class);
    $this->seed(InitialUserSeeder::class);

    expect(User::query()->count())->toBe(14)
        ->and(User::query()->where('email', 'gabriel@example.test')->count())->toBe(1)
        ->and(User::query()->where('email', 'carlos@example.test')->count())->toBe(1)
        ->and(User::query()->where('email', 'like', 'dummy.admin.%@example.test')->count())->toBe(2)
        ->and(User::query()->where('email', 'like', 'dummy.user.%@example.test')->count())->toBe(10);
});
