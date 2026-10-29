{{--
    Сообщения над содержимым кабинета: «Сохранено» и ошибка (о проверке анкеты сказано мягко на странице «Мой профиль»). Обычно их выводит каркас страницы во всю
    ширину; страницы с центрированным содержимым (@section('notices_inline')) подключают этот блок сами, внутри своей
    колонки, чтобы сообщение стояло ровно над ней, а не растягивалось шире.
--}}
@foreach(['success' => 'emerald', 'error' => 'red'] as $key => $color)
    @if(session($key))
        <div class="account-flash account-flash--{{ $key === 'success' ? 'success' : 'error' }} mb-6 rounded-xl border border-{{ $color }}-200 bg-{{ $color }}-50 px-4 py-3 text-sm text-{{ $color }}-800">{{ session($key) }}</div>
    @endif
@endforeach
