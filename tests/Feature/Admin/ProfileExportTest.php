<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ProfileController;
use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileExportTest extends TestCase
{
    use RefreshDatabase;

    private function csv(array $query = []): string
    {
        return $this->actingAsAdmin()->get(route('admin.profiles.export', $query))->assertOk()->streamedContent();
    }

    public function test_the_export_contains_the_participants_data(): void
    {
        $user = BotUser::factory()->approved()->create([
            'full_name' => 'Анна Иванова',
            'email' => 'anna@example.test',
            'phone' => '+37369123456',
            'description' => 'Делаю свечи',
            'expectation' => 'Ищу партнёров',
        ]);
        $user->forceFill(['contact_email' => 'contact@example.test', 'profile_completed_at' => now(), 'plan' => 'active', 'plan_ends_at' => now()->addYear()])->save();

        $csv = $this->csv();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv, 'UTF-8 с BOM, чтобы Excel открыл без плясок');
        foreach (['Анна Иванова', 'anna@example.test', 'contact@example.test', '+37369123456', 'Делаю свечи', 'Ищу партнёров', 'WOMEN’S HUB ACTIVE', 'действует'] as $expected) {
            $this->assertStringContainsString($expected, $csv, $expected);
        }
        $this->assertStringContainsString('Почта для связи', $csv);
    }

    public function test_the_export_never_contains_passwords_or_tokens(): void
    {
        $user = BotUser::factory()->approved()->create();
        $user->forceFill(['password' => 'secret-password-1', 'remember_token' => 'remember-token-xyz', 'profile_completed_at' => now()])->save();

        $csv = $this->csv();

        $this->assertStringNotContainsString('secret-password-1', $csv);
        $this->assertStringNotContainsString($user->fresh()->password, $csv);
        $this->assertStringNotContainsString('remember-token-xyz', $csv);
    }

    public function test_the_export_follows_the_status_filter_and_skips_unfinished_registrations(): void
    {
        BotUser::factory()->approved()->create(['full_name' => 'Одобренная Ольга', 'profile_completed_at' => now()]);
        BotUser::factory()->create(['full_name' => 'Ожидающая Ада', 'status' => BotUser::STATUS_PENDING, 'profile_completed_at' => now()]);
        BotUser::factory()->approved()->create(['full_name' => 'Недозаполнившая Нина', 'profile_completed_at' => null]);

        $all = $this->csv();
        $this->assertStringContainsString('Одобренная Ольга', $all);
        $this->assertStringContainsString('Ожидающая Ада', $all);
        $this->assertStringNotContainsString('Недозаполнившая Нина', $all, 'как и в списке: только заполнившие анкету');

        $pending = $this->csv(['status' => BotUser::STATUS_PENDING]);
        $this->assertStringContainsString('Ожидающая Ада', $pending);
        $this->assertStringNotContainsString('Одобренная Ольга', $pending);
    }

    public function test_text_that_looks_like_a_formula_is_defused(): void
    {
        $this->assertSame("'=HYPERLINK(\"http://evil\")", ProfileController::safeCell('=HYPERLINK("http://evil")'));
        $this->assertSame("'@cmd", ProfileController::safeCell('@cmd'));
        $this->assertSame('+37369123456', ProfileController::safeCell('+37369123456'), 'телефон со знаком «+» остаётся телефоном');
        $this->assertSame('Обычный текст', ProfileController::safeCell('Обычный текст'));
        $this->assertSame(5, ProfileController::safeCell(5));
    }

    public function test_the_export_is_for_administrators_only(): void
    {
        $this->get(route('admin.profiles.export'))->assertRedirect();
    }

    public function test_the_list_has_an_export_button(): void
    {
        BotUser::factory()->approved()->create(['profile_completed_at' => now()]);

        $this->actingAsAdmin()->get(route('admin.profiles.index'))->assertOk()
            ->assertSee(route('admin.profiles.export'), false)
            ->assertSee('Выгрузить в файл');
    }
}
