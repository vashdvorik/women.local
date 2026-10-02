<x-layouts.admin :title="__('Эксперты')">
    <x-slot:actions>
        <a href="{{ route('admin.experts.create') }}" class="btn-primary">{{ __('Добавить эксперта') }}</a>
    </x-slot:actions>

    @if($experts->isEmpty())
        <div class="card text-center">
            <p class="text-reading text-ink-muted">{{ __('Экспертов пока нет. Страница «Эксперты» покажет только шапку.') }}</p>
            <a href="{{ route('admin.experts.create') }}" class="btn-primary mt-4">{{ __('Добавить эксперта') }}</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20"></th>
                        <th>{{ __('Имя') }}</th>
                        <th>{{ __('Должность') }}</th>
                        <th class="w-28">{{ __('Языки') }}</th>
                        <th class="w-28">{{ __('На сайте') }}</th>
                        <th class="w-28">{{ __('Порядок') }}</th>
                        <th class="w-40"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($experts as $expert)
                        @php $ru = $expert->rawTranslation('ru'); @endphp
                        <tr>
                            <td>
                                @if($expert->photoUrl())
                                    <img src="{{ $expert->photoUrl() }}" alt="" class="h-12 w-12 rounded-sm object-cover">
                                @else
                                    <span class="grid h-12 w-12 place-items-center rounded-sm bg-surface-sunken text-ink-faint">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.experts.edit', $expert) }}" class="table-link">
                                    {{ $ru?->name ?: __('Без имени') }}
                                </a>
                            </td>
                            <td class="text-ink-muted">{{ $ru?->role ?: '—' }}</td>
                            <td class="text-caption text-ink-muted">
                                {{ $expert->translations->pluck('locale')->map(fn ($l) => strtoupper($l))->join(' · ') }}
                            </td>
                            <td><x-admin.status-badge :status="$expert->is_published ? 'published' : 'draft'" /></td>
                            <td>
                                <x-admin.move-buttons :action="route('admin.experts.move', $expert)"
                                                      :first="$loop->first" :last="$loop->last" />
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.experts.edit', $expert) }}" class="btn-quiet">{{ __('Изменить') }}</a>
                                    <x-admin.delete-button :action="route('admin.experts.destroy', $expert)"
                                        :subject="$ru?->name ?: __('эксперта')" :noun="__('эксперта')" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>
