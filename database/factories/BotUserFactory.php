<?php

namespace Database\Factories;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BotUser>
 */
class BotUserFactory extends Factory
{
    /**
     * Участница, которая прошла регистрацию до конца: почта подтверждена, анкета (с телефоном) заполнена, Telegram
     * подключён. Тарифа и одобрения у неё нет, их добавляют состояния approved(), openPlan(), community(), private().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'telegram_id'          => $this->faker->unique()->numberBetween(100000, 999999999),
            'telegram_username'    => $this->faker->unique()->userName(),
            'email'                => $this->faker->unique()->safeEmail(),
            'email_verified_at'    => now(),
            'phone'                => '+373 69 '.$this->faker->numerify('######'),
            'profile_completed_at' => now(),
            'first_name'           => $this->faker->firstName(),
            'full_name'            => $this->faker->name(),
            'description'          => $this->faker->optional()->sentence(),
            'expectation'          => $this->faker->optional()->sentence(),
            'status'               => BotUser::STATUS_PENDING,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => BotUser::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    /** Только зарегистрировалась по почте (подтвердила её), анкету ещё не заполняла. */
    public function registered(): static
    {
        return $this->state(fn () => [
            'telegram_id'          => null,
            'telegram_username'    => null,
            'phone'                => null,
            'full_name'            => null,
            'profile_completed_at' => null,
        ]);
    }

    /** Без подключённого Telegram: уведомления идут письмом. */
    public function withoutTelegram(): static
    {
        return $this->state(fn () => ['telegram_id' => null, 'telegram_username' => null]);
    }

    /** Одобренная участница с оплаченным тарифом Open (600, на год). */
    public function openPlan(): static
    {
        return $this->approved()->afterCreating(function (BotUser $user): void {
            app(SubscriptionService::class)->grant($user, Plan::Open, 12, note: 'Фабрика');
        });
    }

    /** Одобренная участница с оплаченным Community (на год). */
    public function community(): static
    {
        return $this->approved()->afterCreating(function (BotUser $user): void {
            app(SubscriptionService::class)->grant($user, Plan::Community, 12, note: 'Фабрика');
        });
    }

    /** Одобренная участница с оплаченным Private (на год). */
    public function private(): static
    {
        return $this->approved()->afterCreating(function (BotUser $user): void {
            app(SubscriptionService::class)->grant($user, Plan::Private, 12, note: 'Фабрика');
        });
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => BotUser::STATUS_PENDING,
        ]);
    }
}
