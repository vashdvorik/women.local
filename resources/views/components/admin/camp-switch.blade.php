@props(['camp', 'pending' => 0])

{{-- Переключатель лагерей админки: «Внешний сайт» и «Кабинеты участниц». Каждая вкладка — обычная
     ссылка на инфопанель своего лагеря; меню под переключателем всегда показывает разделы
     текущего лагеря. Число справа — сколько профилей и постов участниц ждут решения. --}}
<div class="camp-switch" role="navigation" aria-label="Разделы админки">
    <a href="{{ route('admin.dashboard') }}"
       class="camp-switch__tab @if($camp === \App\Support\AdminCamp::SITE) camp-switch__tab--active @endif"
       @if($camp === \App\Support\AdminCamp::SITE) aria-current="page" @endif>
        <span>{{ \App\Support\AdminCamp::label(\App\Support\AdminCamp::SITE) }}</span>
    </a>
    <a href="{{ route('admin.cabinets.dashboard') }}"
       class="camp-switch__tab @if($camp === \App\Support\AdminCamp::CABINETS) camp-switch__tab--active @endif"
       @if($camp === \App\Support\AdminCamp::CABINETS) aria-current="page" @endif>
        <span>{{ \App\Support\AdminCamp::label(\App\Support\AdminCamp::CABINETS) }}</span>
        @if($pending > 0)<span class="nav-counter" title="Ждут решения">{{ $pending }}</span>@endif
    </a>
</div>
