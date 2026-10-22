<x-layouts.admin :title="__('Фотоальбомы')">
    <x-slot:actions>
        <a href="{{ route('admin.albums.create') }}" class="btn-primary">{{ __('Добавить фотоальбом') }}</a>
    </x-slot:actions>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2 max-w-sm">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Поиск по заголовку') }}" class="field-input">
            <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
        </form>

        @if($albums->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">{{ __('Фотоальбомов пока нет.') }}</p>
                <a href="{{ route('admin.albums.create') }}" class="btn-primary mt-4">{{ __('Добавить фотоальбом') }}</a>
            </div>
        @else
            @if($reorderable)
                <p class="text-reading text-ink-muted">
                    {{ __('Перетащите альбом за значок слева (или нажмите стрелки), чтобы поменять порядок на сайте. Новые альбомы встают первыми.') }}
                </p>
            @else
                <p class="text-reading text-ink-muted">
                    {{ __('Порядок на сайте можно менять в списке без поиска и сортировки.') }}
                    <a href="{{ route('admin.albums.index') }}" class="table-link">{{ __('Показать весь список') }}</a>
                </p>
            @endif

            <div @if($reorderable) x-data="rowReorder(@js(route('admin.albums.reorder')), @js(['saving' => __('Сохраняем порядок…'), 'saved' => __('Порядок сохранён.'), 'error' => __('Не удалось сохранить порядок. Обновите страницу и попробуйте ещё раз.')]))" @endif class="overflow-x-auto">
                @if($reorderable)
                    <p class="text-reading text-ink-muted min-h-[1.5rem]" role="status" aria-live="polite" x-text="message()"></p>
                @endif
                <table class="data-table">
                    <thead>
                        <tr>
                            @if($reorderable)
                                <th class="w-24">{{ __('Порядок') }}</th>
                            @endif
                            <th><x-admin.sort-link column="title" :label="__('Название')" /></th>
                            <th class="w-24">{{ __('Фото') }}</th>
                            <th class="w-36"><x-admin.sort-link column="status" :label="__('Статус')" /></th>
                            <th class="w-40"></th>
                        </tr>
                    </thead>
                    <tbody @if($reorderable) x-ref="body" @dragover="over($event)" @drop.prevent @endif>
                        @foreach($albums as $album)
                            @php $t = $album->rawTranslation('ru'); @endphp
                            <tr @if($reorderable) data-id="{{ $album->id }}" @dragstart="start($event)" @dragend="end()" @endif>
                                @if($reorderable)
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
                                @endif
                                <td><a href="{{ route('admin.albums.edit', $album) }}" class="table-link">{{ $t?->title ?: __('Без названия') }}</a></td>
                                <td class="text-ink-muted">{{ $album->photoCount() }}</td>
                                <td><x-admin.status-badge :status="$album->status" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.albums.edit', $album) }}" class="btn-quiet">{{ __('Изменить') }}</a>
                                        <x-admin.delete-button :action="route('admin.albums.destroy', $album)"
                                            :subject="$t?->title ?: __('Без названия')" :noun="__('фотоальбом')" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @unless($reorderable)
                {{ $albums->links() }}
            @endunless
        @endif
    </div>
</x-layouts.admin>
