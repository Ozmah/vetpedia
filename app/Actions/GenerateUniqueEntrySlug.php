<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Entry;
use Illuminate\Support\Str;

final readonly class GenerateUniqueEntrySlug
{
    public function handle(string $title, ?Entry $ignore = null): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'entry';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while ($this->exists($slug, $ignore)) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function exists(string $slug, ?Entry $ignore): bool
    {
        $ignoreKey = $ignore instanceof Entry ? $ignore->getKey() : null;

        return Entry::query()
            ->where('slug', $slug)
            ->when($ignoreKey !== null, fn ($query) => $query->whereKeyNot($ignoreKey))
            ->exists();
    }
}
