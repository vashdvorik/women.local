<?php

namespace App\Support;

use App\Models\Album;

class AlbumEditorData
{
    public static function make(Album $album): array
    {
        $fields = [];

        foreach (Locales::ALL as $locale) {
            $t = $album->exists ? $album->rawTranslation($locale) : null;
            $fields[$locale] = [
                'title' => $t?->title ?? '',
                'excerpt' => $t?->excerpt ?? '',
            ];
        }

        return [
            'slug' => $album->slug ?? '',
            'published_at' => optional($album->published_at)->format('Y-m-d\TH:i') ?? '',
            'cover' => $album->cover_path ?? '',
            'fields' => $fields,
            'blocks' => Blocks::canonical($album->blocks ?? [], Blocks::ALBUM_KINDS),
            'uploadUrl' => route('admin.uploads.store'),
            'ratios' => \App\Support\AspectRatio::SLOTS,
        ];
    }
}
