@props(['status'])

@switch($status)
    @case(\App\Models\BotUser::STATUS_APPROVED)
        <span class="badge badge--published">Одобрена</span>
        @break
    @case(\App\Models\BotUser::STATUS_REJECTED)
        <span class="badge badge--rejected">Отклонена</span>
        @break
    @default
        <span class="badge badge--pending">Ожидает</span>
@endswitch
