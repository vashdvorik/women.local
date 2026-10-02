{{-- Переключатель языка интерфейса админки: RU | EN. Обычные ссылки: выбор запоминается в cookie на год
     (AdminLanguageController), поэтому работает и на странице входа. Язык материалов сайта это не меняет. --}}
@php($current = app()->getLocale())

<div {{ $attributes->merge(['class' => 'lang-switch']) }} role="group" aria-label="{{ __('Язык интерфейса') }}">
    @foreach(\App\Http\Middleware\AdminLocale::LOCALES as $code => $label)
        <a href="{{ route('admin.language', $code) }}"
           class="lang-switch__item @if($current === $code) lang-switch__item--active @endif"
           lang="{{ $code }}" hreflang="{{ $code }}"
           @if($current === $code) aria-current="true" @endif>{{ $label }}</a>
    @endforeach
</div>
