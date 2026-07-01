@extends('account.layout')
@section('title', 'Новая возможность')

@section('content')
<div class="max-w-2xl">

    <div class="mb-8">
        <a href="{{ route('account.opportunities.index') }}"
           class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-gray-400 transition hover:text-brand-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Возможности
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">Новая возможность</h1>
        <p class="mt-1.5 text-sm text-gray-500">
            Опубликуйте запрос, предложение, встречу или событие. Участницы получат уведомление в Telegram.
        </p>
    </div>

    <form action="{{ route('account.opportunities.store') }}" method="POST"
          class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        @csrf

        <div x-data="{ selected: '{{ old('type', '') }}' }">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Тип *</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach(['project' => ['emoji' => '💼', 'label' => 'Запрос'],
                          'meeting' => ['emoji' => '🤝', 'label' => 'Партнёрство'],
                          'event'   => ['emoji' => '📅', 'label' => 'Событие']] as $val => $meta)
                <label @click="selected = '{{ $val }}'"
                       :class="selected === '{{ $val }}'
                           ? 'border-brand-500 bg-brand-50 shadow-sm'
                           : 'border-gray-200 hover:border-brand-200'"
                       class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 p-3 text-center transition-all">
                    <input type="radio" name="type" value="{{ $val }}"
                           x-model="selected" class="sr-only">
                    <span class="text-2xl leading-none">{{ $meta['emoji'] }}</span>
                    <span class="text-xs font-semibold text-gray-700">{{ $meta['label'] }}</span>
                </label>
                @endforeach
            </div>
            @error('type')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="title" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">
                Заголовок *
            </label>
            <input id="title" name="title" type="text" maxlength="200"
                   value="{{ old('title') }}"
                   placeholder="Например: ищу партнёров для участия в выставке"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-[#0f172a] placeholder-gray-300
                          transition focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100
                          @error('title') border-red-300 @enderror">
            @error('title')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="body" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">
                Описание *
            </label>
            <textarea id="body" name="body" rows="5" maxlength="2000"
                      placeholder="Опишите, что вы ищете или предлагаете, кому это может быть полезно и как с вами связаться."
                      class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-[#0f172a] placeholder-gray-300
                             transition focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100
                             @error('body') border-red-300 @enderror">{{ old('body') }}</textarea>
            @error('body')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="event_date" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Дата <span class="font-normal normal-case text-gray-400">(необязательно)</span>
                </label>
                <input id="event_date" name="event_date" type="date"
                       value="{{ old('event_date') }}"
                       min="{{ date('Y-m-d') }}"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-[#0f172a]
                              transition focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100
                              @error('event_date') border-red-300 @enderror">
                @error('event_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="location" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Место <span class="font-normal normal-case text-gray-400">(необязательно)</span>
                </label>
                <input id="location" name="location" type="text" maxlength="200"
                       value="{{ old('location') }}"
                       placeholder="Кишинёв, онлайн, Бельцы..."
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-[#0f172a] placeholder-gray-300
                              transition focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100
                              @error('location') border-red-300 @enderror">
                @error('location')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="contact_url" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">
                Ссылка для связи <span class="font-normal normal-case text-gray-400">(необязательно)</span>
            </label>
            <input id="contact_url" name="contact_url" type="url"
                   value="{{ old('contact_url') }}"
                   placeholder="https://t.me/username или ссылка на форму"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-[#0f172a] placeholder-gray-300
                          transition focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100
                          @error('contact_url') border-red-300 @enderror">
            @error('contact_url')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="border-t border-gray-100 pt-5">
            <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold
                           text-white transition-all hover:-translate-y-px hover:shadow-md sm:w-auto"
                    style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Опубликовать и уведомить участниц
            </button>
        </div>

    </form>
</div>
@endsection
