<?php

namespace Tests\Feature\Admin;

use App\Models\BotUser;
use App\Models\SiteSetting;
use App\Services\AiManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * У AI-менеджера своя модель (вкладка «AI» в настройках кабинетов); пустые поля значат «как у AI-помощника».
 */
class ManagerModelSettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'gemini_base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'gemini_model' => 'gemini-embedding-001',
            'gemini_timeout' => 15,
            'gemini_api_key' => '',
            'openrouter_base_url' => 'https://openrouter.ai/api/v1',
            'openrouter_timeout' => 30,
            'openrouter_api_key' => 'sk-or-test',
            'deepseek_base_url' => 'https://api.deepseek.com',
            'deepseek_model' => 'deepseek-chat',
            'deepseek_temperature' => 0.3,
            'deepseek_max_tokens' => 1024,
            'deepseek_timeout' => 30,
            'deepseek_api_key' => '',
            'embedding_provider' => 'gemini',
            'embedding_model' => 'gemini-embedding-001',
            'embedding_timeout' => 15,
            'search_min_score' => 0.55,
            'agent_provider' => 'openrouter',
            'agent_model' => 'deepseek/deepseek-chat',
            'agent_temperature' => 0.3,
            'agent_max_tokens' => 1024,
            'agent_timeout' => 30,
        ], $overrides);
    }

    public function test_empty_manager_fields_mean_the_same_model_as_the_assistant(): void
    {
        $this->actingAsAdmin()->put(route('admin.cabinets.settings.ai.update'), $this->payload())->assertRedirect();

        $this->assertSame(SiteSetting::agentFeatureConfig(), SiteSetting::managerFeatureConfig());
    }

    public function test_the_manager_can_have_its_own_model_while_the_assistant_keeps_its_own(): void
    {
        $this->actingAsAdmin()->put(route('admin.cabinets.settings.ai.update'), $this->payload([
            'manager_model' => 'openai/gpt-oss-20b',
            'manager_temperature' => 0.2,
        ]))->assertRedirect();

        $manager = SiteSetting::managerFeatureConfig();
        $this->assertSame('openai/gpt-oss-20b', $manager['model']);
        $this->assertSame(0.2, $manager['temperature']);
        // Остальное берётся у помощника
        $this->assertSame('openrouter', $manager['provider']);
        $this->assertSame(1024, $manager['max_tokens']);

        $this->assertSame('deepseek/deepseek-chat', SiteSetting::agentFeatureConfig()['model']);

        // Очистка полей возвращает менеджера на модель помощника
        $this->actingAsAdmin()->put(route('admin.cabinets.settings.ai.update'), $this->payload())->assertRedirect();
        $this->assertSame('deepseek/deepseek-chat', SiteSetting::managerFeatureConfig()['model']);
    }

    public function test_the_form_shows_the_block_and_validates_the_values(): void
    {
        $this->actingAsAdmin()->get(route('admin.cabinets.settings', ['tab' => 'ai']))
            ->assertOk()
            ->assertSee('AI-менеджер')
            ->assertSee('Сейчас менеджер работает на модели:');

        $this->actingAsAdmin()->put(route('admin.cabinets.settings.ai.update'), $this->payload(['manager_temperature' => 5]))
            ->assertSessionHasErrors('manager_temperature');
        $this->actingAsAdmin()->put(route('admin.cabinets.settings.ai.update'), $this->payload(['manager_provider' => 'gemini']))
            ->assertSessionHasErrors('manager_provider');
    }

    public function test_a_manager_question_goes_to_the_manager_model_not_the_assistant_one(): void
    {
        $this->actingAsAdmin()->put(route('admin.cabinets.settings.ai.update'), $this->payload([
            'manager_model' => 'openai/gpt-oss-20b',
        ]))->assertRedirect();

        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => '{"reply":"Привет","answered":true}']]]])]);

        $user = BotUser::factory()->active()->create();
        $result = app(AiManagerService::class)->ask($user, 'Как устроена платформа?', 'ru');

        $this->assertSame('Привет', $result['reply']);
        Http::assertSent(fn ($request) => $request['model'] === 'openai/gpt-oss-20b');
    }
}
