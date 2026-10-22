{!! "Новое обращение с сайта (страница «Контакты»)" !!}

Имя: {!! $contact->name !!}
Почта: {!! $contact->email !!}
Телефон: {!! $contact->phone ?: 'не указан' !!}
Организация и должность: {!! $contact->organization ?: 'не указаны' !!}
Язык страницы: {!! strtoupper($contact->locale) !!}
Получено: {!! $contact->created_at->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('d.m.Y H:i') !!} (по Кишинёву)

Сообщение:
{!! $contact->message !!}

--
Ответить можно прямо на это письмо: оно придёт {!! $contact->email !!}.
Обращение сохранено и в админке: «Внешний сайт → Обращения».
