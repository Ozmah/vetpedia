<?php

declare(strict_types=1);

use App\Models\User;

it('supports the desktop sidebar, tooltip, keyboard menu, and inertia navigation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $page = visit('/dashboard');
    $sidebar = '[data-slot="sidebar"][data-state]';
    $dashboardLink = '[data-sidebar="menu-button"][data-base-ui-tooltip-trigger][href$="/dashboard"]';

    $page->resize(1280, 800)
        ->assertDataAttribute($sidebar, 'state', 'expanded')
        ->click('[data-slot="sidebar-trigger"]')
        ->assertDataAttribute($sidebar, 'state', 'collapsed')
        ->hover($dashboardLink)
        ->assertVisible('[data-slot="tooltip-content"]')
        ->keys($dashboardLink, 'Escape')
        ->keys('[data-slot="sidebar-trigger"]', 'Control+b')
        ->assertDataAttribute($sidebar, 'state', 'expanded')
        ->assertScript("document.cookie.includes('sidebar_state=true')")
        ->script('window.__vetpediaInertiaMarker = true');

    $page->keys('@sidebar-menu-button', 'Enter')
        ->assertVisible('[data-slot="dropdown-menu-content"]')
        ->assertScript("document.activeElement?.textContent?.includes('Settings')")
        ->keys('Settings', 'Enter')
        ->assertPathIs('/settings/profile')
        ->assertScript('window.__vetpediaInertiaMarker === true')
        ->assertNoJavaScriptErrors();
});

it('supports the mobile sidebar and closes it after navigation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $page = visit('/dashboard')->on()->iPhone15Pro();

    $page->assertScript('navigator.maxTouchPoints > 0')
        ->click('[data-slot="sidebar-trigger"]')
        ->assertVisible('[data-mobile="true"]')
        ->assertSee('Sidebar')
        ->keys('[data-mobile="true"]', 'Escape')
        ->assertScript("document.querySelector('[data-mobile=\"true\"]') === null")
        ->assertScript("document.activeElement === document.querySelector('[data-slot=\"sidebar-trigger\"]')")
        ->click('[data-slot="sidebar-trigger"]')
        ->click('@sidebar-menu-button')
        ->click('Settings')
        ->assertPathIs('/settings/profile')
        ->assertScript("document.querySelector('[data-mobile=\"true\"]') === null")
        ->assertScript("document.body.style.pointerEvents !== 'none'");
});
