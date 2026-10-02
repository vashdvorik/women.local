@props(['status'])

@switch($status)
    @case(\App\Models\BotUser::STATUS_APPROVED)
        <span class="badge badge--published">{{ __('Одобрена') }}</span>
        @break
    @case(\App\Models\BotUser::STATUS_REJECTED)
        <span class="badge badge--rejected">{{ __('Отклонена') }}</span>
        @break
    @default
        <span class="badge badge--pending">{{ __('Ожидает') }}</span>
@endswitch
