@props(['catalog', 'action', 'method'])

@php
    $initialPages = collect($catalog->pagePaths())->map(fn ($path) => ['path' => $path, 'url' => $catalog->pageUrl($path)])->all();
    // После ошибки проверки браузер возвращает то, что было в форме: старый список страниц и пропорции
    $oldPages = old('pages');
    if (is_array($oldPages) && $oldPages !== []) {
        $initialPages = collect($oldPages)->filter('is_string')->map(fn ($path) => ['path' => $path, 'url' => '/uploads/'.$path])->values()->all();
    }
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="form-column space-y-6"
      x-data="catalogPages(@js([
          'pages' => $initialPages,
          'ratio' => (float) old('page_ratio', $catalog->page_ratio ?: 0.7071),
          'uploadUrl' => route('admin.catalogs.page'),
          'maxBytes' => \App\Http\Requests\CatalogRequest::maxPdfBytes(),
          'labels' => [
              'tooBig' => __('PDF весит :size МБ, а принять можно не больше :limit МБ. Сожмите PDF (сохраните его «для веб» или сожмите онлайн) и выберите файл снова.'),
              'progress' => __('Страница :done из :total'),
              'failed' => __('Не удалось разобрать PDF. Проверьте файл и попробуйте ещё раз.'),
              'tooMany' => __('В каталоге не может быть больше 300 страниц.'),
          ],
      ]))"
      x-effect="$refs.ratio.value = ratio">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <x-forms.error-summary />

    <section>
        <h3 class="form-section-title">{{ __('Название и описание') }}</h3>
        <div class="space-y-6">
            @foreach(['ru' => __('по-русски'), 'ro' => __('по-румынски'), 'en' => __('по-английски')] as $code => $label)
                <div class="space-y-3">
                    <x-forms.field :label="__('Название :language', ['language' => $label])" name="translations[{{ $code }}][title]"
                                   error="translations.{{ $code }}.title"
                                   :value="$catalog->rawTranslation($code)?->title" :required="$code === 'ru'" />
                    <div class="space-y-1">
                        <label for="description_{{ $code }}" class="field-label">{{ __('Описание :language', ['language' => $label]) }}</label>
                        <textarea id="description_{{ $code }}" name="translations[{{ $code }}][description]" rows="3"
                                  class="field-input @error('translations.'.$code.'.description') field-input--invalid @enderror">{{ old('translations.'.$code.'.description', $catalog->rawTranslation($code)?->description) }}</textarea>
                        @error('translations.'.$code.'.description')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="space-y-3">
        <h3 class="form-section-title">{{ __('Страницы каталога') }}</h3>

        <div class="space-y-1">
            <label for="pdf" class="field-label">{{ __('PDF-файл') }}</label>
            <p class="field-hint">{{ __('Выберите PDF: браузер сам превратит его страницы в лёгкие картинки и отправит на сайт, посетителям PDF не отдаётся и ничего не считается на их устройстве. Это занимает около секунды на страницу, не закрывайте вкладку.') }}</p>
            <p class="field-hint">{{ __('Не больше :limit МБ и :pages страниц.', ['limit' => (int) floor(\App\Http\Requests\CatalogRequest::maxPdfBytes() / 1048576), 'pages' => \App\Http\Requests\CatalogRequest::MAX_PAGES]) }}</p>
            <input id="pdf" type="file" name="pdf" accept="application/pdf" class="field-input" :disabled="busy" @change="convert($event)">
            @error('pdf')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <p x-show="busy" x-cloak class="text-reading text-ink-muted" role="status" x-text="progressLabel"></p>
        <p x-show="error" x-cloak class="field-error" role="alert" x-text="error"></p>
        @error('pages')<p class="field-error">{{ $message }}</p>@enderror

        <input type="hidden" name="pages" :value="paths">
        <input type="hidden" name="page_ratio" x-ref="ratio" value="{{ old('page_ratio', $catalog->page_ratio) }}">

        <p x-show="pages.length" x-cloak class="text-reading text-ink-muted">
            {{ __('Страниц в каталоге:') }} <span x-text="pages.length"></span>.
            {{ __('Лишнюю страницу можно убрать крестиком, порядок страниц — как в PDF.') }}
        </p>

        <ol class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-3" x-show="pages.length" x-cloak>
            <template x-for="(page, index) in pages" :key="page.path">
                <li class="relative rounded-sm border border-line overflow-hidden bg-surface">
                    <img :src="page.url" alt="" loading="lazy" class="w-full block" style="aspect-ratio: 3/4; object-fit: cover">
                    <span class="absolute left-1 bottom-1 rounded-sm bg-surface/90 px-1.5 text-caption" x-text="index + 1"></span>
                    <button type="button" class="image-cell__action absolute right-1 top-1" @click="remove(index)" title="{{ __('Убрать страницу') }}" aria-label="{{ __('Убрать страницу') }}">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
                    </button>
                </li>
            </template>
        </ol>
    </section>

    <section class="space-y-3">
        <h3 class="form-section-title">{{ __('Показ на сайте') }}</h3>

        <x-forms.field :label="__('Адрес страницы')" name="slug" error="slug" :value="$catalog->exists ? $catalog->slug : null"
                       :hint="__('Латиницей, через дефис. Если оставить пустым, адрес будет создан из названия.')" />
        @if($catalog->exists && $catalog->hasPages())
            <p class="field-hint"><a href="{{ route('media.catalogs.show', $catalog->slug) }}" target="_blank" rel="noopener" class="table-link">{{ __('Открыть каталог на сайте') }}</a></p>
        @endif

        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $catalog->is_published))>
            <span>{{ __('Опубликован') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="allow_download" value="1" @checked(old('allow_download', $catalog->allow_download))>
            <span>{{ __('Показывать кнопку «Скачать PDF»') }}</span>
        </label>
        @if($catalog->pdf_path)
            <p class="field-hint">{{ __('PDF уже загружен. Выбрав новый файл выше, вы замените страницы и PDF.') }}</p>
        @else
            <p class="field-hint">{{ __('Кнопка появится, только если загружен PDF.') }}</p>
        @endif
    </section>

    <div class="space-y-1" x-data="videoCover(@js([
            'cover' => old('cover_path', $catalog->cover_path) ?? '',
            'uploadUrl' => route('admin.uploads.store'),
            'ratio' => \App\Support\AspectRatio::dimensions('video_cover'),
        ]))">
        <span class="field-label">{{ __('Обложка (необязательно)') }}</span>
        <p class="field-hint">{{ __('Без неё обложкой в списке служит первая страница.') }}</p>
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

    <div class="flex gap-3">
        <button type="submit" class="btn-primary" :disabled="busy">{{ __('Сохранить') }}</button>
        <a href="{{ route('admin.catalogs.index') }}" class="btn-quiet">{{ __('Отмена') }}</a>
    </div>
</form>
