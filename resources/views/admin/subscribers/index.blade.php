<x-layouts.admin title="Подписчики">
    <x-slot:actions>
        <a href="{{ route('admin.subscribers.export.csv') }}" class="btn-secondary">Скачать CSV</a>
        <a href="{{ route('admin.subscribers.export.txt') }}" class="btn-secondary">Список почт (.txt)</a>
    </x-slot:actions>

    <div class="space-y-4">
        <form method="GET" class="flex gap-2 max-w-sm">
            <input type="search" name="q" value="{{ $search }}" placeholder="Поиск по имени или почте"
                   class="field-input">
            <button type="submit" class="btn-secondary">Найти</button>
        </form>

        @if($subscribers->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">
                    {{ $search !== '' ? 'Ничего не найдено.' : 'Подписчиков пока нет.' }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-64">Почта</th>
                            <th>Имя</th>
                            <th class="w-40">Дата подписки</th>
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
                                        noun="подписчика" />
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
