<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Entry;
use App\Models\EntrySection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntrySection>
 */
final class EntrySectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'key' => fake()->unique()->bothify('section_####'),
            'title' => fake()->words(2, true),
            'body' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function key(string $key): self
    {
        return $this->state(fn (array $attributes): array => [
            'key' => $key,
        ]);
    }

    public function sortOrder(int $sortOrder): self
    {
        return $this->state(fn (array $attributes): array => [
            'sort_order' => $sortOrder,
        ]);
    }
}
