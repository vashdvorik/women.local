{{--
    Возможности тарифа в карточке. $plan — App\Enums\Plan. Тексты — из lang/*/subscription.php (описания заказчика, без правок):
    вводная фраза и три первых пункта видны сразу, остальное (для кого, остальные пункты, заключительная фраза) — по клику.
--}}
@php
    $features = __('subscription.plans.'.$plan->value.'.features');
    $main = array_slice($features, 0, 3);
    $rest = array_slice($features, 3);
    $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
@endphp
<p class="sub-card__lead">{{ __('subscription.plans.'.$plan->value.'.lead') }}</p>
<ul class="sub-card__list">
    @foreach($main as $feature)
        <li>{!! $check !!}<span>{{ $feature }}</span></li>
    @endforeach
</ul>

<details class="sub-card__more">
    <summary>{{ __('subscription.page.more') }}</summary>
    <div class="sub-card__more-body">
        <p class="sub-card__audience">{{ __('subscription.plans.'.$plan->value.'.audience') }}</p>
        @if($rest !== [])
            <ul class="sub-card__list">
                @foreach($rest as $feature)
                    <li>{!! $check !!}<span>{{ $feature }}</span></li>
                @endforeach
            </ul>
        @endif
        <p class="sub-card__closing">{{ __('subscription.plans.'.$plan->value.'.closing') }}</p>
    </div>
</details>
