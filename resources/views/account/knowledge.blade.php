@extends('account.layout')

@section('title', __('account.knowledge.title'))

@section('content')
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">{{ __('account.nav.knowledge') }}</p>
        <h1 class="mt-2 text-3xl font-black text-slate-950">{{ __('account.knowledge.title') }}</h1>
        <p class="mt-2 max-w-2xl text-slate-600">{{ __('account.knowledge.subtitle') }}</p>
    </div>

    <div class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
        <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
            <div>
                <span class="inline-flex rounded-full bg-orange-100 px-4 py-2 text-sm font-black text-orange-800">{{ __('account.nav.knowledge') }}</span>
                <h2 class="mt-5 text-3xl font-black text-slate-950">{{ __('account.knowledge.coming_title') }}</h2>
                <p class="mt-4 leading-7 text-slate-600">{{ __('account.knowledge.coming_text') }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                @foreach(__('account.knowledge.modules') as $module)
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 text-center">
                        <div class="mx-auto mb-3 h-10 w-10 rounded-2xl bg-teal-700"></div>
                        <p class="font-black text-slate-950">{{ $module }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
