{{-- Подсказка «Подробнее →» внизу карточки. Это не ссылка: кликабельна вся карточка. Ссылка стоит в заголовке
     (.miro-event-card__title-link) и растягивается на карточку (см. navigation.css), поэтому экранные дикторы
     читают название новости, а не «Подробнее», и у карточки одна остановка при обходе клавишей Tab.
     $kind: more (по умолчанию) | watch; $external: ссылка откроется в новой вкладке — вместо → стоит ↗. --}}
@php
    $kind = $kind ?? 'more';
    $external = $external ?? false;
@endphp
<span class="miro-event-card__link" aria-hidden="true">
    @if($kind === 'watch')
        <span data-lang="ru">Смотреть</span><span data-lang="en">Watch</span><span data-lang="ro">Vezi</span>
    @else
        <span data-lang="ru">Подробнее</span><span data-lang="en">Read more</span><span data-lang="ro">Află mai multe</span>
    @endif
    <span class="miro-event-card__arrow">{{ $external ? '↗' : '→' }}</span>
</span>
