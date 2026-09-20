@props(['editor', 'action', 'method' => 'POST', 'album'])

@php
    $saved = $album->exists;
    $previewUrl = $saved ? url('media/photos/'.$album->slug) : null;
    $isPublished = $saved && $album->status === \App\Enums\PublishStatus::Published;
    $cancelUrl = route('admin.albums.index');
@endphp

<form id="album-form" method="POST" action="{{ $action }}"
      x-data="albumEditor(@js($editor), @js($cancelUrl))">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <input type="hidden" name="intent" :value="intent">
    <input type="hidden" name="cover_path" :value="cover">
    <input type="hidden" name="blocks" :value="serialized">
    <input type="hidden" name="slug" :value="slug">
    <input type="hidden" name="published_at" :value="publishedAt">
    @foreach(['ru', 'ro', 'en'] as $l)
        <input type="hidden" name="translations[{{ $l }}][title]" :value="fields.{{ $l }}.title">
        <input type="hidden" name="translations[{{ $l }}][excerpt]" :value="fields.{{ $l }}.excerpt">
    @endforeach

    <header class="admin-header">
        <button type="button" @click="menu = !menu" class="btn-icon lg:hidden shrink-0" aria-label="Меню">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round"/></svg>
        </button>
        <h1 class="text-page font-semibold truncate min-w-0 hidden sm:block"
            x-text="fields.ru.title || @js($saved ? 'Фотоальбом' : 'Новый фотоальбом')"></h1>
        <div class="ml-auto flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button type="button" class="btn-quiet shrink-0" @click="cancel()">Отмена</button>

            @if($previewUrl)
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn-secondary shrink-0 hidden md:inline-flex">Предпросмотр</a>
            @else
                <span class="btn-secondary shrink-0 hidden md:inline-flex opacity-50 cursor-default">Предпросмотр</span>
            @endif

            @if($isPublished)
                <button type="submit" class="btn-secondary shrink-0" @click="submit('unpublish')">Снять с публикации</button>
                <button type="submit" class="btn-primary shrink-0" @click="submit('save')">Сохранить изменения</button>
            @elseif($saved)
                <button type="submit" class="btn-secondary shrink-0" @click="submit('save')">Сохранить</button>
                <button type="submit" class="btn-primary shrink-0" @click="submit('publish')">Опубликовать</button>
            @else
                {{-- Новый альбом публикуется по умолчанию; «черновик» — явный выбор. --}}
                <button type="submit" class="btn-secondary shrink-0" @click="submit('unpublish')">Сохранить черновик</button>
                <button type="submit" class="btn-primary shrink-0" @click="submit('publish')">Опубликовать</button>
            @endif
        </div>
    </header>

    <div class="tabs px-8 sticky top-header z-20">
        <button type="button" class="tab" :class="{ 'tab--active': activeTab === 'photos' }" @click="activeTab = 'photos'">Фото</button>
        @foreach(['ru' => 'Русский', 'ro' => 'Română', 'en' => 'English'] as $code => $name)
            <button type="button" class="tab" :class="{ 'tab--active': activeTab === '{{ $code }}' }" @click="activeTab = '{{ $code }}'">{{ $name }}</button>
        @endforeach
        <button type="button" class="tab" :class="{ 'tab--active': activeTab === 'settings' }" @click="activeTab = 'settings'">Настройки</button>
    </div>

    <div class="px-8 py-8">
        <div class="form-column space-y-6">
            <x-forms.error-summary />

            <div x-show="activeTab === 'photos'" class="space-y-6">
                <div class="space-y-1">
                    <span class="field-label">Обложка</span>
                    <p class="field-hint">Если не загрузить обложку, на сайте будет использована первая фотография.</p>
                    <div class="image-cell max-w-md" :class="{ 'image-cell--filled': cover }" style="aspect-ratio: {{ \App\Support\AspectRatio::css('album') }}">
                        <template x-if="cover"><img :src="'/uploads/' + cover" alt="" class="w-full h-full object-cover"></template>
                        <label x-show="!cover && !coverUploading" class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
                            Загрузить обложку
                            <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="uploadCover($event)">
                        </label>
                        <div x-show="coverUploading" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">Обработка…</div>
                        <div class="image-cell__actions" x-show="cover">
                            <button type="button" class="image-cell__action" @click="cover = ''" title="Удалить">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <template x-for="(block, i) in blocks" :key="block.uid">
                        <div class="block-card" :id="'ablock-' + block.uid">
                            <p class="block-card__type" x-text="{ image: 'Изображение', gallery_2: 'Галерея из 2', gallery_3: 'Галерея из 3', gallery_4: 'Галерея из 4' }[block.type]"></p>
                            <div class="block-card__tools">
                                <button type="button" class="btn-icon" :disabled="i === 0" @click="move(i, -1)" title="Вверх">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <button type="button" class="btn-icon" :disabled="i === blocks.length - 1" @click="move(i, 1)" title="Вниз">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <button type="button" class="btn-icon" @click="duplicate(i)" title="Дублировать">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="5" width="8" height="8" rx="1"/><path d="M3 11V3h8" stroke-linecap="round"/></svg>
                                </button>
                                <button type="button" class="btn-icon" @click="remove(i)" title="Удалить">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4h10M6 4V3h4v1M5 4l1 9h4l1-9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>
                            <div class="mt-6">
                                <template x-if="block.type === 'image'">
                                    <div class="max-w-md">
                                        <div class="image-cell" :class="{ 'image-cell--filled': block.data.path }" style="aspect-ratio: {{ \App\Support\AspectRatio::css('album') }}">
                                            <template x-if="block.data.path"><img :src="'/uploads/' + block.data.path" alt="" class="w-full h-full object-cover"></template>
                                            <label x-show="!block.data.path && !block._busy" class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
                                                Загрузить
                                                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="uploadBlock($event, i, null)">
                                            </label>
                                            <div x-show="block._busy" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">Обработка…</div>
                                            <div class="image-cell__actions" x-show="block.data.path">
                                                <button type="button" class="image-cell__action" @click="block.data.path = null" title="Удалить">
                                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="block.type !== 'image'">
                                    <div class="grid gap-3" :class="{
                                            'grid-cols-2': block.type === 'gallery_2',
                                            'grid-cols-1 sm:grid-cols-3': block.type === 'gallery_3',
                                            'grid-cols-2 sm:grid-cols-4': block.type === 'gallery_4',
                                         }">
                                        <template x-for="(img, cell) in block.data.images" :key="cell">
                                            <div class="image-cell" :class="{ 'image-cell--filled': img }" :style="`aspect-ratio: ${block.type === 'gallery_4' ? '3 / 4' : '4 / 3'}`">
                                                <template x-if="img"><img :src="'/uploads/' + img" alt="" class="w-full h-full object-cover"></template>
                                                <label x-show="!img && !block._busy" class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
                                                    Загрузить
                                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="uploadBlock($event, i, cell)">
                                                </label>
                                                <div class="image-cell__actions" x-show="img">
                                                    <button type="button" class="image-cell__action" @click="block.data.images[cell] = null" title="Удалить">
                                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <span class="text-caption text-ink-muted self-center mr-1">Добавить блок:</span>
                        <button type="button" class="btn-secondary" @click="addBlock('image')">Изображение</button>
                        <button type="button" class="btn-secondary" @click="addBlock('gallery_2')">Галерея 2</button>
                        <button type="button" class="btn-secondary" @click="addBlock('gallery_3')">Галерея 3</button>
                        <button type="button" class="btn-secondary" @click="addBlock('gallery_4')">Галерея 4</button>
                    </div>
                </div>
            </div>

            @foreach(['ru', 'ro', 'en'] as $l)
                <div x-show="activeTab === '{{ $l }}'" class="space-y-4">
                    <div class="space-y-1">
                        <label class="field-label">Заголовок</label>
                        <input type="text" x-model="fields.{{ $l }}.title" class="field-input">
                    </div>
                    <div class="space-y-1">
                        <label class="field-label">Описание</label>
                        <textarea rows="3" x-model="fields.{{ $l }}.excerpt" class="field-input"></textarea>
                        @if($l !== 'ru')
                            <p class="field-hint">Русский текст: <span x-text="fields.ru.excerpt || '—'"></span></p>
                        @endif
                    </div>
                </div>
            @endforeach

            <div x-show="activeTab === 'settings'" class="space-y-4">
                <div class="space-y-1">
                    <label class="field-label">Адрес страницы</label>
                    <input type="text" x-model="slug" class="field-input">
                    <p class="field-hint">Формируется из русского заголовка, если не заполнить.</p>
                </div>
                <div class="space-y-1">
                    <label class="field-label">Дата публикации</label>
                    <input type="datetime-local" x-model="publishedAt" :max="new Date().toISOString().slice(0,16)" class="field-input">
                </div>
            </div>
        </div>
    </div>
</form>
