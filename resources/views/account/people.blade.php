@extends('account.layout')
@section('title', 'Люди')

@section('content')
<div class="max-w-3xl">

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">Люди</h1>
        <p class="mt-1.5 text-sm text-gray-500">{{ $people->count() }} {{ trans_choice('участник|участника|участников', $people->count()) }} сообщества</p>
    </div>

    @if($people->isEmpty())
    <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
        <svg class="mx-auto mb-3 h-8 w-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm text-gray-400">Пока нет других участников</p>
    </div>
    @else
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach($people as $person)
        <div class="relative flex flex-col rounded-2xl border border-gray-100 bg-white p-5 shadow-sm
                    transition-all duration-200 hover:border-brand-200 hover:shadow-md">
            {{-- Stretched link covering entire card --}}
            <a href="{{ route('account.people.show', $person) }}"
               class="absolute inset-0 rounded-2xl" aria-label="{{ $person->full_name }}"></a>

            <div class="mb-3 flex items-center gap-3">
                @if($person->avatar_path)
                <img src="{{ Storage::url($person->avatar_path) }}"
                     alt="{{ $person->full_name }}"
                     class="h-11 w-11 shrink-0 rounded-full object-cover"
                     style="box-shadow:0 2px 8px rgba(124,58,237,.2)">
                @else
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                     style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 2px 8px rgba(124,58,237,.2)">
                    {{ mb_strtoupper(mb_substr($person->full_name ?? '?', 0, 1)) }}
                </div>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-[#0f172a]">{{ $person->full_name }}</p>
                    @if($person->telegram_username)
                    <p class="mt-0.5 truncate text-xs font-medium text-brand-600">
                        {{ '@' . $person->telegram_username }}
                    </p>
                    @endif
                </div>
            </div>
            @if($person->description)
            <p class="flex-1 text-xs leading-relaxed text-gray-500 line-clamp-3">{{ $person->description }}</p>
            @else
            <p class="text-xs italic text-gray-300">Профиль не заполнен</p>
            @endif
            @if($person->telegram_username)
            <div class="relative z-10 mt-4 border-t border-gray-50 pt-3">
                <a href="https://t.me/{{ $person->telegram_username }}" target="_blank"
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 transition hover:text-brand-700">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                    Написать в Telegram
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
