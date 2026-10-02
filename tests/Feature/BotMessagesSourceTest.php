<?php

namespace Tests\Feature;

use App\Support\BotMessages;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Охранник правила «все тексты бота — в одном файле»: пока он зелёный, никто не спрятал русскую фразу для
 * участницы прямо в коде, любой ключ в коде существует в resources/data/bot_messages.php, а в файле нет
 * сообщений, которые код не использует.
 */
class BotMessagesSourceTest extends TestCase
{
    /** Файлы, из которых уходят сообщения участницам в Telegram. */
    private function sendingFiles(): array
    {
        $files = [
            base_path('routes/telegram.php'),
            app_path('Services/ProfileModeration.php'),
            app_path('Services/BotCommandSync.php'),
            app_path('Jobs/NotifyOpportunity.php'),
            app_path('Http/Controllers/Account/AccountController.php'),
        ];

        foreach (File::allFiles(app_path('Telegram')) as $file) {
            $files[] = $file->getPathname();
        }

        return $files;
    }

    /** @return array<string, list<string>> файл → строки кода с кириллицей в строковых литералах (без комментариев и логов) */
    private function hardcodedCyrillic(): array
    {
        $found = [];

        foreach ($this->sendingFiles() as $path) {
            $source = (string) file_get_contents($path);
            $lines = explode("\n", $source);

            foreach (token_get_all($source) as $token) {
                if (! is_array($token) || ! in_array($token[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) {
                    continue;
                }

                if (! preg_match('/\p{Cyrillic}/u', $token[1])) {
                    continue;
                }

                $line = $lines[$token[2] - 1] ?? '';

                // Записи для разработчика (лог, исключение) участнице не показываются.
                if (preg_match('/Log::|logger\(|report\(|throw new|Exception\(/', $line)) {
                    continue;
                }

                $found[str_replace(base_path().DIRECTORY_SEPARATOR, '', $path)][] = trim($line);
            }
        }

        return $found;
    }

    public function test_no_text_for_participants_is_hardcoded_in_the_sending_code(): void
    {
        $this->assertSame(
            [],
            $this->hardcodedCyrillic(),
            'Текст для участницы нужно перенести в resources/data/bot_messages.php и брать через BotMessages::text().',
        );
    }

    /** @return list<string> ключи, которые код запрашивает у BotMessages литералом */
    private function usedKeys(): array
    {
        $keys = [];
        $sources = array_merge(File::allFiles(app_path()), File::allFiles(base_path('routes')));

        foreach ($sources as $file) {
            if (preg_match_all('/BotMessages::(?:text|default|template|matches|isCustomized|check)\(\s*[\'"]([a-z_]+)[\'"]/', (string) file_get_contents($file->getPathname()), $m)) {
                array_push($keys, ...$m[1]);
            }
        }

        return array_values(array_unique($keys));
    }

    public function test_every_key_the_code_asks_for_exists_in_the_messages_file(): void
    {
        $used = $this->usedKeys();
        $this->assertNotEmpty($used);

        foreach ($used as $key) {
            $this->assertContains($key, BotMessages::keys(), "Код запрашивает сообщение «{$key}», которого нет в resources/data/bot_messages.php.");
        }
    }

    public function test_every_message_in_the_file_is_used_by_the_code(): void
    {
        $code = '';

        foreach (array_merge(File::allFiles(app_path()), File::allFiles(base_path('routes'))) as $file) {
            $code .= file_get_contents($file->getPathname())."\n";
        }

        foreach (BotMessages::keys() as $key) {
            $this->assertTrue(
                str_contains($code, "'{$key}'") || str_contains($code, "\"{$key}\""),
                "Сообщение «{$key}» есть в файле текстов, но код его нигде не использует.",
            );
        }
    }
}
