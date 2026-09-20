<?php

namespace Tests\Feature\Admin;

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders_with_empty_data(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.statistics.index'))
            ->assertOk()
            ->assertSee('Индекс готовности сообщества')
            ->assertSee('Одобренных профилей пока нет');
    }

    public function test_page_counts_only_approved_member_posts(): void
    {
        $author = BotUser::factory()->approved()->create(['full_name' => 'Мария Автор']);

        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'project', 'title' => 'Одобренный пост', 'body' => 'x', 'status' => 'approved']);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'event', 'title' => 'Пост на проверке', 'body' => 'x', 'status' => 'pending']);
        Opportunity::create(['bot_user_id' => $author->id, 'type' => 'event', 'title' => 'Отклонённый пост', 'body' => 'x', 'status' => 'rejected']);

        $this->actingAsAdmin()
            ->get(route('admin.statistics.index'))
            ->assertOk()
            ->assertSee('Одобренный пост')
            ->assertDontSee('Пост на проверке')
            ->assertDontSee('Отклонённый пост')
            ->assertSee('Мария Автор');
    }

    public function test_funnel_reflects_profile_statuses_and_cabinet_activity(): void
    {
        BotUser::factory()->approved()->count(2)->create();
        BotUser::factory()->pending()->create();
        BotUser::factory()->create(['status' => BotUser::STATUS_REJECTED]);

        $data = app(\App\Services\ImpactReport::class)->viewData();

        $this->assertSame(4, $data['totalApplications']);
        $this->assertSame(2, $data['approvedCount']);
        $this->assertSame(50, $data['approvalRate']);
        $this->assertSame(0, $data['activeCabinetUsers']);
        $this->assertSame(LoginToken::count(), $data['loginTokensIssued']);
    }

    public function test_pdf_is_downloaded_as_a_file(): void
    {
        BotUser::factory()->approved()->create();

        $response = $this->actingAsAdmin()->get(route('admin.statistics.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('impact-report-', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_statistics_are_closed_to_non_admins(): void
    {
        config(['admin.email' => 'someone-else@example.test']);
        $intruder = \App\Models\User::factory()->create(['email' => 'intruder@example.test']);

        $this->actingAs($intruder)->get(route('admin.statistics.index'))->assertForbidden();
        $this->actingAs($intruder)->get(route('admin.statistics.pdf'))->assertForbidden();
    }
}
