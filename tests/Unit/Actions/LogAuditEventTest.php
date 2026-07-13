<?php

declare(strict_types=1);

use App\Actions\LogAuditEvent;
use App\Models\AuditEvent;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('writes authenticated domain audit events with structured payloads', function (): void {
    $actor = User::factory()->admin()->create();
    $entry = Entry::factory()->create();
    $action = resolve(LogAuditEvent::class);

    $event = $action->handle(
        actor: $actor,
        action: 'entry.updated',
        summary: 'Updated entry title.',
        subject: $entry,
        before: ['title' => 'Old title'],
        after: ['title' => 'New title'],
        ipAddress: '203.0.113.10',
        userAgent: 'Vetpedia Test Agent',
    );

    expect($event)->toBeInstanceOf(AuditEvent::class)
        ->and($event->actor?->is($actor))->toBeTrue()
        ->and($event->action)->toBe('entry.updated')
        ->and($event->subject_type)->toBe(Entry::class)
        ->and($event->subject_id)->toBe($entry->id)
        ->and($event->summary)->toBe('Updated entry title.')
        ->and($event->before)->toBe(['title' => 'Old title'])
        ->and($event->after)->toBe(['title' => 'New title'])
        ->and($event->ip_address)->toBe('203.0.113.10')
        ->and($event->user_agent)->toBe('Vetpedia Test Agent');
});

it('writes anonymous and system audit events', function (): void {
    $action = resolve(LogAuditEvent::class);

    $event = $action->handle(
        actor: null,
        action: 'auth.failed_login',
        summary: 'Failed login attempt.',
        subject: 'auth',
        ipAddress: '203.0.113.20',
        userAgent: 'Browser Test Agent',
    );

    expect($event->actor_id)->toBeNull()
        ->and($event->subject_type)->toBe('auth')
        ->and($event->subject_id)->toBeNull()
        ->and($event->before)->toBeNull()
        ->and($event->after)->toBeNull();
});

it('supports explicit UUID subject ids for operational events', function (): void {
    $subjectId = Str::uuid()->toString();
    $action = resolve(LogAuditEvent::class);

    $event = $action->handle(
        actor: null,
        action: 'backup.completed',
        summary: 'Backup completed.',
        subject: 'backup',
        subjectId: $subjectId,
    );

    expect($event->subject_type)->toBe('backup')
        ->and($event->subject_id)->toBe($subjectId);
});

it('rejects malformed audit events before writing', function (): void {
    $action = resolve(LogAuditEvent::class);

    expect(fn () => $action->handle(
        actor: null,
        action: '   ',
        summary: 'Blank action.',
    ))->toThrow(InvalidArgumentException::class, 'Audit event action is required.');

    expect(fn () => $action->handle(
        actor: null,
        action: 'auth.failed_login',
        summary: '   ',
    ))->toThrow(InvalidArgumentException::class, 'Audit event summary is required.');

    expect(fn () => $action->handle(
        actor: null,
        action: 'backup.completed',
        summary: 'Backup completed.',
        subject: 'backup',
        subjectId: 'not-a-uuid',
    ))->toThrow(InvalidArgumentException::class, 'Audit event subject id must be a UUID.');

    expect(AuditEvent::query()->count())->toBe(0);
});

it('participates in the caller transaction instead of swallowing failures', function (): void {
    $action = resolve(LogAuditEvent::class);

    expect(fn () => DB::transaction(function () use ($action): void {
        $action->handle(
            actor: null,
            action: 'entry.created',
            summary: 'Created entry.',
            subject: 'entry',
        );

        throw new RuntimeException('Abort domain mutation.');
    }))->toThrow(RuntimeException::class, 'Abort domain mutation.');

    expect(AuditEvent::query()->count())->toBe(0);
});
