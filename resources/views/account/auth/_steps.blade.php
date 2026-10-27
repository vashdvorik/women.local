{{-- Три шага до кабинета: почта → анкета → тариф. $step — текущий (1, 2 или 3). --}}
@php
    $items = [1 => __('participant.onboarding.step_email'), 2 => __('participant.onboarding.step_profile'), 3 => __('participant.onboarding.step_plan')];
@endphp
<div class="account-steps" aria-label="{{ __('participant.onboarding.title') }}">
    @foreach($items as $number => $label)
        @if($number > 1)<span class="account-steps__line" aria-hidden="true"></span>@endif
        <span class="account-steps__item {{ $number < $step ? 'is-done' : '' }} {{ $number === $step ? 'is-current' : '' }}" @if($number === $step) aria-current="step" @endif>
            <span class="account-steps__num">{{ $number < $step ? '✓' : $number }}</span>{{ $label }}
        </span>
    @endforeach
</div>
