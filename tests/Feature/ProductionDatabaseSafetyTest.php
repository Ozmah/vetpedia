<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Command\Command;

it('prohibits destructive database commands in production', function (): void {
    $originalEnvironment = app()->environment();

    app()->detectEnvironment(fn (): string => 'production');
    new AppServiceProvider(app())->boot();

    try {
        $exitCode = Artisan::call('db:wipe', ['--force' => true]);

        expect($exitCode)->toBe(Command::FAILURE)
            ->and(Schema::hasTable('users'))->toBeTrue();
    } finally {
        DB::prohibitDestructiveCommands(false);
        app()->detectEnvironment(fn (): string => $originalEnvironment);
    }
});
