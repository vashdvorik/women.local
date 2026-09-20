@props(['tag', 'action', 'method'])

@php
    $color = old('color', $tag->color ?? '#0066cc');
    $ruName = old('names.ru', $tag->rawTranslation('ru')?->name ?? '');
@endphp

<form method="POST" action="{{ $action }}" class="form-column space-y-6"
      x-data="tagColor(@js($color), @js($ruName))">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <x-forms.error-summary />

    <div class="space-y-1">
        <label for="names_ru" class="field-label">Название по-русски <span class="text-danger">*</span></label>
        <input id="names_ru" type="text" name="names[ru]" x-model="ruName" required
               class="field-input @error('names.ru') field-input--invalid @enderror">
        @error('names.ru')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <x-forms.field label="Название по-румынски" name="names[ro]" error="names.ro"
                   :value="$tag->rawTranslation('ro')?->name" required />
    <x-forms.field label="Название по-английски" name="names[en]" error="names.en"
                   :value="$tag->rawTranslation('en')?->name" required />

    <div class="space-y-1">
        <label class="field-label">Цвет</label>
        <div class="flex flex-wrap items-center gap-3">
            <input type="color" :value="normalized" @input="setFromPicker($event.target.value)"
                   class="h-10 w-14 rounded-sm border border-hairline bg-surface p-1">
            <input type="text" x-model="hex" @blur="setFromText($event.target.value)"
                   class="field-input w-32 @error('color') field-input--invalid @enderror">
            <input type="hidden" name="color" :value="hex">

            <span class="badge" :style="`background: ${normalized}; color: ${textColor}`"
                  x-text="ruName.trim() || 'Тег'"></span>
        </div>
        @error('color')<p class="field-error">{{ $message }}</p>@enderror
        <p class="field-hint">Цвет текста на плашке вычисляется из яркости фона.</p>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Сохранить</button>
        <a href="{{ route('admin.tags.index') }}" class="btn-quiet">Отмена</a>
    </div>
</form>
