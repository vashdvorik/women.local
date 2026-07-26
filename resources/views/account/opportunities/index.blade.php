@extends('account.layout')

@section('title', __('account.opportunities.title'))

@section('content')
    <div class="miro-page">
        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <p class="miro-eyebrow">{{ __('account.nav.opportunities') }}</p>
                <h1 class="miro-page-title">{{ __('account.opportunities.title') }}</h1>
                <p class="miro-page-description">{{ __('account.opportunities.subtitle') }}</p>
            </div>
            <a href="{{ route('account.opportunities.create') }}" class="miro-button miro-button--dark">{{ __('account.opportunities.add') }}</a>
        </header>

        @if($opportunities->isEmpty())
            <div class="miro-empty">
                <span class="miro-empty__mark"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m-7-7h14"/></svg></span>
                <h2>{{ __('account.opportunities.empty_title') }}</h2>
                <p>{{ __('account.opportunities.empty_text') }}</p>
                <a href="{{ route('account.opportunities.create') }}" class="miro-button miro-button--pink mt-6">{{ __('account.opportunities.publish_first') }}</a>
            </div>
        @else
            <div class="space-y-5">
                @foreach($opportunities as $opportunity)
                    <article class="miro-card p-6 sm:p-7">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <span class="inline-flex rounded-full bg-[#c3faf5] px-3 py-1.5 text-xs font-medium text-[#187574]">{{ $opportunity->typeEmoji() }} {{ $opportunity->typeLabel() }}</span>
                                <h2 class="mt-4 text-2xl font-medium tracking-tight text-[#050038]">{{ $opportunity->title }}</h2>
                            </div>

                            @if($opportunity->bot_user_id === $accountUser->id)
                                <form method="POST" action="{{ route('account.opportunities.destroy', $opportunity) }}" onsubmit="return confirm(@json(__('account.opportunities.delete_confirm')));">
                                    @csrf
                                    @method('DELETE')
                                    <button class="miro-button miro-button--outline text-red-700 hover:border-red-400">{{ __('account.delete') }}</button>
                                </form>
                            @endif
                        </div>

                        <p class="mt-5 whitespace-pre-line text-sm leading-7 text-[#555a6a]">{{ $opportunity->body }}</p>

                        <div class="mt-6 flex flex-wrap gap-2 text-xs text-[#6b6f7e]">
                            @if($opportunity->event_date)<span class="rounded-full bg-[#fafbfc] px-3 py-2">{{ $opportunity->event_date->format('d.m.Y') }}</span>@endif
                            @if($opportunity->location)<span class="rounded-full bg-[#fafbfc] px-3 py-2">{{ $opportunity->location }}</span>@endif
                            @if($opportunity->author)<span class="rounded-full bg-[#fafbfc] px-3 py-2">{{ __('account.published_by') }}: {{ $opportunity->author->full_name }}</span>@endif
                        </div>

                        @if($opportunity->contact_url)
                            <a href="{{ $opportunity->contact_url }}" target="_blank" rel="noopener" class="miro-button miro-button--dark mt-6">{{ __('account.contact') }}</a>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-7">{{ $opportunities->links() }}</div>
        @endif
    </div>
@endsection
