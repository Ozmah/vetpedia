<?php

declare(strict_types=1);

use App\Actions\AssertLocalDatabase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

function makeLocalDatabaseGuard(array $overrides = []): AssertLocalDatabase
{
    $configuration = array_merge([
        'isLocal' => true,
        'hasDevelopmentMarker' => true,
        'infisicalEnvironment' => 'local',
        'connection' => 'sqlite',
        'databaseUrl' => null,
        'database' => '/app-data/vetpedia.sqlite',
    ], $overrides);

    $app = Mockery::mock(Application::class);
    $app->allows('environment')
        ->with('local')
        ->andReturn($configuration['isLocal']);

    $files = Mockery::mock(Filesystem::class);
    $files->allows('exists')
        ->with('/usr/local/share/vetpedia-development-image')
        ->andReturn($configuration['hasDevelopmentMarker']);

    $config = Mockery::mock(Repository::class);
    $config->allows('get')
        ->with('vetpedia.environment')
        ->andReturn($configuration['infisicalEnvironment']);
    $config->allows('get')
        ->with('database.default')
        ->andReturn($configuration['connection']);
    $config->allows('get')
        ->with('database.connections.sqlite.url')
        ->andReturn($configuration['databaseUrl']);
    $config->allows('get')
        ->with('database.connections.sqlite.database')
        ->andReturn($configuration['database']);

    return new AssertLocalDatabase($app, $config, $files);
}

it('accepts only the guarded local development database', function (): void {
    makeLocalDatabaseGuard()->handle();

    expect(true)->toBeTrue();
});

it('rejects artisan environment overrides', function (): void {
    expect(fn () => makeLocalDatabaseGuard()->handle('local'))
        ->toThrow(RuntimeException::class, 'does not allow the --env option');
});

it('rejects unsafe local database configurations', function (array $overrides, string $message): void {
    expect(fn () => makeLocalDatabaseGuard($overrides)->handle())
        ->toThrow(RuntimeException::class, $message);
})->with([
    'non-local application' => [['isLocal' => false], 'requires APP_ENV=local'],
    'non-development image' => [['hasDevelopmentMarker' => false], 'requires the development Docker image'],
    'non-local secrets environment' => [['infisicalEnvironment' => 'production'], 'requires INFISICAL_ENV=local'],
    'non-SQLite connection' => [['connection' => 'pgsql'], 'requires the SQLite connection'],
    'database URL' => [['databaseUrl' => 'sqlite:///tmp/unsafe.sqlite'], 'refuses connections configured through DB_URL'],
    'unexpected database path' => [['database' => '/tmp/unsafe.sqlite'], 'database path must be exactly'],
]);
