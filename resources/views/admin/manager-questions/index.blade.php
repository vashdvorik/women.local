@php
    $tabs = [
        'open' => [__('Без ответа'), $counts['open']],
        'all' => [__('Все вопросы'), $counts['all']],
    ];
@endphp

<x-layouts.admin :title="__('Вопросы AI-менеджеру')">
    <div class="space-y-4">
        <div class="card space-y-1 text-ui text-ink-muted">
            <p>{{ __('Здесь вопросы участниц AI-менеджеру кабинета. «Без ответа» — те, на которые менеджер не нашёл ответа в своих знаниях (или не смог ответить из-за сбоя). Допишите знания на вкладке «AI-менеджер» в «Настройках кабинетов» или ответьте участнице сами, а затем отметьте вопрос разобранным.') }}</p>
        </div>

        <div class="tabs overflow-x-auto no-scrollbar">
            @foreach($tabs as $key => [$label, $count])
                <a href="{{ route('admin.manager-questions.index', ['filter' => $key]) }}"
                   class="tab whitespace-nowrap @if($filter === $key) tab--active @endif">
                    {{ $label }}<span class="text-ink-faint">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        @if($questions->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">{{ $filter === 'open' ? __('Вопросов без ответа нет.') : __('Вопросов пока нет.') }}</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($questions as $item)
                    @php $person = $item->botUser; @endphp
                    <article class="card space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <p class="text-ui text-ink-muted">
                                @if($person)
                                    <a href="{{ route('admin.subscriptions.edit', $person) }}" class="font-semibold text-accent">{{ $person->full_name ?: $person->email }}</a>
                                @else
                                    {{ __('Участница удалена') }}
                                @endif
                                · {{ $item->plan ? \App\Enums\Plan::fromStored($item->plan)->shortTitle() : '—' }}
                                · {{ $item->created_at->format('d.m.Y H:i') }}
                            </p>
                            <div class="flex items-center gap-2">
                                @if($item->answered === false)
                                    <span class="badge badge--pending">{{ $item->handled_at ? __('Разобран') : __('Без ответа') }}</span>
                                @endif
                                @if($item->answered === false && ! $item->handled_at)
                                    <form method="POST" action="{{ route('admin.manager-questions.handled', $item) }}">
                                        @csrf
                                        <button type="submit" class="btn-secondary">{{ __('Разобран') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div>
                            <p class="field-label">{{ __('Вопрос') }}</p>
                            <p class="text-reading whitespace-pre-line">{{ $item->question }}</p>
                        </div>
                        <div>
                            <p class="field-label">{{ __('Ответ менеджера') }}</p>
                            <p class="text-reading whitespace-pre-line text-ink-muted">{{ $item->content }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $questions->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
