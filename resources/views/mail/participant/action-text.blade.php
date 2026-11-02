{{-- Текстовая часть письма участнице (рядом с HTML): письмо только из HTML почтовые службы чаще считают подозрительным. --}}
{{ __('participant.mail.greeting', [], $locale) }}

{{ $line }}

{{ $button }}: {{ $url }}

{{ $note }}

--
Women’s Hub
{{ __('participant.mail.footer', [], $locale) }}
