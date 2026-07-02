@extends('account.layout')

@section('title', __('account.opportunities.title'))

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">{{ __('account.nav.opportunities') }}</p>
            <h1 class="mt-2 text-3xl font-black text-slate-950">{{ __('account.opportunities.title') }}</h1>
            <p class="mt-2 max-w-2xl text-slate-600">{{ __('account.opportunities.subtitle') }}</p>
        </div>

        <a href="{{ route('account.opportunities.create') }}" class="inline-flex justify-center rounded-full bg-orange-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700">
            {{ __('account.opportunities.add') }}
        </a>
    </div>

    @if($opportunities->isEmpty())
        <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <h2 class="text-xl font-bold text-slate-950">{{ __('account.opportunities.empty_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('account.opportunities.empty_text') }}</p>
            <a href="{{ route('account.opportunities.create') }}" class="mt-6 inline-flex rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white transition hover:bg-teal-800">
                {{ __('account.opportunities.publish_first') }}
            </a>
        </div>
    @else
        <div class="space-y-5">
            @foreach($opportunities as $opportunity)
                <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <span class="inline-flex rounded-full bg-teal-50 px-4 py-2 text-sm font-black text-teal-800">
                                {{ $opportunity->typeEmoji() }} {{ $opportunity->typeLabel() }}
                            </span>
                            <h2 class="mt-4 text-2xl font-black text-slate-950">{{ $opportunity->title }}</h2>
                        </div>

                        @if($opportunity->bot_user_id === $accountUser->id)
                            <form method="POST" action="{{ route('account.opportunities.destroy', $opportunity) }}" onsubmit="return confirm(@json(__('account.opportunities.delete_confirm')));">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full border border-red-200 px-4 py-2 text-sm font-bold text-red-700 transition hover:bg-red-50">
                                    {{ __('account.delete') }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <p class="mt-4 whitespace-pre-line leading-7 text-slate-700">{{ $opportunity->body }}</p>

                    <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-600">
                        @if($opportunity->event_date)
                            <span class="rounded-full bg-slate-100 px-4 py-2">{{ $opportunity->event_date->format('d.m.Y') }}</span>
                        @endif
                        @if($opportunity->location)
                            <span class="rounded-full bg-slate-100 px-4 py-2">{{ $opportunity->location }}</span>
                        @endif
                        @if($opportunity->author)
                            <span class="rounded-full bg-slate-100 px-4 py-2">{{ __('account.published_by') }}: {{ $opportunity->author->full_name }}</span>
                        @endif
                    </div>

                    @if($opportunity->contact_url)
                        <a href="{{ $opportunity->contact_url }}" target="_blank" rel="noopener" class="mt-5 inline-flex rounded-full bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-800">
                            {{ __('account.contact') }}
                        </a>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $opportunities->links() }}
        </div>
    @endif
@endsection
