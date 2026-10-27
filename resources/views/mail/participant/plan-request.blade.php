@php($user = $planRequest->botUser)
{!! "Новая заявка на тариф {$planRequest->planEnum()->title('ru')} ({$planRequest->planEnum()->priceLabel('ru')} в год)" !!}

Имя: {!! $user?->full_name ?: 'не указано' !!}
Телефон: {!! $user?->phone ?: 'не указан' !!}
Почта: {!! $user?->email ?: 'не указана' !!}
Telegram: {!! $user?->telegram_username ? '@'.$user->telegram_username : 'не подключён' !!}
@if($planRequest->comment)

Комментарий участницы:
{!! $planRequest->comment !!}
@endif

Получено: {!! $planRequest->created_at->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('d.m.Y H:i') !!} (по Кишинёву)

Что делать: позвонить по номеру выше, а затем открыть участнице покупку тарифа в админке:
{!! route('admin.plan-requests.index') !!}
