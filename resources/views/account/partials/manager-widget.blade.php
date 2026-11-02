{{--
    Плавающая кнопка и окно AI-менеджера на всех страницах кабинета (для участниц с тарифом, кроме самой страницы менеджера).
--}}
<button type="button" class="mgr-fab" data-mgr-open aria-label="{{ __('account.manager.open') }}">
    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Support\CabinetNav::icon('manager') }}"/></svg>
    <span class="mgr-fab__label">{{ __('account.manager.open') }}</span>
</button>
<section class="mgr-panel" data-mgr-panel hidden role="dialog" aria-label="{{ __('account.manager.title') }}">
    <header class="mgr-panel__head">
        <div>
            <p class="mgr-panel__title">{{ __('account.manager.title') }}</p>
            <p class="mgr-panel__sub">{{ __('account.manager.subtitle') }}</p>
        </div>
        <button type="button" class="mgr-panel__close" data-mgr-close aria-label="{{ __('account.manager.close') }}">&times;</button>
    </header>
    @include('account.partials.manager-chat', ['autoload' => false])
</section>
