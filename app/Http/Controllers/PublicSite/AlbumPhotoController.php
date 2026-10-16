<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\ImageSource;
use App\Support\PublicCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Фото альбома в полном размере. Плитка в полотне вырезана по пропорциям ряда (4:3 или 3:4), а в просмотре нужен
 * снимок целиком. Сжатое фото целиком хранится на закрытом диске `originals` (его сохраняет любая загрузка картинки,
 * по нему в админке меняют кадр); отсюда оно отдаётся наружу.
 *
 * Отдаётся только фото, которое стоит в опубликованном альбоме: у картинок других разделов обрезанное редактором
 * могло быть обрезано намеренно, и целиком их показывать нельзя. Нет сохранённого фото целиком (старая картинка) —
 * просто открывается плитка.
 */
class AlbumPhotoController extends Controller
{
    public function full(string $path): Response|RedirectResponse
    {
        abort_unless(isset($this->albumPhotos()[$path]), 404);

        $source = ImageSource::query()->where('path', $path)->latest('id')->first();

        if ($source === null || ! Storage::disk('originals')->exists($source->source_path)) {
            return redirect('/uploads/'.$path);
        }

        // Имя файла с фото целиком уникально, и файл не меняется: браузер может хранить его год.
        return Storage::disk('originals')->response($source->source_path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /** Пути плиток всех фото опубликованных альбомов: путь → номер. Лежит в кеше публичных страниц. @return array<string, int> */
    private function albumPhotos(): array
    {
        return PublicCache::remember('albums.photo-paths', fn () => array_flip(
            Album::published()->get()->flatMap(fn (Album $album) => $album->photoPaths())->unique()->values()->all()
        ));
    }
}
