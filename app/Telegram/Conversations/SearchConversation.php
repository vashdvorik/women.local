<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Services\EmbeddingService;
use App\Services\MatchingService;
use App\Support\BotMessages;
use App\Telegram\TelegramLocale;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Поиск контактов с помощью ИИ. Все тексты — из resources/data/bot_messages.php (раздел «Поиск контактов»).
 */
class SearchConversation extends Conversation
{
    /**
     * @var array<int, array{name: string, username: string|null, description: string|null, expectation: string|null, score: float}>
     */
    protected array $results = [];

    public function start(Nutgram $bot): void
    {
        $this->say($bot, 'search_intro');

        $this->next('handleQuery');
    }

    public function handleQuery(Nutgram $bot): void
    {
        if ($bot->callbackQuery()) {
            $bot->answerCallbackQuery();
            $this->next('handleQuery');
            return;
        }

        $query = trim((string) ($bot->message()?->text ?? ''));

        if ($query === '') {
            $this->say($bot, 'search_query_required');
            $this->next('handleQuery');
            return;
        }

        $telegramId  = $bot->userId();
        $currentUser = BotUser::where('telegram_id', $telegramId)->first();

        if (! $currentUser) {
            $this->say($bot, 'search_profile_missing');
            $this->end();
            return;
        }

        // Диалог мог начаться, пока подписка действовала, и дойти сюда уже после её окончания.
        if (! $currentUser->hasPlan(Plan::Community)) {
            $this->say($bot, 'plan_required', ['url' => route('account.subscription')]);
            $this->end();
            return;
        }

        $this->say($bot, 'search_in_progress');

        try {
            /** @var EmbeddingService $embedder */
            $embedder = app(EmbeddingService::class);
            /** @var MatchingService $matcher */
            $matcher = app(MatchingService::class);

            $vector  = $embedder->embedQuery($query);
            $matches = $matcher->searchByQuery($vector, $currentUser, 3);

            if ($matches->isEmpty()) {
                $this->say($bot, 'search_no_results', ['query' => $query]);
                $this->end();
                return;
            }

            $this->results = $matches->map(fn (array $item) => [
                'name'        => (string) ($item['user']->full_name ?? ''),
                'username'    => $item['user']->telegram_username,
                'description' => $item['user']->description,
                'expectation' => $item['user']->expectation,
                'score'       => (float) $item['score'],
            ])->values()->all();

            $this->sendResult($bot, $this->results[0], 1, count($this->results));

            if (count($this->results) > 1) {
                $this->next('handleShowMore');
            } else {
                $this->end();
            }
        } catch (\Throwable $e) {
            logger()->warning('Bot AI search failed', ['error' => $e->getMessage()]);
            $this->say($bot, 'search_unavailable');
            $this->end();
        }
    }

    public function handleShowMore(Nutgram $bot): void
    {
        if ($bot->callbackQuery()?->data === 'search:more') {
            $bot->answerCallbackQuery();

            foreach (array_slice($this->results, 1) as $i => $result) {
                $this->sendResult($bot, $result, $i + 2, count($this->results));
            }

            $this->end();
            return;
        }

        $this->handleQuery($bot);
    }

    private function sendResult(Nutgram $bot, array $result, int $pos, int $total): void
    {
        $locale = TelegramLocale::for($bot);

        $text = BotMessages::text('search_result', $locale, [
            'name'    => (string) $result['name'],
            'percent' => (int) round($result['score'] * 100),
        ]);

        // Описание и «что ищет» — данные участницы, а не наши слова: обрезаются и экранируются, оформление — из файла текстов.
        if ($result['description']) {
            $text .= "\n\n" . BotMessages::escape(mb_substr($result['description'], 0, 250));
        }

        if ($result['expectation']) {
            $text .= "\n\n" . BotMessages::text('search_result_expectation', $locale, [
                'text' => mb_substr($result['expectation'], 0, 150),
            ]);
        }

        $keyboard = InlineKeyboardMarkup::make();

        if ($result['username']) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    BotMessages::text('search_write_button', $locale, ['username' => $result['username']]),
                    url: "https://t.me/{$result['username']}"
                )
            );
        }

        if ($pos === 1 && $total > 1) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    BotMessages::text('search_more_button', $locale, ['count' => $total - 1]),
                    callback_data: 'search:more'
                )
            );
        }

        $bot->sendMessage($text, parse_mode: 'HTML', reply_markup: $keyboard);
    }

    /** Сообщение из файла текстов на языке Telegram участницы. */
    private function say(Nutgram $bot, string $key, array $vars = []): void
    {
        $bot->sendMessage(BotMessages::text($key, TelegramLocale::for($bot), $vars), parse_mode: 'HTML');
    }
}
