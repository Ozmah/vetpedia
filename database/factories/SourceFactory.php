<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SourceType;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Source>
 */
final class SourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(SourceType::cases()),
            'title' => fake()->sentence(4),
            'authors' => fake()->name().'; '.fake()->name(),
            'edition' => fake()->optional()->randomElement(['1st', '2nd', '3rd']),
            'year' => fake()->numberBetween(1980, (int) now()->format('Y')),
            'publisher' => fake()->optional()->company(),
            'isbn' => fake()->optional()->isbn13(),
            'doi' => fake()->optional()->bothify('10.####/????.####'),
            'url' => fake()->optional()->url(),
            'language' => fake()->randomElement(['es', 'en']),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory()->admin(),
        ];
    }

    public function type(SourceType $type): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }

    public function book(): self
    {
        return $this->type(SourceType::Book);
    }

    public function website(): self
    {
        return $this->type(SourceType::Website)->state(fn (array $attributes): array => [
            'authors' => null,
            'edition' => null,
            'publisher' => null,
            'isbn' => null,
            'doi' => null,
            'url' => fake()->url(),
        ]);
    }
}
