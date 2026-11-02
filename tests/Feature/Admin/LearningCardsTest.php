<?php

namespace Tests\Feature\Admin;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\SiteOpportunity;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Карточки из «Возможностей» с галочкой «В кабинете участниц, в «Обучении»» показываются в разделе кабинета «Обучение».
 */
class LearningCardsTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ru');
    }

    private function member(Plan $plan = Plan::Community): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    private function card(string $slug, string $title, array $flags = [], string $status = 'published'): SiteOpportunity
    {
        $item = SiteOpportunity::create(array_merge([
            'slug' => $slug,
            'status' => $status,
            'published_at' => $status === 'published' ? now()->subDay() : null,
            'show_on_site' => false,
            'show_in_cabinet' => false,
            'show_in_learning' => false,
        ], $flags));
        $item->translations()->create(['locale' => 'ru', 'title' => $title, 'excerpt' => "Кратко: {$title}"]);

        return $item;
    }

    public function test_the_form_has_three_places_and_learning_is_off_by_default(): void
    {
        $this->actingAsAdmin()->get(route('admin.opportunities.create'))->assertOk()
            ->assertSee('name="show_in_learning"', false)
            ->assertSee('В кабинете участниц, в «Обучении»', false);

        $this->assertFalse((new SiteOpportunity())->show_in_learning);
        $this->assertTrue((new SiteOpportunity())->show_in_cabinet);
    }

    public function test_the_flag_is_saved_from_the_admin_form(): void
    {
        $item = $this->card('grant', 'Грант', ['show_in_cabinet' => true]);

        $this->actingAsAdmin()->put(route('admin.opportunities.update', $item), [
            'translations' => ['ru' => ['title' => 'Грант', 'excerpt' => 'Кратко']],
            'show_on_site' => '0',
            'show_in_cabinet' => '1',
            'show_in_learning' => '1',
        ])->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertTrue($item->show_in_learning);
        $this->assertTrue($item->show_in_cabinet);
        $this->assertFalse($item->show_on_site);
    }

    public function test_the_learning_section_lists_only_published_cards_with_the_flag(): void
    {
        $this->card('only-learning', 'Только обучение', ['show_in_learning' => true]);
        $this->card('both', 'И там и там', ['show_in_learning' => true, 'show_in_cabinet' => true]);
        $this->card('only-possibilities', 'Только возможности', ['show_in_cabinet' => true]);
        $this->card('draft', 'Черновик обучения', ['show_in_learning' => true], 'draft');

        $this->asParticipant($this->member())->get(route('account.knowledge'))->assertOk()
            ->assertSee('Только обучение')
            ->assertSee('И там и там')
            ->assertSee(route('account.knowledge.show', 'only-learning'), false)
            ->assertDontSee('Только возможности')
            ->assertDontSee('Черновик обучения')
            ->assertDontSee('Обучающие модули скоро появятся');

        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $this->asParticipant($this->member())->get(route('account.possibilities.index'))->assertOk()
            ->assertSee('И там и там')->assertSee('Только возможности')->assertDontSee('Только обучение');
    }

    public function test_a_learning_card_opens_with_a_way_back_to_learning(): void
    {
        $this->card('lesson', 'Урок про деньги', ['show_in_learning' => true]);

        $this->asParticipant($this->member())->get(route('account.knowledge.show', 'lesson'))->assertOk()
            ->assertSee('Урок про деньги')
            ->assertSee('href="'.route('account.knowledge').'"', false)
            ->assertSee('Всё обучение');
    }

    public function test_a_card_without_the_flag_or_a_draft_does_not_open_in_learning(): void
    {
        $this->card('hidden', 'Скрытая', ['show_in_cabinet' => true]);
        $this->card('draft', 'Черновик', ['show_in_learning' => true], 'draft');
        $member = $this->member();

        $this->asParticipant($member)->get(route('account.knowledge.show', 'hidden'))->assertNotFound();
        $this->asParticipant($member)->get(route('account.knowledge.show', 'draft'))->assertNotFound();
    }

    public function test_cards_and_videos_are_shown_together_in_two_blocks(): void
    {
        $this->card('lesson', 'Урок про деньги', ['show_in_learning' => true]);
        $video = \App\Models\LearningVideo::create(['youtube_id' => 'aaaaaaaaaaa', 'youtube_url' => 'https://youtu.be/aaaaaaaaaaa', 'position' => 1, 'is_published' => true]);
        $video->translations()->create(['locale' => 'ru', 'title' => 'Видеоурок', 'description' => 'О видео']);

        $html = $this->asParticipant($this->member())->get(route('account.knowledge'))->assertOk()
            ->assertSeeInOrder(['Материалы', 'Урок про деньги', 'Видеоурок'])->getContent();

        $this->assertStringContainsString('youtube-nocookie.com/embed/aaaaaaaaaaa', $html);
    }

    public function test_without_a_plan_the_card_is_closed(): void
    {
        $this->card('lesson', 'Урок про деньги', ['show_in_learning' => true]);
        $user = BotUser::factory()->approved()->create();

        $this->asParticipant($user)->get(route('account.knowledge.show', 'lesson'))->assertRedirect();
    }

    public function test_the_telegram_draft_links_a_learning_only_card_into_the_cabinet(): void
    {
        $item = $this->card('lesson', 'Урок', ['show_in_learning' => true])->load('translations');
        $draft = app(\App\Services\Broadcasts\BroadcastDrafts::class)->create($item, \App\Models\Broadcast::AUDIENCE_APPROVED, null);

        $this->assertSame(route('account.knowledge.show', 'lesson'), $draft->buttons[0]['url']);
    }
}
