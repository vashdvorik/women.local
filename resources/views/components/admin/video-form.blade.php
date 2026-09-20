@props(['video', 'action', 'method'])

<form method="POST" action="{{ $action }}" class="form-column space-y-6"
      x-data="videoCover(@js([
          'cover' => $video->cover_path ?? '',
          'uploadUrl' => route('admin.uploads.store'),
          'ratio' => \App\Support\AspectRatio::dimensions('video_cover'),
      ]))">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <x-forms.error-summary />

    <x-forms.field label="Ссылка на видео YouTube" name="youtube_url" error="youtube_url"
                   :value="$video->youtube_url" required
                   hint="watch?v=…, youtu.be/…, /shorts/…, /embed/… или /live/…" />

    <div class="space-y-1">
        <label for="event_date" class="field-label">Дата события</label>
        <input id="event_date" type="date" name="event_date"
               value="{{ old('event_date', $video->event_date?->format('Y-m-d')) }}"
               class="field-input @error('event_date') field-input--invalid @enderror">
        @error('event_date')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <section>
        <h3 class="form-section-title">Название</h3>
        <div class="space-y-4">
            @foreach(['ru' => 'по-русски', 'ro' => 'по-румынски', 'en' => 'по-английски'] as $code => $label)
                <x-forms.field :label="'Название '.$label" name="translations[{{ $code }}][title]"
                               error="translations.{{ $code }}.title"
                               :value="$video->rawTranslation($code)?->title" />
            @endforeach
        </div>
    </section>

    <div class="space-y-1">
        <span class="field-label">Обложка (необязательно)</span>
        <p class="field-hint">Без неё берётся миниатюра с YouTube.</p>
        <div class="image-cell max-w-md" :class="{ 'image-cell--filled': cover }"
             style="aspect-ratio: {{ \App\Support\AspectRatio::css('video_cover') }}">
            <template x-if="cover"><img :src="'/uploads/' + cover" alt="" class="w-full h-full object-cover"></template>
            <label x-show="!cover && !uploading" class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
                Загрузить обложку
                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="upload($event)">
            </label>
            <div x-show="uploading" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">Обработка…</div>
            <div class="image-cell__actions" x-show="cover">
                <button type="button" class="image-cell__action" @click="clear()" title="Удалить">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        <input type="hidden" name="cover_path" :value="cover">
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Сохранить</button>
        <a href="{{ route('admin.videos.index') }}" class="btn-quiet">Отмена</a>
    </div>
</form>
