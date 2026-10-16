<?php

namespace App\Http\Controllers\Admin;

use App\Actions\StoreUploadedFile;
use App\Actions\StoreUploadedImage;
use App\Http\Controllers\Controller;
use App\Models\ImageSource;
use App\Support\AspectRatio;
use App\Support\Blocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadController extends Controller
{
    /**
     * Изображение отправляется на сервер в момент выбора файла, отдельным
     * запросом (SETUP.md §8). Обработка: кадр по рамке (или по центру) под
     * соотношение слота → WebP для сайта, а само фото целиком сжимается и
     * сохраняется отдельно, чтобы кадр можно было изменить позже (recrop()).
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
            'image' => __('изображение'),
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
     * Что нужно окну кадрирования, чтобы открыть уже загруженную картинку: где лежит сжатое фото целиком, его размер
     * и рамка. 404, если у картинки нет сохранённого фото (она загружена до этой возможности): её можно только
     * заменить, загрузив файл заново.
     */
    public function sourceInfo(Request $request): JsonResponse
    {
        $source = $this->sourceFor($request->query('path'));

        if ($source === null) {
            return response()->json(['message' => __('У этой картинки нет сохранённого фото целиком: перекадрировать её нельзя. Загрузите её заново.')], 404);
        }

        return response()->json([
            'path' => $source->path,
            'source_url' => route('admin.uploads.source', $source),
            'width' => $source->source_width,
            'height' => $source->source_height,
            'crop' => $source->cropRect(),
            'slot' => $source->slot,
            'ratio' => AspectRatio::dimensions($source->slot),
        ]);
    }

    /** Сжатое фото целиком для окна кадрирования. Только администратору: в нём виден и тот край, что остался за кадром. */
    public function source(ImageSource $imageSource): StreamedResponse
    {
        abort_unless(Storage::disk('originals')->exists($imageSource->source_path), 404);

        return Storage::disk('originals')->response($imageSource->source_path, null, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Новая рамка для загруженной картинки: кадр для сайта пересчитывается из сохранённого фото целиком. Возвращает путь
     * нового кадра; прежний остаётся на диске (форма, где его заменили, могла не сохраниться).
     */
    public function recrop(Request $request, StoreUploadedImage $action): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:255'],
            'crop' => ['required', 'array'],
            'crop.x' => ['required', 'numeric', 'min:0'],
            'crop.y' => ['required', 'numeric', 'min:0'],
            'crop.width' => ['required', 'numeric', 'min:1'],
            'crop.height' => ['required', 'numeric', 'min:1'],
        ]);

        $source = $this->sourceFor($validated['path']);

        if ($source === null) {
            return response()->json(['message' => __('У этой картинки нет сохранённого фото целиком: перекадрировать её нельзя. Загрузите её заново.')], 404);
        }

        // Рамка должна лежать внутри фото: иначе в кадр попало бы то, чего на снимке нет (допуск в один пиксель на округление).
        $crop = $validated['crop'];

        if ($crop['x'] + $crop['width'] > $source->source_width + 1 || $crop['y'] + $crop['height'] > $source->source_height + 1) {
            return response()->json(['message' => __('Рамка выходит за границы фото.')], 422);
        }

        $path = $action->recrop($source, $crop);

        return response()->json([
            'path' => $path,
            'url' => '/uploads/'.$path,
        ]);
    }

    /** Запись о фото по пути готового кадра. Путь приводится к безопасному виду: чужой формат даёт null. */
    private function sourceFor(mixed $path): ?ImageSource
    {
        $path = Blocks::normalizePath($path);

        return $path === null ? null : ImageSource::query()->where('path', $path)->first();
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
            'file.required' => __('Выберите PDF-файл.'),
            'file.uploaded' => __('Файл не загрузился: он больше, чем разрешено настройками сервера. Допустимо до :size МБ.', ['size' => $maxMb]),
            'file.mimetypes' => __('Нужен файл в формате PDF.'),
            'file.max' => __('Файл слишком большой: допустимо до :size МБ.', ['size' => $maxMb]),
        ], [
            'file' => __('файл'),
        ]);

        $stored = $action->handle($validated['file']);

        return response()->json($stored + ['url' => '/uploads/'.$stored['path']]);
    }
}
