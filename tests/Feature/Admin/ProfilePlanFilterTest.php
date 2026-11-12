<?php

namespace Tests\Feature\Admin;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** /admin/profiles: колонка «Пакет» и фильтр по пакетам. */
class ProfilePlanFilterTest extends TestCase
{
    use RefreshDatabase;

    private function member(string $name, ?Plan $plan = null, bool $expired = false): BotUser
    {
        $user = BotUser::factory()->approved()->create(['full_name' => $name, 'profile_completed_at' => now()]);

        if ($plan) {
            app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

            if ($expired) {
                $user->forceFill(['plan_ends_at' => now()->subDay()])->save();
            }
        }

        return $user;
    }

    public function test_the_list_shows_the_plan_and_filters_by_it(): void
    {
        $this->member('Анна Сообщество', Plan::Community);
        $this->member('Бэлла Актив', Plan::Active);
        $this->member('Вера Приват', Plan::Private);
        $this->member('Галина Истекла', Plan::Active, expired: true);
        $this->member('Дарья Без');

        $this->actingAsAdmin()->get(route('admin.profiles.index'))->assertOk()
            ->assertSee('Community')->assertSee('Private')->assertSee('Без пакета');

        $names = fn (string $plan) => $this->actingAsAdmin()->get(route('admin.profiles.index', ['plan' => $plan]))->assertOk();

        $names('active')->assertSee('Бэлла Актив')->assertDontSee('Анна Сообщество')->assertDontSee('Галина Истекла')->assertDontSee('Дарья Без');
        $names('private')->assertSee('Вера Приват')->assertDontSee('Бэлла Актив');
        $names('expired')->assertSee('Галина Истекла')->assertDontSee('Бэлла Актив')->assertDontSee('Дарья Без');
        $names('none')->assertSee('Дарья Без')->assertDontSee('Бэлла Актив')->assertDontSee('Вера Приват');
        $names('чепуха')->assertSee('Анна Сообщество')->assertSee('Дарья Без');
    }

    public function test_the_export_follows_the_plan_filter(): void
    {
        $this->member('Бэлла Актив', Plan::Active);
        $this->member('Дарья Без');

        $csv = $this->actingAsAdmin()->get(route('admin.profiles.export', ['plan' => 'active']))->assertOk()->streamedContent();

        $this->assertStringContainsString('Бэлла Актив', $csv);
        $this->assertStringNotContainsString('Дарья Без', $csv);
    }
}
