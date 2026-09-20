<x-layouts.admin title="Возможности">
    <x-slot:actions>
        <a href="{{ route('admin.opportunities.create') }}" class="btn-primary">Добавить возможность</a>
    </x-slot:actions>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2 max-w-sm">
            <input type="search" name="q" value="{{ $search }}" placeholder="Поиск по заголовку" class="field-input">
            <button type="submit" class="btn-secondary">Найти</button>
        </form>

        @if($opportunities->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">Возможностей пока нет.</p>
                <a href="{{ route('admin.opportunities.create') }}" class="btn-primary mt-4">Добавить возможность</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-24"></th>
                            <th><x-admin.sort-link column="title" label="Заголовок" /></th>
                            <th class="w-36">Тег</th>
                            <th class="w-32"><x-admin.sort-link column="deadline_at" label="Подать до" /></th>
                            <th class="w-36"><x-admin.sort-link column="status" label="Статус" /></th>
                            <th class="w-40"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($opportunities as $opportunity)
                            @php $t = $opportunity->rawTranslation('ru'); @endphp
                            <tr>
                                <td>
                                    @if($opportunity->cover_path)
                                        <img src="/uploads/{{ $opportunity->cover_path }}" alt="" class="w-20 rounded-sm"
                                             style="aspect-ratio: 16/9; object-fit: cover">
                                    @else
                                        <span class="grid w-20 place-items-center rounded-sm bg-surface-sunken text-ink-faint"
                                              style="aspect-ratio: 16/9">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="table-link">
                                        {{ $t?->title ?: 'Без заголовка' }}
                                    </a>
                                </td>
                                <td>
                                    @if($opportunity->tag)
                                        <x-public.tag-badge :tag="$opportunity->tag" />
                                    @else
                                        <span class="text-ink-faint">—</span>
                                    @endif
                                </td>
                                <td class="text-ink-muted">{{ $opportunity->deadline_at?->format('d.m.Y') ?? '—' }}</td>
                                <td><x-admin.status-badge :status="$opportunity->status" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="btn-quiet">Изменить</a>
                                        <x-admin.delete-button
                                            :action="route('admin.opportunities.destroy', $opportunity)"
                                            :subject="$t?->title ?: 'Без заголовка'"
                                            noun="возможность" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $opportunities->links() }}
        @endif
    </div>
</x-layouts.admin>
