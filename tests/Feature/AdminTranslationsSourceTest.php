<?php

namespace Tests\Feature;

use App\Support\AdminI18n;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Страж перевода админки. Русский текст в админке пишется только как __('Русский текст') (в скриптах — t('…')):
 * русский служит ключом, английский лежит в lang/en.json (скрипты — lang/en/adminjs.php). Тест не даёт
 * добавить «голый» русский текст в шаблон, контроллер или скрипт и не даёт забыть английский перевод.
 */
class AdminTranslationsSourceTest extends TestCase
{
    private const CYRILLIC = '/[А-Яа-яЁё]/u';

    /** Шаблоны, из которых состоит админка. */
    private const BLADE_DIRS = [
        'resources/views/admin', 'resources/views/components/admin', 'resources/views/components/forms',
        'resources/views/components/layouts', 'resources/views/auth', 'resources/views/layouts',
    ];

    /** PHP-классы, чьи сообщения видит администратор. */
    private const PHP_FILES = [
        'app/Http/Controllers/Admin', 'app/Http/Controllers/Concerns', 'app/Http/Requests', 'app/Actions',
        'app/Enums/PublishStatus.php', 'app/Models/Payment.php', 'app/Models/Subscription.php',
        'app/Services/ImpactReport.php', 'app/Support/AdminCamp.php', 'app/Support/TelegramHtml.php', 'app/Support/BotMessages.php',
    ];

    /**
     * Русские строки в этих файлах переводить не нужно: записи в журнал для разработчиков и значения,
     * которые переводятся уже при выводе (константы не могут вызывать __()).
     */
    private const NOT_FOR_TRANSLATION = [
        'app/Http/Controllers/Admin/SubscriptionController.php' => ['Цены подписки изменены в админке', 'было', 'стало'],
        'app/Support/BotMessages.php' => ['Нет сообщения бота «', '» в resources/data/bot_messages.php.'],
    ];

    /** @return list<string> */
    private function files(array $paths, string $extension): array
    {
        $found = [];

        foreach ($paths as $path) {
            $full = base_path($path);

            if (is_file($full)) {
                $found[] = $full;

                continue;
            }

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($full, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if (str_ends_with((string) $file, $extension)) {
                    $found[] = (string) $file;
                }
            }
        }

        sort($found);

        return $found;
    }

    private function relative(string $file): string
    {
        return str_replace('\\', '/', substr($file, strlen(base_path()) + 1));
    }

    private function unescape(string $literal): string
    {
        return str_replace(["\\'", '\\\\'], ["'", '\\'], $literal);
    }

    /** Ключи, которые админка запрашивает через __(): литералы в коде и данные, переводимые при выводе. */
    private function usedKeys(): array
    {
        $keys = [];

        foreach ([...$this->files(self::BLADE_DIRS, '.blade.php'), ...$this->files(self::PHP_FILES, '.php'), ...$this->files(['app/Support/CardTone.php', 'app/Models/SiteSetting.php'], '.php')] as $file) {
            preg_match_all("/(?<![\\w\$>])__\\(\\s*'((?:\\\\\\\\.|[^'\\\\\\\\])*)'/", file_get_contents($file), $found);

            foreach ($found[1] as $literal) {
                $key = $this->unescape($literal);

                if (preg_match(self::CYRILLIC, $key) || $key === 'Română') {
                    $keys[$key] = $this->relative($file);
                }
            }
        }

        // Данные, которые показываются через __($значение): палитра карточек, темы кабинета, реестр сообщений бота.
        foreach ([...\App\Support\CardTone::OPTIONS, ...\App\Models\SiteSetting::ACCOUNT_THEMES] as $label) {
            if (preg_match(self::CYRILLIC, $label)) {
                $keys[$label] = 'константа';
            }
        }

        $registry = require base_path('resources/data/bot_messages.php');

        foreach ($registry['groups'] as $group) {
            $keys[$group['title']] = $keys[$group['hint']] = 'resources/data/bot_messages.php';
        }

        foreach ($registry['messages'] as $message) {
            foreach (['title', 'when', 'inactive'] as $field) {
                if (! empty($message[$field])) {
                    $keys[$message[$field]] = 'resources/data/bot_messages.php';
                }
            }

            foreach ($message['vars'] ?? [] as $hint) {
                $keys[$hint] = 'resources/data/bot_messages.php';
            }
        }

        return array_filter($keys, fn (string $_, string $key): bool => (bool) preg_match(self::CYRILLIC, $key) || $key === 'Română', ARRAY_FILTER_USE_BOTH);
    }

