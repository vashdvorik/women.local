@extends('account.layout')
@section('title', 'Поиск контактов')

@section('content')
<div class="max-w-2xl">

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">Поиск контактов</h1>
        <p class="mt-1.5 text-sm text-gray-500">Опишите, кого или какую экспертизу вы ищете. Платформа предложит участниц с близкими профилями.</p>
    </div>

    <form method="GET" action="{{ route('account.search') }}" class="mb-8">
        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="text"
                   name="q"
                   value="{{ $query }}"
                   placeholder="Например: ищу партнёрку для экспорта или эксперта по маркетингу"
                   autofocus
                   maxlength="500"
                   class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm
                          placeholder-gray-400 shadow-sm outline-none transition
                          focus:border-brand-400 focus:ring-2 focus:ring-brand-100">
            <button type="submit"
                    class="inline-flex h-12 shrink-0 items-center gap-2 rounded-xl px-6 text-sm font-semibold
                           text-white transition hover:opacity-90 active:scale-95"
                    style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 12px rgba(124,58,237,.3)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                Найти
            </button>
        </div>
    </form>

    @if($results === null)
    <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl"
             style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
            <svg class="h-6 w-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-[#0f172a]">Введите запрос</p>
        <p class="mt-1 text-xs text-gray-400">Поиск помогает ориентироваться в профилях участниц и находить полезные контакты.</p>
        <div class="mt-5 flex flex-wrap justify-center gap-2">
            @foreach(['Ищу партнёрку для совместного проекта', 'Нужен эксперт по продвижению', 'Ищу поставщиков упаковки', 'Хочу найти ментора по финансам'] as $example)
            <a href="{{ route('account.search', ['q' => $example]) }}"
               class="rounded-full border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700
                      transition hover:bg-brand-100">
                {{ $example }}
            </a>
            @endforeach
        </div>
    </div>

    @elseif($results->isEmpty())
    <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50">
            <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-[#0f172a]">Ничего не найдено</p>
        <p class="mt-1 text-xs text-gray-400">Попробуйте изменить формулировку: укажите сферу, тип контакта, задачу или формат сотрудничества.</p>
    </div>

    @else
    <p class="mb-4 text-xs text-gray-400">Найдено {{ $results->count() }} {{ trans_choice('профиль|профиля|профилей', $results->count()) }} по запросу «{{ $query }}»</p>

    <div class="space-y-4">
        @foreach($results as $match)
        @php
            /** @var \App\Models\BotUser $person */
            $person = $match['user'];
            $score  = (float) $match['score'];
            $pct    = (int) round($score * 100);
        @endphp
        <div class="relative flex gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm
                    transition-all duration-200 hover:border-brand-200 hover:shadow-md">

            <a href="{{ route('account.people.show', $person) }}"
               class="absolute inset-0 rounded-2xl" aria-label="{{ $person->full_name }}"></a>

            <div class="shrink-0">
                @if($person->avatar_path)
                <img src="{{ Storage::url($person->avatar_path) }}"
                     alt="{{ $person->full_name }}"
                     class="h-14 w-14 rounded-2xl object-cover"
                     style="box-shadow:0 2px 8px rgba(124,58,237,.2)">
                @else
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-lg font-bold text-white"
                     style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 2px 8px rgba(124,58,237,.2)">
                    {{ mb_strtoupper(mb_substr($person->full_name ?? '?', 0, 1)) }}
                </div>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-[#0f172a]">{{ $person->full_name }}</p>
                        @if($person->telegram_username)
                        <p class="mt-0.5 text-xs font-medium text-brand-600">
                            {{ '@' . $person->telegram_username }}
                        </p>
                        @endif
                    </div>
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold
                                 {{ $pct >= 80 ? 'bg-emerald-50 text-emerald-700' : ($pct >= 60 ? 'bg-brand-50 text-brand-700' : 'bg-gray-100 text-gray-500') }}">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ $pct }}%
                    </span>
                </div>

                @if($person->description)
                <p class="mt-2 text-xs leading-relaxed text-gray-500 line-clamp-2">{{ $person->description }}</p>
                @endif

                @if($person->expectation)
                <p class="mt-1 text-xs leading-relaxed text-gray-400 line-clamp-1">
                    <span class="font-medium text-gray-500">Ищет:</span> {{ $person->expectation }}
                </p>
                @endif

                @if($person->telegram_username)
                <div class="relative z-10 mt-3">
                    <a href="https://t.me/{{ $person->telegram_username }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5
                              text-xs font-semibold text-brand-700 transition hover:bg-brand-100">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        Написать
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
