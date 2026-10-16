<?php

namespace Tests\Feature\Console;

use App\Models\BotUser;
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
        $this->assertStringContainsString('отличается от проверяемого', $output);
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
        $this->assertStringContainsString('API-ключ DeepSeek не задан', $output);
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

    public function test_an_alias_that_burns_the_token_limit_on_reasoning_is_explained(): void
    {
        // Реальный случай: имя из настроек провайдера нет в списке, он подставляет другую модель, и она «думает» весь лимит.
        $this->configureDeepSeek('deepseek-v4-flash');
        Http::fake([
            'api.deepseek.com/models' => Http::response(['data' => [['id' => 'deepseek-flash'], ['id' => 'deepseek-v4-pro']]]),
            'api.deepseek.com/chat/completions' => Http::response([
                'model' => 'deepseek-flash',
                'choices' => [['finish_reason' => 'length', 'message' => ['content' => '', 'reasoning_content' => str_repeat('думаю ', 40)]]],
                'usage' => ['prompt_tokens' => 37, 'completion_tokens' => 64, 'completion_tokens_details' => ['reasoning_tokens' => 64]],
            ]),
        ]);

        [$code, $output] = $this->run_();

        $this->assertSame(1, $code);
        $this->assertStringContainsString('«deepseek-v4-flash» в нём НЕТ', $output);
        $this->assertStringContainsString('Доступные модели: deepseek-flash, deepseek-v4-pro', $output);
        $this->assertStringContainsString('принимает как псевдоним модели «deepseek-flash»', $output);
        $this->assertStringContainsString('Рассуждения:', $output);
        $this->assertStringContainsString('64 токенов', $output);
        $this->assertStringContainsString('лимит 1024 токенов закончился ещё на рассуждениях', $output);
    }

    public function test_the_model_and_the_token_limit_can_be_tried_without_changing_the_settings(): void
    {
        $this->configureDeepSeek('deepseek-v4-flash');
        Http::fake([
            'api.deepseek.com/models' => Http::response(['data' => [['id' => 'deepseek-flash']]]),
            'api.deepseek.com/chat/completions' => Http::response([
                'model' => 'deepseek-flash',
                'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'ок']]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 90],
            ]),
        ]);

        $code = Artisan::call('ai:check-assistant', ['--model' => 'deepseek-flash', '--tokens' => '4096']);
        $output = Artisan::output();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('Проверяется другое: DeepSeek напрямую (https://api.deepseek.com), модель deepseek-flash', $output);
        $this->assertStringContainsString('«deepseek-flash» в нём есть', $output);
        $this->assertStringContainsString('лимит 4096 токенов', $output);
        $this->assertStringContainsString('Имя в ответе совпадает', $output);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/chat/completions')
            && $request['model'] === 'deepseek-flash' && $request['max_tokens'] === 4096);

        $this->assertSame('deepseek-v4-flash', SiteSetting::agentFeatureConfig()['model'], 'сохранённая модель не изменилась');
    }

    public function test_ask_runs_a_real_assistant_request_and_shows_time_tokens_and_the_reply(): void
    {
        $this->configureDeepSeek('deepseek-flash');
        BotUser::factory()->community()->create(['full_name' => 'Анна Спрашивающая', 'telegram_id' => 5001]);
        Http::fake([
            'api.deepseek.com/models' => Http::response(['data' => [['id' => 'deepseek-flash']]]),
            'api.deepseek.com/chat/completions' => Http::sequence()
                ->push(['model' => 'deepseek-flash', 'choices' => [['finish_reason' => 'stop', 'message' => ['content' => 'ок']]], 'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5]])
                ->push([
                    'model' => 'deepseek-flash',
                    'choices' => [['finish_reason' => 'stop', 'message' => ['content' => json_encode(['reply' => 'Подскажу, как подготовить профиль для экспорта.'], JSON_UNESCAPED_UNICODE)]]],
                    'usage' => ['prompt_tokens' => 900, 'completion_tokens' => 420, 'completion_tokens_details' => ['reasoning_tokens' => 300]],
                ]),
        ]);

        $code = Artisan::call('ai:check-assistant', ['--ask' => 'Как найти партнёра для экспорта?']);
        $output = Artisan::output();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('Настоящий вопрос:        «Как найти партнёра для экспорта?» (от имени Анна Спрашивающая)', $output);
        $this->assertStringContainsString('Время ответа:', $output);
        $this->assertStringContainsString('900 на входе, 420 на выходе, из них на рассуждения 300; причина остановки: stop', $output);
        $this->assertStringContainsString('Ответ ассистента:        «Подскажу, как подготовить профиль для экспорта.»', $output);
        $this->assertStringNotContainsString(self::SECRET, $output);
    }

    public function test_ask_reports_a_reply_lost_to_reasoning_as_the_participant_would_see_it(): void
    {
        $this->configureDeepSeek('deepseek-flash');
        BotUser::factory()->community()->create();
        Http::fake([
            'api.deepseek.com/models' => Http::response(['data' => [['id' => 'deepseek-flash']]]),
            'api.deepseek.com/chat/completions' => Http::response([
                'model' => 'deepseek-flash',
                'choices' => [['finish_reason' => 'length', 'message' => ['content' => '']]],
                'usage' => ['prompt_tokens' => 900, 'completion_tokens' => 1024, 'completion_tokens_details' => ['reasoning_tokens' => 1024]],
            ]),
        ]);

        $code = Artisan::call('ai:check-assistant', ['--ask' => 'Подбери партнёра']);
        $output = Artisan::output();

        $this->assertSame(1, $code);
        $this->assertStringContainsString('Ассистент не смог ответить', $output);
        $this->assertStringContainsString('помощник временно недоступен', $output);
        $this->assertStringContainsString('из них на рассуждения 1024; причина остановки: length', $output);
    }

    public function test_ask_needs_at_least_one_approved_participant(): void
    {
        $this->configureDeepSeek('deepseek-flash');
        Http::fake(['api.deepseek.com/*' => Http::response(['model' => 'deepseek-flash', 'choices' => [['message' => ['content' => 'ок']]]])]);

        $code = Artisan::call('ai:check-assistant', ['--ask' => 'Привет']);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('нужна хотя бы одна одобренная', Artisan::output());
    }

    public function test_another_provider_and_model_can_be_tried_on_a_real_question_without_touching_the_settings(): void
    {
        // Рабочие настройки — DeepSeek; проверяем кандидата на OpenRouter тем же настоящим запросом.
        $this->configureDeepSeek('deepseek-flash');
        SiteSetting::setOpenRouterProviderConfig(['base_url' => 'https://openrouter.ai/api/v1', 'timeout' => 30, 'api_key' => 'or-test-key']);
        BotUser::factory()->community()->create(['full_name' => 'Анна Проверяющая']);

        Http::fake([
            'openrouter.ai/api/v1/models' => Http::response(['data' => [['id' => 'google/gemma-test:free']]]),
            'openrouter.ai/api/v1/chat/completions' => Http::response([
                'model' => 'google/gemma-test:free',
                'choices' => [['finish_reason' => 'stop', 'message' => ['content' => '```json'."\n".'{"reply":"Могу помочь с профилем и поиском."}'."\n".'```']]],
                'usage' => ['prompt_tokens' => 800, 'completion_tokens' => 40],
            ]),
        ]);

        $code = Artisan::call('ai:check-assistant', ['--provider' => 'openrouter', '--model' => 'google/gemma-test:free', '--ask' => 'Привет, чем поможешь?']);
        $output = Artisan::output();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('Проверяется другое: OpenRouter (https://openrouter.ai/api/v1), модель google/gemma-test:free', $output);
        $this->assertStringContainsString('«google/gemma-test:free» в нём есть', $output);
        $this->assertStringContainsString('Ответ ассистента:        «Могу помочь с профилем и поиском.»', $output, 'JSON в обёртке ```json принят');
        $this->assertStringNotContainsString('or-test-key', $output);

        // Оба запроса (пробный и настоящий) ушли на OpenRouter с ключом OpenRouter, на DeepSeek — ни одного.
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://openrouter.ai/') && $request->hasHeader('Authorization', 'Bearer or-test-key'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.deepseek.com'));

        $saved = SiteSetting::agentFeatureConfig();
        $this->assertSame(['deepseek', 'deepseek-flash'], [$saved['provider'], $saved['model']], 'сохранённые настройки не менялись');
    }

    public function test_another_provider_requires_a_model_and_a_known_name(): void
    {
        $this->configureDeepSeek('deepseek-flash');
        Http::fake();

        $this->assertSame(1, Artisan::call('ai:check-assistant', ['--provider' => 'openrouter']));
        $this->assertStringContainsString('Для другого провайдера укажите и модель', Artisan::output());

        $this->assertSame(1, Artisan::call('ai:check-assistant', ['--provider' => 'gemini', '--model' => 'x']));
        $this->assertStringContainsString('допустимо openrouter или deepseek', Artisan::output());

        Http::assertNothingSent();
    }

    public function test_a_system_prompt_rejected_by_the_model_is_shown_as_the_providers_own_error(): void
    {
        // Часть моделей не принимает роль «system»: ассистент должен показать причину, а не молча упасть.
        $this->configureDeepSeek('deepseek-flash');
        SiteSetting::setOpenRouterProviderConfig(['base_url' => 'https://openrouter.ai/api/v1', 'timeout' => 30, 'api_key' => 'or-test-key']);
        BotUser::factory()->community()->create();
        Http::fake([
            'openrouter.ai/api/v1/models' => Http::response([], 404),
            'openrouter.ai/api/v1/chat/completions' => Http::sequence()
                ->push(['model' => 'x/y', 'choices' => [['message' => ['content' => 'ок']]]])
                ->push(['error' => ['message' => 'Developer instruction is not enabled for this model']], 400),
        ]);

        $code = Artisan::call('ai:check-assistant', ['--provider' => 'openrouter', '--model' => 'x/y', '--ask' => 'Привет']);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('Ассистент не смог ответить', Artisan::output());
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
        $this->assertStringContainsString('Имя в ответе совпадает с проверяемым', $output);
        $this->assertStringNotContainsString('Доступные модели', $output, 'огромный список OpenRouter не печатается');
    }
}
