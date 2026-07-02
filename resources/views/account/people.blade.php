@extends('account.layout')

@section('title', __('account.people.title'))

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">{{ __('account.nav.people') }}</p>
            <h1 class="mt-2 text-3xl font-black text-slate-950">{{ __('account.people.title') }}</h1>
            <p class="mt-2 max-w-2xl text-slate-600">{{ __('account.people.subtitle') }}</p>
        </div>
    </div>

    @if($people->isEmpty())
        <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <h2 class="text-xl font-bold text-slate-950">{{ __('account.people.empty_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('account.people.empty_text') }}</p>
        </div>
    @else
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($people as $person)
                <article class="group rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-start gap-4">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-teal-100 to-orange-100">
                            @if($person->avatar_path)
                                <img src="{{ Storage::url($person->avatar_path) }}" alt="{{ $person->full_name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xl font-black text-teal-800">
                                    {{ mb_substr($person->full_name ?: $person->telegram_username, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h2 class="line-clamp-2 text-lg font-black text-slate-950">{{ $person->full_name ?: __('account.not_specified') }}</h2>
                            @if($person->telegram_username)
                                <p class="mt-1 text-sm text-slate-500">@{{ $person->telegram_username }}</p>
                            @endif
                        </div>
                    </div>

                    <p class="mt-5 line-clamp-4 min-h-[6rem] text-sm leading-6 text-slate-600">
                        {{ $person->description ?: $person->expectation ?: __('account.not_filled') }}
                    </p>

                    <a href="{{ route('account.people.show', $person) }}" class="mt-5 inline-flex items-center rounded-full bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-800">
                        {{ __('account.people.open') }}
                    </a>
                </article>
            @endforeach
        </div>
    @endif
@endsection
