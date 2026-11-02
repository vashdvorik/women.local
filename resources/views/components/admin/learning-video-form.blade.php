@props(['video', 'action', 'method'])

{{-- Обучающее видео для кабинета. Обложка загружается так же, как у видео открытого сайта (Alpine videoCover). --}}
<form method="POST" action="{{ $action }}" class="form-column space-y-6"
      x-data="videoCover(@js([
          'cover' => $video->cover_path ?? '',
          'uploadUrl' => route('admin.uploads.store'),
          'ratio' => \App\Support\AspectRatio::dimensions('video_cover'),
      ]))">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <x-forms.error-summary />

    <x-forms.field :label="__('Ссылка на видео YouTube')" name="youtube_url" error="youtube_url"
                   :value="$video->youtube_url" required
                   :hint="__('watch?v=…, youtu.be/…, /shorts/…, /embed/… или /live/…. В настройках видео на YouTube должно быть разрешено встраивание на других сайтах, иначе в кабинете оно не откроется.')" />

    <section>
        <h3 class="form-section-title">{{ __('Название и описание') }}</h3>
        <p class="field-hint mb-3">{{ __('Русский текст обязателен: если на другом языке пусто, участнице покажется русский.') }}</p>
        <div class="space-y-6">
            @foreach(['ru' => __('по-русски'), 'ro' => __('по-румынски'), 'en' => __('по-английски')] as $code => $label)
                <div class="space-y-3">
                    <x-forms.field :label="__('Название :language', ['language' => $label])" name="translations[{{ $code }}][title]"
                                   error="translations.{{ $code }}.title"
                                   :value="$video->rawTranslation($code)?->title" :required="$code === 'ru'" />
                    <div class="space-y-1">
                        <label for="description-{{ $code }}" class="field-label">{{ __('Описание :language', ['language' => $label]) }}</label>
                        <textarea id="description-{{ $code }}" name="translations[{{ $code }}][description]" rows="3" maxlength="2000"
                                  class="field-input @error('translations.'.$code.'.description') field-input--invalid @enderror">{{ old('translations.'.$code.'.description', $video->rawTranslation($code)?->description) }}</textarea>
                        @error('translations.'.$code.'.description')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="space-y-1">
        <span class="field-label">{{ __('Обложка (необязательно)') }}</span>
        <p class="field-hint">{{ __('Без неё берётся миниатюра с YouTube.') }}</p>
        <div class="image-cell max-w-md" :class="{ 'image-cell--filled': cover }"
             style="aspect-ratio: {{ \App\Support\AspectRatio::css('video_cover') }}">
            <template x-if="cover"><img :src="'/uploads/' + cover" alt="" class="w-full h-full object-cover"></template>
            <label x-show="!cover && !uploading" class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
                {{ __('Загрузить обложку') }}
                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="upload($event)">
            </label>
            <div x-show="uploading" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">{{ __('Обработка…') }}</div>
            <div class="image-cell__actions" x-show="cover">
                <button type="button" class="image-cell__action" @click="recrop()" title="{{ __('Изменить кадр') }}">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 1v11h11M1 4h11v11" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="image-cell__action" @click="clear()" title="{{ __('Удалить') }}">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        <input type="hidden" name="cover_path" :value="cover">
    </div>

    <label class="flex items-start gap-3 text-ui">
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" value="1" class="mt-1 rounded border-hairline text-accent focus:ring-accent-soft"
               @checked(old('is_published', $video->is_published ?? true))>
        <span>
            <span class="field-label block">{{ __('Показывать в кабинете') }}</span>
            <span class="field-hint block">{{ __('Снимите галочку, чтобы оставить видео черновиком: участницы его не увидят.') }}</span>
        </span>
    </label>

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">{{ __('Сохранить') }}</button>
        <a href="{{ route('admin.learning-videos.index') }}" class="btn-quiet">{{ __('Отмена') }}</a>
    </div>
</form>
