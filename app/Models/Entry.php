<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntryStatus;
use App\Enums\EntryType;
use App\Policies\EntryPolicy;
use Carbon\CarbonInterface;
use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
 * @property-read Collection<int, EntrySection> $sections
 * @property-read Collection<int, EntryAlias> $aliases
 * @property-read Collection<int, Species> $species
 * @property-read Collection<int, Source> $sources
 */
#[UsePolicy(EntryPolicy::class)]
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
        'status' => 'draft',
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

    /**
     * @return HasMany<EntrySection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(EntrySection::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<EntryAlias, $this>
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(EntryAlias::class);
    }

    /**
     * @return BelongsToMany<Species, $this>
     */
    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'entry_species')->withTimestamps();
    }

    /**
     * @return BelongsToMany<Source, $this, Pivot, 'citation'>
     */
    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(Source::class, 'entry_sources')
            ->as('citation')
            ->withPivot(['locator', 'note', 'created_by'])
            ->withTimestamps();
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
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<Entry>  $query
     */
    #[Scope]
    protected function archived(Builder $query): void
    {
        $query->whereNotNull('archived_at');
    }

    /**
     * @param  Builder<Entry>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', EntryStatus::VetApproved->value)
            ->whereNotNull('approved_by')
            ->whereNotNull('approved_at');
    }

    /**
     * @param  Builder<Entry>  $query
     */
    #[Scope]
    protected function status(Builder $query, EntryStatus $status): void
    {
        $query->where('status', $status->value);
    }

    /**
     * @param  Builder<Entry>  $query
     */
    #[Scope]
    protected function type(Builder $query, EntryType $type): void
    {
        $query->where('type', $type->value);
    }
}
