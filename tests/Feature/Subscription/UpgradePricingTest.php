<?php

namespace Tests\Feature\Subscription;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Payments\WebPayment\Checkout;
use App\Services\Subscriptions\SubscriptionService;
use App\Services\Subscriptions\UpgradePricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Переход на тариф выше: неиспользованная часть оплаченного тарифа вычитается из цены. Тариф, который подарил
 * администратор, не пересчитывается.
 */
class UpgradePricingTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    /** Участница с оплаченным через банк тарифом на год. */
    private function paid(Plan $plan): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        $payment = Payment::create([
            'bot_user_id' => $user->id,
            'plan' => $plan->value,
            'months' => 12,
            'amount' => $plan->priceKopecks(),
            'currency' => '498',
            'invoice_id' => 'WHT'.strtoupper(substr(md5((string) $user->id.$plan->value), 0, 10)),
            'status' => Payment::STATUS_PAID,
            'is_test' => true,
            'driver' => 'fake',
            'expires_at' => now()->addMinutes(30),
            'paid_at' => now(),
        ]);
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT, $payment);

        return $user->fresh();
    }

    private function gifted(Plan $plan): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_ADMIN);

        return $user->fresh();
    }

    public function test_the_unused_part_of_a_paid_plan_is_subtracted_from_the_price(): void
    {
        $user = $this->paid(Plan::Community);

        $this->travel(6)->months();

        $quote = app(UpgradePricing::class)->quote($user->fresh(), Plan::Active);

        $this->assertSame(Plan::Active->priceKopecks(), $quote['full']);
        $this->assertSame(Plan::Community, $quote['from']);
        // Прошло около половины года: засчитывается примерно половина оплаты (600 руб.), в целых рублях
        $this->assertEqualsWithDelta(30000, $quote['credit'], 2500);
        $this->assertSame(0, $quote['credit'] % 100);
        $this->assertSame($quote['full'] - $quote['credit'], $quote['payable']);
        $this->assertCount(1, $quote['subscriptions']);
    }

    public function test_a_fresh_purchase_is_credited_almost_in_full(): void
    {
        $quote = app(UpgradePricing::class)->quote($this->paid(Plan::Community), Plan::Active);

        $this->assertGreaterThanOrEqual(Plan::Community->priceKopecks() - 100, $quote['credit']);
        $this->assertLessThanOrEqual(Plan::Community->priceKopecks(), $quote['credit']);
    }

    public function test_a_plan_gifted_by_the_administrator_is_not_recalculated(): void
    {
        $quote = app(UpgradePricing::class)->quote($this->gifted(Plan::Community), Plan::Active);

        $this->assertSame(0, $quote['credit']);
        $this->assertSame(Plan::Active->priceKopecks(), $quote['payable']);
        $this->assertTrue($quote['gift']);
        $this->assertSame([], $quote['subscriptions']);
    }

    public function test_there_is_no_credit_without_a_current_plan_or_for_a_renewal_or_lower_plan(): void
    {
        $pricing = app(UpgradePricing::class);
        $none = BotUser::factory()->approved()->create();
        $active = $this->paid(Plan::Active);

        $this->assertSame(0, $pricing->quote($none, Plan::Active)['credit']);
        $this->assertSame(0, $pricing->quote($active, Plan::Active)['credit'], 'продление');
        $this->assertSame(0, $pricing->quote($active, Plan::Community)['credit'], 'тариф ниже');
        $this->assertFalse($pricing->quote($none, Plan::Active)['gift']);
    }

    public function test_the_invoice_is_created_for_the_reduced_amount_and_remembers_what_was_credited(): void
    {
        $user = $this->paid(Plan::Community);
        $this->travel(3)->months();

        $payment = app(Checkout::class)->begin($user->fresh(), Plan::Active);

        $this->assertLessThan(Plan::Active->priceKopecks(), $payment->amount);
        $this->assertSame(Plan::Active->priceKopecks() - $payment->payload['upgrade']['credit'], $payment->amount);
        $this->assertSame('community', $payment->payload['upgrade']['from']);
        $this->assertNotEmpty($payment->payload['upgrade']['subscriptions']);
    }

    public function test_the_invoice_for_a_gifted_plan_has_the_full_price_and_no_credit_note(): void
    {
        $payment = app(Checkout::class)->begin($this->gifted(Plan::Community), Plan::Active);

        $this->assertSame(Plan::Active->priceKopecks(), $payment->amount);
        $this->assertNull($payment->payload);
    }

    public function test_after_payment_the_credited_periods_are_closed_and_do_not_come_back(): void
    {
        $user = $this->paid(Plan::Community);
        $ids = app(UpgradePricing::class)->quote($user, Plan::Active)['subscriptions'];
        $service = app(SubscriptionService::class);

        $service->grant($user, Plan::Active, 12, Subscription::SOURCE_PAYMENT);
        $service->spend($user, $ids);

        $user = $user->fresh();
        $this->assertSame(Plan::Active, $user->currentPlan());
        $this->assertSame(0, $user->subscriptions()->where('plan', 'community')->where('ends_at', '>', now()->addMinute())->count(), 'остаток Community не возвращается');

        // Когда закончится Active, Community не оживает
        $this->travel(13)->months();
        $this->assertSame(Plan::None, $user->fresh()->currentPlan());
    }

    public function test_the_subscription_page_shows_the_reduced_price_and_the_note(): void
    {
        $user = $this->paid(Plan::Community);
        $this->travel(6)->months();
        $user = $user->fresh();

        $quote = app(UpgradePricing::class)->quote($user, Plan::Active);

        $this->withHeader('Accept-Language', 'ru')
            ->withSession($this->participantSession($user))
            ->get(route('account.subscription'))
            ->assertOk()
            ->assertSee(UpgradePricing::label($quote['payable']), false)
            ->assertSee('Учтён неиспользованный остаток пакета Community', false);
    }
}
