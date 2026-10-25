<x-layouts.admin :title="__('Видео')">
    <x-slot:actions>
        <a href="{{ route('admin.videos.create') }}" class="btn-primary">{{ __('Добавить видео') }}</a>
    </x-slot:actions>

    @if($videos->isEmpty())
        <div class="card text-center">
            <p class="text-reading text-ink-muted">{{ __('Видео пока нет.') }}</p>
            <a href="{{ route('admin.videos.create') }}" class="btn-primary mt-4">{{ __('Добавить видео') }}</a>
        </div>
    @else
        <p class="text-reading text-ink-muted">
            {{ __('Перетащите видео за значок слева (или нажмите стрелки), чтобы поменять порядок на сайте.') }}
        </p>

        <div x-data="rowReorder(@js(route('admin.videos.reorder')), @js(['saving' => __('Сохраняем порядок…'), 'saved' => __('Порядок сохранён.'), 'error' => __('Не удалось сохранить порядок. Обновите страницу и попробуйте ещё раз.')]))" class="overflow-x-auto">
            <p class="text-reading text-ink-muted min-h-[1.5rem]" role="status" aria-live="polite" x-text="message()"></p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-24">{{ __('Порядок') }}</th>
                        <th class="w-28"></th>
                        <th>{{ __('Название') }}</th>
                        <th class="w-40">{{ __('Дата события') }}</th>
                        <th class="w-40"></th>
                    </tr>
                </thead>
                <tbody x-ref="body" @dragover="over($event)" @drop.prevent>
                    @foreach($videos as $video)
                        <tr data-id="{{ $video->id }}" @dragstart="start($event)" @dragend="end()">
                            <td>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="btn-icon cursor-grab" @mousedown="arm($event)" @mouseup="disarm($event)"
                                            title="{{ __('Перетащить') }}" aria-label="{{ __('Перетащить') }}">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><circle cx="6" cy="4" r="1.2"/><circle cx="10" cy="4" r="1.2"/><circle cx="6" cy="8" r="1.2"/><circle cx="10" cy="8" r="1.2"/><circle cx="6" cy="12" r="1.2"/><circle cx="10" cy="12" r="1.2"/></svg>
                                    </button>
                                    <button type="button" class="btn-icon" data-move="up" @click="move($event, -1)"
                                            title="{{ __('Вверх') }}" aria-label="{{ __('Вверх') }}">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <button type="button" class="btn-icon" data-move="down" @click="move($event, 1)"
                                            title="{{ __('Вниз') }}" aria-label="{{ __('Вниз') }}">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <img src="{{ $video->coverUrl() }}" alt="" class="w-20 rounded-sm"
                                     style="aspect-ratio: 16/9; object-fit: cover">
                            </td>
                            <td>
                                <a href="{{ route('admin.videos.edit', $video) }}" class="table-link">
                                    {{ $video->rawTranslation('ru')?->title ?: $video->youtube_id }}
                                </a>
                            </td>
                            <td class="text-ink-muted">{{ $video->event_date?->format('d.m.Y') ?? '—' }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.videos.edit', $video) }}" class="btn-quiet">{{ __('Изменить') }}</a>
                                    <x-admin.delete-button :action="route('admin.videos.destroy', $video)"
                                        :subject="$video->rawTranslation('ru')?->title ?: $video->youtube_id" :noun="__('видео')" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>
