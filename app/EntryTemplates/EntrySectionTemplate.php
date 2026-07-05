<?php

declare(strict_types=1);

namespace App\EntryTemplates;

final readonly class EntrySectionTemplate
{
    public function __construct(
        public string $key,
        public string $title,
        public int $sortOrder,
        public string $guidance,
    ) {}

    /**
     * @return array{key: string, title: string, sort_order: int, guidance: string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'sort_order' => $this->sortOrder,
            'guidance' => $this->guidance,
        ];
    }
}
