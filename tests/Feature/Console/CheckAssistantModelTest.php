<?php

namespace Tests\Feature\Console;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Команда ai:check-assistant: показывает, какая модель реально отвечает ИИ-помощнику, и никогда не печатает ключ.
 */
class CheckAssistantModelTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'sk-test-very-secret-key';

    private function configureDeepSeek(string $model = 'deepseek-v4-flash', ?string $key = self::SECRET): void
    {
        SiteSetting::setDeepSeekProviderConfig([
            'base_url' => 'https://api.deepseek.com', 'model' => $model, 'temperature' => 0.3, 'max_tokens' => 1024, 'timeout' => 30,
            'api_key' => (string) $key,
        ]);
        SiteSetting::setAgentFeatureConfig(['provider' => 'deepseek', 'model' => $model, 'temperature' => 0.3, 'max_tokens' => 1024, 'timeout' => 30]);
    }

    private function run_(): array
    {
        $code = Artisan::call('ai:check-assistant');

        return [$code, Artisan::output()];
    }

    public function test_it_shows_the_model_that_the_provider_says_answered_and_never_prints_the_key(): void
    {
        $this->configureDeepSeek();
        Http::fake([
            'api.deepseek.com/models' => Http::response(['data' => [['id' => 'deepseek-chat'], ['id' => 'deepseek-v4-flash']]]),
            'api.deepseek.com/chat/completions' => Http::response([
                'model' => 'deepseek-v4-flash-0912',
                'choices' => [['message' => ['content' => 'ок']]],
                'usage' => ['prompt_tokens' => 11, 'completion_tokens' => 2],
            ]),
        ]);

        [$code, $output] = $this->run_();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('DeepSeek напрямую', $output);
        $this->assertStringContainsString('Модель в настройках:     deepseek-v4-flash', $output);
        $this->assertStringContainsString('«deepseek-v4-flash» в нём есть', $output);
        $this->assertStringContainsString('Ответила модель:         deepseek-v4-flash-0912', $output);
        $this->assertStringContainsString('отличается от настройки', $output);
        $this->assertStringContainsString('11 на входе, 2 на выходе', $output);
        $this->assertStringNotContainsString(self::SECRET, $output);

        // Ключ ушёл провайдеру в заголовке, а не в адресе.
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer '.self::SECRET) && ! str_contains($request->url(), self::SECRET));
    }

    public function test_it_says_when_the_chosen_model_is_not_in_the_providers_list(): void
    {
        $this->configureDeepSeek('deepseek-v9-imaginary');
        Http::fake([
            'api.deepseek.com/models' => Http::response(['data' => [['id' => 'deepseek-chat'], ['id' => 'deepseek-reasoner']]]),
            'api.deepseek.com/chat/completions' => Http::response(['error' => ['message' => 'Model Not Exist']], 400),
        ]);

        [$code, $output] = $this->run_();

        $this->assertSame(1, $code);
        $this->assertStringContainsString('«deepseek-v9-imaginary» в нём НЕТ', $output);
        $this->assertStringContainsString('Доступные модели: deepseek-chat, deepseek-reasoner', $output);
        $this->assertStringContainsString('HTTP 400: Model Not Exist', $output);
    }

    public function test_it_reports_a_missing_key_without_calling_the_provider(): void
    {
        $this->configureDeepSeek(key: null);
        Http::fake();

        [$code, $output] = $this->run_();

        $this->assertSame(1, $code);
        $this->assertStringContainsString('API-ключ этого провайдера не задан', $output);
        Http::assertNothingSent();
    }

    public function test_it_reports_a_rejected_key_and_a_dead_connection(): void
    {
        $this->configureDeepSeek();
        Http::fake([
            'api.deepseek.com/models' => Http::response([], 401),
            'api.deepseek.com/chat/completions' => Http::response(['error' => ['message' => 'Authentication Fails']], 401),
        ]);

        [$code, $output] = $this->run_();
        $this->assertSame(1, $code);
        $this->assertStringContainsString('HTTP 401: Authentication Fails', $output);
        $this->assertStringNotContainsString(self::SECRET, $output);

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('cURL error 6: Could not resolve host'));

        [$code, $output] = $this->run_();
        $this->assertSame(1, $code);
        $this->assertStringContainsString('Нет связи с провайдером', $output);
    }

    public function test_it_works_for_open_router_too(): void
    {
        SiteSetting::setOpenRouterProviderConfig(['base_url' => 'https://openrouter.ai/api/v1', 'timeout' => 30, 'api_key' => self::SECRET]);
        SiteSetting::setAgentFeatureConfig(['provider' => 'openrouter', 'model' => 'deepseek/deepseek-chat', 'temperature' => 0.3, 'max_tokens' => 1024, 'timeout' => 30]);
        Http::fake([
            'openrouter.ai/api/v1/models' => Http::response(['data' => array_map(fn (int $i) => ['id' => "vendor/model-{$i}"], range(1, 300)) + [300 => ['id' => 'deepseek/deepseek-chat']]]),
            'openrouter.ai/api/v1/chat/completions' => Http::response(['model' => 'deepseek/deepseek-chat', 'choices' => [['message' => ['content' => 'ок']]]]),
        ]);

        [$code, $output] = $this->run_();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('OpenRouter', $output);
        $this->assertStringContainsString('Имя в ответе совпадает с настройкой', $output);
        $this->assertStringNotContainsString('Доступные модели', $output, 'огромный список OpenRouter не печатается');
    }
}
