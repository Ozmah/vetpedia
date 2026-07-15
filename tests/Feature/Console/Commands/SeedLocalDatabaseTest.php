<?php

declare(strict_types=1);

use App\Models\Species;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;

it('refuses to seed outside the guarded local database', function (): void {
    $this->artisan('app:db-seed')
        ->expectsOutputToContain('requires APP_ENV=local')
        ->assertFailed();
});

it('seeds the database after the local guard passes', function (): void {
    config()->set([
        'vetpedia.seed_users.superadmin' => [
            'name' => 'Superadmin',
            'email' => 'superadmin@example.test',
            'password' => 'password',
        ],
        'vetpedia.seed_users.admin' => [
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
        ],
        'vetpedia.seed_users.dummy_password' => 'password',
    ]);

    $this->app->detectEnvironment(fn (): string => 'local');
    config()->set([
        'database.default' => 'sqlite',
        'database.connections.sqlite.url' => null,
        'database.connections.sqlite.database' => '/app-data/vetpedia.sqlite',
        'vetpedia.environment' => 'local',
    ]);

    $files = $this->mock(Filesystem::class);
    $files->expects('exists')
        ->with('/usr/local/share/vetpedia-development-image')
        ->andReturnTrue();

    $this->artisan('app:db-seed')->assertSuccessful();

    expect(User::query()->count())->toBe(14)
        ->and(Species::query()->count())->toBe(2);
});
