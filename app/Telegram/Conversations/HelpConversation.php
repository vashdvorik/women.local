<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Models\BotUser;
use App\Services\BotHelpAssistant;
use App\Support\BotMessages;
use App\Telegram\BotReplies;
use App\Telegram\TelegramKeyboards;
use App\Telegram\TelegramLocale;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use Throwable;

/**
 * Разговор с AI помощником в боте: участница пишет вопросы обычными словами, помощник отвечает (как войти в кабинет,
 * что где находится, чем отличаются тарифы). Разговор идёт, пока она не закроет его кнопкой «Закрыть помощника»,
 * не нажмёт кнопку главного меню или не отправит команду (/start, /login). Все тексты — из resources/data/bot_messages.php
 * (раздел «AI помощник»), знания помощника — App\Support\BotHelp (правятся в админке).
 */
class HelpConversation extends Conversation
{
    /** @var list<array{role: string, content: string}> последние реплики: модели нужен контекст вопроса «а как это сделать?» */
    protected array $history = [];

    public function start(Nutgram $bot): void
    {
        $user = BotUser::where('telegram_id', $bot->userId())->first();

        // Помощник — для одобренных: у остальных свои ответы (заявка рассматривается, заявки нет).
        if ($user === null || ! $user->isApproved()) {
            BotReplies::startOrLogin($bot, sendLoginLink: false);
            $this->end();

            return;
        }

        $locale = TelegramLocale::for($bot, $user);

        $bot->sendMessage(
            BotMessages::text('help_intro', $locale, [
                'name' => BotMessages::firstName($user->full_name),
                'contact' => (string) config('site.contacts.telegram'),
            ]),
            parse_mode: 'HTML',
            reply_markup: TelegramKeyboards::helpMenu($locale),
        );

        $this->next('ask');
    }

    public function ask(Nutgram $bot): void
    {
        if ($bot->callbackQuery()) {
            $bot->answerCallbackQuery();
            $this->next('ask');

            return;
        }

        $user = BotUser::where('telegram_id', $bot->userId())->first();

        // Доступ закрыли (или анкету удалили), пока шёл разговор.
        if ($user === null || ! $user->isApproved()) {
            $this->end();
            BotReplies::startOrLogin($bot, sendLoginLink: false);

            return;
        }

        $locale = TelegramLocale::for($bot, $user);
        $text = trim((string) ($bot->message()?->text ?? ''));

        // Команды и кнопки меню выводят из разговора: участница не должна «застрять» в помощнике.
        if (str_starts_with($text, '/')) {
            $this->end();
            BotReplies::handleCommandText($bot, $text);

            return;
        }

        if (BotMessages::matches('help_exit_button', $text)) {
            $this->end();
            $bot->sendMessage(BotMessages::text('help_exit', $locale), parse_mode: 'HTML', reply_markup: TelegramKeyboards::mainMenu($locale));

            return;
        }

        if (BotMessages::matches('menu_help_button', $text)) {
            $this->end();
            self::begin($bot);

            return;
        }

        if (BotReplies::isMenuText($text)) {
            $this->end();
            BotReplies::handleMenuText($bot, $user, $text, $locale);

            return;
        }

        if ($text === '') {
            $this->say($bot, 'help_text_only', $locale);
            $this->next('ask');

            return;
        }

        $assistant = app(BotHelpAssistant::class);

        if ($assistant->remaining($user) <= 0) {
            $this->say($bot, 'help_limit', $locale, ['limit' => BotHelpAssistant::DAILY_LIMIT]);
            $this->next('ask');

            return;
        }

        $bot->sendChatAction('typing');

        try {
            $reply = $assistant->answer($user, $text, $this->history, $locale);
        } catch (Throwable $e) {
            logger()->warning('Bot help assistant failed', ['error' => $e->getMessage()]);
            $this->say($bot, 'help_unavailable', $locale);
            $this->next('ask');

            return;
        }

        $this->history[] = ['role' => 'user', 'content' => $text];
        $this->history[] = ['role' => 'assistant', 'content' => $reply];
        $this->history = array_slice($this->history, -BotHelpAssistant::HISTORY);

        // Без parse_mode: ответ модели — обычный текст, любые символы в нём безопасны.
        $bot->sendMessage($reply);

        $this->next('ask');
    }

    /** Сообщение из файла текстов (с контактом команды проекта по умолчанию). */
    private function say(Nutgram $bot, string $key, string $locale, array $vars = []): void
    {
        $bot->sendMessage(
            BotMessages::text($key, $locale, $vars + ['contact' => (string) config('site.contacts.telegram')]),
            parse_mode: 'HTML',
        );
    }
}
