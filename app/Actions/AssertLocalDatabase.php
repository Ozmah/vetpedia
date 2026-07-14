<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final readonly class AssertLocalDatabase
{
    private const string DevelopmentImageMarker = '/usr/local/share/vetpedia-development-image';

    private const string LocalDatabasePath = '/app-data/vetpedia.sqlite';

    public function __construct(
        private Application $app,
        private Repository $config,
        private Filesystem $files,
    ) {
        //
    }

    public function handle(?string $environmentOverride = null): void
    {
        $this->failUnless(
            $environmentOverride === null,
            'The local database command does not allow the --env option.',
        );

        $this->failUnless(
            $this->app->environment('local'),
            'The local database command requires APP_ENV=local.',
        );

        $this->failUnless(
            $this->files->exists(self::DevelopmentImageMarker),
            'The local database command requires the development Docker image.',
        );

        $this->failUnless(
            $this->config->get('vetpedia.environment') === 'local',
            'The local database command requires INFISICAL_ENV=local.',
        );

        $this->failUnless(
            $this->config->get('database.default') === 'sqlite',
            'The local database command requires the SQLite connection.',
        );

        $databaseUrl = $this->config->get('database.connections.sqlite.url');

        $this->failUnless(
            $databaseUrl === null || $databaseUrl === '',
            'The local database command refuses connections configured through DB_URL.',
        );

        $this->failUnless(
            $this->config->get('database.connections.sqlite.database') === self::LocalDatabasePath,
            sprintf('The local database path must be exactly [%s].', self::LocalDatabasePath),
        );
    }

    private function failUnless(bool $condition, string $message): void
    {
        throw_unless($condition, RuntimeException::class, $message);
    }
}
