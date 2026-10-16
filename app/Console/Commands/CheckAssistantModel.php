<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Показывает, какая модель на самом деле отвечает ИИ-помощнику в кабинете: что сохранено в админке, есть ли эта модель в
 * списке провайдера, какое имя модели провайдер называет в ответе на пробный запрос и хватает ли лимита токенов, чтобы
 * ответ дошёл до конца (модели с рассуждениями тратят часть лимита на размышления). Ключ нигде не печатается.
 *
 * Другое имя модели или другой лимит токенов можно проверить, не меняя настроек: --model=… и --tokens=….
 */
class CheckAssistantModel extends Command
{
    protected $signature = 'ai:check-assistant
                            {--model= : Проверить другое имя модели (настройки не меняются)}
                            {--tokens= : Лимит токенов пробного ответа (по умолчанию — «Максимум токенов» из настроек)}';

    protected $description = 'Показывает, какая модель реально отвечает ИИ-помощнику (настройки, список моделей провайдера, пробный запрос)';

    public function handle(): int
    {
        $config = SiteSetting::agentFeatureConfig();
        $isRouter = $config['provider'] === 'openrouter';
        $base = rtrim($isRouter ? SiteSetting::openRouterProviderConfig()['base_url'] : SiteSetting::deepSeekProviderConfig()['base_url'], '/');
        $key = $isRouter ? SiteSetting::openRouterProviderApiKey() : SiteSetting::deepSeekProviderApiKey();

        $model = filled($this->option('model')) ? trim((string) $this->option('model')) : $config['model'];
        $tokens = filled($this->option('tokens')) ? max(16, min(8192, (int) $this->option('tokens'))) : $config['max_tokens'];

        $this->line('Провайдер в настройках:  '.($isRouter ? 'OpenRouter' : 'DeepSeek напрямую')." ({$base})");
        $this->line("Модель в настройках:     {$config['model']}");
        $this->line("Параметры:               temperature {$config['temperature']}, максимум токенов {$config['max_tokens']}, тайм-аут {$config['timeout']} с");

        if ($model !== $config['model']) {
            $this->warn("Проверяется другое имя: {$model} (настройки не менялись).");
        }

        if (! is_string($key) || $key === '') {
            $this->error('API-ключ этого провайдера не задан: ассистент отвечать не будет (админка → «ИИ-провайдеры»).');

            return self::FAILURE;
        }

        $listed = $this->checkModelList($base, $key, $model);

        return $this->probe($base, $key, $model, $tokens, $config, $listed);
    }

    /**
     * Есть ли выбранная модель в списке, который отдаёт провайдер. Необязательная проверка: без списка пропускается.
     *
     * @return Collection<int, string>|null имена моделей провайдера или null, если списка нет
     */
    private function checkModelList(string $base, string $key, string $model): ?Collection
    {
        try {
            $response = Http::acceptJson()->withToken($key)->timeout(15)->get($base.'/models');
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $ids = collect($response->json('data', []))->pluck('id')->filter()->values();

        if ($ids->isEmpty()) {
            return null;
        }

        if ($ids->contains($model)) {
            $this->info("Список моделей провайдера: «{$model}» в нём есть.");

            return $ids;
        }

        $this->warn("Список моделей провайдера: «{$model}» в нём НЕТ.");

        if ($ids->count() <= 20) {
            $this->line('Доступные модели: '.$ids->implode(', '));
        }

        return $ids;
    }

    /**
     * @param  array{provider: string, model: string, temperature: float, max_tokens: int, timeout: int}  $config
     * @param  Collection<int, string>|null  $listed
     */
    private function probe(string $base, string $key, string $model, int $tokens, array $config, ?Collection $listed): int
    {
        $started = microtime(true);

        try {
            $response = Http::acceptJson()->withToken($key)->timeout($config['timeout'])->post($base.'/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => 'Ответь одним словом: ок']],
                'temperature' => $config['temperature'],
                'max_tokens' => $tokens,
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

        $this->line("Пробный запрос:          HTTP {$response->status()}, {$seconds} с, лимит {$tokens} токенов");
        $this->line("Ответила модель:         {$answered}");

        if ($answered === $model) {
            $this->line('Имя в ответе совпадает с проверяемым.');
        } elseif ($listed !== null && ! $listed->contains($model) && $listed->contains($answered)) {
            $this->warn("«{$model}» провайдер принимает как псевдоним модели «{$answered}». Надёжнее указать в настройках настоящее имя «{$answered}»: псевдоним провайдер может убрать.");
        } else {
            $this->line('Имя в ответе отличается от проверяемого: провайдер может подставлять свою версию модели; сверьте с его документацией.');
        }

        $usage = $response->json('usage');

        if (is_array($usage)) {
            $this->line('Токены:                  '.($usage['prompt_tokens'] ?? '?').' на входе, '.($usage['completion_tokens'] ?? '?').' на выходе');
        }

        $reasoning = (int) ($response->json('usage.completion_tokens_details.reasoning_tokens') ?? 0);
        $thoughts = mb_strlen((string) $response->json('choices.0.message.reasoning_content', ''));

        if ($reasoning > 0 || $thoughts > 0) {
            $this->line('Рассуждения:             модель сначала «думает» ('.($reasoning > 0 ? "{$reasoning} токенов" : "{$thoughts} символов").') и только потом отвечает');
        }

        $finish = (string) $response->json('choices.0.finish_reason', '');
        $text = trim((string) $response->json('choices.0.message.content', ''));

        $this->line('Текст ответа:            '.($text === '' ? '(пусто)' : '«'.mb_substr($text, 0, 80).'»'));

        if ($text === '' && $finish === 'length') {
            $this->error("Ответа нет: лимит {$tokens} токенов закончился ещё на рассуждениях. Ассистент в кабинете в таком случае показывает участнице запасной ответ. Увеличьте «Максимум токенов» или выберите модель без рассуждений.");

            return self::FAILURE;
        }

        if ($finish === 'length') {
            $this->warn('Ответ обрезан по лимиту токенов: увеличьте «Максимум токенов».');
        }

        return self::SUCCESS;
    }
}
