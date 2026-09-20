<form method="POST" action="{{ route('admin.cabinets.settings.knowledge') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <div class="space-y-2 text-ui text-ink-muted">
        <p>
            Помощник получает этот текст вместе со свежими данными каталога участниц и возможностей при каждом
            вопросе. История чата не сохраняется.
        </p>
        <p>
            Контакты участниц (Telegram, телефоны, email, адреса и ссылки для связи) в ИИ не отправляются.
            Для статичных страниц сайта, например «О нас», информацию нужно поддерживать здесь вручную.
        </p>
    </div>

    <div class="space-y-1">
        <label for="knowledge_rules" class="field-label">Общие правила для помощника</label>
        <textarea id="knowledge_rules" name="rules" rows="6"
                  placeholder="Например: всегда отвечай доброжелательно, не придумывай факты, предложи следующий полезный шаг."
                  class="field-input @error('rules') field-input--invalid @enderror">{{ old('rules', $knowledge['rules']) }}</textarea>
        @error('rules')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    @foreach(['ru' => 'Русский', 'en' => 'English', 'ro' => 'Română'] as $locale => $label)
        <div class="space-y-1">
            <label for="knowledge_{{ $locale }}" class="field-label">Информация о платформе — {{ $label }}</label>
            <textarea id="knowledge_{{ $locale }}" name="{{ $locale }}" rows="10"
                      placeholder="Добавьте проверенную информацию о платформе, разделах сайта, команде, правилах и программах."
                      class="field-input @error($locale) field-input--invalid @enderror">{{ old($locale, $knowledge[$locale]) }}</textarea>
            @error($locale)<p class="field-error">{{ $message }}</p>@enderror
        </div>
    @endforeach

    <button type="submit" class="btn-primary">Сохранить базу знаний</button>
</form>
