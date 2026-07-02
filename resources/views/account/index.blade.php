@extends('account.layout')
@section('title', __('account.dashboard.title'))

@section('content')
<div class="max-w-3xl">
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">{{ __('account.dashboard.hello', ['name' => explode(' ', (string) $accountUser->full_name)[0]]) }}</h1>
        <p class="mt-1.5 text-sm text-gray-500">{{ __('account.dashboard.intro') }}</p>
    </div>

    @if(empty($accountUser->description))
        <div class="mb-8 flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-900">{{ __('account.dashboard.complete_title') }}</p>
                <p class="mt-0.5 text-xs leading-relaxed text-amber-700">{{ __('account.dashboard.complete_text') }}</p>
            </div>
            <a href="{{ route('account.profile.edit') }}" class="shrink-0 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white">{{ __('account.dashboard.complete_button') }}</a>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach([
            ['route' => 'account.profile', 'title' => __('account.nav.profile'), 'note' => __('account.dashboard.profile_note')],
            ['route' => 'account.matches', 'title' => __('account.nav.matches'), 'note' => __('account.dashboard.matches_note')],
            ['route' => 'account.people', 'title' => __('account.nav.people'), 'note' => __('account.dashboard.people_note')],
        ] as $card)
            <a href="{{ route($card['route']) }}" class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <p class="text-sm font-semibold text-[#0f172a] group-hover:text-brand-700">{{ $card['title'] }}</p>
                <p class="mt-1 text-xs leading-relaxed text-gray-400">{{ $card['note'] }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
