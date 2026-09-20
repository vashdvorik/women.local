<x-layouts.admin title="Фотоальбомы">
    <x-slot:actions>
        <a href="{{ route('admin.albums.create') }}" class="btn-primary">Добавить фотоальбом</a>
    </x-slot:actions>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2 max-w-sm">
            <input type="search" name="q" value="{{ $search }}" placeholder="Поиск по заголовку" class="field-input">
            <button type="submit" class="btn-secondary">Найти</button>
        </form>

        @if($albums->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">Фотоальбомов пока нет.</p>
                <a href="{{ route('admin.albums.create') }}" class="btn-primary mt-4">Добавить фотоальбом</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><x-admin.sort-link column="title" label="Название" /></th>
                            <th class="w-24">Фото</th>
                            <th class="w-36"><x-admin.sort-link column="status" label="Статус" /></th>
                            <th class="w-40"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($albums as $album)
                            @php $t = $album->rawTranslation('ru'); @endphp
                            <tr>
                                <td><a href="{{ route('admin.albums.edit', $album) }}" class="table-link">{{ $t?->title ?: 'Без названия' }}</a></td>
                                <td class="text-ink-muted">{{ $album->photoCount() }}</td>
                                <td><x-admin.status-badge :status="$album->status" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.albums.edit', $album) }}" class="btn-quiet">Изменить</a>
                                        <x-admin.delete-button :action="route('admin.albums.destroy', $album)"
                                            :subject="$t?->title ?: 'Без названия'" noun="фотоальбом" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $albums->links() }}
        @endif
    </div>
</x-layouts.admin>
