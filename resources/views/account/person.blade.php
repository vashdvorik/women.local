@extends('account.layout')
@section('title', $person->full_name)

@section('content')
<div class="max-w-2xl">

    {{-- Back --}}
    <a href="{{ route('account.people') }}"
       class="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 transition hover:text-brand-600">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Назад к людям
    </a>

    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            @if($person->avatar_path)
            <img src="{{ Storage::url($person->avatar_path) }}"
                 alt="{{ $person->full_name }}"
                 class="h-16 w-16 shrink-0 rounded-2xl object-cover shadow-md">
            @else
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-xl font-bold text-white shadow-md"
                 style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                {{ mb_strtoupper(mb_substr($person->full_name ?? '?', 0, 1)) }}
            </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">{{ $person->full_name }}</h1>
                @if($person->telegram_username)
                <a href="https://t.me/{{ $person->telegram_username }}" target="_blank"
                   class="mt-1 block text-sm font-medium text-brand-600 hover:underline">
                    {{ '@' . $person->telegram_username }}
                </a>
                @endif
            </div>
        </div>
        @if($person->telegram_username)
        <a href="https://t.me/{{ $person->telegram_username }}" target="_blank"
           class="inline-flex h-10 items-center gap-2 rounded-xl px-5 text-sm font-semibold text-white
                  transition hover:opacity-90 sm:shrink-0"
           style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
            Написать в Telegram
        </a>
        @endif
    </div>

    {{-- Info --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">О себе</h2>
        <div class="space-y-5">

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Кто и чем занимается</p>
                <p class="mt-1 whitespace-pre-line text-sm text-[#0f172a]">
                    {{ $person->description ?: '—' }}
                </p>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Что ищет в сообществе</p>
                <p class="mt-1 whitespace-pre-line text-sm text-[#0f172a]">
                    {{ $person->expectation ?: '—' }}
                </p>
            </div>

        </div>
    </div>

</div>
@endsection
