<?php

namespace Tests\Feature\Subscription;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Кто что видит между тарифами: Open (600) читает возможности и обучение, но никого не ищет; Community (5000) и
 * Private (19 000) видят профили всех платных тарифов вместе с телефонами.
 */
class PlanVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Http::fake();
    }

    public function test_open_reads_opportunities_and_learning_but_has_no_search_catalog_or_assistant(): void
    {
        $open = BotUser::factory()->community()->create();
        $other = BotUser::factory()->active()->create();

        $this->withSession($this->participantSession($open))->get(route('account.opportunities.index'))->assertOk()->assertDontSee(route('account.opportunities.create'));
        $this->withSession($this->participantSession($open))->get(route('account.knowledge'))->assertOk();

        foreach (['account.people', 'account.matches', 'account.search', 'account.assistant', 'account.opportunities.create'] as $route) {
            $this->withSession($this->participantSession($open))->get(route($route))->assertOk()
                ->assertSee('WOMEN’S HUB COMMUNITY', false);
        }

        $this->withSession($this->participantSession($open))->get(route('account.people.show', $other))->assertOk()
            ->assertDontSee($other->phone);
        $this->withSession($this->participantSession($open))->postJson(route('account.assistant.message'), ['message' => 'привет'])->assertForbidden();
    }

    public function test_community_sees_every_paid_plan_with_phones_but_not_unpaid_or_hidden_or_unapproved_people(): void
    {
        $viewer = BotUser::factory()->active()->create();
        $openMember = BotUser::factory()->community()->create(['full_name' => 'Открытая Участница', 'phone' => '+373 69 000 111']);
        $privateMember = BotUser::factory()->private()->create(['full_name' => 'Приватная Участница', 'phone' => '+373 69 000 222']);
        $noPlan = BotUser::factory()->approved()->create(['full_name' => 'Без Тарифа Участница']);
        $hidden = BotUser::factory()->active()->create(['full_name' => 'Скрытая Участница', 'is_hidden' => true]);
        $pending = BotUser::factory()->pending()->create(['full_name' => 'Непроверенная Участница']);

        $html = $this->withSession($this->participantSession($viewer))->get(route('account.people'))->assertOk()->getContent();

        $this->assertStringContainsString('Открытая Участница', $html, 'профиль тарифа Open виден Community');
        $this->assertStringContainsString('Приватная Участница', $html);
        $this->assertStringContainsString('tel:+37369000111', $html, 'телефон виден');
        $this->assertStringContainsString('tel:+37369000222', $html);
        $this->assertStringNotContainsString('Без Тарифа Участница', $html);
        $this->assertStringNotContainsString('Скрытая Участница', $html);
        $this->assertStringNotContainsString('Непроверенная Участница', $html);

        $this->withSession($this->participantSession($viewer))->get(route('account.people.show', $openMember))->assertOk()->assertSee('tel:+37369000111', false);
        $this->withSession($this->participantSession($viewer))->get(route('account.people.show', $noPlan))->assertNotFound();
        $this->withSession($this->participantSession($viewer))->get(route('account.people.show', $hidden))->assertNotFound();
        $this->withSession($this->participantSession($viewer))->get(route('account.people.show', $pending))->assertNotFound();
        $this->assertNotNull($privateMember);
    }

    public function test_private_sees_the_same_as_community_and_open_members_are_in_the_broadcast_audience(): void
    {
        $viewer = BotUser::factory()->private()->create();
        $openMember = BotUser::factory()->community()->create(['full_name' => 'Открытая Участница']);

        $this->withSession($this->participantSession($viewer))->get(route('account.people'))->assertOk()->assertSee('Открытая Участница');

        $this->assertTrue(BotUser::members()->whereKey($openMember->id)->exists());
        $this->assertTrue(BotUser::discoverable()->whereKey($openMember->id)->exists());
    }

    public function test_the_profile_shows_the_phone_field_and_the_telegram_connection(): void
    {
        $user = BotUser::factory()->community()->withoutTelegram()->create();

        $this->withSession($this->participantSession($user))->get(route('account.profile'))->assertOk()
            ->assertSee(route('account.telegram.connect'), false);

        $this->withSession($this->participantSession($user))->get(route('account.profile.edit'))->assertOk()->assertSee('name="phone"', false);

        $this->withSession($this->participantSession($user))->post(route('account.profile.update'), ['full_name' => 'Анна', 'phone' => ''])
            ->assertSessionHasErrors('phone');
    }
}
