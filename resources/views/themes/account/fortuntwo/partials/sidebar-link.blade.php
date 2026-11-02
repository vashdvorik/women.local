{{-- Пункт бокового меню. $item — раздел из CabinetNav; разделы только для ACTIVE и PRIVATE получают метку PREMIUM. --}}
@php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
<a href="{{ route($item['route']) }}" @click="sidebarOpen = false" data-tour="{{ $item['key'] }}"
   class="account-nav-link group mb-0.5 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150 {{ $active ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
    <span class="account-nav-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $active ? 'account-nav-icon--active text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' }}">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['path'] }}"/></svg>
    </span>
    <span class="flex-1">{{ $item['label'] }}</span>
    @if(! empty($item['premium']))
        <span class="account-premium-tag" title="{{ __('account.nav.premium_hint') }}">PREMIUM</span>
    @endif
</a>
