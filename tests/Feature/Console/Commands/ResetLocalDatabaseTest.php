<?php

declare(strict_types=1);

use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;

it('refuses to rebuild outside the guarded local database', function (): void {
    $this->artisan('app:db-fresh')
        ->expectsOutputToContain('requires APP_ENV=local')
        ->assertFailed();
});

it('refuses artisan environment overrides', function (): void {
    $this->artisan('app:db-fresh', ['--env' => 'local'])
        ->expectsOutputToContain('does not allow the --env option')
        ->assertFailed();
});

it('rebuilds and seeds the database after the local guard passes', function (): void {
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

    $migrateFresh = new #[Signature('migrate:fresh {--seed}')] class extends Command
    {
        public bool $receivedSeed = false;

        public bool $receivedNoInteraction = false;

        public function handle(): int
        {
            $this->receivedSeed = (bool) $this->option('seed');
            $this->receivedNoInteraction = ! $this->input->isInteractive();

            return self::SUCCESS;
        }
    };

    $kernel = $this->app->make(Kernel::class);
    $kernel->all();
    $kernel->registerCommand($migrateFresh);

    $this->artisan('app:db-fresh')->assertSuccessful();

    expect($migrateFresh->receivedSeed)->toBeTrue()
        ->and($migrateFresh->receivedNoInteraction)->toBeTrue();
});
