<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\AuditEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string|null $actor_id
 * @property-read string $action
 * @property-read string|null $subject_type
 * @property-read string|null $subject_id
 * @property-read string $summary
 * @property-read array<string, mixed>|null $before
 * @property-read array<string, mixed>|null $after
 * @property-read string|null $ip_address
 * @property-read string|null $user_agent
 * @property-read CarbonInterface $created_at
 * @property-read User|null $actor
 */
#[Fillable([
    'actor_id',
    'action',
    'subject_type',
    'subject_id',
    'summary',
    'before',
    'after',
    'ip_address',
    'user_agent',
])]
final class AuditEvent extends Model
{
    /** @use HasFactory<AuditEventFactory> */
    use HasFactory;

    use HasUuids;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'actor_id' => 'string',
            'action' => 'string',
            'subject_type' => 'string',
            'subject_id' => 'string',
            'summary' => 'string',
            'before' => 'array',
            'after' => 'array',
            'ip_address' => 'string',
            'user_agent' => 'string',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
