<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->actingAs(User::factory()->superadmin()->withoutTwoFactor()->create());
});

it('supports dialog, menu, tooltip, and navigation menu keyboard contracts', function (): void {
    $page = visit('/local/ui');

    $page->click('Eliminar entrada')
        ->assertVisible('[data-slot="dialog-content"]')
        ->keys('[data-slot="dialog-content"]', 'Escape')
        ->assertScript("document.activeElement === document.querySelector('[data-slot=\"dialog-trigger\"]')")
        ->keys('[aria-label="Vista previa de la entrada"]', 'ArrowRight')
        ->assertVisible('[data-slot="tooltip-content"]')
        ->keys('[aria-label="Vista previa de la entrada"]', 'Escape')
        ->keys('Más acciones', 'Enter')
        ->assertVisible('[data-slot="dropdown-menu-content"]')
        ->assertScript("document.activeElement?.textContent?.includes('Editar entrada')")
        ->keys('Editar entrada', 'ArrowDown')
        ->assertScript("document.activeElement?.textContent?.includes('Marcar como documentada')")
        ->keys('Marcar como documentada', 'Escape')
        ->assertScript("document.activeElement?.textContent?.includes('Más acciones')")
        ->keys('[data-slot="navigation-menu-trigger"]', 'Enter')
        ->assertDataAttribute('[data-slot="navigation-menu-trigger"]', 'popup-open', '')
        ->assertVisible('[data-slot="navigation-menu-content"]')
        ->keys('[data-slot="navigation-menu-trigger"]', 'Escape')
        ->assertNoJavaScriptErrors();
});

it('keeps overlays usable with reduced motion', function (): void {
    $page = visit('/local/ui', [
        'reducedMotion' => 'reduce',
    ]);

    $page->assertScript("matchMedia('(prefers-reduced-motion: reduce)').matches")
        ->click('Eliminar entrada')
        ->assertVisible('[data-slot="dialog-content"]')
        ->keys('[data-slot="dialog-content"]', 'Escape')
        ->keys('Más acciones', 'Enter')
        ->assertVisible('[data-slot="dropdown-menu-content"]')
        ->keys('[data-slot="dropdown-menu-content"]', 'Escape')
        ->assertNoJavaScriptErrors();
});
