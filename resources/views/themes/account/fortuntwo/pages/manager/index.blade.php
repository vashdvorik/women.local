@extends('themes.account.fortuntwo.layout')

@section('title', __('account.manager.title'))

@section('content')
    <div class="miro-page mgr-page">
        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <h1 class="miro-page-title">{{ __('account.manager.title') }}</h1>
                <p class="miro-page-description">{{ __('account.manager.subtitle') }}</p>
            </div>
        </header>

        @include('account.partials.manager-chat', ['autoload' => true])
    </div>
@endsection
