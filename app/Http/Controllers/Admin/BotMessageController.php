<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BotCommandSync;
use App\Support\BotMessages;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * «Сообщения бота» — лагерь «Кабинеты участниц». Все тексты, которые бот и сайт отправляют участницам в Telegram,
 * берутся из resources/data/bot_messages.php (эталон); здесь администратор правит их по языкам. Правки хранятся в
 * базе поверх эталона, поэтому переживают выкладку кода. Логика — в App\Support\BotMessages.
 */
class BotMessageController extends Controller
{
    /** Названия языков для вкладок. */
    public const LOCALE_LABELS = ['ru' => 'Русский', 'en' => 'English', 'ro' => 'Română'];

    public function edit(Request $request): View
    {
        $locale = $this->locale($request);
        $registry = BotMessages::registry();

        $groups = [];

        foreach ($registry['groups'] as $id => $group) {
            $groups[$id] = $group + ['messages' => []];
        }

        foreach ($registry['messages'] as $key => $definition) {
            $current = BotMessages::template($key, $locale);

            $groups[$definition['group']]['messages'][$key] = $definition + [
                'default' => BotMessages::default($key, $locale),
                'current' => $current,
                'rows' => max(2, min(14, substr_count($current, "\n") + 2)),
            ];
        }

        return view('admin.cabinets.bot-messages', [
            'locale' => $locale,
            'localeLabels' => self::LOCALE_LABELS,
            'groups' => array_filter($groups, fn (array $group): bool => $group['messages'] !== []),
            'total' => count($registry['messages']),
        ]);
    }

    public function update(Request $request, BotCommandSync $commands): RedirectResponse
    {
        $locale = $this->locale($request);
        $input = (array) $request->input('messages', []);

        $before = [];
        $texts = [];
        $errors = [];

        foreach (BotMessages::keys() as $key) {
            $definition = BotMessages::definition($key);
            $before[$key] = BotMessages::template($key, $locale);

            // Поля нет в форме (сообщение добавили в файл после открытия страницы): оставляем как есть.
            if (! array_key_exists($key, $input)) {
                $texts[$key] = $before[$key];

                continue;
            }

            // Пустое поле — «вернуть исходный текст»: пустое сообщение Telegram всё равно не примет.
            if (trim((string) $input[$key]) === '') {
                $texts[$key] = '';

                continue;
            }

            [$text, $problems] = BotMessages::check($key, (string) $input[$key]);

            if ($problems !== []) {
                $errors["messages.{$key}"] = '«'.$definition['title'].'»: '.implode(' ', $problems);

                continue;
            }

            $texts[$key] = $text;
        }

        foreach ($this->triggerConflicts($locale, $texts) as $key => $other) {
            $errors["messages.{$key}"] ??= '«'.BotMessages::definition($key)['title']."»: такая подпись уже у кнопки «{$other}». Подписи кнопок меню должны различаться: по ним бот узнаёт, какую кнопку нажали.";
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        BotMessages::save($locale, $texts);

        $changed = 0;
        $commandsChanged = false;

        foreach (BotMessages::keys() as $key) {
            if (BotMessages::template($key, $locale) !== $before[$key]) {
                $changed++;
                $commandsChanged = $commandsChanged || BotMessages::definition($key)['kind'] === 'command';
            }
        }

        $message = $changed === 0
            ? 'Изменений нет: тексты остались прежними.'
            : "Сообщения сохранены ({$this->label($locale)}). Изменено: {$changed}. Бот уже отвечает по-новому.";

        if ($commandsChanged) {
            $message .= ' '.$this->syncCommands($commands);
        }

        return redirect()->route('admin.cabinets.bot-messages', ['lang' => $locale])->with('success', $message);
    }

    /** Язык вкладки: ru / en / ro, всё остальное — русский. */
    private function locale(Request $request): string
    {
        $lang = (string) $request->input('lang', $request->query('lang', BotMessages::DEFAULT_LOCALE));

        return in_array($lang, BotMessages::LOCALES, true) ? $lang : BotMessages::DEFAULT_LOCALE;
    }

    private function label(string $locale): string
    {
        return self::LOCALE_LABELS[$locale];
    }

    /**
     * Подписи кнопок меню, по которым бот узнаёт нажатие, не должны совпадать между собой ни на одном языке
     * (включая эталонные: их бот узнаёт тоже). Возвращает ключи кнопок этой вкладки, у которых нашлась пара.
     *
     * @param  array<string, string>  $texts  новые тексты вкладки (пустой — эталон)
     * @return array<string, string> ключ кнопки → название кнопки, с которой она совпала
     */
    private function triggerConflicts(string $locale, array $texts): array
    {
        $triggers = array_filter(BotMessages::keys(), fn (string $key): bool => (BotMessages::definition($key)['trigger'] ?? false) === true);

        $owners = [];

        foreach ($triggers as $key) {
            foreach (BotMessages::LOCALES as $code) {
                $effective = $code === $locale
                    ? (($texts[$key] ?? '') !== '' ? $texts[$key] : BotMessages::default($key, $code))
                    : BotMessages::template($key, $code);

                foreach ([$effective, BotMessages::default($key, $code)] as $label) {
                    $owners[mb_strtolower(trim($label))][$key] = true;
                }
            }
        }

        $conflicts = [];

        foreach ($owners as $keys) {
            if (count($keys) > 1) {
                foreach (array_keys($keys) as $key) {
                    $other = collect(array_keys($keys))->first(fn (string $k): bool => $k !== $key);
                    $conflicts[$key] = BotMessages::definition($other)['title'];
                }
            }
        }

        return $conflicts;
    }

    /** Отправляет обновлённые описания команд в Telegram; сбой не отменяет сохранение. */
    private function syncCommands(BotCommandSync $commands): string
    {
        if (! $commands->isConfigured()) {
            return 'Меню команд в Telegram не обновлено: не задан TELEGRAM_TOKEN.';
        }

        try {
            $commands->sync();
        } catch (\Throwable $e) {
            report($e);

            return 'Тексты сохранены, но обновить меню команд в Telegram не удалось. Повторите: php artisan bot:sync-commands.';
        }

        return 'Меню команд в Telegram обновлено.';
    }
}
