@php
    $message = session('success') ?? session('error');
    $kind = session('success') ? 'success' : 'error';
@endphp

{{-- Сообщения из скриптов: ошибки загрузки и перекадрирования картинок (flashError в uploader.js присылает событие
     «flash» на window). Серверные сообщения показывает блок ниже. --}}
<div x-data="{ show: false, kind: 'error', text: '', timer: null }" x-cloak
     x-on:flash.window="kind = $event.detail.type === 'success' ? 'success' : 'error'; text = $event.detail.text || ''; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, 6000)"
     x-show="show" x-transition.opacity :class="'toast toast--' + kind" role="alert" data-script-toast>
    <div class="flex items-start gap-3">
        <p class="text-ui flex-1" x-text="text"></p>
        <button type="button" @click="show = false" class="btn-icon -mt-1 -mr-1" aria-label="{{ __('Закрыть') }}">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
</div>

@if($message)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity
         x-init="setTimeout(() => show = false, 4000)"
         class="toast toast--{{ $kind }}" role="status">
        <div class="flex items-start gap-3">
            <p class="text-ui flex-1">{{ $message }}</p>
            <button type="button" @click="show = false" class="btn-icon -mt-1 -mr-1" aria-label="{{ __('Закрыть') }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>
@endif
