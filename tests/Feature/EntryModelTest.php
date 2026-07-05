<?php

declare(strict_types=1);

use App\Enums\EntryStatus;
use App\Enums\EntryType;
use App\Models\Entry;
use App\Models\User;

it('casts entry fields to enums and dates', function (): void {
    $entry = Entry::factory()->create([
        'type' => EntryType::Drug,
        'status' => EntryStatus::NeedsReview,
        'approved_at' => now(),
        'archived_at' => now(),
    ]);

    expect($entry->type)->toBe(EntryType::Drug)
        ->and($entry->status)->toBe(EntryStatus::NeedsReview)
        ->and($entry->approved_at)->not->toBeNull()
        ->and($entry->archived_at)->not->toBeNull();
});

it('belongs to creator updater and approver users', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $approver = User::factory()->admin()->create();

    $entry = Entry::factory()->create([
        'created_by' => $creator->id,
        'updated_by' => $updater->id,
        'approved_by' => $approver->id,
    ]);

    expect($entry->createdBy->is($creator))->toBeTrue()
        ->and($entry->updatedBy?->is($updater))->toBeTrue()
        ->and($entry->approvedBy?->is($approver))->toBeTrue();
});

it('filters active archived approved status and type scopes', function (): void {
    Entry::factory()->type(EntryType::Drug)->status(EntryStatus::NeedsReview)->create();
    Entry::factory()->type(EntryType::Formula)->archived()->create();
    Entry::factory()->type(EntryType::Protocol)->approved()->create();

    expect(Entry::query()->active()->count())->toBe(2)
        ->and(Entry::query()->archived()->count())->toBe(1)
        ->and(Entry::query()->approved()->count())->toBe(1)
        ->and(Entry::query()->status(EntryStatus::NeedsReview)->count())->toBe(1)
        ->and(Entry::query()->type(EntryType::Formula)->count())->toBe(1);
});

it('knows approved and archived state', function (): void {
    $approved = Entry::factory()->approved()->create();
    $archived = Entry::factory()->archived()->create();

    expect($approved->isApproved())->toBeTrue()
        ->and($approved->isArchived())->toBeFalse()
        ->and($archived->isApproved())->toBeFalse()
        ->and($archived->isArchived())->toBeTrue();
});
