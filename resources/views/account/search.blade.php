@extends('account.layout')

@section('title', __('account.search.title'))

@section('content')
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">{{ __('account.nav.search') }}</p>
        <h1 class="mt-2 text-3xl font-black text-slate-950">{{ __('account.search.title') }}</h1>
        <p class="mt-2 max-w-2xl text-slate-600">{{ __('account.search.subtitle') }}</p>
    </div>

    <form method="GET" action="{{ route('account.search') }}" class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row">
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="{{ __('account.search.placeholder') }}"
                class="min-h-14 flex-1 rounded-2xl border border-slate-200 px-5 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            >
            <button class="rounded-2xl bg-slate-950 px-7 py-4 font-black text-white transition hover:bg-teal-800">
                {{ __('account.search.button') }}
            </button>
        </div>
    </form>

    <div class="mt-6">
        @if($results === null)
            <div class="rounded-[2rem] border border-teal-100 bg-teal-50 p-6">
                <h2 class="text-lg font-black text-teal-950">{{ __('account.search.hint_title') }}</h2>
                <p class="mt-2 text-teal-900/80">{{ __('account.search.hint_text') }}</p>
            </div>
        @elseif($results->isEmpty())
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">{{ __('account.search.empty_title') }}</h2>
                <p class="mt-2 text-slate-600">{{ __('account.search.empty_text') }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($results as $result)
                    @php($person = $result['user'])
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

                            <a href="{{ route('account.people.show', $person) }}" class="shrink-0 rounded-full bg-slate-950 px-5 py-2.5 text-center text-sm font-bold text-white transition hover:bg-teal-800">
                                {{ __('account.people.open') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
