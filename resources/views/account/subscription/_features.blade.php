{{-- Список возможностей тарифа. $plan — App\Enums\Plan. Тексты берутся из lang/*/subscription.php (описания заказчика). --}}
<p class="text-sm font-medium text-gray-700">{{ __('subscription.plans.'.$plan->value.'.lead') }}</p>
<ul class="mt-3 space-y-2">
    @foreach(__('subscription.plans.'.$plan->value.'.features') as $feature)
        <li class="flex items-start gap-2.5 text-sm leading-6 text-gray-600">
            <svg class="mt-1 h-4 w-4 shrink-0 text-brand-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>{{ $feature }}</span>
        </li>
    @endforeach
</ul>
