@extends('themes.account.fortuntwo.layout')

@section('title', __('account.manager.title'))

@section('content')
{{-- Оформление то же, что у страницы «AI-помощник»: чат без дополнительного контейнера --}}
<div class="fortun-dashboard-page mgr-page">
    <section class="fortun-ai-assistant" aria-label="{{ __('account.manager.title') }}">
        @include('account.partials.manager-chat', ['autoload' => true, 'withHeader' => true])
    </section>
</div>
@endsection
