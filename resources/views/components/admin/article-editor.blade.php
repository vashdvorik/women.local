@props([
    'editor',
    'type' => 'post',            // post | opportunity
    'action',
    'method' => 'POST',
    'article',
    'noun' => 'новость',
])

@php
    $isOpportunity = $type === 'opportunity';
    $saved = $article->exists;
    $previewUrl = $saved
        ? url(($isOpportunity ? 'opportunities/' : 'media/publications/').$article->slug)
        : null;
    $isPublished = $saved && $article->status === \App\Enums\PublishStatus::Published;
    $publishFailed = session('publish_failed');
    $cancelUrl = $isOpportunity ? route('admin.opportunities.index') : route('admin.posts.index');
@endphp

<form id="article-form" method="POST" action="{{ $action }}"
      x-data="articleEditor(@js($editor), @js($cancelUrl))">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <input type="hidden" name="intent" :value="intent">
    <input type="hidden" name="cover_path" :value="cover">
    @foreach(['ru', 'ro', 'en'] as $l)
        <input type="hidden" name="translations[{{ $l }}][content]" :value="serialized.{{ $l }}">
    @endforeach

    {{-- ---------- Липкая шапка ---------- --}}
    <header class="admin-header">
        <button type="button" @click="menu = !menu" class="btn-icon lg:hidden shrink-0" aria-label="Меню">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round"/>
            </svg>
        </button>

        <h1 class="text-page font-semibold truncate min-w-0 hidden sm:block">
            <span x-text="titleForHeader || @js($saved ? ($noun === 'новость' ? 'Новость' : 'Возможность') : ($noun === 'новость' ? 'Новая новость' : 'Новая возможность'))"></span>
        </h1>

        <div class="ml-auto flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button type="button" class="btn-quiet shrink-0" @click="cancel()">Отмена</button>

            @if($previewUrl)
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn-secondary shrink-0 hidden md:inline-flex">Предпросмотр</a>
            @else
                <span class="btn-secondary shrink-0 hidden md:inline-flex opacity-50 cursor-default" aria-disabled="true">Предпросмотр</span>
            @endif

            @if($isPublished)
                <button type="submit" class="btn-secondary shrink-0" @click="submit('unpublish')">Снять с публикации</button>
                <button type="submit" class="btn-primary shrink-0" @click="submit('save')">Сохранить изменения</button>
            @else
                <button type="submit" class="btn-secondary shrink-0" @click="submit('save')">Сохранить черновик</button>
                <button type="submit" class="btn-primary shrink-0"
                        :disabled="!canPublish"
                        :title="canPublish ? '' : 'Для публикации заполните русский заголовок и краткое описание'"
                        @click="submit('publish')">Опубликовать</button>
            @endif
        </div>
    </header>

    {{-- ---------- Вкладки ---------- --}}
    <div class="tabs px-8 sticky top-header z-20">
        @foreach(['ru' => 'Русский', 'ro' => 'Română', 'en' => 'English'] as $code => $name)
            <button type="button" class="tab" :class="{ 'tab--active': activeTab === '{{ $code }}' }"
                    @click="activeTab = '{{ $code }}'">
                {{ $name }}
                @if($code === 'ru')
                    <x-admin.tab-badge locale="ru" />
                @else
                    <x-admin.tab-badge :locale="$code" />
                @endif
            </button>
        @endforeach
        <button type="button" class="tab" :class="{ 'tab--active': activeTab === 'extra' }"
                @click="activeTab = 'extra'">Дополнительные настройки</button>
    </div>

    <div class="px-8 py-8">
        <div class="form-column space-y-6">
            <x-forms.error-summary :title="$publishFailed ? 'Не удалось опубликовать материал.' : 'Исправьте ошибки в форме.'" />

            {{-- ---------- Языковые вкладки ---------- --}}
            @foreach(['ru', 'ro', 'en'] as $l)
                <div x-show="activeTab === '{{ $l }}'" class="space-y-6">

                    @if($l !== 'ru')
                        <x-admin.translation-status :locale="$l" />
                    @endif

                    <div>
                        <input type="text" name="translations[{{ $l }}][title]"
                               x-model="fields.{{ $l }}.title"
                               placeholder="Заголовок"
                               class="field-bare field-bare--title w-full">
                        @error("translations.$l.title") <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex justify-end">
                            <span class="text-caption"
                                  :class="(fields.{{ $l }}.excerpt || '').length > 100 ? 'text-warning' : 'text-ink-muted'"
                                  x-text="`${(fields.{{ $l }}.excerpt || '').length} / 100 символов`"></span>
                        </div>
                        <textarea name="translations[{{ $l }}][excerpt]" rows="2"
                                  x-model="fields.{{ $l }}.excerpt"
                                  placeholder="Краткое описание"
                                  class="field-bare w-full"></textarea>
                        @if($l !== 'ru')
                            <p class="field-hint mt-1">Русский текст: <span x-text="fields.ru.excerpt || '—'"></span></p>
                        @endif
                        @error("translations.$l.excerpt") <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($l === 'ru')
                        <x-admin.cover-field />
                    @endif

                    <x-admin.block-editor :locale="$l" />
                </div>
            @endforeach

            {{-- ---------- Дополнительные настройки ---------- --}}
            <div x-show="activeTab === 'extra'" class="space-y-6">
                <x-forms.field label="Адрес страницы" name="slug" x-model="slug"
                               hint="Нижний регистр, дефисы вместо пробелов. Формируется из русского заголовка, если не заполнить." />

                <div class="space-y-1">
                    <label class="field-label">Дата публикации</label>
                    <input type="datetime-local" x-model="publishedAt"
                           :max="new Date().toISOString().slice(0,16)"
                           class="field-input @error('published_at') field-input--invalid @enderror">
                    <input type="hidden" name="published_at" :value="publishedAt">
                    @error('published_at')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p class="field-hint">Можно указать текущую или прошедшую дату.</p>
                    @enderror
                </div>

                <x-forms.field label="Автор" name="author" x-model="author" />

                @if($isOpportunity)
                    <div class="space-y-1">
                        <label class="field-label">Подать заявку до</label>
                        <input type="date" x-model="deadlineAt"
                               class="field-input @error('deadline_at') field-input--invalid @enderror">
                        <input type="hidden" name="deadline_at" :value="deadlineAt">
                        @error('deadline_at') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="field-label">Тег</label>
                    <select x-model="tagId" class="field-input @error('tag_id') field-input--invalid @enderror">
                        <option value="">— без тега —</option>
                        <template x-for="tag in tags" :key="tag.id">
                            <option :value="tag.id" x-text="tag.name"></option>
                        </template>
                    </select>
                    <input type="hidden" name="tag_id" :value="tagId">
                    @error('tag_id')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p class="field-hint">
                            Теги общие для новостей и возможностей —
                            <a href="{{ route('admin.tags.index') }}" class="text-accent" target="_blank" rel="noopener">управлять</a>.
                        </p>
                    @enderror
                </div>

                <section>
                    <h3 class="form-section-title">SEO</h3>
                    <div class="space-y-4">
                        <x-forms.field label="SEO-заголовок" name="translations[ru][seo_title]"
                                       error="translations.ru.seo_title" x-model="seo.ru.seo_title" />
                        <x-forms.field label="SEO-описание" name="translations[ru][seo_description]"
                                       error="translations.ru.seo_description" x-model="seo.ru.seo_description" />
                    </div>
                </section>
            </div>
        </div>
    </div>
</form>
