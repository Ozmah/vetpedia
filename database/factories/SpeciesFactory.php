<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Species>
 */
final class SpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }

    public function named(string $name): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }
}
