<x-layouts.admin title="Теги">
    <x-slot:actions>
        <a href="{{ route('admin.tags.create') }}" class="btn-primary">Добавить тег</a>
    </x-slot:actions>

    @if($tags->isEmpty())
        <div class="card text-center">
            <p class="text-reading text-ink-muted">Тегов пока нет.</p>
            <a href="{{ route('admin.tags.create') }}" class="btn-primary mt-4">Добавить тег</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th class="w-24">Новостей</th>
                        <th class="w-32">Возможностей</th>
                        <th class="w-40"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tags as $tag)
                        <tr>
                            <td>
                                <a href="{{ route('admin.tags.edit', $tag) }}" class="inline-flex items-center gap-2">
                                    <x-public.tag-badge :tag="$tag" />
                                </a>
                            </td>
                            <td class="text-ink-muted">{{ $tag->posts_count }}</td>
                            <td class="text-ink-muted">{{ $tag->opportunities_count }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.tags.edit', $tag) }}" class="btn-quiet">Изменить</a>
                                    <x-admin.delete-button
                                        :action="route('admin.tags.destroy', $tag)"
                                        :subject="$tag->rawTranslation('ru')?->name"
                                        noun="тег" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>
