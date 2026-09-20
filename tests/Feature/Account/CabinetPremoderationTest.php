<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Jobs\NotifyOpportunity;
use App\Models\BotUser;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Посты участниц проходят премодерацию: сразу после создания они видны только
 * автору и не рассылаются; всем — после одобрения в админке.
 */
class CabinetPremoderationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsParticipant(BotUser $user): static
    {
        return $this->withSession([
            'account_telegram_id' => $user->telegram_id,
            '_account_expires' => now()->addDays(7)->timestamp,
        ]);
    }

    public function test_new_post_is_created_pending_and_does_not_notify_anyone(): void
    {
        Queue::fake();
        $author = BotUser::factory()->approved()->create();

        $this->actingAsParticipant($author)
            ->post(route('account.opportunities.store'), [
                'type' => 'project',
                'title' => 'Ищу партнёров для выставки',
                'body' => 'Нужны партнёры для совместного стенда.',
            ])
            ->assertRedirect(route('account.opportunities.index'));

        $post = Opportunity::firstOrFail();
        $this->assertTrue($post->isPending());
        $this->assertSame($author->id, $post->bot_user_id);
        Queue::assertNotPushed(NotifyOpportunity::class);
    }

    public function test_pending_post_is_visible_to_its_author_with_a_moderation_note_only(): void
    {
        $author = BotUser::factory()->approved()->create();
        $other = BotUser::factory()->approved()->create();

        Opportunity::create([
            'bot_user_id' => $author->id, 'type' => 'project', 'status' => 'pending',
            'title' => 'Пост на проверке', 'body' => 'Текст',
        ]);
        Opportunity::create([
            'bot_user_id' => $other->id, 'type' => 'event', 'status' => 'approved',
            'title' => 'Опубликованный пост', 'body' => 'Текст',
        ]);

        $this->actingAsParticipant($author)
            ->get(route('account.opportunities.index'))
            ->assertOk()
            ->assertSee('Пост на проверке')
            ->assertSee('Опубликованный пост')
            ->assertSee('На модерации');

        $this->actingAsParticipant($other)
            ->get(route('account.opportunities.index'))
            ->assertOk()
            ->assertSee('Опубликованный пост')
            ->assertDontSee('Пост на проверке');
    }

    public function test_rejected_post_is_marked_for_the_author_and_hidden_from_others(): void
    {
        $author = BotUser::factory()->approved()->create();
        $other = BotUser::factory()->approved()->create();

        Opportunity::create([
            'bot_user_id' => $author->id, 'type' => 'meeting', 'status' => 'rejected',
            'title' => 'Отклонённый пост', 'body' => 'Текст',
        ]);

        $this->actingAsParticipant($author)
            ->get(route('account.opportunities.index'))
            ->assertSee('Отклонённый пост')
            ->assertSee('Отклонено модератором');

        $this->actingAsParticipant($other)
            ->get(route('account.opportunities.index'))
            ->assertDontSee('Отклонённый пост');
    }
}
