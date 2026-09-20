@php
    $tabs = [
        '' => ['Все', $counts->sum()],
        \App\Models\Opportunity::STATUS_PENDING => ['Ожидают', $counts[\App\Models\Opportunity::STATUS_PENDING] ?? 0],
        \App\Models\Opportunity::STATUS_APPROVED => ['Одобрены', $counts[\App\Models\Opportunity::STATUS_APPROVED] ?? 0],
        \App\Models\Opportunity::STATUS_REJECTED => ['Отклонены', $counts[\App\Models\Opportunity::STATUS_REJECTED] ?? 0],
    ];
    $types = ['project' => 'Запрос', 'meeting' => 'Партнёрство', 'event' => 'Событие'];
@endphp

<x-layouts.admin title="Посты участниц">
    <div class="space-y-4">
        <div class="tabs">
            @foreach($tabs as $key => [$label, $count])
                <a href="{{ route('admin.member-posts.index', array_filter(['status' => $key, 'type' => $type, 'q' => $search])) }}"
                   class="tab @if($status === (string) $key) tab--active @endif">
                    {{ $label }}<span class="text-ink-faint">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" class="flex flex-wrap gap-2 max-w-2xl">
            @if($status !== '') <input type="hidden" name="status" value="{{ $status }}"> @endif
            <input type="search" name="q" value="{{ $search }}" placeholder="Поиск по заголовку или автору"
                   class="field-input flex-1 min-w-[12rem]">
            <select name="type" class="field-input w-44" onchange="this.form.submit()">
                <option value="">Все типы</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Найти</button>
        </form>

        @if($posts->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">
                    {{ ($search !== '' || $status !== '' || $type !== '') ? 'Ничего не найдено.' : 'Постов участниц пока нет.' }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="min-w-[12rem]">Заголовок</th>
                            <th class="whitespace-nowrap">Тип</th>
                            <th class="whitespace-nowrap">Автор</th>
                            <th class="whitespace-nowrap">Статус</th>
                            <th class="whitespace-nowrap">Создан</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.member-posts.show', $post) }}" class="table-link">{{ $post->title }}</a>
                                </td>
                                <td class="text-ink-muted">{{ $post->typeLabel() }}</td>
                                <td class="text-ink-muted">{{ $post->author?->full_name ?: '—' }}</td>
                                <td><x-admin.post-status-badge :status="$post->status" /></td>
                                <td class="whitespace-nowrap text-ink-muted">{{ $post->created_at?->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                                        @unless($post->isApproved())
                                            <x-admin.confirm-button
                                                :action="route('admin.member-posts.approve', $post)"
                                                label="Одобрить"
                                                title="Одобрить публикацию?"
                                                message="Она появится в кабинете у всех участниц, а они получат уведомление в Telegram." />
                                        @endunless
                                        @unless($post->isRejected())
                                            <x-admin.confirm-button
                                                :action="route('admin.member-posts.reject', $post)"
                                                label="Отклонить"
                                                title="Отклонить публикацию?"
                                                message="Публикация будет скрыта от других участниц. Автор увидит пометку об отказе."
                                                :danger="true" />
                                        @endunless
                                        <x-admin.delete-button
                                            :action="route('admin.member-posts.destroy', $post)"
                                            :subject="$post->title"
                                            noun="публикацию" />
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
