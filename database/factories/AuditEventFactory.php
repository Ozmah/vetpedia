<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditEvent;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuditEvent>
 */
final class AuditEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'action' => fake()->randomElement(['entry.created', 'entry.updated', 'auth.login']),
            'subject_type' => Entry::class,
            'subject_id' => Str::uuid()->toString(),
            'summary' => fake()->sentence(),
            'before' => null,
            'after' => [
                'title' => fake()->sentence(3),
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function anonymous(): self
    {
        return $this->state(fn (array $attributes): array => [
            'actor_id' => null,
        ]);
    }

    public function subject(string $type, ?string $id = null): self
    {
        return $this->state(fn (array $attributes): array => [
            'subject_type' => $type,
            'subject_id' => $id,
        ]);
    }
}
