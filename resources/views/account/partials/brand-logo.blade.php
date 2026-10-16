{{--
    Логотип кабинета: тот же рисунок, что в шапке публичного сайта (themes/public/miro/partials/miro-header.blade.php),
    в тёмных тонах (logo-dark.webp): боковое меню светлое, плашка не нужна. Класс не «account-brand-mark»:
    его перекрашивают стили тем.
--}}
<a href="{{ route('account.index') }}" aria-label="Women's hub"
   class="account-brand-logo mb-2 flex shrink-0 items-center px-5 pb-3 pt-6">
    <img src="{{ asset('themes/public/miro/images/brand/logo-dark.webp') }}" alt="Women's hub"
         class="h-[44px] w-auto max-w-full object-contain object-left">
</a>
