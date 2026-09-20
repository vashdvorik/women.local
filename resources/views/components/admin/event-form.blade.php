@props(['event', 'action', 'method'])

@php
    $langs = ['ru' => 'по-русски', 'ro' => 'по-румынски', 'en' => 'по-английски'];
@endphp

<form method="POST" action="{{ $action }}" class="form-column space-y-6">
    @csrf
    @if($method !== 'POST') @method($method) @endif

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

    <x-forms.field name="url" type="url" label="Ссылка «Подробнее»"
                   :value="$event->url ?? ''" placeholder="https://…"
                   hint="Полный адрес страницы новости. Откроется в новой вкладке." />

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
            </div>
        </section>
    @endforeach

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">Сохранить</button>
        <a href="{{ route('admin.events.index') }}" class="btn-quiet">Отмена</a>
    </div>
</form>
