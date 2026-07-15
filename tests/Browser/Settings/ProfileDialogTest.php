<?php

declare(strict_types=1);

use App\Models\User;

it('closes the account dialog with escape and restores focus', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $page = visit('/settings/profile');

    $page->click('@delete-user-button')
        ->assertVisible('[data-slot="dialog-content"]')
        ->assertSee('Are you sure you want to delete your account?')
        ->keys('[data-slot="dialog-content"]', 'Escape')
        ->assertScript("document.querySelector('[data-slot=\"dialog-content\"]') === null")
        ->assertScript("document.activeElement === document.querySelector('[data-test=\"delete-user-button\"]')")
        ->assertNoJavaScriptErrors();
});

it('keeps the account dialog open and focuses an invalid password', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $page = visit('/settings/profile');

    $page->click('@delete-user-button')
        ->fill('password', 'incorrect-password')
        ->click('@confirm-delete-user-button')
        ->assertVisible('[data-slot="dialog-content"]')
        ->assertScript("document.activeElement === document.querySelector('#password')")
        ->assertNoJavaScriptErrors();
});
