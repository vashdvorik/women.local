<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\BotUser;
use App\Models\Opportunity;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * «Скрыт от других участниц»: администратор, спонсор или партнёр пользуется платформой изнутри, но другие не видят и не
 * находят его (каталог, поиск, подбор контактов, ИИ-помощник, «новые участницы», его инициативы). Скрытый не публикует
 * инициативы, но получает рассылки. Скрывает только администратор платформы, в «Профили участниц».
 */
class HiddenMembersTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    private function member(string $name, bool $hidden = false, ?array $embedding = null): BotUser
    {
        $user = BotUser::factory()->community()->create([
            'full_name' => $name,
            'embedding' => $embedding,
        ]);

        if ($hidden) {
            $user->forceFill(['is_hidden' => true])->save();
        }

        return $user;
    }

    // ---------------------------------------------------------------- каталог, поиск, подбор

    public function test_a_hidden_member_is_not_in_the_catalog_and_has_no_page_for_others(): void
    {
        $viewer = $this->member('Зрительница Анна');
        $visible = $this->member('Видимая Мария');
        $hidden = $this->member('Невидимый Спонсор', hidden: true);

        $html = $this->asParticipant($viewer)->get(route('account.people'))->assertOk()->getContent();

        $this->assertStringContainsString('Видимая Мария', $html);
        $this->assertStringNotContainsString('Невидимый Спонсор', $html);

        $this->asParticipant($viewer)->get(route('account.people.show', $visible))->assertOk();
        $this->asParticipant($viewer)->get(route('account.people.show', $hidden))->assertNotFound();

        // Свою страницу скрытый открыть может.
        $this->asParticipant($hidden)->get(route('account.people.show', $hidden))->assertOk();
    }

    public function test_hidden_members_are_never_recommended_or_found_by_search(): void
    {
        $me = $this->member('Я', embedding: [1.0, 0.0]);
        $visible = $this->member('Открытая', embedding: [0.9, 0.1]);
        $hidden = $this->member('Скрытый', hidden: true, embedding: [1.0, 0.0]);

        $matcher = app(MatchingService::class);

        $recommended = $matcher->compute($me, 10)->pluck('user.id')->all();
        $this->assertSame([$visible->id], $recommended);

        $found = $matcher->searchByQuery([1.0, 0.0], $me, 10)->pluck('user.id')->all();
        $this->assertContains($visible->id, $found);
        $this->assertNotContains($hidden->id, $found);
    }

    public function test_hidden_members_are_not_among_the_newest_members_on_the_cabinet_home(): void
    {
        $viewer = $this->member('Зрительница');
        $this->member('Новая Видимая');
        $this->member('Новый Скрытый Спонсор', hidden: true);

        $html = $this->asParticipant($viewer)->get(route('account.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Новая Видимая', $html);
        $this->assertStringNotContainsString('Новый Скрытый Спонсор', $html);
    }

    // ---------------------------------------------------------------- инициативы

    public function test_initiatives_of_a_hidden_member_are_not_shown_to_others_but_stay_with_the_author(): void
    {
        $viewer = $this->member('Зрительница');
        $hidden = $this->member('Скрытый Автор', hidden: true);
        $visible = $this->member('Видимая Автор');

        Opportunity::create(['bot_user_id' => $hidden->id, 'type' => 'project', 'title' => 'Инициатива скрытого', 'body' => 'x', 'status' => 'approved']);
        Opportunity::create(['bot_user_id' => $visible->id, 'type' => 'project', 'title' => 'Инициатива видимой', 'body' => 'x', 'status' => 'approved']);

        $others = $this->asParticipant($viewer)->get(route('account.opportunities.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Инициатива видимой', $others);
        $this->assertStringNotContainsString('Инициатива скрытого', $others);

        // Автор свою (раньше опубликованную) видит.
        $own = $this->asParticipant($hidden)->get(route('account.opportunities.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Инициатива скрытого', $own);
    }

    public function test_a_hidden_member_cannot_publish_initiatives(): void
    {
        $hidden = $this->member('Скрытый Спонсор', hidden: true);

        $html = $this->asParticipant($hidden)->get(route('account.opportunities.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString(route('account.opportunities.create'), $html, 'кнопки «Добавить» нет');

        $this->asParticipant($hidden)->get(route('account.opportunities.create'))->assertForbidden();
        $this->asParticipant($hidden)->post(route('account.opportunities.store'), [
            'type' => 'project', 'title' => 'Попытка', 'body' => 'Текст',
        ])->assertForbidden();

        $this->assertDatabaseCount('opportunities', 0);

        // Обычная участница по-прежнему публикует.
        $visible = $this->member('Видимая');
        $this->asParticipant($visible)->get(route('account.opportunities.create'))->assertOk();
    }

    // ---------------------------------------------------------------- рассылки и собственная страница

    public function test_hidden_members_still_get_broadcasts(): void
    {
        $hidden = $this->member('Скрытый', hidden: true);

        $this->assertTrue(Broadcast::audienceQuery(Broadcast::AUDIENCE_MEMBERS)->whereKey($hidden->id)->exists());
        $this->assertTrue(Broadcast::audienceQuery('all')->whereKey($hidden->id)->exists());
    }

    public function test_the_profile_page_tells_a_hidden_member_that_she_is_hidden(): void
    {
        $hidden = $this->member('Скрытый', hidden: true);
        $visible = $this->member('Видимая');

        $this->asParticipant($hidden)->get(route('account.profile'))->assertOk()->assertSee('Скрыт от других участниц')->assertDontSee('Видно участницам');
        $this->asParticipant($visible)->get(route('account.profile'))->assertOk()->assertSee('Видно участницам')->assertDontSee('Скрыт от других участниц');
    }

    // ---------------------------------------------------------------- админка

    public function test_the_administrator_hides_and_shows_a_profile(): void
    {
        $profile = $this->member('Спонсор проекта');

        $this->actingAsAdmin()->post(route('admin.profiles.visibility', $profile))->assertRedirect();
        $this->assertTrue($profile->fresh()->is_hidden);

        $list = $this->actingAsAdmin()->get(route('admin.profiles.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Скрыт', $list);
        $this->assertStringContainsString('Показать', $list);

        $onlyHidden = $this->actingAsAdmin()->get(route('admin.profiles.index', ['hidden' => 1]))->assertOk()->getContent();
        $this->assertStringContainsString('Спонсор проекта', $onlyHidden);

        $this->actingAsAdmin()->post(route('admin.profiles.visibility', $profile))->assertRedirect();
        $this->assertFalse($profile->fresh()->is_hidden);
    }

    public function test_the_hidden_filter_lists_only_hidden_profiles(): void
    {
        $this->member('Обычная Участница');
        $this->member('Скрытый Спонсор', hidden: true);

        $html = $this->actingAsAdmin()->get(route('admin.profiles.index', ['hidden' => 1]))->assertOk()->getContent();

        $this->assertStringContainsString('Скрытый Спонсор', $html);
        $this->assertStringNotContainsString('Обычная Участница', $html);
    }

    public function test_the_profile_card_shows_the_toggle(): void
    {
        $profile = $this->member('Спонсор');

        $this->actingAsAdmin()->get(route('admin.profiles.show', $profile))
            ->assertOk()->assertSee('Скрыть от других участниц')->assertSee(route('admin.profiles.visibility', $profile));
    }

    public function test_only_the_administrator_can_hide_a_profile(): void
    {
        $profile = $this->member('Участница');
        $other = $this->member('Другая');

        // Гость админки.
        $this->post(route('admin.profiles.visibility', $profile))->assertRedirect();
        $this->assertFalse($profile->fresh()->is_hidden);
    }

    public function test_a_participant_cannot_hide_herself_or_anyone_through_the_cabinet(): void
    {
        $user = $this->member('Участница');

        // Поле не принимается формой профиля: скрыть себя может только администратор.
        $this->asParticipant($user)->post(route('account.profile.update'), [
            'full_name' => 'Участница', 'description' => 'Описание', 'expectation' => 'Запрос', 'is_hidden' => 1,
        ])->assertRedirect(route('account.profile'));

        $this->assertFalse($user->fresh()->is_hidden);
    }
}
