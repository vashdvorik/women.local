<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use App\Support\BotMessages;
use App\Telegram\TelegramLocale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Анкета новой участницы. Все тексты — из resources/data/bot_messages.php (раздел «Заявка на участие»).
 */
class RegistrationConversation extends Conversation
{
    protected ?string $fullName = null;
    protected ?string $description = null;
    protected ?string $expectation = null;

    public function start(Nutgram $bot): void
    {
        $this->say($bot, 'registration_welcome', keyboard: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make($this->label($bot, 'registration_start_button'), callback_data: 'reg:yes')));

        $this->next('waitForConfirmation');
    }

    public function waitForConfirmation(Nutgram $bot): void
    {
        if ($bot->callbackQuery()?->data === 'reg:yes') {
            $bot->answerCallbackQuery();

            $this->say($bot, 'registration_ask_name');
            $this->next('handleName');

            return;
        }

        $this->next('waitForConfirmation');
    }

    public function handleName(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if (empty($text)) {
            $this->say($bot, 'registration_name_required');
            $this->next('handleName');

            return;
        }

        $this->fullName = $text;

        $this->say($bot, 'registration_ask_description');

        $this->next('handleDescription');
    }

    public function handleDescription(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if (empty($text)) {
            $this->say($bot, 'registration_description_required');
            $this->next('handleDescription');

            return;
        }

        $this->description = $text;

        $this->say($bot, 'registration_ask_expectation', keyboard: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make($this->label($bot, 'registration_skip_button'), callback_data: 'reg:skip')));

        $this->next('handleExpectation');
    }

    public function handleExpectation(Nutgram $bot): void
    {
        if ($bot->callbackQuery()?->data === 'reg:skip') {
            $bot->answerCallbackQuery();
            $this->expectation = null;
        } else {
            $text = $bot->message()?->text;
            if (empty($text)) {
                $this->next('handleExpectation');

                return;
            }
            $this->expectation = $text;
        }

        $this->save($bot);
    }

    private function save(Nutgram $bot): void
    {
        $telegramUser = $bot->user();

        $botUser = BotUser::create([
            'telegram_id'       => $telegramUser->id,
            'telegram_username' => $telegramUser->username,
            'locale'            => TelegramLocale::for($bot),
            'first_name'        => $telegramUser->first_name,
            'full_name'         => $this->fullName,
            'description'       => $this->description,
            'expectation'       => $this->expectation,
            'status'            => BotUser::STATUS_PENDING,
        ]);

        $this->downloadAvatar($bot, $botUser);

        ComputeUserEmbedding::dispatch($botUser);

        $this->say($bot, 'registration_done', [
            'name'     => BotMessages::firstName($this->fullName),
            'site_url' => config('nutgram.community_url', config('app.url')),
        ]);

        $this->end();
    }

    /** Сообщение из файла текстов на языке Telegram участницы. */
    private function say(Nutgram $bot, string $key, array $vars = [], ?InlineKeyboardMarkup $keyboard = null): void
    {
        $bot->sendMessage(
            text: BotMessages::text($key, TelegramLocale::for($bot), $vars),
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );
    }

    /** Подпись кнопки на языке Telegram участницы. */
    private function label(Nutgram $bot, string $key): string
    {
        return BotMessages::text($key, TelegramLocale::for($bot));
    }

    private function downloadAvatar(Nutgram $bot, BotUser $botUser): void
    {
        try {
            $photos = $bot->getUserProfilePhotos(user_id: $botUser->telegram_id, limit: 1);

            if (! $photos || $photos->total_count === 0) {
                return;
            }

            $photoSizes = $photos->photos[0];
            $largest    = $photoSizes[count($photoSizes) - 1];

            $fileInfo = $bot->getFile(file_id: $largest->file_id);

            if (! $fileInfo?->file_path) {
                return;
            }

            $token    = config('nutgram.token');
            $response = Http::timeout(10)->get(
                "https://api.telegram.org/file/bot{$token}/{$fileInfo->file_path}"
            );

            if (! $response->successful()) {
                return;
            }

            $path = "avatars/{$botUser->telegram_id}.jpg";
            Storage::disk('public')->put($path, $response->body());

            $botUser->update(['avatar_path' => $path]);
        } catch (\Throwable) {
            // Avatar download must not block registration.
        }
    }
}
