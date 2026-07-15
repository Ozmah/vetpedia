<?php

declare(strict_types=1);

use App\Models\User;

it('logs in with the remember checkbox using the keyboard', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'browser@example.com',
    ]);

    $page = visit('/login');

    $page->assertPathIs('/login')
        ->keys('#remember', 'Space')
        ->assertChecked('#remember')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertPathIs('/dashboard')
        ->assertSee('Dashboard')
        ->assertNoSmoke();
});

it('has an accessible login page', function (): void {
    visit('/login')
        ->assertNoAccessibilityIssues(1)
        ->assertNoSmoke();
});