    /** @return array<string, string> */
    private function english(): array
    {
        return json_decode(file_get_contents(lang_path('en.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    // ---------------------------------------------------------------- словарь

    public function test_every_russian_key_used_by_the_admin_has_an_english_translation(): void
    {
        $english = $this->english();
        $missing = array_diff_key($this->usedKeys(), $english);

        $this->assertSame([], $missing, 'Добавьте перевод в lang/en.json: '.json_encode(array_keys($missing), JSON_UNESCAPED_UNICODE));
    }

    public function test_the_english_file_has_no_stale_keys(): void
    {
        $stale = array_diff_key($this->english(), $this->usedKeys());

        $this->assertSame([], array_keys($stale), 'Ключи в lang/en.json, которых нет в коде (удалите): '.json_encode(array_keys($stale), JSON_UNESCAPED_UNICODE));
    }

    public function test_english_texts_keep_placeholders_and_markup_of_the_original(): void
    {
        $placeholders = fn (string $text): array => tap(array_unique(preg_match_all('/:[a-z][a-z0-9_]*/', $text, $m) ? $m[0] : []), 'sort');
        $tags = fn (string $text): string => implode('', preg_match_all('/<\/?[a-z][^>]*>/i', $text, $m) ? $m[0] : []);

        foreach ($this->english() as $russian => $english) {
            $this->assertNotSame('', trim($english), "Пустой перевод: {$russian}");
            $this->assertSame(array_values($placeholders($russian)), array_values($placeholders($english)), "Подстановки не совпали: {$russian}");
            $this->assertSame($tags($russian), $tags($english), "Теги не совпали: {$russian}");
            $this->assertDoesNotMatchRegularExpression(self::CYRILLIC, $english, "В английском тексте осталась кириллица: {$russian}");
        }
    }

    public function test_every_script_text_has_an_english_translation(): void
    {
        $used = [];

        foreach ($this->files(['resources/js'], '.js') as $file) {
            preg_match_all("/\\bt\\(\\s*'((?:\\\\\\\\.|[^'\\\\\\\\])*)'/", file_get_contents($file), $found);

            foreach ($found[1] as $literal) {
                $used[str_replace("\\'", "'", $literal)] = basename($file);
            }
        }

        $dictionary = AdminI18n::scriptDictionary('en');

        $this->assertNotEmpty($used);
        $this->assertSame([], array_keys(array_diff_key($used, $dictionary)), 'Добавьте перевод в lang/en/adminjs.php');
        $this->assertSame([], array_keys(array_diff_key($dictionary, $used)), 'Лишние ключи в lang/en/adminjs.php');
    }

    // ---------------------------------------------------------------- «голый» русский текст

    public function test_admin_templates_have_no_untranslated_russian_text(): void
    {
        $offenders = [];

        foreach ($this->files(self::BLADE_DIRS, '.blade.php') as $file) {
            $source = file_get_contents($file);
            $source = preg_replace('/\{\{--.*?--\}\}/s', '', $source);
            $source = preg_replace('/<!--.*?-->/s', '', $source);
            $source = preg_replace_callback('/@props\(\[.*?\]\)/s', fn (array $m): string => preg_replace('~//[^\n]*~', '', $m[0]), $source);
            $source = preg_replace_callback('/@php\b.*?@endphp/s', fn (array $m): string => preg_replace('~(?<!:)//[^\n]*|/\*.*?\*/~s', '', $m[0]), $source);
            $source = preg_replace("/__\\(\\s*'(?:\\\\\\\\.|[^'\\\\\\\\])*'/", '__(', $source);

            if (preg_match_all('/[^\n]*[А-Яа-яЁё][^\n]*/u', $source, $lines)) {
                foreach ($lines[0] as $line) {
                    $offenders[] = $this->relative($file).': '.trim(mb_substr($line, 0, 120));
                }
            }
        }

        $this->assertSame([], $offenders, "Оберните русский текст в __('…') и добавьте перевод в lang/en.json");
    }

    public function test_admin_php_classes_have_no_untranslated_russian_messages(): void
    {
        $offenders = [];

        foreach ($this->files(self::PHP_FILES, '.php') as $file) {
            $relative = $this->relative($file);
            $tokens = array_values(array_filter(token_get_all(file_get_contents($file)), fn ($t): bool => ! is_array($t) || ! in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)));

            foreach ($tokens as $i => $token) {
                if (! is_array($token) || ! in_array($token[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true) || ! preg_match(self::CYRILLIC, $token[1])) {
                    continue;
                }

                $wrapped = $token[0] === T_CONSTANT_ENCAPSED_STRING
                    && ($tokens[$i - 1] ?? null) === '('
                    && is_array($tokens[$i - 2] ?? null) && $tokens[$i - 2][1] === '__';

                $allowed = array_filter(self::NOT_FOR_TRANSLATION[$relative] ?? [], fn (string $part): bool => str_contains($token[1], $part));

                if (! $wrapped && ! $allowed) {
                    $offenders[] = "{$relative}:{$token[2]} ".mb_substr(trim($token[1]), 0, 90);
                }
            }
        }

        $this->assertSame([], $offenders, "Оберните сообщение в __('…') и добавьте перевод в lang/en.json");
    }

    public function test_admin_scripts_have_no_untranslated_russian_text(): void
    {
        $offenders = [];

        foreach ($this->files(['resources/js'], '.js') as $file) {
            $source = file_get_contents($file);
            $source = preg_replace('~/\*.*?\*/~s', '', $source);
            $source = preg_replace('~(?<![:\'"])//[^\n]*~', '', $source);
            $source = preg_replace("/\\bt\\(\\s*'(?:\\\\\\\\.|[^'\\\\\\\\])*'/", 't(', $source);
            // Названия языков (endonym) в списке вкладок — не интерфейс.
            $source = preg_replace("/localeNames:[^\n]*/", '', $source);

            if (preg_match_all('/[^\n]*[А-Яа-яЁё][^\n]*/u', $source, $lines)) {
                foreach ($lines[0] as $line) {
                    $offenders[] = basename($file).': '.trim(mb_substr($line, 0, 120));
                }
            }
        }

        $this->assertSame([], $offenders, "Оберните текст в t('…') и добавьте перевод в lang/en/adminjs.php");
    }
}
