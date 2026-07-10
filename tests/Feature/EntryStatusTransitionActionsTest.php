<?php

declare(strict_types=1);

use App\Actions\ApproveEntry;
use App\Actions\ArchiveEntry;
use App\Actions\DeleteArchivedEntry;
use App\Actions\RestoreArchivedEntry;
use App\Actions\UpdateEntry;
use App\Enums\EntryStatus;
use App\Exceptions\InvalidEntryTransition;
use App\Models\AuditEvent;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

it('allows admins to approve documented entries and audits the transition', function (): void {
    $admin = User::factory()->admin()->create();
    $entry = Entry::factory()->status(EntryStatus::Documented)->create();

    $approved = resolve(ApproveEntry::class)->handle($admin, $entry);

    expect($approved->status)->toBe(EntryStatus::VetApproved)
        ->and($approved->approved_by)->toBe($admin->id)
        ->and($approved->approved_at)->not->toBeNull();

    $auditEvent = AuditEvent::query()->sole();

    expect($auditEvent->action)->toBe('entry.approved')
        ->and($auditEvent->actor_id)->toBe($admin->id)
        ->and($auditEvent->subject_id)->toBe($entry->id)
        ->and($auditEvent->before['status'])->toBe('documented')
        ->and($auditEvent->after['status'])->toBe('vet_approved')
        ->and($auditEvent->after['approved_by'])->toBe($admin->id);
});

it('allows superadmins to approve documented entries', function (): void {
    $superadmin = User::factory()->superadmin()->create();
    $entry = Entry::factory()->status(EntryStatus::Documented)->create();

    $approved = resolve(ApproveEntry::class)->handle($superadmin, $entry);

    expect($approved->status)->toBe(EntryStatus::VetApproved)
        ->and($approved->approved_by)->toBe($superadmin->id);
});

it('rejects unauthorized and invalid approval transitions', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $documented = Entry::factory()->status(EntryStatus::Documented)->create();
    $draft = Entry::factory()->status(EntryStatus::Draft)->create();
    $archived = Entry::factory()->status(EntryStatus::Documented)->archived()->create();

    expect(fn () => resolve(ApproveEntry::class)->handle($user, $documented))
        ->toThrow(AuthorizationException::class);

    expect(fn () => resolve(ApproveEntry::class)->handle($admin, $draft))
        ->toThrow(InvalidEntryTransition::class, 'Only documented entries can be approved.');

    expect(fn () => resolve(ApproveEntry::class)->handle($admin, $archived))
        ->toThrow(InvalidEntryTransition::class, 'Archived entries cannot be approved.');

    expect($documented->refresh()->status)->toBe(EntryStatus::Documented)
        ->and($draft->refresh()->status)->toBe(EntryStatus::Draft)
        ->and(AuditEvent::query()->count())->toBe(0);
});

it('archives and restores entries through explicit audited transitions', function (): void {
    $admin = User::factory()->admin()->create();
    $entry = Entry::factory()->approved()->create();

    $archived = resolve(ArchiveEntry::class)->handle($admin, $entry);

    expect($archived->isArchived())->toBeTrue()
        ->and($archived->status)->toBe(EntryStatus::VetApproved);

    $restored = resolve(RestoreArchivedEntry::class)->handle($admin, $archived);

    expect($restored->isArchived())->toBeFalse()
        ->and($restored->status)->toBe(EntryStatus::VetApproved)
        ->and(AuditEvent::query()->pluck('action')->all())->toBe([
            'entry.archived',
            'entry.restored',
        ]);
});

it('rejects unauthorized and repeated archive transitions', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $entry = Entry::factory()->create();
    $archived = Entry::factory()->archived()->create();

    expect(fn () => resolve(ArchiveEntry::class)->handle($user, $entry))
        ->toThrow(AuthorizationException::class);

    expect(fn () => resolve(ArchiveEntry::class)->handle($admin, $archived))
        ->toThrow(InvalidEntryTransition::class, 'Entry is already archived.');

    expect(fn () => resolve(RestoreArchivedEntry::class)->handle($admin, $entry))
        ->toThrow(InvalidEntryTransition::class, 'Only archived entries can be restored.');

    expect(AuditEvent::query()->count())->toBe(0);
});

it('allows only admins to edit approved entries and preserves approval', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $entry = Entry::factory()->approved()->create(['title' => 'Original title']);

    expect(fn () => resolve(UpdateEntry::class)->handle($user, $entry, [
        'title' => 'Unauthorized title',
    ]))->toThrow(AuthorizationException::class);

    $updated = resolve(UpdateEntry::class)->handle($admin, $entry, [
        'title' => 'Reviewed title',
        'sources' => [],
        'sections' => [],
    ]);

    expect($updated->title)->toBe('Reviewed title')
        ->and($updated->status)->toBe(EntryStatus::VetApproved)
        ->and($updated->approved_by)->not->toBeNull()
        ->and($updated->approved_at)->not->toBeNull();
});

it('prevents editing archived entries', function (): void {
    $admin = User::factory()->admin()->create();
    $entry = Entry::factory()->archived()->create();

    expect(fn () => resolve(UpdateEntry::class)->handle($admin, $entry, [
        'title' => 'Archived title',
    ]))->toThrow(InvalidEntryTransition::class, 'Archived entries cannot be edited.');

    expect($entry->refresh()->title)->not->toBe('Archived title')
        ->and(AuditEvent::query()->count())->toBe(0);
});

it('permanently deletes only confirmed archived entries and preserves the audit event', function (): void {
    $admin = User::factory()->admin()->create();
    $active = Entry::factory()->create();
    $archived = Entry::factory()->archived()->create();

    expect(fn () => resolve(DeleteArchivedEntry::class)->handle($admin, $archived, false))
        ->toThrow(InvalidEntryTransition::class, 'Permanent entry deletion requires explicit confirmation.');

    expect(fn () => resolve(DeleteArchivedEntry::class)->handle($admin, $active, true))
        ->toThrow(InvalidEntryTransition::class, 'Only archived entries can be permanently deleted.');

    resolve(DeleteArchivedEntry::class)->handle($admin, $archived, true);

    expect(Entry::query()->find($archived->id))->toBeNull()
        ->and(Entry::query()->find($active->id))->not->toBeNull();

    $auditEvent = AuditEvent::query()->sole();

    expect($auditEvent->action)->toBe('entry.deleted')
        ->and($auditEvent->subject_id)->toBe($archived->id)
        ->and($auditEvent->before['archived_at'])->not->toBeNull();
});
