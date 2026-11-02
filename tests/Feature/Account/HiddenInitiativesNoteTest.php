<?php

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SubscriptionHelpers;
use Tests\TestCase;

/**
 * Скрытый участник читает инициативы, но публиковать не может: страница объясняет, почему нет кнопки.
 */
class HiddenInitiativesNoteTest extends TestCase
{
    use RefreshDatabase;
    use SubscriptionHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ru');
    }

    public function test_a_hidden_member_sees_the_note_and_no_add_button(): void
    {
        $hidden = BotUser::factory()->active()->create();
        $hidden->forceFill(['is_hidden' => true])->save();

        $this->asParticipant($hidden)->get(route('account.opportunities.index'))->assertOk()
            ->assertSee('Ваш профиль скрыт от других участниц')
            ->assertDontSee('Добавить инициативу')
            ->assertDontSee(route('account.opportunities.create'), false);

        $this->asParticipant($hidden)->get(route('account.opportunities.create'))->assertForbidden();
    }

    public function test_a_regular_active_member_sees_the_add_button_and_no_note(): void
    {
        $this->asParticipant(BotUser::factory()->active()->create())->get(route('account.opportunities.index'))->assertOk()
            ->assertSee('Добавить инициативу')
            ->assertDontSee('Ваш профиль скрыт от других участниц');
    }
}
