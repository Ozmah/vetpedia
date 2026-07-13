<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('does not expose the public registration page', function (): void {
    $response = $this->fromRoute('home')
        ->get('/register');

    $response->assertNotFound();
});

it('does not allow public user registration', function (): void {
    $response = $this->fromRoute('home')
        ->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

    $response->assertNotFound();

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();

    $this->assertGuest();
});

it('does not expose registration to authenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get('/register');

    $response->assertNotFound();
});

it('may delete user account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('user-profile.edit')
        ->delete(route('user.destroy'), [
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('home');

    expect(User::query()->find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id)?->trashed())->toBeTrue();

    $this->assertGuest();
});

it('does not allow superadmin account deletion', function (): void {
    $user = User::factory()->superadmin()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('user-profile.edit')
        ->delete(route('user.destroy'), [
            'password' => 'password',
        ]);

    $response->assertForbidden();

    expect($user->fresh())->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

it('requires password to delete account', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('user-profile.edit')
        ->delete(route('user.destroy'), []);

    $response->assertRedirectToRoute('user-profile.edit')
        ->assertSessionHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});

it('requires correct password to delete account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('user-profile.edit')
        ->delete(route('user.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response->assertRedirectToRoute('user-profile.edit')
        ->assertSessionHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});
