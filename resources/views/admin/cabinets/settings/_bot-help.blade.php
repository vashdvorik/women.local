@php
    $locales = ['ru' => __('Русский'), 'en' => 'English', 'ro' => __('Română')];
@endphp

<form method="POST" action="{{ route('admin.cabinets.settings.bot-help') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <div class="space-y-2 text-ui text-ink-muted">
        <p>{{ __('Это всё, что знает «AI помощник» в Telegram-боте: он объясняет участнице, как пользоваться ботом и кабинетом, как войти, чем отличаются тарифы. Больше он ничего не придумывает: если ответа в тексте нет, отправляет её к команде проекта.') }}</p>
        <p>{{ __('Пишите факты коротко и точно. В тексте работают переменные: {site_url} — адрес сайта, {community_price} и {private_price} — цена подписки на год (берётся из раздела «Подписки», поэтому не устаревает), {contact} — Telegram команды проекта.') }}</p>
        <p>{{ __('Правка действует сразу и сохраняется при обновлении сайта. Чтобы вернуться к исходному тексту, нажмите «Вернуть исходный» под нужным языком.') }}</p>
    </div>

    @foreach($locales as $code => $label)
        <div class="space-y-1">
            <label for="bot_help_{{ $code }}" class="field-label">
                {{ __('Инструкция — :language', ['language' => $label]) }}
                @if(\App\Support\BotHelp::isCustomized($code))<span class="badge badge--new">{{ __('Изменено') }}</span>@endif
            </label>
            <textarea id="bot_help_{{ $code }}" name="texts[{{ $code }}]" rows="18" maxlength="{{ \App\Support\BotHelp::MAX_LENGTH }}"
                      class="field-input font-mono @error('texts.'.$code) field-input--invalid @enderror">{{ old('texts.'.$code, \App\Support\BotHelp::template($code)) }}</textarea>
            @error('texts.'.$code)<p class="field-error">{{ $message }}</p>@enderror
        </div>
    @endforeach

    <button type="submit" class="btn-primary">{{ __('Сохранить инструкцию') }}</button>
</form>

@foreach($locales as $code => $label)
    @if(\App\Support\BotHelp::isCustomized($code))
        <form method="POST" action="{{ route('admin.cabinets.settings.bot-help.reset') }}" class="mt-3"
              onsubmit="return confirm(@js(__('Вернуть исходный текст инструкции и потерять свои правки?')))">
            @csrf
            <input type="hidden" name="locale" value="{{ $code }}">
            <button type="submit" class="btn-quiet">{{ __('Вернуть исходный: :language', ['language' => $label]) }}</button>
        </form>
    @endif
@endforeach
