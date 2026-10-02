<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Единственный путь, которым тексты попадают в Telegram: бот, уведомления, рассылка и сообщения сайта в бот.
 *
 * Эталон всех текстов — resources/data/bot_messages.php (там же описан формат). Администратор правит их в
 * админке; правки лежат в site_settings.bot_messages и накладываются поверх эталона. Язык выбирается по
 * языку Telegram участницы (ru / en / ro), по умолчанию русский.
 */
final class BotMessages
{
    public const LOCALES = ['ru', 'en', 'ro'];

    public const DEFAULT_LOCALE = 'ru';

    public const SETTING_KEY = 'bot_messages';

    /** Видимая длина обычного сообщения: лимит Telegram — 4096, остальное оставлено под подставляемые значения. */
    public const MAX_TEXT = 3000;

    public const MAX_BUTTON = 60;

    public const MAX_COMMAND = 256;

    private const CACHE_KEY = 'bot_messages.overrides';

    /** @var array{groups: array<string, array{title: string, hint: string}>, messages: array<string, array<string, mixed>>}|null */
    private static ?array $registry = null;

    /** @return array{groups: array<string, array{title: string, hint: string}>, messages: array<string, array<string, mixed>>} */
    public static function registry(): array
    {
        return self::$registry ??= require resource_path('data/bot_messages.php');
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::registry()['messages']);
    }

    /** @return array<string, mixed> */
    public static function definition(string $key): array
    {
        return self::registry()['messages'][$key]
            ?? throw new InvalidArgumentException("Нет сообщения бота «{$key}» в resources/data/bot_messages.php.");
    }

    /** «ru-RU» → ru, «ro» → ro; всё неизвестное (в том числе отсутствующий язык) — русский. */
    public static function locale(?string $code): string
    {
        $code = strtolower(substr((string) $code, 0, 2));

        return in_array($code, self::LOCALES, true) ? $code : self::DEFAULT_LOCALE;
    }

    /** Текст из эталонного файла; если нужного языка там нет — русский. */
    public static function default(string $key, string $locale): string
    {
        $texts = self::definition($key)['text'];

        return (string) ($texts[$locale] ?? $texts[self::DEFAULT_LOCALE]);
    }

    /** @return array<string, array<string, string>> язык → ключ → текст, изменённый администратором */
    public static function overrides(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function (): array {
                $stored = SiteSetting::read(self::SETTING_KEY, []);

                return is_array($stored) ? $stored : [];
            });
        } catch (\Throwable) {
            // Бот не должен молчать из-за сбоя чтения настроек: тогда работают эталонные тексты.
            return [];
        }
    }

    /** Действующий текст: правка администратора или эталон. Переменные ещё не подставлены. */
    public static function template(string $key, string $locale): string
    {
        $custom = self::overrides()[$locale][$key] ?? null;

        return is_string($custom) && trim($custom) !== '' ? $custom : self::default($key, $locale);
    }

    public static function isCustomized(string $key, string $locale): bool
    {
        return self::template($key, $locale) !== self::default($key, $locale);
    }

    /**
     * Готовый текст сообщения с подставленными переменными.
     *
     * В обычных сообщениях (Telegram-HTML) значения переменных экранируются: имя или запрос участницы не
     * может ни сломать разметку, ни вставить свой тег. Исключение — переменные из 'raw' записи: их код
     * собрал сам как безопасный HTML. В подписях кнопок и описаниях команд HTML нет, значения идут как есть.
     *
     * @param  array<string, scalar|null>  $vars
     */
    public static function text(string $key, ?string $locale = null, array $vars = []): string
    {
        $definition = self::definition($key);
        $template = self::template($key, self::locale($locale));
        $html = $definition['kind'] === 'text';
        $raw = $definition['raw'] ?? [];

        // Один проход: то, что подставлено, заново не разбирается — «{name}» в тексте запроса останется как есть.
        return (string) preg_replace_callback('/\{([a-z_]+)\}/', function (array $m) use ($definition, $vars, $html, $raw): string {
            if (! array_key_exists($m[1], $definition['vars'])) {
                return $m[0];
            }

            $value = (string) ($vars[$m[1]] ?? '');

            return $html && ! in_array($m[1], $raw, true) ? self::escape($value) : $value;
        }, $template);
    }

    /**
     * Узнаёт нажатие кнопки меню по подписи. Сравнивается со всеми языками и с эталоном тоже: у тех, кто ещё
     * не получил новую клавиатуру, в Telegram остаётся прежняя подпись, и кнопка должна продолжать работать.
     */
    public static function matches(string $key, ?string $text): bool
    {
        $needle = trim((string) $text);

        if ($needle === '') {
            return false;
        }

        foreach (self::LOCALES as $locale) {
            if ($needle === trim(self::template($key, $locale)) || $needle === trim(self::default($key, $locale))) {
                return true;
            }
        }

        return false;
    }

    /** Первое слово имени: «Анна Иванова» → «Анна». */
    public static function firstName(?string $fullName): string
    {
        return explode(' ', trim((string) $fullName))[0];
    }

    public static function escape(string $value): string
    {
        // ENT_HTML401: апостроф превращается в числовую &#039;, а не в &apos; — именованную &apos; Telegram не понимает.
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }

    /**
     * Проверка текста, введённого в админке.
     *
     * @return array{0: string, 1: list<string>} приведённый текст и ошибки (по-русски)
     */
    public static function check(string $key, string $text): array
    {
        $definition = self::definition($key);
        $kind = $definition['kind'];
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
        $errors = [];

        if ($kind === 'text') {
            [$text, $errors] = TelegramHtml::check($text);

            if (TelegramHtml::visibleLength($text) > self::MAX_TEXT) {
                $errors[] = 'Текст слишком длинный: не больше '.self::MAX_TEXT.' знаков (лимит Telegram — 4096, часть нужна под подставляемые значения).';
            }
        } else {
            // Подпись кнопки и описание команды — одна строка без разметки.
            $text = trim((string) preg_replace('/\s+/u', ' ', $text));
            $limit = $kind === 'command' ? self::MAX_COMMAND : self::MAX_BUTTON;

            if (mb_strlen($text) > $limit) {
                $errors[] = "Слишком длинно: не больше {$limit} знаков.";
            }
        }

        $used = [];
        preg_match_all('/\{([^{}\s]*)\}/', $text, $found);

        foreach ($found[1] as $name) {
            if (! array_key_exists($name, $definition['vars'])) {
                $errors[] = $definition['vars'] === []
                    ? "Переменная {{$name}} здесь недоступна: в этом сообщении переменных нет."
                    : "Переменная {{$name}} здесь недоступна. Можно использовать: ".implode(', ', array_map(fn (string $v): string => "{{$v}}", array_keys($definition['vars']))).'.';
            }

            $used[] = $name;
        }

        foreach ($definition['required'] ?? [] as $name) {
            if (! in_array($name, $used, true)) {
                $hint = $definition['vars'][$name];

                $errors[] = "В тексте должна быть переменная {{$name}} — ".mb_strtolower(mb_substr($hint, 0, 1)).mb_substr($hint, 1).'.';
            }
        }

        return [$text, array_values(array_unique($errors))];
    }

    /**
     * Сохраняет тексты одного языка. Хранятся только отличия от эталона: пустой текст или текст, совпавший с
     * эталоном, возвращает сообщение к исходному. Тексты должны быть уже проверены через check().
     *
     * @param  array<string, string>  $texts  ключ → текст
     */
    public static function save(string $locale, array $texts): void
    {
        $overrides = self::overrides();
        $forLocale = [];

        foreach (self::keys() as $key) {
            $text = trim((string) ($texts[$key] ?? ''));

            if ($text !== '' && $text !== self::default($key, $locale)) {
                $forLocale[$key] = $text;
            }
        }

        if ($forLocale === []) {
            unset($overrides[$locale]);
        } else {
            $overrides[$locale] = $forLocale;
        }

        SiteSetting::write(self::SETTING_KEY, $overrides);

        self::flush();
    }

    /** Сбрасывает кэш правок: после записи в базу и в тестах. */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
