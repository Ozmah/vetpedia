<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Actions\NormalizeEntryAliasName;
use App\Models\Entry;
use App\Models\EntryAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntryAlias>
 */
final class EntryAliasFactory extends Factory
{
    public function configure(): self
    {
        return $this->afterMaking(function (EntryAlias $alias): void {
            if ($alias->getAttribute('normalized_name') === null) {
                $name = $alias->getAttribute('name');

                $alias->setAttribute(
                    'normalized_name',
                    resolve(NormalizeEntryAliasName::class)->handle(is_string($name) ? $name : ''),
                );
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'name' => fake()->unique()->words(2, true),
            'normalized_name' => null,
        ];
    }

    public function name(string $name): self
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $name,
            'normalized_name' => resolve(NormalizeEntryAliasName::class)->handle($name),
        ]);
    }
}
