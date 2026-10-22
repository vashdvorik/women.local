<?php

namespace Tests\Feature\Admin;

use App\Models\Broadcast;
use App\Models\Event;
use App\Models\Post;
use App\Models\SiteOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Галочка «Создать черновик для рассылки» в формах новости, публикации и возможности: после сохранения в разделе
 * «Рассылка» появляется черновик (текст на языках, обложка, кнопка со ссылкой на страницу). Ничего не отправляется.
 */
class BroadcastDraftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        Storage::fake('originals');
        URL::forceRootUrl('https://womenhub.example.test');
        URL::forceScheme('https');
        Http::fake();
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'is_published' => '1',
            'tone' => 'blue',
            'starts_at' => '2026-06-18',
            'translations' => [
                'ru' => ['title' => 'Бизнес-лаборатория', 'description' => 'Разберите задачу на шаги.', 'content' => json_encode([['uid' => 'e1', 'type' => 'text', 'data' => ['html' => '<p>Подробности встречи.</p>']]])],
                'en' => ['title' => 'Business lab', 'description' => 'Break the task into steps.'],
            ],
        ], $overrides);
    }

    private function articlePayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'intent' => 'publish',
            'translations' => [
                'ru' => ['title' => 'Грант для основательниц', 'excerpt' => 'Открыт приём заявок.', 'content' => json_encode([['uid' => 'a1', 'type' => 'text', 'data' => ['html' => '<p>Текст</p>']]])],
                'en' => ['title' => 'Grant for founders', 'excerpt' => 'Applications are open.'],
            ],
        ], $overrides);
    }

    public function test_the_news_form_creates_a_draft_with_texts_and_a_link_button_and_sends_nothing(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->eventPayload(['broadcast_draft' => '1', 'broadcast_audience' => 'members']))
            ->assertRedirect(route('admin.events.index'))
            ->assertSessionHas('success');

        $this->assertStringContainsString('Черновик рассылки создан', session('success'));
        $event = Event::firstOrFail();
        $draft = Broadcast::firstOrFail();

        $this->assertSame('draft', $draft->status);
        $this->assertSame('members', $draft->audience);
        $this->assertSame(Event::class, $draft->source_type);
        $this->assertSame($event->id, $draft->source_id);
        $this->assertSame("<b>Бизнес-лаборатория</b>\n\nРазберите задачу на шаги.", $draft->texts['ru']);
        $this->assertSame("<b>Business lab</b>\n\nBreak the task into steps.", $draft->texts['en']);
        $this->assertSame('', $draft->texts['ro'], 'нет перевода — пусто, при отправке подставится русский');
        $this->assertSame('https://womenhub.example.test/events/'.$event->slug, $draft->buttons[0]['url']);
        $this->assertSame(['ru' => 'Подробнее', 'en' => 'Read more', 'ro' => 'Citește mai mult'], $draft->buttons[0]['label']);
        Http::assertNothingSent();
    }

    public function test_without_the_checkbox_no_draft_is_created(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->eventPayload())->assertRedirect();

        $this->assertSame(0, Broadcast::count());
    }

    public function test_publications_and_opportunities_get_drafts_that_link_to_their_pages(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), $this->articlePayload(['broadcast_draft' => '1']))->assertRedirect();
        $this->actingAsAdmin()->post(route('admin.opportunities.store'), $this->articlePayload(['broadcast_draft' => '1', 'broadcast_audience' => 'open']))->assertRedirect();

        $post = Post::firstOrFail();
        $opportunity = SiteOpportunity::firstOrFail();
        $drafts = Broadcast::all()->keyBy('source_type');

        $this->assertSame(route('media.publications.show', $post), $drafts[Post::class]->buttons[0]['url']);
        $this->assertSame(route('opportunities.show', $opportunity), $drafts[SiteOpportunity::class]->buttons[0]['url']);
        $this->assertSame("<b>Грант для основательниц</b>\n\nОткрыт приём заявок.", $drafts[Post::class]->texts['ru']);
        $this->assertSame('approved', $drafts[Post::class]->audience, 'по умолчанию все одобренные');
        $this->assertSame('open', $drafts[SiteOpportunity::class]->audience);
        $this->assertSame(['draft', 'draft'], $drafts->pluck('status')->values()->all());
    }

    public function test_the_cover_becomes_the_photo_of_the_draft(): void
    {
        Storage::disk('uploads')->put('2026/10/cover.webp', UploadedFile::fake()->image('c.webp', 1320, 600)->getContent());

        $this->actingAsAdmin()->post(route('admin.posts.store'), $this->articlePayload(['cover_path' => '2026/10/cover.webp', 'broadcast_draft' => '1']))->assertRedirect();

        $draft = Broadcast::firstOrFail();
        $this->assertTrue($draft->hasPhoto());
        Storage::disk('originals')->assertExists($draft->photo_path);
        $this->assertSame('image/jpeg', getimagesizefromstring(Storage::disk('originals')->get($draft->photo_path))['mime']);
    }

    public function test_a_missing_cover_file_just_means_a_draft_without_a_photo(): void
    {
        $this->actingAsAdmin()->post(route('admin.posts.store'), $this->articlePayload(['cover_path' => '2026/10/gone.webp', 'broadcast_draft' => '1']))->assertRedirect();

        $this->assertFalse(Broadcast::firstOrFail()->hasPhoto());
    }

    public function test_a_long_description_is_shortened_to_fit_a_photo_caption(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->eventPayload([
            'broadcast_draft' => '1',
            'translations' => ['ru' => ['description' => str_repeat('Очень длинное описание. ', 60)]],
        ]))->assertRedirect();

        $text = Broadcast::firstOrFail()->texts['ru'];
        $this->assertLessThanOrEqual(\App\Support\BroadcastText::MAX_CAPTION, \App\Support\BroadcastText::length($text));
        $this->assertStringEndsWith('…', $text);
    }

    public function test_the_draft_is_created_once_per_item(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->eventPayload(['broadcast_draft' => '1']));
        $event = Event::firstOrFail();

        $this->actingAsAdmin()->put(route('admin.events.update', $event), $this->eventPayload(['broadcast_draft' => '1']))
            ->assertSessionHas('success');

        $this->assertStringContainsString('уже есть', session('success'));
        $this->assertSame(1, Broadcast::count());
    }

    public function test_the_form_shows_the_checkbox_and_after_creation_a_link_to_the_draft(): void
    {
        $this->actingAsAdmin()->get(route('admin.events.create'))->assertOk()->assertSee('Создать черновик для рассылки')->assertSee('name="broadcast_draft"', false);
        $this->actingAsAdmin()->get(route('admin.posts.create'))->assertOk()->assertSee('Создать черновик для рассылки');
        $this->actingAsAdmin()->get(route('admin.opportunities.create'))->assertOk()->assertSee('Создать черновик для рассылки');

        $this->actingAsAdmin()->post(route('admin.events.store'), $this->eventPayload(['broadcast_draft' => '1']));
        $event = Event::firstOrFail();
        $draft = Broadcast::firstOrFail();

        $this->actingAsAdmin()->get(route('admin.events.edit', $event))->assertOk()
            ->assertSee('Рассылка по этому материалу уже создана')
            ->assertSee(route('admin.broadcasts.show', $draft), false)
            ->assertDontSee('name="broadcast_draft"', false);
    }

    public function test_an_unknown_audience_is_rejected(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->eventPayload(['broadcast_draft' => '1', 'broadcast_audience' => 'everyone']))
            ->assertSessionHasErrors('broadcast_audience');

        $this->assertSame(0, Event::count());
    }

    public function test_the_draft_opens_for_editing_in_the_broadcast_section(): void
    {
        $this->actingAsAdmin()->post(route('admin.events.store'), $this->eventPayload(['broadcast_draft' => '1']));
        $draft = Broadcast::firstOrFail();

        $this->actingAsAdmin()->get(route('admin.broadcasts.edit', $draft))->assertOk()->assertSee('Бизнес-лаборатория')->assertSee('Подробнее');
        $this->actingAsAdmin()->get(route('admin.broadcasts.index'))->assertOk()->assertSee('Бизнес-лаборатория');
    }
}
