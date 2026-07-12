<?php

declare(strict_types=1);

use App\Models\User;

it('requires authentication', function (): void {
    $response = $this->get(route('local.ui.index'));

    $response->assertRedirectToRoute('login');
});

it('denies non superadmin users', function (): void {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get(route('local.ui.index'));

    $response->assertForbidden();
});

it('hides the local ui navigation from non superadmin users', function (): void {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();

    expect($response->viewData('page')['props']['localTools']['ui'])->toBeNull();
});

it('requires a verified superadmin', function (): void {
    $superadmin = User::factory()->superadmin()->unverified()->create();

    $response = $this->actingAs($superadmin)
        ->get(route('local.ui.index'));

    $response->assertRedirectToRoute('verification.notice');
});

it('denies suspended superadmins', function (): void {
    $superadmin = User::factory()->superadmin()->suspended()->create();

    $response = $this->actingAs($superadmin)
        ->get(route('local.ui.index'));

    $response->assertRedirectToRoute('login');
    $this->assertGuest();
});

it('renders the local ui laboratory for superadmins', function (): void {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)
        ->get(route('local.ui.index'));

    $response->assertOk();

    $page = $response->viewData('page');

    expect($page['component'])->toBe('local/ui')
        ->and($page['props']['auth']['user']['id'])->toBe($superadmin->id)
        ->and($page['props']['localTools']['ui'])->toBe(route('local.ui.index', absolute: false));
});
