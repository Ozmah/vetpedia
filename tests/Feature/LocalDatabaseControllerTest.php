<?php

declare(strict_types=1);

use App\Models\User;

it('requires authentication', function (): void {
    $response = $this->get(route('local.database.index'));

    $response->assertRedirectToRoute('login');
});

it('denies non superadmin users', function (): void {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get(route('local.database.index'));

    $response->assertForbidden();
});

it('renders the local database table list for superadmins', function (): void {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)
        ->get(route('local.database.index'));

    $response->assertOk();

    $page = $response->viewData('page');

    expect($page['component'])->toBe('local/database')
        ->and(collect($page['props']['tables'])->contains(fn (array $table): bool => $table['name'] === 'users'))->toBeTrue()
        ->and($page['props']['selectedTable'])->toBeNull();
});

it('renders schema and redacted records for a selected table', function (): void {
    $superadmin = User::factory()->superadmin()->create([
        'id' => '00000000-0000-4000-8000-000000000000',
        'email' => 'superadmin@example.test',
    ]);

    $response = $this->actingAs($superadmin)
        ->get(route('local.database.show', ['table' => 'users']));

    $response->assertOk();

    $selectedTable = $response->viewData('page')['props']['selectedTable'];

    expect($selectedTable['name'])->toBe('users')
        ->and(collect($selectedTable['columns'])->contains(fn (array $column): bool => $column['name'] === 'email'))->toBeTrue()
        ->and($selectedTable['redacted_columns'])->toContain('password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes')
        ->and($selectedTable['records']['data'][0]['email'])->toBe('superadmin@example.test')
        ->and($selectedTable['records']['data'][0]['password'])->toBe('[redacted]')
        ->and($selectedTable['records']['data'][0]['remember_token'])->toBe('[redacted]');
});

it('returns not found for unknown tables', function (): void {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)
        ->get(route('local.database.show', ['table' => 'unknown_table']));

    $response->assertNotFound();
});
