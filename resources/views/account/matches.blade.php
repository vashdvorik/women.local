@extends('account.layout')
@section('title', 'AI-матчи')

@section('content')
<div class="max-w-2xl">

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">AI-матчи</h1>
        <p class="mt-1.5 text-sm text-gray-500">Участники с наибольшим потенциалом для партнёрства</p>
    </div>

    @if($matches->isEmpty())

        @if(empty($accountUser->embedding))
        {{-- Embedding not yet computed --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl"
                 style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
                <svg class="h-8 w-8 text-brand-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            @if(empty($accountUser->description))
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-500">Нужны данные</p>
            <h2 class="mt-2 text-base font-semibold text-[#0f172a]">Заполни профиль для матчинга</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-gray-500">
                Алгоритм подбирает партнёров на основе твоего описания и ожиданий. Чем подробнее — тем точнее результат.
            </p>
            <a href="{{ route('account.profile.edit') }}"
               class="mt-6 inline-flex h-10 items-center gap-2 rounded-xl px-5 text-sm font-semibold text-white
                      transition-all hover:-translate-y-px hover:shadow-md"
               style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                Заполнить профиль →
            </a>
            @else
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-500">Готовится</p>
            <h2 class="mt-2 text-base font-semibold text-[#0f172a]">Матчи рассчитываются</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-gray-500">
                AI анализирует твой профиль. Обычно занимает меньше минуты — загляни чуть позже.
            </p>
            @endif
        </div>

        @else
        {{-- Has embedding, no matches yet (community too small) --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
            <p class="text-sm text-gray-400">Пока нет других участников с заполненными профилями.</p>
        </div>
        @endif

    @else

    <div class="space-y-4">
        @foreach($matches as $match)
        @php
            /** @var \App\Models\BotUser $person */
            $person = $match['user'];
            $score  = (float) $match['score'];
            $pct    = (int) round($score * 100);
        @endphp
        <div class="relative flex gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm
                    transition-all duration-200 hover:border-brand-200 hover:shadow-md">

            {{-- Stretched link covering entire card --}}
            <a href="{{ route('account.people.show', $person) }}"
               class="absolute inset-0 rounded-2xl" aria-label="{{ $person->full_name }}"></a>

            {{-- Avatar --}}
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

            {{-- Content --}}
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-[#0f172a]">{{ $person->full_name }}</p>
                        @if($person->telegram_username)
                        <p class="mt-0.5 block text-xs font-medium text-brand-600">
                            {{ '@' . $person->telegram_username }}
                        </p>
                        @endif
                    </div>
                    {{-- Match score badge --}}
                    <div class="shrink-0 text-right">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold
                                     {{ $pct >= 80 ? 'bg-emerald-50 text-emerald-700' : ($pct >= 60 ? 'bg-brand-50 text-brand-700' : 'bg-gray-100 text-gray-500') }}">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            {{ $pct }}%
                        </span>
                    </div>
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

    <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 text-sm text-gray-500 shadow-sm">
        <p class="mb-2 font-semibold text-[#0f172a]">Как работают матчи</p>
        <ul class="space-y-1.5 text-xs leading-relaxed text-gray-500">
            <li>· AI анализирует твоё описание и ожидания, сравнивает с профилями других участников</li>
            <li>· Когда ты меняешь профиль — твои матчи пересчитываются автоматически в течение ~1 минуты</li>
            <li>· Рекомендации для других участников обновляются в течение 24 часов</li>
            <li>· Чем подробнее заполнен профиль — тем точнее подбор</li>
        </ul>
    </div>

    @endif

</div>
@endsection

