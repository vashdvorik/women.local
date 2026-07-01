<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class RegistrationConversation extends Conversation
{
    protected ?string $fullName = null;
    protected ?string $description = null;
    protected ?string $expectation = null;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "Здравствуйте! 👋\n\nЭто заявка на участие в Women Entrepreneurs Platform of the Two Banks — цифровом пространстве для женщин-предпринимательниц, где можно представить бизнес, учиться, находить контакты и узнавать о возможностях.\n\nЯ задам несколько коротких вопросов. Это займёт 2-3 минуты.\n\nГотовы начать?",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('Да, начать', callback_data: 'reg:yes')),
        );

        $this->next('waitForConfirmation');
    }

    public function waitForConfirmation(Nutgram $bot): void
    {
        if ($bot->callbackQuery()?->data === 'reg:yes') {
            $bot->answerCallbackQuery();

            $bot->sendMessage('Как вас зовут? Укажите имя и фамилию.');
            $this->next('handleName');

            return;
        }

        $this->next('waitForConfirmation');
    }

    public function handleName(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if (empty($text)) {
            $bot->sendMessage('Пожалуйста, отправьте имя и фамилию текстом.');
            $this->next('handleName');

            return;
        }

        $this->fullName = $text;

        $bot->sendMessage(
            text: "Что вы представляете?\n\nРасскажите о бизнесе, сфере, продуктах, услугах, опыте или идее. Можно добавить ссылку.",
        );

        $this->next('handleDescription');
    }

    public function handleDescription(Nutgram $bot): void
    {
        $text = $bot->message()?->text;

        if (empty($text)) {
            $bot->sendMessage('Пожалуйста, опишите ваш бизнес, опыт или идею текстом.');
            $this->next('handleDescription');

            return;
        }

        $this->description = $text;

        $bot->sendMessage(
            text: "Что вы ищете на платформе и чем можете быть полезны другим участницам?\n\nНапример: партнёры, клиенты, поставщики, знания, менторство, новые рынки, услуги или опыт.",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('Пропустить', callback_data: 'reg:skip')),
        );

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
            'first_name'        => $telegramUser->first_name,
            'full_name'         => $this->fullName,
            'description'       => $this->description,
            'expectation'       => $this->expectation,
            'status'            => BotUser::STATUS_PENDING,
        ]);

        $this->downloadAvatar($bot, $botUser);

        ComputeUserEmbedding::dispatch($botUser);

        $firstName = explode(' ', (string) $this->fullName)[0];

        $bot->sendMessage(
            "Спасибо, {$firstName}!\n\nЗаявка отправлена на рассмотрение. После одобрения вы получите доступ к кабинету, каталогу участниц, рекомендациям и возможностям платформы.\n\nЕсли есть вопрос, напишите команде проекта: @lesnichenkoP\n\nСайт платформы:\n" . config('nutgram.community_url', config('app.url')),
        );

        $this->end();
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
