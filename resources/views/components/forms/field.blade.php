@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'placeholder' => null,
    'error' => null,          // ключ ошибки, если отличается от name
    'id' => null,
])

@php
    $id = $id ?? ($name ? \Illuminate\Support\Str::slug($name, '_') : null);
    $errorKey = $error ?? $name;
    $messages = $errorKey ? $errors->get($errorKey) : [];
    $hasError = ! empty($messages);
    $shown = old($name, $value);
@endphp

<div class="space-y-1">
    @if($label)
        <label @if($id) for="{{ $id }}" @endif class="field-label">
            {{ $label }}@if($required)<span class="text-danger"> *</span>@endif
        </label>
    @endif

    @if($slot->isNotEmpty())
        {{ $slot }}
    @else
        <input
            type="{{ $type }}"
            @if($id) id="{{ $id }}" @endif
            name="{{ $name }}"
            value="{{ $shown }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            {{ $attributes->class(['field-input', 'field-input--invalid' => $hasError]) }}
        >
    @endif

    @if($hasError)
        <p class="field-error">{{ $messages[0] }}</p>
    @elseif($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif
</div>
