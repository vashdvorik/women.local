<?php

namespace Tests\Feature\Account;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Event;
use App\Models\LearningVideo;
use App\Models\Opportunity;
use App\Models\Post;
use App\Models\SiteOpportunity;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Главная кабинета: пакет и заполненность профиля, короткие плитки, рекомендации, «Что нового» и ближайшие события.
 */
class HomeConceptTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ru');
    }

    private function member(Plan $plan, array $attributes = [], int $months = 12): BotUser
    {
        $user = BotUser::factory()->approved()->create($attributes);
        app(SubscriptionService::class)->grant($user, $plan, $months, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    private function home(BotUser $user): string
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        return $this->asParticipant($user)->get(route('account.index'))->assertOk()->getContent();
    }

    public function test_the_plan_and_its_end_date_are_shown_and_renewal_is_offered_only_near_the_end(): void
    {
        $far = $this->member(Plan::Active);
        $html = $this->home($far);
        $this->assertStringContainsString('WOMEN’S HUB ACTIVE', $html);
        $this->assertStringContainsString('Действует до', $html);
        $this->assertStringNotContainsString('Продлить пакет', $html);

        $near = $this->member(Plan::Active, months: 1);
        $near->forceFill(['plan_ends_at' => now()->addDays(10)])->save();
        $this->assertStringContainsString('Продлить пакет', $this->home($near->fresh()));
    }

    public function test_profile_completeness_counts_the_filled_fields_and_names_the_next_step(): void
    {
        $empty = $this->member(Plan::Community, ['avatar_path' => null, 'description' => null, 'expectation' => null, 'phone' => null, 'telegram_id' => null, 'telegram_username' => null]);
        $html = $this->home($empty);
        $this->assertStringContainsString('0%', $html);
        $this->assertStringContainsString('Добавьте фото', $html);

        $full = $this->member(Plan::Community, ['avatar_path' => 'a.jpg', 'description' => 'О себе', 'expectation' => 'Ищу', 'phone' => '+37360000000', 'telegram_username' => 'olga']);
        $html = $this->home($full);
        $this->assertStringContainsString('100%', $html);
        $this->assertStringContainsString('Профиль заполнен полностью', $html);
    }

    public function test_the_tiles_are_short_navigation_without_the_old_long_explanations(): void
    {
        $html = $this->home($this->member(Plan::Active));

        $this->assertStringContainsString('home-tile', $html);
        $this->assertStringNotContainsString('Как пользоваться:', $html);
        $this->assertStringNotContainsString('Гранты, конкурсы, программы и предложения партнёров', $html);
    }

    public function test_community_sees_locked_tiles_no_recommendations_and_the_upgrade_invitation(): void
    {
        $html = $this->home($this->member(Plan::Community));

        $this->assertStringContainsString('home-tile--locked', $html);
        $this->assertStringNotContainsString('Мои рекомендации', $html);
        $this->assertStringContainsString('Что откроет ACTIVE', $html);
        $this->assertStringContainsString(route('account.subscription'), $html);
    }

    public function test_active_sees_the_recommendations_block_and_no_upgrade_invitation(): void
    {
        $html = $this->home($this->member(Plan::Active));

        $this->assertStringContainsString('Мои рекомендации', $html);
        $this->assertStringContainsString('Рекомендации появятся', $html);   // эмбеддингов в тестах нет: подсказка вместо людей
        $this->assertStringNotContainsString('Что откроет ACTIVE', $html);
    }

    public function test_whats_new_shows_only_fresh_opportunities_and_learning_materials(): void
    {
        $grant = SiteOpportunity::create(['slug' => 'grant', 'status' => 'published', 'published_at' => now()->subDay(), 'show_in_cabinet' => true]);
        $grant->translations()->create(['locale' => 'ru', 'title' => 'Грант на старт', 'excerpt' => 'Описание гранта']);
        $lesson = Post::create(['slug' => 'lesson', 'status' => 'published', 'published_at' => now()->subHour(), 'show_on_site' => false, 'show_in_learning' => true]);
        $lesson->translations()->create(['locale' => 'ru', 'title' => 'Урок про деньги', 'excerpt' => 'Основы']);
        $hidden = SiteOpportunity::create(['slug' => 'draft', 'status' => 'draft', 'show_in_cabinet' => true]);
        $hidden->translations()->create(['locale' => 'ru', 'title' => 'Чужой черновик']);
        $video = LearningVideo::create(['youtube_id' => 'aaaaaaaaaaa', 'youtube_url' => 'https://youtu.be/aaaaaaaaaaa', 'position' => 1, 'is_published' => true]);
        $video->translations()->create(['locale' => 'ru', 'title' => 'Видеоурок', 'description' => 'Про продажи']);
        $author = $this->member(Plan::Community);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'project', 'title' => 'Ищу партнёров', 'body' => 'Совместный проект', 'status' => 'approved']);
        $this->member(Plan::Active, ['full_name' => 'Новенькая Участница']);

        foreach ([Plan::Community, Plan::Active] as $plan) {
            $html = $this->home($this->member($plan));
            $block = substr($html, strpos($html, 'home-news-title'));
            $block = substr($block, 0, strpos($block, '</section>'));

            foreach (['Грант на старт', 'Урок про деньги', 'Видеоурок'] as $text) {
                $this->assertStringContainsString($text, $block);
            }
            $this->assertStringNotContainsString('Чужой черновик', $block);
            $this->assertStringNotContainsString('Новенькая Участница', $block, 'новых участниц в ленте нет');
            $this->assertStringNotContainsString('Ищу партнёров', $block, 'инициатив в ленте нет');
        }
    }

    public function test_upcoming_events_are_future_platform_news_only(): void
    {
        $future = Event::create(['slug' => 'forum', 'starts_at' => now()->addDays(5), 'is_published' => true, 'position' => 1, 'tone' => 'blue', 'url' => 'https://example.com']);
        $future->translations()->create(['locale' => 'ru', 'title' => 'Бизнес-форум']);
        $past = Event::create(['slug' => 'old', 'starts_at' => now()->subDays(5), 'is_published' => true, 'position' => 2, 'tone' => 'blue']);
        $past->translations()->create(['locale' => 'ru', 'title' => 'Прошедшее событие']);
        $draft = Event::create(['slug' => 'draft', 'starts_at' => now()->addDays(2), 'is_published' => false, 'position' => 3, 'tone' => 'blue']);
        $draft->translations()->create(['locale' => 'ru', 'title' => 'Неопубликованное событие']);
        $author = $this->member(Plan::Community);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'event', 'title' => 'Встреча участниц', 'body' => 'Кофе', 'event_date' => now()->addDays(9), 'location' => 'Кишинёв', 'status' => 'approved']);

        $html = $this->home($this->member(Plan::Community));
        $events = substr($html, strpos($html, 'home-events-title'));
        $events = substr($events, 0, strpos($events, '</section>'));

        $this->assertStringContainsString('Ближайшие события', $html);
        $this->assertStringContainsString('Бизнес-форум', $events);
        $this->assertStringNotContainsString('Прошедшее событие', $html);
        $this->assertStringNotContainsString('Неопубликованное событие', $html);
        $this->assertStringNotContainsString('Встреча участниц', $events, 'инициативы идут отдельным блоком');
    }

    public function test_initiatives_are_a_separate_block_with_approved_ones_only(): void
    {
        $author = $this->member(Plan::Community);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'event', 'title' => 'Встреча участниц', 'body' => 'Кофе и знакомство', 'event_date' => now()->addDays(9), 'location' => 'Кишинёв', 'status' => 'approved']);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'project', 'title' => 'Ищу партнёров', 'body' => 'Совместный проект', 'status' => 'approved']);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'project', 'title' => 'Ждёт проверки', 'body' => 'Текст', 'status' => 'pending']);

        foreach ([Plan::Community, Plan::Active] as $plan) {
            $html = $this->home($this->member($plan));

            $this->assertStringContainsString('Инициативы участниц', $html);
            $block = substr($html, strpos($html, 'home-initiatives-title'));
            $block = substr($block, 0, strpos($block, '</section>'));

            $this->assertStringContainsString('Встреча участниц', $block);
            $this->assertStringContainsString('Кишинёв', $block);
            $this->assertStringContainsString('Ищу партнёров', $block, 'без даты и места карточка тоже выводится');
            $this->assertStringNotContainsString('Ждёт проверки', $html);
            $this->assertStringContainsString(route('account.opportunities.index'), $block);
        }
    }

    public function test_empty_blocks_are_not_rendered(): void
    {
        $html = $this->home($this->member(Plan::Community));

        $this->assertStringNotContainsString('Ближайшие события', $html);
        $this->assertStringNotContainsString('Что нового', $html);
        $this->assertStringNotContainsString('Инициативы участниц', $html);
    }

    public function test_the_home_renders_in_english_and_romanian(): void
    {
        $user = $this->member(Plan::Active);

        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->withHeader('Accept-Language', 'en');
        $this->asParticipant($user)->get(route('account.index', ['lang' => 'en']))->assertOk()->assertSee('My recommendations')->assertSee('Your plan');

        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->asParticipant($user)->get(route('account.index', ['lang' => 'ro']))->assertOk()->assertSee('Recomandările mele');
    }
}
