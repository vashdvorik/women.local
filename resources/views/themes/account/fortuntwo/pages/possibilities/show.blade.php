@extends('themes.account.fortuntwo.layout')

@section('title', $article['title'])

@section('content')
    <div class="miro-page pos-article">
        <a href="{{ route('account.possibilities.index') }}" class="miro-button miro-button--text mb-4">← {{ __('account.possibilities.back') }}</a>

        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <p class="pos-card__meta">
                    @if($article['badge'] !== '')<span class="pos-card__badge">{{ $article['badge'] }}</span>@endif
                    @if($article['date'] !== '')<span class="pos-card__date">{{ $article['date'] }}</span>@endif
                </p>
                <h1 class="miro-page-title">{{ $article['title'] }}</h1>
            </div>
        </header>

        @if($article['image'])
            <img src="{{ $article['image'] }}" alt="" class="pos-article__cover">
        @endif

        <div class="pos-article__body">
            @include('themes.public.miro.partials.blocks', ['blocks' => $article['blocks'], 'lang' => $article['locale']])
        </div>
    </div>
@endsection
