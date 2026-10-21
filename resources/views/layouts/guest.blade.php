<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Вход в панель') }} — {{ config('app.name') }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink flex items-center justify-center p-4">
    <div class="w-full max-w-[360px]">
        <div class="mb-4 flex justify-end">
            <x-admin.lang-switch />
        </div>
        {{-- Основной логотип платформы (тот же, что в шапке публичного сайта и в кабинете), цветной: страница светлая. --}}
        <a href="{{ url('/') }}" class="admin-login-logo mb-8 flex justify-center">
            <img src="{{ asset('themes/public/miro/images/brand/logo-platform.webp') }}" alt="{{ config('app.name') }}"
                 class="h-14 w-auto max-w-full object-contain">
        </a>
        {{ $slot }}
    </div>
</body>
</html>
