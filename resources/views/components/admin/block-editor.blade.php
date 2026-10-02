@props(['locale'])

@php $isPrimary = $locale === 'ru'; @endphp

<div class="space-y-4">
    <template x-for="(block, i) in blocks.{{ $locale }}" :key="block.uid">
        <div class="block-card" :id="'block-' + block.uid">
            <p class="block-card__type" x-text="@js([
                'text' => __('Текст'), 'heading' => __('Заголовок'), 'embed' => __('HTML-код'), 'file' => __('Файл (PDF)'), 'image' => __('Изображение'),
                'gallery_2' => __('Галерея из 2'), 'gallery_3' => __('Галерея из 3'), 'gallery_4' => __('Галерея из 4'),
            ])[block.type]"></p>

            <div class="block-card__tools">
                <button type="button" class="btn-icon" title="{{ __('Вверх') }}"
                        :disabled="i === 0" @click="move(i, -1)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="btn-icon" title="{{ __('Вниз') }}"
                        :disabled="i === blocks.{{ $locale }}.length - 1" @click="move(i, 1)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="btn-icon" title="{{ __('Дублировать') }}" @click="duplicate(i)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="5" width="8" height="8" rx="1"/><path d="M3 11V3h8" stroke-linecap="round"/></svg>
                </button>
                <button type="button" class="btn-icon" title="{{ __('Удалить') }}" @click="remove(i)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4h10M6 4V3h4v1M5 4l1 9h4l1-9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>

            <div class="mt-6">
                {{-- ТЕКСТ --}}
                <template x-if="block.type === 'text'">
                    <div x-data="richText(() => block.data.html, (v) => block.data.html = v)">
                        <div class="flex flex-wrap items-center gap-1 mb-2">
                            <div x-show="mode === 'visual'" class="flex flex-wrap items-center gap-1">
                                <button type="button" class="btn-icon font-bold" title="{{ __('Жирный') }}" @click="exec('bold')">{{ __('Ж') }}</button>
                                <button type="button" class="btn-icon italic" title="{{ __('Курсив') }}" @click="exec('italic')">{{ __('К') }}</button>
                                <button type="button" class="btn-icon underline" title="{{ __('Подчёркнутый') }}" @click="exec('underline')">{{ __('Ч') }}</button>
                                <button type="button" class="btn-icon line-through" title="{{ __('Зачёркнутый') }}" @click="exec('strikeThrough')">{{ __('З') }}</button>
                                <span class="w-px h-5 bg-hairline mx-1"></span>
                                <button type="button" class="btn-icon text-[13px] font-semibold" title="{{ __('Подзаголовок H2') }}" @click="formatBlock('<h2>')">H2</button>
                                <button type="button" class="btn-icon text-[13px] font-semibold" title="{{ __('Подзаголовок H3') }}" @click="formatBlock('<h3>')">H3</button>
                                <button type="button" class="btn-icon" title="{{ __('Обычный абзац') }}" @click="formatBlock('<p>')">¶</button>
                                <button type="button" class="btn-icon" title="{{ __('Цитата') }}" @click="formatBlock('<blockquote>')">&bdquo;&ldquo;</button>
                                <span class="w-px h-5 bg-hairline mx-1"></span>
                                <button type="button" class="btn-icon" title="{{ __('Маркированный список') }}" @click="exec('insertUnorderedList')">•—</button>
                                <button type="button" class="btn-icon" title="{{ __('Нумерованный список') }}" @click="exec('insertOrderedList')">1.</button>
                                <button type="button" class="btn-icon" title="{{ __('Ссылка') }}" @click="link()">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 10l4-4M7 4l1-1a3 3 0 014 4l-1 1M9 12l-1 1a3 3 0 01-4-4l1-1" stroke-linecap="round"/></svg>
                                </button>
                                <button type="button" class="btn-icon" title="{{ __('Убрать ссылку') }}" @click="exec('unlink')">⛓</button>
                                <button type="button" class="btn-icon" title="{{ __('Убрать форматирование') }}" @click="exec('removeFormat')">Tx</button>
                                <span class="w-px h-5 bg-hairline mx-1"></span>
                                <button type="button" class="btn-icon" title="{{ __('Отменить') }}" @click="exec('undo')">↶</button>
                                <button type="button" class="btn-icon" title="{{ __('Повторить') }}" @click="exec('redo')">↷</button>
                            </div>
                            <button type="button" class="btn-icon ml-auto"
                                    :class="mode === 'source' ? 'bg-accent-soft text-accent' : ''"
                                    title="{{ __('Править HTML') }}" @click="toggleSource()">&lt;/&gt;</button>
                        </div>

                        <div x-ref="area" contenteditable x-show="mode === 'visual'"
                             class="field-input min-h-[8rem] prose-editor" style="height:auto"></div>

                        {{-- Своё окно ссылки вместо системного window.prompt(): позволяет
                             задать текст, адрес и выбрать, открывать ли в новом окне. --}}
                        <template x-teleport="body">
                            <div x-show="linkModalOpen" x-transition.opacity class="modal-backdrop" hidden
                                 @keydown.escape.window="linkModalOpen && closeLinkModal()"
                                 @click.self="closeLinkModal()">
                                <div class="modal" @click.stop>
                                    <p class="modal__title" x-text="linkMode === 'edit' ? @js(__('Изменить ссылку')) : @js(__('Добавить ссылку'))"></p>

                                    <div class="space-y-3 mt-4">
                                        <template x-if="linkMode !== 'wrap'">
                                            <div class="space-y-1">
                                                <label class="field-label">{{ __('Текст ссылки') }}</label>
                                                <input type="text" class="field-input" x-model="linkText"
                                                       placeholder="{{ __('Что будет написано ссылкой') }}">
                                            </div>
                                        </template>
                                        <template x-if="linkMode === 'wrap'">
                                            <p class="field-hint">{{ __('Ссылка применится к выделенному тексту:') }} «<span x-text="linkText"></span>»</p>
                                        </template>

                                        <div class="space-y-1">
                                            <label class="field-label">{{ __('Адрес ссылки') }}</label>
                                            <input type="url" class="field-input" x-model="linkUrl"
                                                   placeholder="{{ __('https://example.com или mailto:info@example.com') }}"
                                                   @keydown.enter.prevent="applyLink()">
                                        </div>

                                        <label class="flex items-start gap-2 text-ui">
                                            <input type="checkbox" x-model="linkNewTab"
                                                   class="mt-0.5 rounded border-hairline text-accent focus:ring-accent-soft">
                                            <span>{{ __('Открывать в новом окне') }}</span>
                                        </label>
                                    </div>

                                    <div class="flex justify-end gap-3 mt-6">
                                        <button type="button" @click="closeLinkModal()" class="btn-quiet">{{ __('Отмена') }}</button>
                                        <button type="button" @click="applyLink()" class="btn-primary" :disabled="!linkUrl.trim()">
                                            {{ __('Сохранить') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <textarea x-show="mode === 'source'" x-model="block.data.html" spellcheck="false"
                                  class="field-input font-mono !text-[13px] min-h-[8rem] leading-relaxed"></textarea>

                        <p class="field-hint mt-1" x-show="mode === 'source'">
                            {{ __('Разрешённые теги при показе на сайте: p, br, strong, em, u, s, a, h2–h4, blockquote, pre, code, ul, ol, li. Остальное вырезается — для произвольного кода используйте блок «HTML-код».') }}
                        </p>

                        @unless($isPrimary)
                            <div class="field-hint mt-2">
                                <span class="text-ink-faint">{{ __('Русский:') }}</span>
                                <span x-text="(blocks.ru[i] && blocks.ru[i].data.html || '').replace(/<[^>]*>/g,' ').trim() || '—'"></span>
                            </div>
                        @endunless
                    </div>
                </template>

                {{-- HTML-КОД: вставляется на страницу как есть, без обработки --}}
                <template x-if="block.type === 'embed'">
                    <div class="space-y-2">
                        <div class="rounded-sm bg-warning-soft text-warning text-caption p-2">
                            {{ __('Код вставляется на страницу без обработки. Вы отвечаете за его корректность и безопасность.') }}
                        </div>
                        <textarea :value="block.data.html" @input="setEmbedHtml(i, $event.target.value)"
                                  spellcheck="false"
                                  placeholder="{{ __('<iframe …>, <script …>, любая разметка') }}"
                                  class="field-input font-mono !text-[13px] min-h-[9rem] leading-relaxed"></textarea>
                        <details class="text-caption text-ink-muted">
                            <summary class="cursor-pointer">{{ __('Предпросмотр') }}</summary>
                            <iframe class="w-full mt-2 border border-hairline rounded-sm bg-surface"
                                    style="height: 220px" sandbox="allow-scripts allow-popups allow-forms"
                                    :srcdoc="block.data.html || '<!-- empty -->'"></iframe>
                        </details>
                    </div>
                </template>

                {{-- ФАЙЛ (PDF): каталог, брошюра. Сам файл общий для всех языков (как картинка),
                     название на странице — на каждой вкладке своё. --}}
                <template x-if="block.type === 'file'">
                    <div class="space-y-3 max-w-xl">
                        <div class="file-cell" :class="{ 'file-cell--filled': block.data.path }">
                            <span class="file-cell__icon" aria-hidden="true">PDF</span>

                            <div class="file-cell__body">
                                <template x-if="block.data.path && !block._busy">
                                    <div>
                                        <a :href="'/uploads/' + block.data.path" target="_blank" rel="noopener"
                                           class="file-cell__name" title="{{ __('Открыть файл в новой вкладке') }}"
                                           x-text="block.data.name || @js(__('Открыть файл'))"></a>
                                        <span class="file-cell__meta" x-text="fileSize(block.data.size)"></span>
                                    </div>
                                </template>
                                <span x-show="!block.data.path && !block._busy" class="text-caption text-ink-muted">{{ __('Файл не выбран') }}</span>
                                <span x-show="block._busy" class="text-caption text-ink-muted">{{ __('Загрузка…') }}</span>
                            </div>

                            <div class="file-cell__actions">
                                <label class="btn-secondary cursor-pointer focus-within:ring-2 focus-within:ring-accent">
                                    <span x-text="block.data.path ? @js(__('Заменить')) : @js(__('Загрузить PDF'))"></span>
                                    <input type="file" accept="application/pdf,.pdf" class="sr-only"
                                           @change="uploadPdf($event, i, (b) => block._busy = b)">
                                </label>
                                <button type="button" class="btn-quiet" x-show="block.data.path && !block._busy"
                                        @click="setBlockFile(i, null)">{{ __('Убрать') }}</button>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="field-label">{{ __('Название на странице') }}</label>
                            <input type="text" class="field-input field-input--content" maxlength="191"
                                   placeholder="{{ __('Например: Каталог участниц 2026') }}" x-model="block.data.title">
                            @unless($isPrimary)
                                <div class="field-hint">
                                    <span class="text-ink-faint">{{ __('Русский:') }}</span>
                                    <span x-text="(blocks.ru[i] && blocks.ru[i].data.title) || '—'"></span>
                                    · {{ __('если оставить пустым, покажется русское название') }}
                                </div>
                            @endunless
                        </div>

                        <p class="field-hint">
                            {{ __('PDF до :size МБ. Файл один на все языки; посетитель увидит название, размер и кнопку «Скачать».', ['size' => \App\Actions\StoreUploadedFile::MAX_KB / 1024]) }}
                        </p>
                    </div>
                </template>

                {{-- ЗАГОЛОВОК --}}
                <template x-if="block.type === 'heading'">
                    <div class="flex gap-2 items-start">
                        <select class="field-input w-24" :value="block.data.level"
                                @change="setLevel(i, $event.target.value)">
                            <option value="h2">H2</option>
                            <option value="h3">H3</option>
                        </select>
                        <input type="text" class="field-input field-input--content" placeholder="{{ __('Текст заголовка') }}"
                               x-model="block.data.text">
                    </div>
                </template>

                {{-- ИЗОБРАЖЕНИЕ --}}
                <template x-if="block.type === 'image'">
                    <div class="max-w-md">
                        <div class="image-cell" :class="{ 'image-cell--filled': block.data.path }"
                             style="aspect-ratio: 16 / 9">
                            <template x-if="block.data.path">
                                <img :src="'/uploads/' + block.data.path" alt="" class="w-full h-full object-cover">
                            </template>
                            <label x-show="!block.data.path && !block._busy" class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
                                {{ __('Загрузить') }}
                                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                       @change="upload($event, 'image', (p) => setBlockImage(i, p), (b) => block._busy = b)">
                            </label>
                            <div x-show="block._busy" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">{{ __('Обработка…') }}</div>
                            <div class="image-cell__actions" x-show="block.data.path">
                                <label class="image-cell__action cursor-pointer" title="{{ __('Заменить') }}">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 3v4h-4M3 13V9h4M13 7A5 5 0 003 6M3 9a5 5 0 0010 1" stroke-linecap="round"/></svg>
                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                           @change="upload($event, 'image', (p) => setBlockImage(i, p), (b) => block._busy = b)">
                                </label>
                                <button type="button" class="image-cell__action" title="{{ __('Удалить') }}" @click="setBlockImage(i, null)">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- ГАЛЕРЕИ. Галерея из 4 — в один ряд (на узких экранах 2×2). --}}
                <template x-if="block.type.startsWith('gallery')">
                    <div class="grid gap-3"
                         :class="{
                            'grid-cols-2': block.type === 'gallery_2',
                            'grid-cols-1 sm:grid-cols-3': block.type === 'gallery_3',
                            'grid-cols-2 sm:grid-cols-4': block.type === 'gallery_4',
                         }">
                        <template x-for="(img, cell) in block.data.images" :key="cell">
                            <div class="image-cell" :class="{ 'image-cell--filled': img }"
                                 :style="`aspect-ratio: ${block.type === 'gallery_4' ? '3 / 4' : '4 / 3'}`">
                                <template x-if="img">
                                    <img :src="'/uploads/' + img" alt="" class="w-full h-full object-cover">
                                </template>
                                <label x-show="!img && !block._busy" class="absolute inset-0 flex items-center justify-center cursor-pointer text-caption text-ink-muted">
                                    {{ __('Загрузить') }}
                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                           @change="upload($event, block.type, (p) => setBlockImage(i, p, cell), (b) => block._busy = b)">
                                </label>
                                <div x-show="block._busy" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">…</div>
                                <div class="image-cell__actions" x-show="img">
                                    <label class="image-cell__action cursor-pointer" title="{{ __('Заменить') }}">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 3v4h-4M3 13V9h4M13 7A5 5 0 003 6M3 9a5 5 0 0010 1" stroke-linecap="round"/></svg>
                                        <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                               @change="upload($event, block.type, (p) => setBlockImage(i, p, cell), (b) => block._busy = b)">
                                    </label>
                                    <button type="button" class="image-cell__action" title="{{ __('Удалить') }}" @click="setBlockImage(i, null, cell)">
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
        <span class="text-caption text-ink-muted self-center mr-1">{{ __('Добавить блок:') }}</span>
        <button type="button" class="btn-secondary" @click="addBlock('text')">{{ __('Текст') }}</button>
        <button type="button" class="btn-secondary" @click="addBlock('heading')">{{ __('Заголовок') }}</button>
        <button type="button" class="btn-secondary" @click="addBlock('embed')">{{ __('HTML-код') }}</button>
        <button type="button" class="btn-secondary" @click="addBlock('file')">{{ __('Файл (PDF)') }}</button>
        <button type="button" class="btn-secondary" @click="addBlock('image')">{{ __('Изображение') }}</button>
        <button type="button" class="btn-secondary" @click="addBlock('gallery_2')">{{ __('Галерея 2') }}</button>
        <button type="button" class="btn-secondary" @click="addBlock('gallery_3')">{{ __('Галерея 3') }}</button>
        <button type="button" class="btn-secondary" @click="addBlock('gallery_4')">{{ __('Галерея 4') }}</button>
    </div>
</div>
