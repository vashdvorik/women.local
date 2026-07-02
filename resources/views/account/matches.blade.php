@extends('account.layout')

@section('title', __('account.matches.title'))

@section('content')
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">{{ __('account.nav.matches') }}</p>
        <h1 class="mt-2 text-3xl font-black text-slate-950">{{ __('account.matches.title') }}</h1>
        <p class="mt-2 max-w-2xl text-slate-600">{{ __('account.matches.subtitle') }}</p>
    </div>

    @if($matches->isEmpty())
        <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <h2 class="text-xl font-bold text-slate-950">{{ __('account.matches.empty_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('account.matches.empty_text') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($matches as $match)
                @php($person = $match['user'])
                <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex gap-4">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-teal-100 to-orange-100">
                                @if($person->avatar_path)
                                    <img src="{{ Storage::url($person->avatar_path) }}" alt="{{ $person->full_name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xl font-black text-teal-800">
                                        {{ mb_substr($person->full_name ?: $person->telegram_username, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-950">{{ $person->full_name ?: __('account.not_specified') }}</h2>
                                @if($person->telegram_username)
                                    <p class="mt-1 text-sm text-slate-500">@{{ $person->telegram_username }}</p>
                                @endif
                                <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $person->description ?: $person->expectation ?: __('account.not_filled') }}</p>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-row items-center gap-3 sm:flex-col sm:items-end">
                            <span class="rounded-full bg-teal-50 px-4 py-2 text-sm font-black text-teal-800">
                                {{ max(0, min(100, round((float) $match['score'] * 100))) }}% {{ __('account.matches.score') }}
                            </span>
                            <a href="{{ route('account.people.show', $person) }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-800">
                                {{ __('account.people.open') }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
