<x-layouts.admin title="Новости">
    <x-slot:actions>
        <a href="{{ route('admin.posts.create') }}" class="btn-primary">Добавить новость</a>
    </x-slot:actions>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2 max-w-sm">
            <input type="search" name="q" value="{{ $search }}" placeholder="Поиск по заголовку"
                   class="field-input">
            <button type="submit" class="btn-secondary">Найти</button>
        </form>

        @if($posts->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">Новостей пока нет.</p>
                <a href="{{ route('admin.posts.create') }}" class="btn-primary mt-4">Добавить новость</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-24"></th>
                            <th><x-admin.sort-link column="title" :label="'Заголовок'" /></th>
                            <th class="w-36">Тег</th>
                            <th class="w-36"><x-admin.sort-link column="status" :label="'Статус'" /></th>
                            <th class="w-44"><x-admin.sort-link column="published_at" :label="'Опубликовано'" /></th>
                            <th class="w-40"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            @php $t = $post->rawTranslation('ru'); @endphp
                            <tr>
                                <td>
                                    @if($post->cover_path)
                                        <img src="/uploads/{{ $post->cover_path }}" alt="" class="w-20 rounded-sm"
                                             style="aspect-ratio: 16/9; object-fit: cover">
                                    @else
                                        <span class="grid w-20 place-items-center rounded-sm bg-surface-sunken text-ink-faint"
                                              style="aspect-ratio: 16/9">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="table-link">
                                        {{ $t?->title ?: 'Без заголовка' }}
                                    </a>
                                </td>
                                <td>
                                    @if($post->tag)
                                        <x-public.tag-badge :tag="$post->tag" />
                                    @else
                                        <span class="text-ink-faint">—</span>
                                    @endif
                                </td>
                                <td><x-admin.status-badge :status="$post->status" /></td>
                                <td class="text-ink-muted">
                                    {{ $post->published_at?->format('d.m.Y H:i') ?? '—' }}
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn-quiet">Изменить</a>
                                        <x-admin.delete-button
                                            :action="route('admin.posts.destroy', $post)"
                                            :subject="$t?->title ?: 'Без заголовка'"
                                            noun="новость" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $posts->links() }}
        @endif
    </div>
</x-layouts.admin>
