@props(['expert', 'action', 'method'])

@php
    $langs = ['ru' => __('по-русски'), 'ro' => __('по-румынски'), 'en' => __('по-английски')];
    $tags = fn (string $code) => old(
        'translations.'.$code.'.tags',
        implode(', ', $expert->rawTranslation($code)?->tags ?? [])
    );
@endphp

<form method="POST" action="{{ $action }}" class="form-column space-y-6">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <x-forms.error-summary />

    <label class="flex items-start gap-2 text-ui">
        <input type="checkbox" name="is_published" value="1"
               @checked(old('is_published', $expert->exists ? $expert->is_published : true))
               class="mt-0.5 rounded border-hairline text-accent focus:ring-accent-soft">
        <span>{{ __('Показывать на сайте') }}</span>
    </label>

    <div class="grid gap-6 sm:grid-cols-[auto_1fr]">
        <x-admin.image-field name="photo_path" aspect="expert" :label="__('Портрет')" width="w-56"
                             :value="$expert->photo_path ?? ''" />

        <x-forms.field :label="__('Цвет подложки карточки')" name="tone">
            <select id="tone" name="tone" class="field-input max-w-xs">
                @foreach(\App\Support\CardTone::OPTIONS as $value => $label)
                    <option value="{{ $value }}" @selected(old('tone', $expert->tone) === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
        </x-forms.field>
    </div>

    @foreach($langs as $code => $label)
        <section class="border-t border-hairline pt-5">
            <h3 class="form-section-title">{{ __('Карточка :language', ['language' => $label]) }}@if($code === 'ru')<span class="text-danger"> *</span>@endif</h3>
            <div class="space-y-4">
                <x-forms.field :label="__('Имя :language', ['language' => $label])" name="translations[{{ $code }}][name]"
                               error="translations.{{ $code }}.name"
                               :value="$expert->rawTranslation($code)?->name" :required="$code === 'ru'" />
                <x-forms.field :label="__('Должность :language', ['language' => $label])" name="translations[{{ $code }}][role]"
                               error="translations.{{ $code }}.role"
                               :value="$expert->rawTranslation($code)?->role"
                               :hint="__('Показывается под именем на карточке.')" />
                <x-forms.field :label="__('Специализация :language', ['language' => $label])" name="translations[{{ $code }}][specialization]"
                               error="translations.{{ $code }}.specialization"
                               :value="$expert->rawTranslation($code)?->specialization" />

                @foreach(['description' => __('Описание'), 'looking_for' => __('Что ищет'), 'can_offer' => __('Чем может быть полезна')] as $field => $fieldLabel)
                    <div class="space-y-1">
                        <label for="exp_{{ $field }}_{{ $code }}" class="field-label">{{ $fieldLabel }} {{ $label }}</label>
                        <textarea id="exp_{{ $field }}_{{ $code }}" name="translations[{{ $code }}][{{ $field }}]" rows="3"
                                  class="field-input @error('translations.'.$code.'.'.$field) field-input--invalid @enderror">{{ old('translations.'.$code.'.'.$field, $expert->rawTranslation($code)?->{$field}) }}</textarea>
                        @error('translations.'.$code.'.'.$field)<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach

                <x-forms.field :label="__('Теги :language', ['language' => $label])" name="translations[{{ $code }}][tags]"
                               error="translations.{{ $code }}.tags"
                               :value="$tags($code)"
                               :hint="__('Короткие ярлыки через запятую: «Эксперт, Партнёрства».')" />
            </div>
        </section>
    @endforeach

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">{{ __('Сохранить') }}</button>
        <a href="{{ route('admin.experts.index') }}" class="btn-quiet">{{ __('Отмена') }}</a>
    </div>
</form>
