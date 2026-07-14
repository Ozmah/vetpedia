<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\AssertLocalDatabase;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('app:db-seed')]
#[Description('Seed the guarded local Docker database')]
final class SeedLocalDatabaseCommand extends Command
{
    public function __construct(private readonly AssertLocalDatabase $assertLocalDatabase)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $environmentOverride = $this->option('env');

            $this->assertLocalDatabase->handle(
                is_string($environmentOverride) ? $environmentOverride : null,
            );
        } catch (RuntimeException $runtimeException) {
            $this->components->error($runtimeException->getMessage());

            return self::FAILURE;
        }

        return $this->call('db:seed', [
            '--no-interaction' => true,
        ]);
    }
}
