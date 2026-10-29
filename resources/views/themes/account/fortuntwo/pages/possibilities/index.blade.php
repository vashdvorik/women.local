@extends('themes.account.fortuntwo.layout')

@section('title', __('account.possibilities.title'))

@section('content')
    <div class="miro-page">
        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <h1 class="miro-page-title">{{ __('account.possibilities.title') }}</h1>
                <p class="miro-page-description">{{ __('account.possibilities.subtitle') }}</p>
            </div>
        </header>

        @if($cards->isEmpty())
            <div class="miro-empty">
                <span class="miro-empty__mark"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.5 6.6 19.5l1.2-6L3.3 9.3l6.1-.7L12 3z"/></svg></span>
                <h2>{{ __('account.possibilities.empty_title') }}</h2>
                <p>{{ __('account.possibilities.empty_text') }}</p>
            </div>
        @else
            <div class="pos-grid">
                @foreach($cards as $card)
                    <a href="{{ $card['href'] }}" class="pos-card">
                        @if($card['image'])
                            <span class="pos-card__image"><img src="{{ $card['image'] }}" alt="" loading="lazy"></span>
                        @endif
                        <span class="pos-card__body">
                            <span class="pos-card__meta">
                                @if($card['badge'] !== '')<span class="pos-card__badge">{{ $card['badge'] }}</span>@endif
                                @if($card['date'] !== '')<span class="pos-card__date">{{ $card['date'] }}</span>@endif
                            </span>
                            <h2 class="pos-card__title">{{ $card['title'] }}</h2>
                            @if($card['excerpt'] !== '')<p class="pos-card__excerpt">{{ $card['excerpt'] }}</p>@endif
                            <span class="pos-card__more">{{ __('account.possibilities.more') }} →</span>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-7">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
