{{--
    Логотип кабинета: тот же файл и та же тёмная плашка, что в шапке публичного сайта
    (themes/public/miro/partials/miro-header.blade.php, navigation.css). Белая надпись видна только на тёмном,
    поэтому плашка #21140F остаётся тёмной во всех четырёх темах кабинета. Класс не «account-brand-mark»:
    его перекрашивают стили тем.
--}}
<a href="{{ route('account.index') }}" aria-label="Women's hub"
   class="account-brand-logo mb-4 flex h-[72px] shrink-0 items-center px-5"
   style="background:#21140F;border-bottom:1px solid rgba(201,154,115,.32)">
    <img src="{{ asset('themes/public/miro/images/brand/logo-white.webp') }}" alt="Women's hub"
         class="h-[44px] w-auto max-w-full object-contain object-left">
</a>
