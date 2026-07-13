<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\LogAuditEvent;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use LogicException;

final readonly class LogSuccessfulLogin
{
    public function __construct(private LogAuditEvent $logAuditEvent)
    {
        //
    }

    public function handle(Login $event): void
    {
        throw_unless($event->user instanceof User, LogicException::class, 'Vetpedia authentication events must use the User model.');

        $this->logAuditEvent->handle(
            actor: $event->user,
            action: 'auth.login',
            summary: 'User logged in.',
            subject: 'auth',
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
        );
    }
}
