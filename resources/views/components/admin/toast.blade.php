@php
    $message = session('success') ?? session('error');
    $kind = session('success') ? 'success' : 'error';
@endphp

@if($message)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity
         x-init="setTimeout(() => show = false, 4000)"
         class="toast toast--{{ $kind }}" role="status">
        <div class="flex items-start gap-3">
            <p class="text-ui flex-1">{{ $message }}</p>
            <button type="button" @click="show = false" class="btn-icon -mt-1 -mr-1" aria-label="Закрыть">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>
@endif
