<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        ->and($selectedTable['records']['data'][0]['password'])->toBe('[censurado]')
        ->and($selectedTable['records']['data'][0]['remember_token'])->toBe('[censurado]');
});

it('redacts common credential columns and their schema defaults', function (): void {
    Schema::create('local_credentials', function (Blueprint $table): void {
        $table->id();
        $table->string('api_key')->default('default-api-key');
        $table->string('private_key');
        $table->string('accessKey');
        $table->string('credential');
        $table->string('Authorization');
        $table->string('value');
        $table->string('section_key');
    });

    DB::table('local_credentials')->insert([
        'api_key' => 'api-key',
        'private_key' => 'private-key',
        'accessKey' => 'access-key',
        'credential' => 'credential-value',
        'Authorization' => 'Bearer secret',
        'value' => 'generic-credential-value',
        'section_key' => 'safe-section-key',
    ]);

    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)
        ->get(route('local.database.show', ['table' => 'local_credentials']));

    $response->assertOk();

    $selectedTable = $response->viewData('page')['props']['selectedTable'];
    $record = $selectedTable['records']['data'][0];
    $apiKeyColumn = collect($selectedTable['columns'])->firstWhere('name', 'api_key');

    expect($selectedTable['redacted_columns'])
        ->toContain('api_key', 'private_key', 'accessKey', 'credential', 'Authorization', 'value')
        ->not->toContain('section_key')
        ->and($record['api_key'])->toBe('[censurado]')
        ->and($record['private_key'])->toBe('[censurado]')
        ->and($record['accessKey'])->toBe('[censurado]')
        ->and($record['credential'])->toBe('[censurado]')
        ->and($record['Authorization'])->toBe('[censurado]')
        ->and($record['value'])->toBe('[censurado]')
        ->and($record['section_key'])->toBe('safe-section-key')
        ->and($apiKeyColumn['default'])->toBe('[censurado]');
});

it('returns not found for unknown tables', function (): void {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)
        ->get(route('local.database.show', ['table' => 'unknown_table']));

    $response->assertNotFound();
});
