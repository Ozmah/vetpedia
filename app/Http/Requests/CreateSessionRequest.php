<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Actions\LogAuditEvent;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class CreateSessionRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function validateCredentials(LogAuditEvent $logAuditEvent): User
    {
        $this->ensureIsNotRateLimited($logAuditEvent);

        /** @var User|null $user */
        $user = Auth::getProvider()->retrieveByCredentials($this->only('email', 'password'));

        if (! $user || ! Auth::getProvider()->validateCredentials($user, $this->only('password'))) {
            RateLimiter::hit($this->throttleKey());
            $this->logFailedLoginAttempt($logAuditEvent, $user, 'invalid_credentials');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($user->isSuspended()) {
            RateLimiter::hit($this->throttleKey());
            $this->logFailedLoginAttempt($logAuditEvent, $user, 'suspended');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Get the rate-limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return $this->string('email')
            ->lower()
            ->append('|'.$this->ip())
            ->transliterate()
            ->value();
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(LogAuditEvent $logAuditEvent): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $this->logFailedLoginAttempt($logAuditEvent, null, 'rate_limited');

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function logFailedLoginAttempt(LogAuditEvent $logAuditEvent, ?User $user, string $reason): void
    {
        $logAuditEvent->handle(
            actor: $user,
            action: 'auth.failed_login',
            summary: 'Failed login attempt.',
            subject: 'auth',
            after: [
                'attempted_email' => $this->attemptedEmail(),
                'reason' => $reason,
            ],
            ipAddress: $this->ip(),
            userAgent: $this->userAgent(),
        );
    }

    private function attemptedEmail(): string
    {
        return $this->string('email')->trim()->lower()->value();
    }
}
