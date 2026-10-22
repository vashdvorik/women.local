<?php

namespace App\Services;

use App\Models\BotUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Фото профиля участницы, загруженное в кабинете (при регистрации бот сам берёт фото из Telegram, см.
 * RegistrationConversation). Файл режется по центру в квадрат 512 × 512 и сохраняется JPEG на диск public. Имя файла
 * каждый раз новое: у прежнего адреса могла остаться копия в кеше браузера, и участница не увидела бы новую фотографию.
 */
class AvatarStore
{
    public const SIZE = 512;

    private const FOLDER = 'avatars/';

    /** Сохраняет загруженное фото и возвращает путь на диске public (он же ложится в bot_users.avatar_path). */
    public function store(BotUser $user, UploadedFile $file): string
    {
        $image = ImageManager::usingDriver(new Driver())
            ->decode($file->getRealPath())
            ->cover(self::SIZE, self::SIZE);

        $path = self::FOLDER.$user->telegram_id.'-'.Str::lower(Str::random(8)).'.jpg';
        Storage::disk('public')->put($path, (string) $image->encode(new JpegEncoder(quality: 86)));

        $this->delete($user->avatar_path);
        $user->forceFill(['avatar_path' => $path])->save();

        return $path;
    }

    /** Убирает фото: у участницы снова буква-заглушка. */
    public function remove(BotUser $user): void
    {
        $this->delete($user->avatar_path);
        $user->forceFill(['avatar_path' => null])->save();
    }

    /** Файл удаляется только из папки avatars: чужие пути из базы не трогаем. */
    private function delete(?string $path): void
    {
        if ($path !== null && str_starts_with($path, self::FOLDER)) {
            Storage::disk('public')->delete($path);
        }
    }
}
