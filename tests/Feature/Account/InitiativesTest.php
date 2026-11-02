<?php

namespace Tests\Feature\Account;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Opportunity;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * «Инициативы»: читают все с тарифом, добавляют только Active и выше, каждая публикация сначала проходит модерацию и
 * лишь после одобрения становится видна другим участницам.
 */
class InitiativesTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Mail::fake();
        $this->withHeader('Accept-Language', 'ru');
    }

    private function member(Plan $plan, bool $hidden = false, string $name = 'Анна Участница'): BotUser
    {
        $user = BotUser::factory()->approved()->create(['full_name' => $name, 'is_hidden' => $hidden]);
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    /** Запрос от имени участницы в её собственной сессии (у каждой участницы своя: один браузер — один вход). */
    private function as(BotUser $user): static
    {
        // Внутри одного теста сторож входа помнит прошлую участницу (в жизни каждый запрос — свежий процесс), поэтому сбрасываем.
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->asParticipant($user);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['type' => 'project', 'title' => 'Ищу партнёров для выставки', 'body' => 'Нужны соорганизаторы стенда.'], $overrides);
    }

    // ------------------------------------------------------------------ кто видит кнопку «Добавить»

    public function test_active_and_private_see_the_add_button(): void
    {
        foreach ([Plan::Active, Plan::Private] as $plan) {
            $user = $this->member($plan);

            $this->as($user)->get(route('account.opportunities.index'))->assertOk()
                ->assertSee('Добавить инициативу')
                ->assertSee(route('account.opportunities.create'), false);
        }
    }

    public function test_community_reads_but_has_no_add_button_and_is_told_how_to_get_one(): void
    {
        $user = $this->member(Plan::Community);

        $this->as($user)->get(route('account.opportunities.index'))->assertOk()
            ->assertDontSee('Добавить инициативу')
            ->assertDontSee(route('account.opportunities.create'), false)
            ->assertSee('Публиковать инициативы можно с тарифом')
            ->assertSee(route('account.subscription'), false);
    }

    public function test_the_hidden_account_only_reads(): void
    {
        $hidden = $this->member(Plan::Active, hidden: true);

        $this->as($hidden)->get(route('account.opportunities.index'))->assertOk()->assertDontSee('Добавить инициативу');
        $this->as($hidden)->post(route('account.opportunities.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_community_cannot_publish_even_with_a_forged_request(): void
    {
        $user = $this->member(Plan::Community);

        $this->as($user)->post(route('account.opportunities.store'), $this->payload())->assertRedirect();
        // Страница создания закрыта объяснением (раздел входит в тариф Active), формы на ней нет.
        $this->as($user)->get(route('account.opportunities.create'))->assertOk()
            ->assertDontSee('name="body"', false);

        $this->assertDatabaseCount('opportunities', 0);
    }

    // ------------------------------------------------------------------ модерация

    public function test_a_new_initiative_goes_to_moderation_and_is_hidden_from_others(): void
    {
        $author = $this->member(Plan::Active, name: 'Автор Инициативы');
        $reader = $this->member(Plan::Community, name: 'Читательница');

        $this->as($author)->post(route('account.opportunities.store'), $this->payload())
            ->assertRedirect(route('account.opportunities.index'))
            ->assertSessionHas('success', 'Публикация отправлена на модерацию. После одобрения она появится в кабинете.');

        $post = Opportunity::firstOrFail();
        $this->assertSame(Opportunity::STATUS_PENDING, $post->status);

        // Автор видит её с пометкой «на модерации», остальные не видят совсем.
        $this->as($author)->get(route('account.opportunities.index'))->assertOk()
            ->assertSee('Ищу партнёров для выставки')->assertSee('На модерации');
        $this->as($reader)->get(route('account.opportunities.index'))->assertOk()->assertDontSee('Ищу партнёров для выставки');
    }

    public function test_an_author_cannot_make_her_initiative_approved_by_sending_a_status(): void
    {
        $author = $this->member(Plan::Active);

        $this->as($author)->post(route('account.opportunities.store'), $this->payload(['status' => 'approved', 'moderated_at' => now()->toDateTimeString()]));

        $this->assertSame(Opportunity::STATUS_PENDING, Opportunity::firstOrFail()->status);
    }

    public function test_after_approval_the_initiative_is_published_for_everyone(): void
    {
        $author = $this->member(Plan::Active, name: 'Автор Инициативы');
        $reader = $this->member(Plan::Community, name: 'Читательница');
        $this->as($author)->post(route('account.opportunities.store'), $this->payload());
        $post = Opportunity::firstOrFail();

        $this->actingAsAdmin()->post(route('admin.member-posts.approve', $post))->assertSessionHas('success');

        $this->assertSame(Opportunity::STATUS_APPROVED, $post->fresh()->status);
        $this->assertNotNull($post->fresh()->moderated_at);
        $this->as($reader)->get(route('account.opportunities.index'))->assertOk()
            ->assertSee('Ищу партнёров для выставки')->assertSee('Автор Инициативы');
        $this->as($author)->get(route('account.opportunities.index'))->assertDontSee('На модерации');
    }

    public function test_a_rejected_initiative_stays_hidden_from_others_and_is_marked_for_the_author(): void
    {
        $author = $this->member(Plan::Active);
        $reader = $this->member(Plan::Active, name: 'Другая участница');
        $this->as($author)->post(route('account.opportunities.store'), $this->payload());
        $post = Opportunity::firstOrFail();

        $this->actingAsAdmin()->post(route('admin.member-posts.reject', $post))->assertSessionHas('success');

        $this->as($reader)->get(route('account.opportunities.index'))->assertDontSee('Ищу партнёров для выставки');
        $this->as($author)->get(route('account.opportunities.index'))->assertSee('Отклонено модератором');
    }

    public function test_an_approved_initiative_of_a_hidden_account_is_not_shown_to_others(): void
    {
        $hidden = $this->member(Plan::Active, hidden: true, name: 'Скрытая');
        $reader = $this->member(Plan::Active, name: 'Читательница');
        Opportunity::create(['bot_user_id' => $hidden->id, 'type' => 'event', 'title' => 'Закрытая встреча', 'body' => '...', 'status' => Opportunity::STATUS_APPROVED]);

        $this->as($reader)->get(route('account.opportunities.index'))->assertDontSee('Закрытая встреча');
    }

    public function test_the_author_can_delete_only_her_own_initiative(): void
    {
        $author = $this->member(Plan::Active);
        $other = $this->member(Plan::Active, name: 'Чужая');
        $this->as($author)->post(route('account.opportunities.store'), $this->payload());
        $post = Opportunity::firstOrFail();

        $this->as($other)->delete(route('account.opportunities.destroy', $post))->assertForbidden();
        $this->assertDatabaseCount('opportunities', 1);

        $this->as($author)->delete(route('account.opportunities.destroy', $post))->assertRedirect();
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_the_form_validates_the_fields(): void
    {
        $author = $this->member(Plan::Active);

        $this->as($author)->post(route('account.opportunities.store'), ['type' => 'bad', 'title' => '', 'body' => ''])
            ->assertSessionHasErrors(['type', 'title', 'body']);
        $this->as($author)->post(route('account.opportunities.store'), $this->payload(['contact_url' => 'не ссылка']))->assertSessionHasErrors('contact_url');
        $this->as($author)->post(route('account.opportunities.store'), $this->payload(['event_date' => '2020-01-01']))->assertSessionHasErrors('event_date');
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_the_admin_queue_shows_pending_initiatives(): void
    {
        $author = $this->member(Plan::Active, name: 'Автор Инициативы');
        $this->as($author)->post(route('account.opportunities.store'), $this->payload());

        $this->actingAsAdmin()->get(route('admin.member-posts.index', ['status' => 'pending']))->assertOk()
            ->assertSee('Ищу партнёров для выставки')->assertSee('Автор Инициативы');
    }
}
