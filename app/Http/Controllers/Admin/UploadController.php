<?php

namespace App\Http\Controllers\Admin;

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
}
