@props(['event', 'editor', 'action', 'method'])

@php
    $langs = ['ru' => 'по-русски', 'ro' => 'по-румынски', 'en' => 'по-английски'];
@endphp

<form method="POST" action="{{ $action }}" class="form-column space-y-6" x-data="eventEditor(@js($editor))">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    @foreach(array_keys($langs) as $l)
        <input type="hidden" name="translations[{{ $l }}][content]" :value="serialized.{{ $l }}">
    @endforeach

    <x-forms.error-summary />

    <label class="flex items-start gap-2 text-ui">
        <input type="checkbox" name="is_published" value="1"
               @checked(old('is_published', $event->exists ? $event->is_published : true))
               class="mt-0.5 rounded border-hairline text-accent focus:ring-accent-soft">
        <span>Показывать на сайте</span>
    </label>

    <x-admin.image-field name="image_path" aspect="event" label="Обложка" :value="$event->image_path ?? ''" />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-forms.field label="Дата начала" name="starts_at" type="date"
                       :value="$event->starts_at?->format('Y-m-d')"
                       hint="Показывается на карточке, если ниже не задана своя подпись вместо даты." />

        <x-forms.field label="Цвет подложки карточки" name="tone">
            <select id="tone" name="tone" class="field-input">
                @foreach(\App\Support\CardTone::OPTIONS as $value => $label)
                    <option value="{{ $value }}" @selected(old('tone', $event->tone) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-forms.field>
    </div>

    <x-forms.field name="url" type="url" label="Ссылка на сайт организации"
                   :value="$event->url ?? ''" placeholder="https://…"
                   hint="Необязательно. Если у новости нет своего текста, «Подробнее» ведёт сюда (в новой вкладке); если текст есть, ссылка показывается в конце страницы." />

    <x-forms.field name="slug" label="Адрес страницы новости"
                   :value="$event->slug ?? ''" placeholder="belyj-shum-vstrecha-kreativa"
                   hint="Нижний регистр, дефисы вместо пробелов. Формируется из русского названия, если не заполнить." />

    @if($event->exists && filled($event->slug))
        <p class="field-hint">
            Страница на сайте:
            <a href="{{ route('events.show', ['event' => $event->slug]) }}" target="_blank" rel="noopener" class="table-link">/events/{{ $event->slug }}</a>
            @unless($event->hasBody()) (карточка ведёт на неё, когда появится текст) @endunless
        </p>
    @endif

    @foreach($langs as $code => $label)
        <section class="border-t border-hairline pt-5">
            <h3 class="form-section-title">Карточка {{ $label }}@if($code === 'ru')<span class="text-danger"> *</span>@endif</h3>
            <div class="space-y-4">
                <x-forms.field :label="'Название '.$label" name="translations[{{ $code }}][title]"
                               error="translations.{{ $code }}.title"
                               :value="$event->rawTranslation($code)?->title" :required="$code === 'ru'" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-forms.field :label="'Тип '.$label" name="translations[{{ $code }}][type]"
                                   error="translations.{{ $code }}.type"
                                   :value="$event->rawTranslation($code)?->type"
                                   hint="Плашка на обложке: «Обучение», «Нетворкинг»." />
                    <x-forms.field :label="'Подпись вместо даты '.$label" name="translations[{{ $code }}][date_label]"
                                   error="translations.{{ $code }}.date_label"
                                   :value="$event->rawTranslation($code)?->date_label"
                                   hint="Необязательно: «Открыт набор». Если пусто, покажется дата начала." />
                </div>
                <div class="space-y-1">
                    <label for="ev_description_{{ $code }}" class="field-label">Описание {{ $label }}</label>
                    <textarea id="ev_description_{{ $code }}" name="translations[{{ $code }}][description]" rows="4"
                              class="field-input @error('translations.'.$code.'.description') field-input--invalid @enderror">{{ old('translations.'.$code.'.description', $event->rawTranslation($code)?->description) }}</textarea>
                    @error('translations.'.$code.'.description')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2 pt-2">
                    <p class="field-label">Текст страницы новости {{ $label }}</p>
                    @if($code === 'ru')
                        <p class="field-hint">Добавьте текст и фото, и у новости появится своя страница на сайте. Пока блоков нет, «Подробнее» ведёт по ссылке выше.</p>
                    @else
                        <p class="field-hint">Блоки и фото общие для всех языков, переводится только текст. Если оставить пусто, покажется русский.</p>
                    @endif
                    <x-admin.block-editor :locale="$code" />
                </div>
            </div>
        </section>
    @endforeach

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Сохранить</button>
        <a href="{{ route('admin.events.index') }}" class="btn-quiet">Отмена</a>
    </div>
</form>
