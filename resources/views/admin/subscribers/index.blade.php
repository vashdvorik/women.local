<x-layouts.admin :title="__('Подписчики')">
    <x-slot:actions>
        <a href="{{ route('admin.subscribers.export.csv') }}" class="btn-secondary">{{ __('Скачать CSV') }}</a>
        <a href="{{ route('admin.subscribers.export.txt') }}" class="btn-secondary">{{ __('Список почт (.txt)') }}</a>
    </x-slot:actions>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2 max-w-sm">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Поиск по имени или почте') }}"
                   class="field-input">
            <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
        </form>

        @if($subscribers->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">
                    {{ $search !== '' ? __('Ничего не найдено.') : __('Подписчиков пока нет.') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-64">{{ __('Почта') }}</th>
                            <th>{{ __('Имя') }}</th>
                            <th class="w-40">{{ __('Дата подписки') }}</th>
                            <th class="w-24"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscribers as $subscriber)
                            <tr>
                                <td>
                                    <a href="mailto:{{ $subscriber->email }}" class="table-link">{{ $subscriber->email }}</a>
                                </td>
                                <td class="text-ink-muted">{{ $subscriber->name ?: '—' }}</td>
                                <td class="text-ink-muted">{{ $subscriber->created_at->format('d.m.Y') }}</td>
                                <td class="text-right">
                                    <x-admin.delete-button
                                        :action="route('admin.subscribers.destroy', $subscriber)"
                                        :subject="$subscriber->email"
                                        :noun="__('подписчика')" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $subscribers->links() }}
        @endif
    </div>
</x-layouts.admin>
