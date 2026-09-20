@props(['status'])

@php
    $value = $status instanceof \App\Enums\PublishStatus ? $status : \App\Enums\PublishStatus::from($status);
@endphp

@if($value === \App\Enums\PublishStatus::Published)
    <span class="badge badge--published">Опубликовано</span>
@else
    <span class="badge badge--draft">Черновик</span>
@endif
