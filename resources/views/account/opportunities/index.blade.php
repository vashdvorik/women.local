@extends('account.layout')
@section('title', 'Возможности')

@section('content')
<div class="max-w-3xl">

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">Возможности</h1>
            <p class="mt-1.5 text-sm text-gray-500">Запросы, предложения, события и партнёрские инициативы от участниц сообщества.</p>
        </div>
        <a href="{{ route('account.opportunities.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white
                  transition-all hover:-translate-y-px hover:shadow-md"
           style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить возможность
        </a>
    </div>

    @if($opportunities->isEmpty())
    <div class="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl"
             style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
            <svg class="h-8 w-8 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-base font-semibold text-[#0f172a]">Пока нет опубликованных возможностей</h2>
        <p class="mx-auto mt-2 max-w-xs text-sm leading-relaxed text-gray-400">
            Новые запросы, предложения, события и партнёрские инициативы будут появляться здесь.
        </p>
        <a href="{{ route('account.opportunities.create') }}"
           class="mt-6 inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white
                  transition-all hover:-translate-y-px hover:shadow-md"
           style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            Опубликовать первую →
        </a>
    </div>

    @else
    <div class="space-y-4">
        @foreach($opportunities as $opp)
        @php
            /** @var \App\Models\Opportunity $opp */
            $isOwn   = $opp->bot_user_id === $accountUser->id;
            $typeBg  = match($opp->type) {
                'project' => 'bg-blue-50 text-blue-700',
                'meeting' => 'bg-emerald-50 text-emerald-700',
                'event'   => 'bg-purple-50 text-purple-700',
                default   => 'bg-gray-50 text-gray-600',
            };
        @endphp
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm
                    transition-all duration-200 hover:border-brand-200 hover:shadow-md">

            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="shrink-0 text-lg leading-none">{{ $opp->typeEmoji() }}</span>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typeBg }}">
                        {{ $opp->typeLabel() }}
                    </span>
                    <h2 class="truncate text-sm font-semibold text-[#0f172a]">{{ $opp->title }}</h2>
                </div>
                @if($isOwn)
                <form action="{{ route('account.opportunities.destroy', $opp) }}" method="POST"
                      onsubmit="return confirm('Удалить эту публикацию?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="shrink-0 rounded-lg p-1.5 text-gray-300 transition hover:bg-red-50 hover:text-red-500"
                            title="Удалить">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
                @endif
            </div>

            <p class="mt-3 text-sm leading-relaxed text-gray-600 line-clamp-4">{{ $opp->body }}</p>

            @if($opp->event_date || $opp->location)
            <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-400">
                @if($opp->event_date)
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $opp->event_date->format('d.m.Y') }}
                </span>
                @endif
                @if($opp->location)
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $opp->location }}
                </span>
                @endif
            </div>
            @endif

            <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-3">
                <div class="flex items-center gap-2">
                    @if($opp->author?->avatar_path)
                    <img src="{{ Storage::url($opp->author->avatar_path) }}"
                         alt="{{ $opp->author->full_name }}"
                         class="h-6 w-6 shrink-0 rounded-full object-cover">
                    @else
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold text-white"
                         style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                        {{ mb_strtoupper(mb_substr($opp->author?->full_name ?? '?', 0, 1)) }}
                    </div>
                    @endif
                    <span class="text-xs font-medium text-gray-500">
                        {{ explode(' ', (string) ($opp->author?->full_name ?? 'Участница'))[0] }}
                    </span>
                    <span class="text-xs text-gray-300">·</span>
                    <span class="text-xs text-gray-400">{{ $opp->created_at->diffForHumans() }}</span>
                </div>

                @php
                    $contactHref = $opp->contact_url
                        ?: ($opp->author?->telegram_username ? 'https://t.me/' . $opp->author->telegram_username : null);
                @endphp
                @if($contactHref)
                <a href="{{ $contactHref }}" target="_blank" rel="noopener"
                   class="text-xs font-semibold text-brand-600 transition hover:text-brand-700">
                    Связаться →
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($opportunities->hasPages())
    <div class="mt-6">
        {{ $opportunities->links() }}
    </div>
    @endif
    @endif

</div>
@endsection
