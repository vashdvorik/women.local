<?php

namespace App\Http\Controllers\Admin;

use App\Actions\StoreUploadedFile;
use App\Actions\StoreUploadedImage;
use App\Http\Controllers\Controller;
use App\Support\AspectRatio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UploadController extends Controller
{
    /**
     * Изображение отправляется на сервер в момент выбора файла, отдельным
     * запросом (SETUP.md §8). Обработка: уменьшение → кадрирование по центру
     * под соотношение слота → WebP.
     */
    public function store(Request $request, StoreUploadedImage $action): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
            'slot' => ['required', 'string', Rule::in(array_keys(AspectRatio::SLOTS))],
            // Прямоугольник кадрирования из инструмента в форме (необязателен).
            'crop' => ['nullable', 'array'],
            'crop.x' => ['required_with:crop', 'numeric', 'min:0'],
            'crop.y' => ['required_with:crop', 'numeric', 'min:0'],
            'crop.width' => ['required_with:crop', 'numeric', 'min:1'],
            'crop.height' => ['required_with:crop', 'numeric', 'min:1'],
        ], [], [
            'image' => 'изображение',
        ]);

        $path = $action->handle(
            $validated['image'],
            $validated['slot'],
            $validated['crop'] ?? null,
        );

        return response()->json([
            'path' => $path,
            'url' => '/uploads/'.$path,
        ]);
    }

    /**
     * PDF для блока «Файл (PDF)»: каталог, брошюра. Загружается сразу при выборе файла, как
     * и картинка. Формат проверяется по содержимому файла, а не по расширению в имени:
     * переименованный в .pdf чужой файл не пройдёт.
     */
    public function storeFile(Request $request, StoreUploadedFile $action): JsonResponse
    {
        $maxMb = StoreUploadedFile::MAX_KB / 1024;

        $validated = $request->validate([
            'file' => ['bail', 'required', 'file', 'mimetypes:application/pdf', 'max:'.StoreUploadedFile::MAX_KB],
        ], [
            'file.required' => 'Выберите PDF-файл.',
            'file.uploaded' => "Файл не загрузился: он больше, чем разрешено настройками сервера. Допустимо до {$maxMb} МБ.",
            'file.mimetypes' => 'Нужен файл в формате PDF.',
            'file.max' => "Файл слишком большой: допустимо до {$maxMb} МБ.",
        ], [
            'file' => 'файл',
        ]);

        $stored = $action->handle($validated['file']);

        return response()->json($stored + ['url' => '/uploads/'.$stored['path']]);
    }
}
