@extends('account.layout')

@section('title', $person->full_name ?: __('account.person.title'))

@section('content')
    <a href="{{ route('account.people') }}" class="mb-6 inline-flex text-sm font-bold text-teal-700 hover:text-teal-900">
        {{ __('account.back') }}
    </a>

    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.4fr]">
        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mx-auto h-32 w-32 overflow-hidden rounded-[2rem] bg-gradient-to-br from-teal-100 to-orange-100">
                @if($person->avatar_path)
                    <img src="{{ Storage::url($person->avatar_path) }}" alt="{{ $person->full_name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-4xl font-black text-teal-800">
                        {{ mb_substr($person->full_name ?: $person->telegram_username, 0, 1) }}
                    </div>
                @endif
            </div>

            <h1 class="mt-5 text-center text-2xl font-black text-slate-950">{{ $person->full_name ?: __('account.not_specified') }}</h1>

            @if($person->telegram_username)
                <p class="mt-2 text-center text-slate-500">@{{ $person->telegram_username }}</p>
                <a href="https://t.me/{{ $person->telegram_username }}" target="_blank" rel="noopener" class="mt-6 flex justify-center rounded-full bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700">
                    {{ __('account.contact') }}
                </a>
            @endif
        </aside>

        <section class="space-y-5">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-teal-700">{{ __('account.person.about') }}</p>
                <p class="mt-4 whitespace-pre-line text-slate-700">{{ $person->description ?: __('account.profile.description_empty') }}</p>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">{{ __('account.person.request') }}</p>
                <p class="mt-4 whitespace-pre-line text-slate-700">{{ $person->expectation ?: __('account.profile.expectation_empty') }}</p>
            </div>
        </section>
    </div>
@endsection
