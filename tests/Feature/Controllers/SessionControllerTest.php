<?php

declare(strict_types=1);

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('renders login page', function (): void {
    $response = $this->fromRoute('home')
        ->get(route('login'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('session/create')
            ->has('canResetPassword')
            ->has('status'));
});

it('may create a session', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('dashboard');

    $this->assertAuthenticatedAs($user);
});

it('logs successful authentication audit events', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->withHeader('User-Agent', 'Vetpedia Test Agent')
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
        ->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ])
        ->assertRedirectToRoute('dashboard');

    $event = AuditEvent::query()->sole();

    expect($event->actor_id)->toBe($user->id)
        ->and($event->action)->toBe('auth.login')
        ->and($event->subject_type)->toBe('auth')
        ->and($event->summary)->toBe('User logged in.')
        ->and($event->ip_address)->toBe('203.0.113.10')
        ->and($event->user_agent)->toBe('Vetpedia Test Agent')
        ->and($event->before)->toBeNull()
        ->and($event->after)->toBeNull();
});

it('may create a session with remember me', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

    $response->assertRedirectToRoute('dashboard');

    $this->assertAuthenticatedAs($user);
});

it('does not create a session for suspended users', function (): void {
    User::factory()->withoutTwoFactor()->suspended()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out suspended users with existing sessions', function (): void {
    $user = User::factory()->suspended()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertRedirectToRoute('login');

    $this->assertGuest();
});

it('redirects to two-factor challenge when enabled', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('two-factor.login');

    $this->assertGuest();
});

it('fails with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs failed authentication audit events without sensitive credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->withHeader('User-Agent', 'Vetpedia Test Agent')
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
        ->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])
        ->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');

    $event = AuditEvent::query()->sole();

    expect($event->actor_id)->toBe($user->id)
        ->and($event->action)->toBe('auth.failed_login')
        ->and($event->subject_type)->toBe('auth')
        ->and($event->summary)->toBe('Failed login attempt.')
        ->and($event->after)->toBe([
            'attempted_email' => 'test@example.com',
            'reason' => 'invalid_credentials',
        ])
        ->and($event->ip_address)->toBe('203.0.113.20')
        ->and($event->user_agent)->toBe('Vetpedia Test Agent');

    $encodedEvent = json_encode($event->toArray(), JSON_THROW_ON_ERROR);

    expect($encodedEvent)->not->toContain('wrong-password');
});

it('logs failed authentication attempts for suspended users', function (): void {
    $user = User::factory()->withoutTwoFactor()->suspended()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ])
        ->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');

    $event = AuditEvent::query()->sole();

    expect($event->actor_id)->toBe($user->id)
        ->and($event->action)->toBe('auth.failed_login')
        ->and($event->after)->toBe([
            'attempted_email' => 'test@example.com',
            'reason' => 'suspended',
        ]);
});

it('requires email', function (): void {
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');
});

it('requires password', function (): void {
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('password');
});

it('may destroy a session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('logout'));

    $response->assertRedirect('/');

    $this->assertGuest();
});

it('redirects authenticated users away from login', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('login'));

    $response->assertRedirectToRoute('dashboard');
});

it('throttles login attempts after too many failures', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 5 failed login attempts to trigger rate limiting
    for ($i = 0; $i < 5; $i++) {
        $this->fromRoute('login')
            ->post(route('login.store'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
    }

    // The 6th attempt should be throttled
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');

    $errors = session('errors');
    expect($errors->get('email')[0])->toContain('Too many login attempts');
});

it('clears rate limit after successful login', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make a few failed attempts
    for ($i = 0; $i < 3; $i++) {
        $this->fromRoute('login')
            ->post(route('login.store'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
    }

    // Successful login should clear the rate limit
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertAuthenticatedAs($user);
});

it('dispatches lockout event when rate limit is reached', function (): void {
    Event::fake([Lockout::class]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 6 failed login attempts to trigger rate limiting and Lockout event
    // The Lockout event fires on the 6th attempt when tooManyAttempts returns true
    for ($i = 0; $i < 6; $i++) {
        $this->fromRoute('login')
            ->post(route('login.store'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
    }

    Event::assertDispatched(Lockout::class);

    expect(AuditEvent::query()->latest('created_at')->first()?->after)->toBe([
        'attempted_email' => 'test@example.com',
        'reason' => 'rate_limited',
    ]);
});
