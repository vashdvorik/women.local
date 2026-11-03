@props(['status'])

{{-- Состояние профиля: видна ли участница другим и пущена ли она в кабинет. Объяснение — в подсказке при наведении. --}}
@switch($status)
    @case(\App\Models\BotUser::STATUS_APPROVED)
        <span class="badge badge--published" title="{{ __('Её профиль виден другим участницам: каталог, поиск, подбор контактов, AI-помощник (пока у неё действует платный пакет).') }}">{{ __('Видима другим') }}</span>
        @break
    @case(\App\Models\BotUser::STATUS_REJECTED)
        <span class="badge badge--rejected" title="{{ __('Заблокирована: войти в кабинет она не может, рассылки не приходят.') }}">{{ __('Заблокирована') }}</span>
        @break
    @default
        <span class="badge badge--pending" title="{{ __('Её профиль не виден другим участницам. Кабинет, пакет и рассылки работают.') }}">{{ __('Скрыта от других') }}</span>
@endswitch
