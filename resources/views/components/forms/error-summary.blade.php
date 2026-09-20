@props(['title' => 'Исправьте ошибки в форме.'])

@if($errors->any())
    <div class="error-summary" role="alert">
        <p class="error-summary__title">{{ $title }}</p>
        <ul class="mt-2 space-y-1 text-ui list-disc list-inside">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
