<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class LogAuditEvent
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function handle(
        ?User $actor,
        string $action,
        string $summary,
        Model|string|null $subject = null,
        ?string $subjectId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): AuditEvent {
        $action = mb_trim($action);
        $summary = mb_trim($summary);

        throw_if($action === '', InvalidArgumentException::class, 'Audit event action is required.');

        throw_if($summary === '', InvalidArgumentException::class, 'Audit event summary is required.');

        [$subjectType, $resolvedSubjectId] = $this->resolveSubject($subject, $subjectId);

        return AuditEvent::query()->create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $resolvedSubjectId,
            'summary' => $summary,
            'before' => $before,
            'after' => $after,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveSubject(Model|string|null $subject, ?string $subjectId): array
    {
        if ($subject instanceof Model) {
            $subjectKey = $subject->getKey();

            throw_unless(is_string($subjectKey), InvalidArgumentException::class, 'Audit event subject model must use a string UUID key.');

            $subjectId = $subjectKey;

            throw_unless(Str::isUuid($subjectId), InvalidArgumentException::class, 'Audit event subject model must use a UUID key.');

            return [$subject->getMorphClass(), $subjectId];
        }

        throw_if($subjectId !== null && ! Str::isUuid($subjectId), InvalidArgumentException::class, 'Audit event subject id must be a UUID.');

        return [$subject, $subjectId];
    }
}
