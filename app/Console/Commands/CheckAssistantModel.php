<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Показывает, какая модель на самом деле отвечает ИИ-помощнику в кабинете: что сохранено в админке, есть ли эта модель в
 * списке провайдера и какое имя модели провайдер называет в ответе на пробный запрос. Ключ нигде не печатается.
 */
class CheckAssistantModel extends Command
{
    protected $signature = 'ai:check-assistant';

    protected $description = 'Показывает, какая модель реально отвечает ИИ-помощнику (настройки, список моделей провайдера, пробный запрос)';

    public function handle(): int
    {
        $config = SiteSetting::agentFeatureConfig();
        $isRouter = $config['provider'] === 'openrouter';
        $base = rtrim($isRouter ? SiteSetting::openRouterProviderConfig()['base_url'] : SiteSetting::deepSeekProviderConfig()['base_url'], '/');
        $key = $isRouter ? SiteSetting::openRouterProviderApiKey() : SiteSetting::deepSeekProviderApiKey();

        $this->line('Провайдер в настройках:  '.($isRouter ? 'OpenRouter' : 'DeepSeek напрямую')." ({$base})");
        $this->line("Модель в настройках:     {$config['model']}");
        $this->line("Параметры:               temperature {$config['temperature']}, максимум токенов {$config['max_tokens']}, тайм-аут {$config['timeout']} с");

        if (! is_string($key) || $key === '') {
            $this->error('API-ключ этого провайдера не задан: ассистент отвечать не будет (админка → «ИИ-провайдеры»).');

            return self::FAILURE;
        }

        $this->checkModelList($base, $key, $config['model']);

        return $this->probe($base, $key, $config);
    }

    /** Есть ли выбранная модель в списке, который отдаёт провайдер. Необязательная проверка: без списка просто пропускается. */
    private function checkModelList(string $base, string $key, string $model): void
    {
        try {
            $response = Http::acceptJson()->withToken($key)->timeout(15)->get($base.'/models');
        } catch (Throwable) {
            return;
        }

        if (! $response->successful()) {
            return;
        }

        $ids = collect($response->json('data', []))->pluck('id')->filter()->values();

        if ($ids->isEmpty()) {
            return;
        }

        if ($ids->contains($model)) {
            $this->info("Список моделей провайдера: «{$model}» в нём есть.");

            return;
        }

        $this->warn("Список моделей провайдера: «{$model}» в нём НЕТ.");

        if ($ids->count() <= 20) {
            $this->line('Доступные модели: '.$ids->implode(', '));
        }
    }

    /** @param  array{provider: string, model: string, temperature: float, max_tokens: int, timeout: int}  $config */
    private function probe(string $base, string $key, array $config): int
    {
        $started = microtime(true);

        try {
            $response = Http::acceptJson()->withToken($key)->timeout($config['timeout'])->post($base.'/chat/completions', [
                'model' => $config['model'],
                'messages' => [['role' => 'user', 'content' => 'Ответь одним словом: ок']],
                'temperature' => $config['temperature'],
                'max_tokens' => 64,
            ]);
        } catch (Throwable $e) {
            $this->error('Нет связи с провайдером: '.$e->getMessage());

            return self::FAILURE;
        }

        $seconds = round(microtime(true) - $started, 2);

        if (! $response->successful()) {
            $reason = $response->json('error.message') ?? $response->json('error') ?? $response->body();
            $this->error("Пробный запрос: HTTP {$response->status()}: ".mb_substr(is_string($reason) ? $reason : json_encode($reason, JSON_UNESCAPED_UNICODE), 0, 300));

            return self::FAILURE;
        }

        $answered = (string) $response->json('model', '—');

        $this->line("Пробный запрос:          HTTP {$response->status()}, {$seconds} с");
        $this->line("Ответила модель:         {$answered}");
        $this->line($answered === $config['model']
            ? 'Имя в ответе совпадает с настройкой.'
            : 'Имя в ответе отличается от настройки: провайдер может подставлять свою версию или псевдоним модели; сверьте с его документацией.');

        $usage = $response->json('usage');

        if (is_array($usage)) {
            $this->line('Токены:                  '.($usage['prompt_tokens'] ?? '?').' на входе, '.($usage['completion_tokens'] ?? '?').' на выходе');
        }

        $text = trim((string) $response->json('choices.0.message.content', ''));
        $this->line('Текст ответа:            '.($text === '' ? '(пусто: модель с рассуждениями могла потратить лимит токенов на них)' : '«'.mb_substr($text, 0, 80).'»'));

        return self::SUCCESS;
    }
}
