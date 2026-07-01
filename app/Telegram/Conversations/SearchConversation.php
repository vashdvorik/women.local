<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Models\BotUser;
use App\Services\EmbeddingService;
use App\Services\MatchingService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SearchConversation extends Conversation
{
    /**
     * @var array<int, array{name: string, username: string|null, description: string|null, expectation: string|null, score: float}>
     */
    protected array $results = [];

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            "🔍 <b>Поиск контактов</b>\n\n"
            . "Опишите, кого или какую экспертизу вы ищете. AI поможет сориентироваться в профилях участниц и предложит близкие варианты.\n\n"
            . "<i>Например:\n"
            . "· ищу партнёрку для экспорта\n"
            . "· нужен эксперт по маркетингу\n"
            . "· хочу найти поставщиков упаковки\n"
            . "· ищу ментора по финансам</i>",
            parse_mode: 'HTML',
        );

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
            $bot->sendMessage('Пожалуйста, напишите текстом, кого или какую поддержку вы ищете.');
            $this->next('handleQuery');
            return;
        }

        $bot->sendMessage('⏳ Ищу подходящие профили...');

        $telegramId  = $bot->userId();
        $currentUser = BotUser::where('telegram_id', $telegramId)->first();

        if (! $currentUser) {
            $bot->sendMessage('⚠️ Профиль не найден. Откройте @WomenComBot и отправьте /start, чтобы подать заявку.');
            $this->end();
            return;
        }

        try {
            /** @var EmbeddingService $embedder */
            $embedder = app(EmbeddingService::class);
            /** @var MatchingService $matcher */
            $matcher = app(MatchingService::class);

            $vector  = $embedder->embed($query);
            $matches = $matcher->searchByQuery($vector, $currentUser, 3);

            if ($matches->isEmpty()) {
                $escapedQuery = htmlspecialchars($query, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $bot->sendMessage(
                    "По запросу «{$escapedQuery}» пока нет близких результатов.\n\n"
                    . "Попробуйте переформулировать: укажите сферу, задачу, тип контакта или формат сотрудничества.",
                    parse_mode: 'HTML',
                );
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
            $bot->sendMessage('⚠️ Поиск сейчас недоступен. Попробуйте ещё раз позже.');
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
        $pct  = (int) round($result['score'] * 100);
        $name = htmlspecialchars((string) $result['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $desc = $result['description']
            ? htmlspecialchars(mb_substr($result['description'], 0, 250), ENT_QUOTES | ENT_HTML5, 'UTF-8')
            : null;
        $exp = $result['expectation']
            ? htmlspecialchars(mb_substr($result['expectation'], 0, 150), ENT_QUOTES | ENT_HTML5, 'UTF-8')
            : null;

        $text  = "👤 <b>{$name}</b>";
        $text .= "\nСовпадение по профилю: <b>{$pct}%</b>";

        if ($desc) {
            $text .= "\n\n{$desc}";
        }

        if ($exp) {
            $text .= "\n\n🔎 <b>Ищет или предлагает:</b> {$exp}";
        }

        $keyboard = InlineKeyboardMarkup::make();

        if ($result['username']) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "Написать @{$result['username']}",
                    url: "https://t.me/{$result['username']}"
                )
            );
        }

        if ($pos === 1 && $total > 1) {
            $more = $total - 1;
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "Показать ещё {$more} →",
                    callback_data: 'search:more'
                )
            );
        }

        $bot->sendMessage($text, parse_mode: 'HTML', reply_markup: $keyboard);
    }
}
