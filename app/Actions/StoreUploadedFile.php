<?php

namespace App\Actions;

use App\Support\Blocks;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Сохранение PDF для блока «Файл (PDF)» (каталоги, брошюры). Файл лежит на диске `uploads`
 * рядом с картинками, но в отдельной папке: `files/ГГГГ/ММ/случайное-имя.pdf`.
 *
 * Имя на диске генерируется здесь и никогда не берётся у пользователя: так нельзя подсунуть
 * ни путь, ни расширение. Исходное имя возвращается отдельно — редактор показывает его, чтобы
 * было видно, что загружено. Проверку формата (по содержимому, а не по расширению) и размера
 * делает контроллер до вызова.
 *
 * Возвращается относительный путь, как у картинок, — именно он ложится в данные блока.
 */
class StoreUploadedFile
{
    /** Максимальный размер PDF в килобайтах (так считает правило `max` в Laravel): 25 МБ. */
    public const MAX_KB = 25600;

    /**
     * @return array{path: string, name: ?string, size: int}
     */
    public function handle(UploadedFile $file): array
    {
        $directory = 'files/'.now()->format('Y/m');
        $filename = Str::lower(Str::random(16)).'.pdf';

        $size = (int) $file->getSize();

        // putFileAs читает файл потоком: PDF на десятки мегабайт не попадает в память целиком.
        Storage::disk('uploads')->putFileAs($directory, $file, $filename);

        return [
            'path' => $directory.'/'.$filename,
            'name' => Blocks::fileName($file->getClientOriginalName()),
            'size' => $size,
        ];
    }
}
