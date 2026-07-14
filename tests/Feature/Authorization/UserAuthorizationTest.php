<?php

declare(strict_types=1);

use App\Enums\Ability;
use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

it('allows superadmins to use privileged gates except protected user mutations', function (): void {
    $superadmin = User::factory()->superadmin()->create();
    $admin = User::factory()->admin()->create();
    $normalUser = User::factory()->create();
    $otherSuperadmin = User::factory()->superadmin()->create();

    expect(Gate::forUser($superadmin)->allows(Ability::CreateAdmins->value))->toBeTrue()
        ->and(Gate::forUser($superadmin)->allows(Ability::RunBackups->value))->toBeTrue()
        ->and(Gate::forUser($superadmin)->allows(Ability::ViewLocalDatabase->value))->toBeTrue()
        ->and(Gate::forUser($superadmin)->allows('updateRole', [$admin, UserRole::User]))->toBeTrue()
        ->and(Gate::forUser($superadmin)->allows('updateRole', [$normalUser, UserRole::Admin]))->toBeTrue()
        ->and(Gate::forUser($superadmin)->denies('updateRole', [$normalUser, UserRole::Superadmin]))->toBeTrue()
        ->and(Gate::forUser($superadmin)->denies('updateRole', [$otherSuperadmin, UserRole::Admin]))->toBeTrue()
        ->and(Gate::forUser($superadmin)->denies('delete', $otherSuperadmin))->toBeTrue()
        ->and(Gate::forUser($superadmin)->denies('forceDelete', $normalUser))->toBeTrue()
        ->and(Gate::forUser($superadmin)->denies(Ability::SuspendUsers->value, $admin))->toBeTrue();
});

it('allows admins to manage domain content and normal users only', function (): void {
    $admin = User::factory()->admin()->create();
    $normalUser = User::factory()->create();
    $otherAdmin = User::factory()->admin()->create();

    expect(Gate::forUser($admin)->allows(Ability::ManageApprovedEntries->value))->toBeTrue()
        ->and(Gate::forUser($admin)->allows(Ability::ApproveEntries->value))->toBeTrue()
        ->and(Gate::forUser($admin)->allows(Ability::ManageSources->value))->toBeTrue()
        ->and(Gate::forUser($admin)->allows(Ability::ManageSpecies->value))->toBeTrue()
        ->and(Gate::forUser($admin)->allows(Ability::ManageCatalogs->value))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', User::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $normalUser))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $normalUser))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $normalUser))->toBeTrue()
        ->and(Gate::forUser($admin)->allows(Ability::SuspendUsers->value, $normalUser))->toBeTrue()
        ->and(Gate::forUser($admin)->denies(Ability::CreateAdmins->value))->toBeTrue()
        ->and(Gate::forUser($admin)->denies(Ability::ManageAdmins->value))->toBeTrue()
        ->and(Gate::forUser($admin)->denies(Ability::RunBackups->value))->toBeTrue()
        ->and(Gate::forUser($admin)->denies(Ability::ViewLocalDatabase->value))->toBeTrue()
        ->and(Gate::forUser($admin)->denies('view', $otherAdmin))->toBeTrue()
        ->and(Gate::forUser($admin)->denies('update', $otherAdmin))->toBeTrue()
        ->and(Gate::forUser($admin)->denies('delete', $otherAdmin))->toBeTrue()
        ->and(Gate::forUser($admin)->denies('updateRole', [$normalUser, UserRole::Admin]))->toBeTrue()
        ->and(Gate::forUser($admin)->denies(Ability::SuspendUsers->value, $otherAdmin))->toBeTrue();
});

it('allows users to work only with non-approved entry capabilities', function (): void {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows(Ability::AccessInternal->value))->toBeTrue()
        ->and(Gate::forUser($user)->allows(Ability::CreateEntries->value))->toBeTrue()
        ->and(Gate::forUser($user)->allows(Ability::ManageUnapprovedEntries->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ManageApprovedEntries->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ApproveEntries->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ArchiveEntries->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ManageSources->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ManageSpecies->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ManageCatalogs->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ViewUsers->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::RunBackups->value))->toBeTrue()
        ->and(Gate::forUser($user)->denies(Ability::ViewLocalDatabase->value))->toBeTrue();
});

it('denies authorization gates for suspended users regardless of role', function (): void {
    $admin = User::factory()->admin()->suspended()->create();
    $superadmin = User::factory()->superadmin()->suspended()->create();

    expect(Gate::forUser($admin)->denies(Ability::AccessInternal->value))->toBeTrue()
        ->and(Gate::forUser($admin)->denies(Ability::ManageCatalogs->value))->toBeTrue()
        ->and(Gate::forUser($superadmin)->denies(Ability::RunBackups->value))->toBeTrue()
        ->and(Gate::forUser($superadmin)->denies(Ability::ViewLocalDatabase->value))->toBeTrue();
});

it('enforces user policy defaults independently of global gate hooks', function (): void {
    $policy = resolve(UserPolicy::class);
    $superadmin = User::factory()->superadmin()->create();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->updateRole($superadmin, $superadmin, UserRole::Admin))->toBeFalse()
        ->and($policy->unsuspend($admin, $user))->toBeTrue()
        ->and($policy->unsuspend($user, $user))->toBeFalse()
        ->and($policy->restore())->toBeFalse()
        ->and($policy->forceDelete())->toBeFalse();
});
