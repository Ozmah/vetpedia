<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\EntrySectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property-read string $id
 * @property-read string $entry_id
 * @property-read string $key
 * @property-read string $title
 * @property-read string $body
 * @property-read int $sort_order
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Entry $entry
 * @property-read Collection<int, Source> $sources
 */
#[Fillable([
    'entry_id',
    'key',
    'title',
    'body',
    'sort_order',
])]
final class EntrySection extends Model
{
    /** @use HasFactory<EntrySectionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'entry_id' => 'string',
            'key' => 'string',
            'title' => 'string',
            'body' => 'string',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Entry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * @return BelongsToMany<Source, $this, Pivot, 'citation'>
     */
    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(Source::class, 'section_sources')
            ->as('citation')
            ->withPivot(['locator', 'note', 'created_by'])
            ->withTimestamps();
    }

    /**
     * @param  Builder<EntrySection>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
