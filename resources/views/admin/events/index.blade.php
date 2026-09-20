<x-layouts.admin title="Новости">
    <x-slot:actions>
        <a href="{{ route('admin.events.create') }}" class="btn-primary">Добавить новость</a>
    </x-slot:actions>

    @if($events->isEmpty())
        <div class="card text-center">
            <p class="text-reading text-ink-muted">Новостей пока нет. Страница «Новости» покажет только шапку.</p>
            <a href="{{ route('admin.events.create') }}" class="btn-primary mt-4">Добавить новость</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-24"></th>
                        <th>Название</th>
                        <th class="w-40">Тип</th>
                        <th class="w-40">Дата</th>
                        <th class="w-28">Языки</th>
                        <th class="w-28">На сайте</th>
                        <th class="w-28">Порядок</th>
                        <th class="w-40"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        @php $ru = $event->rawTranslation('ru'); @endphp
                        <tr>
                            <td>
                                @if($event->imageUrl())
                                    <img src="{{ $event->imageUrl() }}" alt="" class="w-20 rounded-sm"
                                         style="aspect-ratio: 16/9; object-fit: cover">
                                @else
                                    <span class="grid w-20 place-items-center rounded-sm bg-surface-sunken text-ink-faint"
                                          style="aspect-ratio: 16/9">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.events.edit', $event) }}" class="table-link">
                                    {{ $ru?->title ?: 'Без названия' }}
                                </a>
                            </td>
                            <td class="text-ink-muted">{{ $ru?->type ?: '—' }}</td>
                            <td class="text-ink-muted">{{ $event->dateLabel('ru') ?: '—' }}</td>
                            <td class="text-caption text-ink-muted">
                                {{ $event->translations->pluck('locale')->map(fn ($l) => strtoupper($l))->join(' · ') }}
                            </td>
                            <td><x-admin.status-badge :status="$event->is_published ? 'published' : 'draft'" /></td>
                            <td>
                                <x-admin.move-buttons :action="route('admin.events.move', $event)"
                                                      :first="$loop->first" :last="$loop->last" />
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn-quiet">Изменить</a>
                                    <x-admin.delete-button :action="route('admin.events.destroy', $event)"
                                        :subject="$ru?->title ?: 'новость'" noun="новость" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>
