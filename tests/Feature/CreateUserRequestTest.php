<?php

declare(strict_types=1);

use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware('web')->post(
        '/_testing/users',
        fn (CreateUserRequest $request) => response()->json($request->validated()),
    );
});

it('accepts a valid user creation payload', function (): void {
    $response = $this->postJson('/_testing/users', [
        'name' => 'Test User',
        'email' => 'test.user@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertOk()
        ->assertJson([
            'name' => 'Test User',
            'email' => 'test.user@example.com',
            'password' => 'password1234',
        ])
        ->assertJsonMissingPath('password_confirmation');
});

it('requires all user creation fields', function (): void {
    $this->postJson('/_testing/users')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejects invalid user attributes', function (array $payload, array $errors): void {
    $this->postJson('/_testing/users', array_merge([
        'name' => 'Test User',
        'email' => 'test.user@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ], $payload))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'non-string name' => [['name' => ['Test User']], ['name']],
    'oversized name' => [['name' => str_repeat('a', 256)], ['name']],
    'uppercase email' => [['email' => 'Test.User@example.com'], ['email']],
    'malformed email' => [['email' => 'test.user@example'], ['email']],
    'oversized email' => [['email' => str_repeat('a', 244).'@example.com'], ['email']],
    'unconfirmed password' => [['password_confirmation' => 'different-password'], ['password']],
    'short password' => [['password' => 'short', 'password_confirmation' => 'short'], ['password']],
]);

it('requires a unique email address', function (): void {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->postJson('/_testing/users', [
        'name' => 'Another User',
        'email' => 'existing@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});
