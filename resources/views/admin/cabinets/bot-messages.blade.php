{{-- «Сообщения бота» — лагерь «Кабинеты участниц». Эталон текстов — resources/data/bot_messages.php; здесь администратор
     правит их по языкам, правки лежат в базе поверх эталона. Логика — App\Support\BotMessages. --}}
<x-layouts.admin title="Сообщения бота">
    <div class="form-column space-y-6">

        <div class="card space-y-3 text-ui text-ink-muted">
            <p>
                Здесь все тексты, которые бот и сайт отправляют участницам в Telegram: анкета, статус заявки, решение,
                вход в кабинет, поиск, рассылка о публикациях, подписи кнопок и описания команд
                ({{ $total }} сообщений). Каждая участница получает их на языке своего Telegram: русский, English или
                Română, по умолчанию русский. Язык выбирается вкладкой ниже.
            </p>
            <p>
                <span class="text-ink font-semibold">Форматирование:</span>
                <code>&lt;b&gt;жирный&lt;/b&gt;</code>,
                <code>&lt;i&gt;курсив&lt;/i&gt;</code>,
                <code>&lt;a href="https://…"&gt;ссылка&lt;/a&gt;</code>.
                Каждый тег нужно закрыть; если что-то не так, текст не сохранится и покажет, что исправить.
                Слова в фигурных скобках, например <code>{name}</code>, бот заменяет сам (имя, ссылку и т. п.): нажмите
                на такую подсказку под полем, чтобы вставить её в место курсора.
            </p>
            <p>
                Очистите поле или нажмите «Вернуть исходный», чтобы вернуть первоначальный текст. Изменения действуют
                сразу, выкладывать код не нужно.
            </p>
        </div>

        <div class="tabs overflow-x-auto no-scrollbar">
            @foreach($localeLabels as $code => $label)
                <a href="{{ route('admin.cabinets.bot-messages', ['lang' => $code]) }}"
                   class="tab whitespace-nowrap @if($locale === $code) tab--active @endif">{{ $label }}</a>
            @endforeach
        </div>

        <nav class="flex flex-wrap gap-x-4 gap-y-1 text-ui" aria-label="Разделы">
            @foreach($groups as $id => $group)
                <a href="#group-{{ $id }}" class="text-accent hover:underline">{{ $group['title'] }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.cabinets.bot-messages.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="lang" value="{{ $locale }}">

            <x-forms.error-summary title="Ничего не сохранено: исправьте отмеченные сообщения." />

            @foreach($groups as $id => $group)
                <section class="card space-y-6" id="group-{{ $id }}">
                    <header class="space-y-1">
                        <h2 class="text-section font-semibold text-ink">{{ $group['title'] }}</h2>
                        <p class="text-caption text-ink-muted">{{ $group['hint'] }}</p>
                    </header>

                    @foreach($group['messages'] as $key => $item)
                        @php
                            $field = 'messages.'.$key;
                            $value = old($field, $item['current']);
                            $isText = $item['kind'] === 'text';
                        @endphp

                        <div class="space-y-1.5 border-t border-hairline pt-5 first:border-t-0 first:pt-0"
                             x-data="botMessage(@js($value), @js($item['default']))">
                            <div class="flex flex-wrap items-center gap-2">
                                <label for="msg-{{ $key }}" class="field-label">{{ $item['title'] }}</label>
                                <span x-show="changed" x-cloak class="badge badge--new">Изменено</span>
                                @if(! empty($item['inactive']))<span class="badge badge--draft">Не используется</span>@endif
                            </div>
                            <p class="field-hint">{{ $item['when'] }}</p>
                            @if(! empty($item['inactive']))<p class="field-hint">{{ $item['inactive'] }}</p>@endif

                            @if($isText)
                                <textarea id="msg-{{ $key }}" name="messages[{{ $key }}]" rows="{{ $item['rows'] }}"
                                          x-ref="field" x-model="text" spellcheck="true"
                                          class="field-input @error($field) field-input--invalid @enderror">{{ $value }}</textarea>
                            @else
                                <input type="text" id="msg-{{ $key }}" name="messages[{{ $key }}]" value="{{ $value }}"
                                       x-ref="field" x-model="text" maxlength="{{ $item['kind'] === 'command' ? \App\Support\BotMessages::MAX_COMMAND : \App\Support\BotMessages::MAX_BUTTON }}"
                                       class="field-input @error($field) field-input--invalid @enderror">
                            @endif

                            @error($field)<p class="field-error">{{ $message }}</p>@enderror

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                @if($item['vars'] !== [])
                                    <div class="flex flex-wrap items-center gap-1.5 text-caption">
                                        <span class="text-ink-muted">Переменные:</span>
                                        @foreach($item['vars'] as $var => $hint)
                                            <button type="button" @click="insert(@js('{'.$var.'}'))" title="Вставить в текст"
                                                    class="btn-quiet !px-2 !py-0.5 font-mono">{{ '{'.$var.'}' }}</button>
                                            <span class="text-ink-muted">{{ $hint }}@if(in_array($var, $item['required'] ?? [], true)), обязательна @endif</span>
                                        @endforeach
                                    </div>
                                @endif

                                <button type="button" x-show="changed" x-cloak @click="restore()" class="btn-quiet text-caption">Вернуть исходный</button>
                            </div>

                            <details x-show="changed" x-cloak class="text-caption text-ink-muted">
                                <summary class="cursor-pointer">Исходный текст</summary>
                                <pre class="mt-1 whitespace-pre-wrap font-sans">{{ $item['default'] }}</pre>
                            </details>
                        </div>
                    @endforeach
                </section>
            @endforeach

            <div class="sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-3 border-t border-hairline bg-surface px-4 py-3 rounded-md">
                <p class="text-caption text-ink-muted">Сохраняются сообщения на языке «{{ $localeLabels[$locale] }}». Другие языки не затрагиваются.</p>
                <button type="submit" class="btn-primary">Сохранить ({{ $localeLabels[$locale] }})</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
