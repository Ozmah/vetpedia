<?php

declare(strict_types=1);

use App\Models\AuditEvent;
use App\Models\Entry;
use App\Models\Source;
use App\Models\Species;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('casts structured before and after payloads to arrays', function (): void {
    $event = AuditEvent::factory()->create([
        'before' => ['title' => 'Old title'],
        'after' => ['title' => 'New title'],
    ]);

    expect($event->before)->toBe(['title' => 'Old title'])
        ->and($event->after)->toBe(['title' => 'New title'])
        ->and($event->created_at)->not->toBeNull();
});

it('tracks the actor when an authenticated user caused the event', function (): void {
    $actor = User::factory()->admin()->create();

    $event = AuditEvent::factory()->create([
        'actor_id' => $actor->id,
    ]);

    expect($event->actor?->is($actor))->toBeTrue();
});

it('allows anonymous and system events without an actor or subject id', function (): void {
    $event = AuditEvent::factory()
        ->anonymous()
        ->subject('auth')
        ->create([
            'action' => 'auth.failed_login',
            'summary' => 'Failed login attempt.',
            'before' => null,
            'after' => null,
        ]);

    expect($event->actor_id)->toBeNull()
        ->and($event->subject_type)->toBe('auth')
        ->and($event->subject_id)->toBeNull();
});

it('supports core domain and operational subject types', function (string $subjectType): void {
    $event = AuditEvent::factory()->subject($subjectType)->create();

    expect($event->subject_type)->toBe($subjectType);
})->with([
    Entry::class,
    Source::class,
    Species::class,
    User::class,
    'auth',
    'search',
    'backup',
]);

it('uses an append oriented schema with admin feed indexes', function (): void {
    expect(Schema::hasColumns('audit_events', [
        'id',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'summary',
        'before',
        'after',
        'ip_address',
        'user_agent',
        'created_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumn('audit_events', 'updated_at'))->toBeFalse();

    $indexNames = collect(DB::select('PRAGMA index_list(audit_events)'))
        ->pluck('name')
        ->all();

    expect($indexNames)->toContain(
        'audit_events_created_at_index',
        'audit_events_actor_id_created_at_index',
        'audit_events_subject_type_subject_id_created_at_index',
        'audit_events_action_created_at_index',
    );
});
