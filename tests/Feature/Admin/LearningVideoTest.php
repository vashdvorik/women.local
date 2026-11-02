<?php

namespace Tests\Feature\Admin;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\LearningVideo;
use App\Models\Subscription;
use App\Models\Video;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Обучающие видео для кабинета («Кабинеты участниц → Обучающие видео»): ведутся в админке отдельно от видео открытого сайта,
 * участницы с любым платным тарифом видят включённые в разделе «Обучение».
 */
class LearningVideoTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ru');
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_published' => '1',
            'translations' => [
                'ru' => ['title' => 'Как вести финансы', 'description' => 'Основы учёта для небольшого бизнеса.'],
                'en' => ['title' => 'How to manage finances', 'description' => ''],
                'ro' => ['title' => '', 'description' => ''],
            ],
        ], $overrides);
    }

    private function member(Plan $plan): BotUser
    {
        $user = BotUser::factory()->approved()->create();
        app(SubscriptionService::class)->grant($user, $plan, 12, Subscription::SOURCE_PAYMENT);

        return $user->fresh();
    }

    private function make(string $youtube, string $title, bool $published = true, int $position = 1): LearningVideo
    {
        $video = LearningVideo::create(['youtube_id' => $youtube, 'youtube_url' => "https://youtu.be/{$youtube}", 'is_published' => $published, 'position' => $position]);
        $video->translations()->create(['locale' => 'ru', 'title' => $title, 'description' => "Описание: {$title}"]);

        return $video;
    }

    // ------------------------------------------------------------------ админка

    public function test_the_menu_item_lives_in_the_content_group_and_the_pages_open(): void
    {
        $video = $this->make('aaaaaaaaaaa', 'Первое видео');

        $this->actingAsAdmin()->get(route('admin.learning-videos.index'))->assertOk()
            ->assertSee('Обучающие видео')->assertSee('Первое видео');
        $this->actingAsAdmin()->get(route('admin.learning-videos.create'))->assertOk()->assertSee('Показывать в кабинете');
        $this->actingAsAdmin()->get(route('admin.learning-videos.edit', $video))->assertOk()->assertSee('Первое видео');

        // Меню одно: пункт «Обучающие видео» стоит в группе «Контент» и виден на страницах любого раздела.
        $this->actingAsAdmin()->get(route('admin.events.index'))->assertSee(route('admin.learning-videos.index'), false);
    }

    public function test_a_video_is_created_with_texts_in_three_languages_and_goes_to_the_end(): void
    {
        $this->make('aaaaaaaaaaa', 'Первое видео', position: 5);

        $this->actingAsAdmin()->post(route('admin.learning-videos.store'), $this->payload())->assertRedirect(route('admin.learning-videos.index'));

        $video = LearningVideo::where('youtube_id', 'dQw4w9WgXcQ')->firstOrFail();
        $this->assertSame(6, $video->position);
        $this->assertTrue($video->is_published);
        $this->assertSame('Как вести финансы', $video->rawTranslation('ru')->title);
        $this->assertSame('Основы учёта для небольшого бизнеса.', $video->rawTranslation('ru')->description);
        $this->assertSame('How to manage finances', $video->rawTranslation('en')->title);
        $this->assertNull($video->rawTranslation('ro'), 'пустой язык не хранится: покажется русский');
    }

    public function test_the_link_the_russian_title_and_uniqueness_are_validated(): void
    {
        $this->actingAsAdmin()->post(route('admin.learning-videos.store'), $this->payload(['youtube_url' => 'не ссылка']))->assertSessionHasErrors('youtube_url');
        $this->actingAsAdmin()->post(route('admin.learning-videos.store'), $this->payload(['translations' => ['ru' => ['title' => '']]]))->assertSessionHasErrors('translations.ru.title');

        $this->actingAsAdmin()->post(route('admin.learning-videos.store'), $this->payload())->assertSessionHasNoErrors();
        $this->actingAsAdmin()->post(route('admin.learning-videos.store'), $this->payload())->assertSessionHasErrors('youtube_url');

        $this->assertDatabaseCount('learning_videos', 1);
    }

    public function test_a_video_can_be_edited_hidden_and_shown_again(): void
    {
        $video = $this->make('aaaaaaaaaaa', 'Старое название');

        $this->actingAsAdmin()->put(route('admin.learning-videos.update', $video), $this->payload(['youtube_url' => 'https://youtu.be/aaaaaaaaaaa', 'is_published' => '0', 'translations' => ['ru' => ['title' => 'Новое название']]]))
            ->assertRedirect(route('admin.learning-videos.index'));

        $video->refresh()->load('translations');
        $this->assertFalse($video->is_published, 'галочка снята: черновик');
        $this->assertSame('Новое название', $video->rawTranslation('ru')->title);

        $this->actingAsAdmin()->post(route('admin.learning-videos.toggle', $video))->assertRedirect();
        $this->assertTrue($video->fresh()->is_published);
        $this->actingAsAdmin()->post(route('admin.learning-videos.toggle', $video))->assertRedirect();
        $this->assertFalse($video->fresh()->is_published);
    }

    public function test_the_order_can_be_changed_by_dragging_and_by_arrows_and_a_video_can_be_deleted(): void
    {
        $a = $this->make('aaaaaaaaaaa', 'A', position: 1);
        $b = $this->make('bbbbbbbbbbb', 'B', position: 2);
        $c = $this->make('ccccccccccc', 'C', position: 3);

        $this->actingAsAdmin()->postJson(route('admin.learning-videos.reorder'), ['ids' => [$c->id, $a->id, $b->id]])->assertOk();
        $this->assertSame([$c->id, $a->id, $b->id], LearningVideo::ordered()->pluck('id')->all());

        $this->actingAsAdmin()->post(route('admin.learning-videos.move', $b), ['direction' => 'up'])->assertRedirect();
        $this->assertSame([$c->id, $b->id, $a->id], LearningVideo::ordered()->pluck('id')->all());

        $this->actingAsAdmin()->delete(route('admin.learning-videos.destroy', $a))->assertRedirect();
        $this->assertDatabaseCount('learning_videos', 2);
        $this->assertDatabaseCount('learning_video_translations', 2);
    }

    public function test_only_the_administrator_can_manage_them(): void
    {
        $this->get(route('admin.learning-videos.index'))->assertRedirect();
        $member = $this->member(Plan::Active);
        $this->withSession($this->participantSession($member))->post(route('admin.learning-videos.store'), $this->payload())->assertRedirect();
        $this->assertDatabaseCount('learning_videos', 0);
    }

    // ------------------------------------------------------------------ кабинет

    public function test_members_with_any_paid_plan_see_the_published_videos_in_the_learning_section(): void
    {
        $this->make('aaaaaaaaaaa', 'Опубликованное видео');
        $this->make('bbbbbbbbbbb', 'Черновик видео', published: false, position: 2);

        foreach ([Plan::Community, Plan::Active, Plan::Private] as $plan) {
            $this->app['auth']->forgetGuards();
            $this->flushSession();

            $this->asParticipant($this->member($plan))->get(route('account.knowledge'))->assertOk()
                ->assertSee('Опубликованное видео')
                ->assertSee('Описание: Опубликованное видео')
                ->assertSee('youtube-nocookie.com/embed/aaaaaaaaaaa', false)
                ->assertDontSee('Черновик видео')
                ->assertDontSee('bbbbbbbbbbb');
        }
    }

    public function test_the_player_uses_the_privacy_friendly_youtube_domain(): void
    {
        $this->make('aaaaaaaaaaa', 'Видео');

        $html = $this->asParticipant($this->member(Plan::Community))->get(route('account.knowledge'))->assertOk()->getContent();

        $this->assertStringContainsString('youtube-nocookie.com/embed/aaaaaaaaaaa', $html);
        $this->assertStringNotContainsString('www.youtube.com/embed', $html);
    }

    public function test_the_text_follows_the_language_and_falls_back_to_russian(): void
    {
        $video = $this->make('aaaaaaaaaaa', 'Русское название');
        $video->translations()->create(['locale' => 'en', 'title' => 'English title', 'description' => null]);
        $member = $this->member(Plan::Community);

        $this->asParticipant($member)->get(route('account.knowledge', ['lang' => 'en']))->assertOk()
            ->assertSee('English title')
            ->assertSee('Описание: Русское название', false); // описания на английском нет: подставился русский

        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->asParticipant($member)->get(route('account.knowledge', ['lang' => 'ro']))->assertOk()->assertSee('Русское название');
    }

    public function test_without_videos_the_section_keeps_its_coming_soon_note(): void
    {
        $this->make('aaaaaaaaaaa', 'Скрытое', published: false);

        $this->asParticipant($this->member(Plan::Community))->get(route('account.knowledge'))->assertOk()
            ->assertSee('Обучающие модули скоро появятся')
            ->assertDontSee('Скрытое');
    }

    public function test_a_participant_without_a_plan_does_not_get_the_videos(): void
    {
        $this->make('aaaaaaaaaaa', 'Закрытое видео');
        $user = BotUser::factory()->approved()->create();

        $this->asParticipant($user)->get(route('account.knowledge'))->assertRedirect();
        $this->assertStringNotContainsString('Закрытое видео', (string) $this->asParticipant($user)->get(route('account.knowledge'))->getContent());
    }

    // ------------------------------------------------------------------ открытый сайт не затронут

    public function test_the_videos_of_the_open_site_and_the_learning_ones_are_independent(): void
    {
        $this->make('aaaaaaaaaaa', 'Только для кабинета');

        $public = Video::create(['youtube_id' => 'zzzzzzzzzzz', 'youtube_url' => 'https://youtu.be/zzzzzzzzzzz', 'position' => 1]);
        $public->translations()->create(['locale' => 'ru', 'title' => 'Видео для открытого сайта']);

        $site = $this->get(route('media.videos'))->assertOk();
        $site->assertSee('Видео для открытого сайта')->assertDontSee('Только для кабинета');

        $cabinet = $this->asParticipant($this->member(Plan::Community))->get(route('account.knowledge'))->assertOk();
        $cabinet->assertSee('Только для кабинета')->assertDontSee('Видео для открытого сайта');
    }
}
