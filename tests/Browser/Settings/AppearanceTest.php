<?php

declare(strict_types=1);

use App\Models\User;

it('persists light, dark, and system appearance modes', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $page = visit('/settings/appearance')->inDarkMode();

    $page->click('Oscuro')
        ->assertScript("document.documentElement.classList.contains('dark')")
        ->assertScript("document.documentElement.style.colorScheme === 'dark'")
        ->assertScript("localStorage.getItem('appearance') === 'dark'")
        ->assertScript("document.cookie.includes('appearance=dark')")
        ->click('Claro')
        ->assertScript("!document.documentElement.classList.contains('dark')")
        ->assertScript("document.documentElement.style.colorScheme === 'light'")
        ->assertScript("localStorage.getItem('appearance') === 'light'")
        ->click('Sistema')
        ->assertScript("matchMedia('(prefers-color-scheme: dark)').matches")
        ->assertScript("document.documentElement.classList.contains('dark')")
        ->assertScript("localStorage.getItem('appearance') === 'system'")
        ->assertNoAccessibilityIssues(1)
        ->assertNoSmoke();
});
