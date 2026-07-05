<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntryStatus;
use App\Enums\EntryType;
use Carbon\CarbonInterface;
use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read EntryType $type
 * @property-read EntryStatus $status
 * @property-read string $title
 * @property-read string $slug
 * @property-read string|null $summary
 * @property-read string|null $warnings
 * @property-read string $created_by
 * @property-read string|null $updated_by
 * @property-read string|null $approved_by
 * @property-read CarbonInterface|null $approved_at
 * @property-read CarbonInterface|null $archived_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $createdBy
 * @property-read User|null $updatedBy
 * @property-read User|null $approvedBy
 */
#[Fillable([
    'type',
    'title',
    'summary',
    'warnings',
])]
final class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'raw',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'type' => EntryType::class,
            'status' => EntryStatus::class,
            'title' => 'string',
            'slug' => 'string',
            'summary' => 'string',
            'warnings' => 'string',
            'created_by' => 'string',
            'updated_by' => 'string',
            'approved_by' => 'string',
            'approved_at' => 'datetime',
            'archived_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->status === EntryStatus::VetApproved;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * @param  Builder<Entry>  $query
     */
    protected function scopeActive(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<Entry>  $query
     */
    protected function scopeArchived(Builder $query): void
    {
        $query->whereNotNull('archived_at');
    }

    /**
     * @param  Builder<Entry>  $query
     */
    protected function scopeApproved(Builder $query): void
    {
        $query->where('status', EntryStatus::VetApproved->value)
            ->whereNotNull('approved_by')
            ->whereNotNull('approved_at');
    }

    /**
     * @param  Builder<Entry>  $query
     */
    protected function scopeStatus(Builder $query, EntryStatus $status): void
    {
        $query->where('status', $status->value);
    }

    /**
     * @param  Builder<Entry>  $query
     */
    protected function scopeType(Builder $query, EntryType $type): void
    {
        $query->where('type', $type->value);
    }
}
