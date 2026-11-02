<form method="POST" action="{{ route('admin.cabinets.settings.manager') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <div class="space-y-2 text-ui text-ink-muted">
        <p>{{ __('AI-менеджер в кабинете рассказывает участницам, как устроена платформа и их тариф, и помогает освоиться. Он отвечает только по платформе и тарифу, ничего не меняет и не принимает платежи.') }}</p>
        <p>{{ __('Цены, состав тарифов, срок подписки участницы и открытые ей разделы менеджер берёт из данных платформы сам, поэтому писать их здесь не нужно. Здесь только то, что в коде не хранится: описание платформы, правила, ответы на частые вопросы. Пока поле пустое, действует стартовый текст; очистите поле и сохраните, чтобы вернуть его. В тексте работает {site_url} — адрес сайта.') }}</p>
        <p>{{ __('Вопросы, на которые менеджер не нашёл ответа, попадают в раздел «Вопросы AI-менеджеру»: по ним удобно дописывать знания. Модель и ключ те же, что у «Агента» на вкладке «AI-провайдеры».') }}</p>
    </div>

    <div class="space-y-1">
        <label for="manager_rules" class="field-label">{{ __('Общие правила для менеджера') }}</label>
        <textarea id="manager_rules" name="rules" rows="5"
                  class="field-input @error('rules') field-input--invalid @enderror">{{ old('rules', $managerKnowledge['rules']) }}</textarea>
        @error('rules')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    @foreach(['ru' => __('Русский'), 'en' => 'English', 'ro' => __('Română')] as $locale => $label)
        <div class="space-y-1">
            <label for="manager_{{ $locale }}" class="field-label">{{ __('Знания о платформе — :language', ['language' => $label]) }}</label>
            <textarea id="manager_{{ $locale }}" name="{{ $locale }}" rows="14"
                      class="field-input @error($locale) field-input--invalid @enderror">{{ old($locale, $managerKnowledge[$locale]) }}</textarea>
            @error($locale)<p class="field-error">{{ $message }}</p>@enderror
        </div>
    @endforeach

    <button type="submit" class="btn-primary">{{ __('Сохранить знания менеджера') }}</button>
</form>
