@props(['locale'])

{{-- Подсказка «Статус перевода» перед содержимым (AGENTS.md §13). --}}
<div class="rounded-sm p-3 text-caption"
     :class="{
        'bg-success-soft text-success': badges.{{ $locale }} === 'done',
        'bg-warning-soft text-warning': badges.{{ $locale }} === 'partial',
        'bg-neutral-soft text-ink-muted': badges.{{ $locale }} === 'empty',
     }">
    <span x-show="badges.{{ $locale }} === 'empty'">
        Языковая версия не заполнена. На сайте вместо неё будет показан русский текст.
    </span>
    <span x-show="badges.{{ $locale }} === 'partial'">
        Заполнена часть полей или блоков. Незаполненные места на сайте покажут русский текст.
    </span>
    <span x-show="badges.{{ $locale }} === 'done'">
        Языковая версия заполнена.
    </span>
</div>
