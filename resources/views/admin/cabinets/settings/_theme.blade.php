<form method="POST" action="{{ route('admin.cabinets.settings.theme') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <p class="text-caption text-ink-muted">
        Оформление личного кабинета участницы (/app/account). Изменение применяется сразу и не затрагивает
        публичный сайт: его тема выбирается отдельно, в разделе «Внешний сайт → Настройки сайта».
    </p>

    <fieldset class="space-y-2">
        <legend class="field-label mb-2">Тема кабинета участницы</legend>
        @foreach($accountThemes as $key => $label)
            <label class="flex items-center gap-2 text-ui">
                <input type="radio" name="account_theme" value="{{ $key }}" @checked(old('account_theme', $accountTheme) === $key)
                       class="border-hairline text-accent focus:ring-accent-soft">
                <span>{{ $label }}</span>
            </label>
        @endforeach
        @error('account_theme')<p class="field-error">{{ $message }}</p>@enderror
    </fieldset>

    <button type="submit" class="btn-primary">Сохранить</button>
</form>
