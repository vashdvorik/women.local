<?php

namespace Tests\Feature\Admin;

use App\Jobs\NotifyOpportunity;
use App\Models\BotUser;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MemberPostModerationTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(string $status = Opportunity::STATUS_PENDING, array $attributes = []): Opportunity
    {
        return Opportunity::create([
            'bot_user_id' => BotUser::factory()->approved()->create()->id,
            'type' => 'project',
            'title' => 'Ищу партнёров',
            'body' => 'Описание публикации',
            'status' => $status,
            ...$attributes,
        ]);
    }

    public function test_new_posts_default_to_pending(): void
    {
        $post = Opportunity::create([
            'bot_user_id' => BotUser::factory()->approved()->create()->id,
            'type' => 'event',
            'title' => 'Встреча',
            'body' => 'Текст',
        ]);

        $this->assertTrue($post->fresh()->isPending());
    }

    public function test_list_filters_by_status_type_and_search(): void
    {
        $this->makePost(Opportunity::STATUS_PENDING, ['title' => 'Ожидающий пост']);
        $this->makePost(Opportunity::STATUS_APPROVED, ['title' => 'Одобренный пост', 'type' => 'event']);

        $this->actingAsAdmin()->get(route('admin.member-posts.index'))
            ->assertOk()->assertSee('Ожидающий пост')->assertSee('Одобренный пост');

        $this->actingAsAdmin()->get(route('admin.member-posts.index', ['status' => 'pending']))
            ->assertSee('Ожидающий пост')->assertDontSee('Одобренный пост');

        $this->actingAsAdmin()->get(route('admin.member-posts.index', ['type' => 'event']))
            ->assertSee('Одобренный пост')->assertDontSee('Ожидающий пост');

        $this->actingAsAdmin()->get(route('admin.member-posts.index', ['q' => 'Ожидающий']))
            ->assertSee('Ожидающий пост')->assertDontSee('Одобренный пост');
    }

    public function test_show_page_renders_the_post(): void
    {
        $post = $this->makePost(Opportunity::STATUS_PENDING, ['location' => 'Кишинёв']);

        $this->actingAsAdmin()->get(route('admin.member-posts.show', $post))
            ->assertOk()->assertSee('Описание публикации')->assertSee('Кишинёв');
    }

    public function test_approving_publishes_and_notifies_participants_once(): void
    {
        Queue::fake();
        $post = $this->makePost();

        $this->actingAsAdmin()->post(route('admin.member-posts.approve', $post))->assertRedirect();

        $this->assertTrue($post->fresh()->isApproved());
        $this->assertNotNull($post->fresh()->moderated_at);
        Queue::assertPushed(NotifyOpportunity::class, 1);

        // Повторное одобрение не рассылает уведомление второй раз.
        $this->actingAsAdmin()->post(route('admin.member-posts.approve', $post))->assertRedirect();
        Queue::assertPushed(NotifyOpportunity::class, 1);
    }

    public function test_rejecting_hides_the_post_without_notifying(): void
    {
        Queue::fake();
        $post = $this->makePost(Opportunity::STATUS_APPROVED);

        $this->actingAsAdmin()->post(route('admin.member-posts.reject', $post))->assertRedirect();

        $this->assertTrue($post->fresh()->isRejected());
        Queue::assertNothingPushed();
    }

    public function test_delete_removes_the_post(): void
    {
        $post = $this->makePost();

        $this->actingAsAdmin()->delete(route('admin.member-posts.destroy', $post))
            ->assertRedirect(route('admin.member-posts.index'));

        $this->assertModelMissing($post);
    }

    public function test_visibility_scope_shows_approved_to_everyone_and_own_posts_to_author(): void
    {
        $author = BotUser::factory()->approved()->create();
        $other = BotUser::factory()->approved()->create();

        $approved = $this->makePost(Opportunity::STATUS_APPROVED, ['bot_user_id' => $author->id, 'title' => 'A']);
        $ownPending = $this->makePost(Opportunity::STATUS_PENDING, ['bot_user_id' => $author->id, 'title' => 'B']);
        $ownRejected = $this->makePost(Opportunity::STATUS_REJECTED, ['bot_user_id' => $author->id, 'title' => 'C']);
        $foreignPending = $this->makePost(Opportunity::STATUS_PENDING, ['bot_user_id' => $other->id, 'title' => 'D']);

        $seenByAuthor = Opportunity::visibleTo($author)->pluck('id')->all();
        $seenByOther = Opportunity::visibleTo($other)->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$approved->id, $ownPending->id, $ownRejected->id], $seenByAuthor);
        $this->assertEqualsCanonicalizing([$approved->id, $foreignPending->id], $seenByOther);
    }

    public function test_endpoints_are_closed_to_non_admins(): void
    {
        config(['admin.email' => 'someone-else@example.test']);
        $post = $this->makePost();

        $this->actingAs(\App\Models\User::factory()->create(['email' => 'intruder@example.test']))
            ->post(route('admin.member-posts.approve', $post))
            ->assertForbidden();

        $this->assertTrue($post->fresh()->isPending());
    }
}
