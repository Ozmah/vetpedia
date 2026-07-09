<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Actions\GenerateUniqueEntrySlug;
use App\Enums\EntryStatus;
use App\Enums\EntryType;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entry>
 */
final class EntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->word().' '.fake()->unique()->word().' '.fake()->unique()->word();

        return [
            'type' => fake()->randomElement(EntryType::cases()),
            'status' => EntryStatus::Draft,
            'title' => $title,
            'slug' => resolve(GenerateUniqueEntrySlug::class)->handle($title),
            'summary' => fake()->sentence(),
            'warnings' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
            'approved_by' => null,
            'approved_at' => null,
            'archived_at' => null,
        ];
    }

    public function approved(?User $approver = null): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EntryStatus::VetApproved,
            'approved_by' => $approver instanceof User ? $approver->id : User::factory()->admin(),
            'approved_at' => now(),
        ]);
    }

    public function archived(): self
    {
        return $this->state(fn (array $attributes): array => [
            'archived_at' => now(),
        ]);
    }

    public function type(EntryType $type): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }

    public function status(EntryStatus $status): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
        ]);
    }
}
