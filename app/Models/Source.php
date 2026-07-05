<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SourceType;
use Carbon\CarbonInterface;
use Database\Factories\SourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read SourceType $type
 * @property-read string $title
 * @property-read string|null $authors
 * @property-read string|null $edition
 * @property-read int|null $year
 * @property-read string|null $publisher
 * @property-read string|null $isbn
 * @property-read string|null $doi
 * @property-read string|null $url
 * @property-read string|null $language
 * @property-read string|null $notes
 * @property-read string $created_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $createdBy
 */
#[Fillable([
    'type',
    'title',
    'authors',
    'edition',
    'year',
    'publisher',
    'isbn',
    'doi',
    'url',
    'language',
    'notes',
])]
final class Source extends Model
{
    /** @use HasFactory<SourceFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'type' => SourceType::class,
            'title' => 'string',
            'authors' => 'string',
            'edition' => 'string',
            'year' => 'integer',
            'publisher' => 'string',
            'isbn' => 'string',
            'doi' => 'string',
            'url' => 'string',
            'language' => 'string',
            'notes' => 'string',
            'created_by' => 'string',
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
}
