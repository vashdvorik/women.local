@props(['locale'])

@php $isPrimary = $locale === 'ru'; @endphp

<div class="space-y-4">
    <template x-for="(block, i) in blocks.{{ $locale }}" :key="block.uid">
        <div class="block-card" :id="'block-' + block.uid">
            <p class="block-card__type" x-text="{
                text: 'Текст', heading: 'Заголовок', embed: 'HTML-код', image: 'Изображение',
                gallery_2: 'Галерея из 2', gallery_3: 'Галерея из 3', gallery_4: 'Галерея из 4'
            }[block.type]"></p>

            <div class="block-card__tools">
                <button type="button" class="btn-icon" title="Вверх"
                        :disabled="i === 0" @click="move(i, -1)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="btn-icon" title="Вниз"
                        :disabled="i === blocks.{{ $locale }}.length - 1" @click="move(i, 1)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="btn-icon" title="Дублировать" @click="duplicate(i)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="5" width="8" height="8" rx="1"/><path d="M3 11V3h8" stroke-linecap="round"/></svg>
                </button>
                <button type="button" class="btn-icon" title="Удалить" @click="remove(i)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4h10M6 4V3h4v1M5 4l1 9h4l1-9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>

            <div class="mt-6">
                {{-- ТЕКСТ --}}
                <template x-if="block.type === 'text'">
                    <div x-data="richText(() => block.data.html, (v) => block.data.html = v)">
                        <div class="flex flex-wrap items-center gap-1 mb-2">
                            <div x-show="mode === 'visual'" class="flex flex-wrap items-center gap-1">
                                <button type="button" class="btn-icon font-bold" title="Жирный" @click="exec('bold')">Ж</button>
                                <button type="button" class="btn-icon italic" title="Курсив" @click="exec('italic')">К</button>
                                <button type="button" class="btn-icon underline" title="Подчёркнутый" @click="exec('underline')">Ч</button>
                                <button type="button" class="btn-icon line-through" title="Зачёркнутый" @click="exec('strikeThrough')">З</button>
                                <span class="w-px h-5 bg-hairline mx-1"></span>
                                <button type="button" class="btn-icon text-[13px] font-semibold" title="Подзаголовок H2" @click="formatBlock('<h2>')">H2</button>
                                <button type="button" class="btn-icon text-[13px] font-semibold" title="Подзаголовок H3" @click="formatBlock('<h3>')">H3</button>
                                <button type="button" class="btn-icon" title="Обычный абзац" @click="formatBlock('<p>')">¶</button>
                                <button type="button" class="btn-icon" title="Цитата" @click="formatBlock('<blockquote>')">&bdquo;&ldquo;</button>
                                <span class="w-px h-5 bg-hairline mx-1"></span>
                                <button type="button" class="btn-icon" title="Маркированный список" @click="exec('insertUnorderedList')">•—</button>
                                <button type="button" class="btn-icon" title="Нумерованный список" @click="exec('insertOrderedList')">1.</button>
                                <button type="button" class="btn-icon" title="Ссылка" @click="link()">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 10l4-4M7 4l1-1a3 3 0 014 4l-1 1M9 12l-1 1a3 3 0 01-4-4l1-1" stroke-linecap="round"/></svg>
                                </button>
                                <button type="button" class="btn-icon" title="Убрать ссылку" @click="exec('unlink')">⛓</button>
                                <button type="button" class="btn-icon" title="Убрать форматирование" @click="exec('removeFormat')">Tx</button>
                                <span class="w-px h-5 bg-hairline mx-1"></span>
                                <button type="button" class="btn-icon" title="Отменить" @click="exec('undo')">↶</button>
                                <button type="button" class="btn-icon" title="Повторить" @click="exec('redo')">↷</button>
                            </div>
                            <button type="button" class="btn-icon ml-auto"
                                    :class="mode === 'source' ? 'bg-accent-soft text-accent' : ''"
                                    title="Править HTML" @click="toggleSource()">&lt;/&gt;</button>
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
                                    <p class="modal__title" x-text="linkMode === 'edit' ? 'Изменить ссылку' : 'Добавить ссылку'"></p>

                                    <div class="space-y-3 mt-4">
                                        <template x-if="linkMode !== 'wrap'">
                                            <div class="space-y-1">
                                                <label class="field-label">Текст ссылки</label>
                                                <input type="text" class="field-input" x-model="linkText"
                                                       placeholder="Что будет написано ссылкой">
                                            </div>
                                        </template>
                                        <template x-if="linkMode === 'wrap'">
                                            <p class="field-hint">Ссылка применится к выделенному тексту: «<span x-text="linkText"></span>»</p>
                                        </template>

                                        <div class="space-y-1">
                                            <label class="field-label">Адрес ссылки</label>
                                            <input type="url" class="field-input" x-model="linkUrl"
                                                   placeholder="https://example.com или mailto:info@example.com"
                                                   @keydown.enter.prevent="applyLink()">
                                        </div>

                                        <label class="flex items-start gap-2 text-ui">
                                            <input type="checkbox" x-model="linkNewTab"
                                                   class="mt-0.5 rounded border-hairline text-accent focus:ring-accent-soft">
                                            <span>Открывать в новом окне</span>
                                        </label>
                                    </div>

                                    <div class="flex justify-end gap-3 mt-6">
                                        <button type="button" @click="closeLinkModal()" class="btn-quiet">Отмена</button>
                                        <button type="button" @click="applyLink()" class="btn-primary" :disabled="!linkUrl.trim()">
                                            Сохранить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <textarea x-show="mode === 'source'" x-model="block.data.html" spellcheck="false"
                                  class="field-input font-mono !text-[13px] min-h-[8rem] leading-relaxed"></textarea>

                        <p class="field-hint mt-1" x-show="mode === 'source'">
                            Разрешённые теги при показе на сайте: p, br, strong, em, u, s, a, h2–h4,
                            blockquote, pre, code, ul, ol, li. Остальное вырезается — для произвольного
                            кода используйте блок «HTML-код».
                        </p>

                        @unless($isPrimary)
                            <div class="field-hint mt-2">
                                <span class="text-ink-faint">Русский:</span>
                                <span x-text="(blocks.ru[i] && blocks.ru[i].data.html || '').replace(/<[^>]*>/g,' ').trim() || '—'"></span>
                            </div>
                        @endunless
                    </div>
                </template>

                {{-- HTML-КОД: вставляется на страницу как есть, без обработки --}}
                <template x-if="block.type === 'embed'">
                    <div class="space-y-2">
                        <div class="rounded-sm bg-warning-soft text-warning text-caption p-2">
                            Код вставляется на страницу без обработки. Вы отвечаете за его
                            корректность и безопасность.
                        </div>
                        <textarea :value="block.data.html" @input="setEmbedHtml(i, $event.target.value)"
                                  spellcheck="false"
                                  placeholder="&lt;iframe …&gt;, &lt;script …&gt;, любая разметка"
                                  class="field-input font-mono !text-[13px] min-h-[9rem] leading-relaxed"></textarea>
                        <details class="text-caption text-ink-muted">
                            <summary class="cursor-pointer">Предпросмотр</summary>
                            <iframe class="w-full mt-2 border border-hairline rounded-sm bg-surface"
                                    style="height: 220px" sandbox="allow-scripts allow-popups allow-forms"
                                    :srcdoc="block.data.html || '<!-- пусто -->'"></iframe>
                        </details>
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
                        <input type="text" class="field-input field-input--content" placeholder="Текст заголовка"
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
                                Загрузить
                                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                       @change="upload($event, 'image', (p) => setBlockImage(i, p), (b) => block._busy = b)">
                            </label>
                            <div x-show="block._busy" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">Обработка…</div>
                            <div class="image-cell__actions" x-show="block.data.path">
                                <label class="image-cell__action cursor-pointer" title="Заменить">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 3v4h-4M3 13V9h4M13 7A5 5 0 003 6M3 9a5 5 0 0010 1" stroke-linecap="round"/></svg>
                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                           @change="upload($event, 'image', (p) => setBlockImage(i, p), (b) => block._busy = b)">
                                </label>
                                <button type="button" class="image-cell__action" title="Удалить" @click="setBlockImage(i, null)">
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
                                    Загрузить
                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                           @change="upload($event, block.type, (p) => setBlockImage(i, p, cell), (b) => block._busy = b)">
                                </label>
                                <div x-show="block._busy" class="absolute inset-0 flex items-center justify-center bg-surface/70 text-caption text-ink-muted">…</div>
                                <div class="image-cell__actions" x-show="img">
                                    <label class="image-cell__action cursor-pointer" title="Заменить">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 3v4h-4M3 13V9h4M13 7A5 5 0 003 6M3 9a5 5 0 0010 1" stroke-linecap="round"/></svg>
                                        <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                               @change="upload($event, block.type, (p) => setBlockImage(i, p, cell), (b) => block._busy = b)">
                                    </label>
                                    <button type="button" class="image-cell__action" title="Удалить" @click="setBlockImage(i, null, cell)">
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
        <button type="button" class="btn-secondary" @click="addBlock('text')">Текст</button>
        <button type="button" class="btn-secondary" @click="addBlock('heading')">Заголовок</button>
        <button type="button" class="btn-secondary" @click="addBlock('embed')">HTML-код</button>
        <button type="button" class="btn-secondary" @click="addBlock('image')">Изображение</button>
        <button type="button" class="btn-secondary" @click="addBlock('gallery_2')">Галерея 2</button>
        <button type="button" class="btn-secondary" @click="addBlock('gallery_3')">Галерея 3</button>
        <button type="button" class="btn-secondary" @click="addBlock('gallery_4')">Галерея 4</button>
    </div>
</div>
