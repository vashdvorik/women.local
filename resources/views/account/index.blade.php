@extends('account.layout')
@section('title', 'Главная')

@section('content')
<div class="max-w-3xl">

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">
            Здравствуйте, {{ explode(' ', (string) $accountUser->full_name)[0] }}
        </h1>
        <p class="mt-1.5 text-sm text-gray-500">
            Личный кабинет Women Entrepreneurs Platform of the Two Banks помогает представить бизнес, найти полезные контакты и отслеживать возможности.
        </p>
    </div>

    @if(empty($accountUser->description))
    <div class="mb-8 flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
            <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-amber-900">Заполните профиль</p>
            <p class="mt-0.5 text-xs text-amber-700 leading-relaxed">
                Расскажите о себе, своём бизнесе, запросах и возможностях для сотрудничества. Так другим участницам будет проще понять, как с вами связаться.
            </p>
        </div>
        <a href="{{ route('account.profile') }}"
           class="shrink-0 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-amber-600 active:scale-95">
            Заполнить
        </a>
    </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">

        <a href="{{ route('account.profile') }}"
           class="group flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm
                  transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl"
                 style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 12px rgba(124,58,237,.25)">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-[#0f172a] transition group-hover:text-brand-700">Мой профиль</p>
                <p class="mt-1 text-xs leading-relaxed text-gray-400">Представить себя и свой бизнес</p>
            </div>
        </a>

        <a href="{{ route('account.matches') }}"
           class="group flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm
                  transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50">
                <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-[#0f172a] transition group-hover:text-brand-700">Рекомендации</p>
                <p class="mt-1 text-xs leading-relaxed text-gray-400">Участницы с близкими интересами</p>
            </div>
        </a>

        <a href="{{ route('account.people') }}"
           class="group flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm
                  transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50">
                <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-[#0f172a] transition group-hover:text-brand-700">Предпринимательницы</p>
                <p class="mt-1 text-xs leading-relaxed text-gray-400">Каталог участниц сообщества</p>
            </div>
        </a>

    </div>
</div>
@endsection
