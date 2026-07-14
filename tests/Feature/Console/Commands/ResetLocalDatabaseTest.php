<?php

declare(strict_types=1);

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
