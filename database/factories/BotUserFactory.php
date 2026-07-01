<?php

namespace Database\Factories;

use App\Models\BotUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BotUser>
 */
class BotUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'telegram_id'       => $this->faker->unique()->numberBetween(100000, 999999999),
            'telegram_username' => $this->faker->unique()->userName(),
            'first_name'        => $this->faker->firstName(),
            'full_name'         => $this->faker->name(),
            'description'       => $this->faker->optional()->sentence(),
            'expectation'       => $this->faker->optional()->sentence(),
            'status'            => \App\Models\BotUser::STATUS_PENDING,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => \App\Models\BotUser::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => \App\Models\BotUser::STATUS_PENDING,
        ]);
    }
}
