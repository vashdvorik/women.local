@props([
    'name',              // имя скрытого поля (хранит путь)
    'aspect',            // слот пропорций из AspectRatio::SLOTS
    'value' => '',       // текущий путь
    'label' => 'Изображение',
    'hint' => null,
    'width' => 'max-w-md',
])

{{-- Одно изображение с кадрированием (imageField в image-field.js).
     Общее для форм отзыва и проекта; для видео — своя (videoCover).
     Параметр называется `aspect`, а не `slot` — `slot` в Blade зарезервирован. --}}
<div class="space-y-1"
     x-data="imageField(@js([
         'path' => $value,
         'slot' => $aspect,
         'ratio' => \App\Support\AspectRatio::dimensions($aspect),
         'uploadUrl' => route('admin.uploads.store'),
     ]))">
    <span class="field-label">{{ $label }}</span>
    @if($hint)<p class="field-hint">{{ $hint }}</p>@endif

    <div class="image-cell {{ $width }}" :class="{ 'image-cell--filled': path }"
         style="aspect-ratio: {{ \App\Support\AspectRatio::css($aspect) }}">
        <template x-if="path">
            <img :src="'/uploads/' + path" alt="" class="w-full h-full object-cover">
        </template>

        <label x-show="!path && !uploading"
               class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
            Загрузить
            <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="upload($event)">
        </label>

        <div x-show="uploading" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">
            Обработка…
        </div>

        <div class="image-cell__actions" x-show="path">
            <label class="image-cell__action cursor-pointer" title="Заменить">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M13 3v4h-4M3 13V9h4" stroke-linecap="round"/>
                    <path d="M13 7A5 5 0 003 6M3 9a5 5 0 0010 1" stroke-linecap="round"/>
                </svg>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="upload($event)">
            </label>
            <button type="button" class="image-cell__action" title="Удалить" @click="clear()">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" :value="path">
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>
