@props(['project', 'action', 'method'])

@php
    $langs = ['ru' => __('по-русски'), 'ro' => __('по-румынски'), 'en' => __('по-английски')];
@endphp

<form method="POST" action="{{ $action }}" class="form-column space-y-6">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <x-forms.error-summary />

    <label class="flex items-start gap-2 text-ui">
        <input type="checkbox" name="is_published" value="1"
               @checked(old('is_published', $project->exists ? $project->is_published : true))
               class="mt-0.5 rounded border-hairline text-accent focus:ring-accent-soft">
        <span>{{ __('Показывать на сайте') }}</span>
    </label>

    <x-admin.image-field name="image_path" aspect="project" :label="__('Картинка карточки (необязательно)')"
                         :value="$project->image_path ?? ''" />

    <x-forms.field name="url" type="url" :label="__('Ссылка на сайт проекта (необязательно)')"
                   :value="$project->url ?? ''"
                   placeholder="https://…"
                   :hint="__('Полный адрес сайта, где проект описан подробнее. Откроется в новой вкладке.')" />

    @foreach($langs as $code => $label)
        <section class="border-t border-hairline pt-5">
            <h3 class="form-section-title">{{ __('Карточка :language', ['language' => $label]) }}@if($code === 'ru')<span class="text-danger"> *</span>@endif</h3>
            <div class="space-y-4">
                <x-forms.field :label="__('Название :language', ['language' => $label])" name="translations[{{ $code }}][title]"
                               error="translations.{{ $code }}.title"
                               :value="$project->rawTranslation($code)?->title" :required="$code === 'ru'" />
                <x-forms.field :label="__('Категория :language', ['language' => $label])" name="translations[{{ $code }}][category]"
                               error="translations.{{ $code }}.category"
                               :value="$project->rawTranslation($code)?->category"
                               :hint="__('Короткий ярлык над названием («Языки», «События»).')" />
                <div class="space-y-1">
                    <label for="proj_text_{{ $code }}" class="field-label">{{ __('Описание :language', ['language' => $label]) }}</label>
                    <textarea id="proj_text_{{ $code }}" name="translations[{{ $code }}][text]" rows="4"
                              class="field-input @error('translations.'.$code.'.text') field-input--invalid @enderror">{{ old('translations.'.$code.'.text', $project->rawTranslation($code)?->text) }}</textarea>
                    @error('translations.'.$code.'.text')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>
    @endforeach

    <div class="flex gap-3">
        <button type="submit" class="btn-primary">{{ __('Сохранить') }}</button>
        <a href="{{ route('admin.projects.index') }}" class="btn-quiet">{{ __('Отмена') }}</a>
    </div>
</form>
