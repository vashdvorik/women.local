<?php

namespace Tests\Feature\Subscription;

use App\Enums\Plan;
use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nutgram\Laravel\Facades\Telegram;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * После первой подписки (оплата или подарок администратора) профиль сам становится «видимым другим»; дальше видимость решает только администратор.
 */
class FirstPaymentVisibilityTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Telegram::fake();
        config(['webpayment.driver' => 'fake', 'webpayment.is_test' => true]);
        $this->registerFakeBank();
    }

    private function pay(BotUser $user, Plan $plan): void
    {
        $payment = $this->checkoutFor($user, $plan);

        $this->asParticipant($user)
            ->post(route('dev.fakebank.complete'), ['nivid' => $payment->invoice_id, 'decision' => 'pay'])
            ->assertRedirect();
    }

    public function test_the_first_payment_makes_the_profile_visible_to_others(): void
    {
        $user = BotUser::factory()->pending()->create();

        $this->pay($user, Plan::Community);

        $this->assertSame(BotUser::STATUS_APPROVED, $user->fresh()->status);
    }

    public function test_a_gift_from_the_administrator_also_makes_the_profile_visible_the_first_time(): void
    {
        $user = BotUser::factory()->pending()->create();

        $this->actingAsAdmin()
            ->post(route('admin.subscriptions.gift', $user), ['plan' => 'community'])
            ->assertRedirect();

        $this->assertSame(BotUser::STATUS_APPROVED, $user->fresh()->status);

        // Второй подарок видимость не трогает: администратор мог её уже скрыть
        $user->fresh()->update(['status' => BotUser::STATUS_PENDING]);
        $this->actingAsAdmin()->post(route('admin.subscriptions.gift', $user), ['plan' => 'active'])->assertRedirect();

        $this->assertSame(BotUser::STATUS_PENDING, $user->fresh()->status);
    }

    public function test_a_hidden_profile_stays_hidden_after_a_renewal(): void
    {
        $user = BotUser::factory()->pending()->create();
        $this->pay($user, Plan::Community);

        // Администратор скрыл профиль от других; продление и повышение пакета видимость не возвращают.
        $user->fresh()->update(['status' => BotUser::STATUS_PENDING]);
        $this->pay($user->fresh(), Plan::Community);
        $this->pay($user->fresh(), Plan::Active);

        $this->assertSame(BotUser::STATUS_PENDING, $user->fresh()->status);
    }

    public function test_a_blocked_participant_is_not_unblocked_by_a_payment(): void
    {
        $user = BotUser::factory()->pending()->create(['status' => BotUser::STATUS_REJECTED]);

        // Оплату блокировка не останавливает на уровне платёжного кода, но видимой профиль не становится
        $this->pay($user, Plan::Community);

        $this->assertSame(BotUser::STATUS_REJECTED, $user->fresh()->status);
    }
}
