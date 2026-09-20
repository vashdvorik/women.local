{{-- Обложка — только на русской вкладке, одна на все языки (AGENTS.md §12). --}}
<div class="space-y-1">
    <span class="field-label">Обложка</span>

    <div class="image-cell max-w-md" :class="{ 'image-cell--filled': cover }"
         style="aspect-ratio: {{ \App\Support\AspectRatio::css('cover') }}">

        <template x-if="cover">
            <img :src="'/uploads/' + cover" alt="" class="w-full h-full object-cover">
        </template>

        <template x-if="!cover && !coverUploading">
            <label class="absolute inset-0 flex flex-col items-center justify-center gap-2 cursor-pointer text-ink-muted">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 14l4-4 3 3 4-5 3 4M3 4h14v12H3z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="text-caption">Загрузить обложку</span>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                       @change="upload($event, 'cover', (p) => cover = p, (b) => coverUploading = b)">
            </label>
        </template>

        <div x-show="coverUploading" class="absolute inset-0 flex items-center justify-center bg-surface/70">
            <span class="text-caption text-ink-muted">Обработка…</span>
        </div>

        <div class="image-cell__actions" x-show="cover">
            <label class="image-cell__action cursor-pointer" title="Заменить">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M13 3v4h-4M3 13V9h4" stroke-linecap="round"/>
                    <path d="M13 7A5 5 0 003 6M3 9a5 5 0 0010 1" stroke-linecap="round"/>
                </svg>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                       @change="upload($event, 'cover', (p) => cover = p, (b) => coverUploading = b)">
            </label>
            <button type="button" class="image-cell__action" title="Удалить" @click="cover = ''">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>
</div>
