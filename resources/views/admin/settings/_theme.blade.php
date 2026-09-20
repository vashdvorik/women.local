<form method="POST" action="{{ route('admin.settings.theme') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <p class="text-caption text-ink-muted">
        Оформление публичного сайта. Изменение применяется сразу и не затрагивает кабинет участницы:
        его тема выбирается отдельно, в разделе «Кабинеты участниц → Настройки кабинетов».
    </p>

    <fieldset class="space-y-2">
        <legend class="field-label mb-2">Тема публичного сайта</legend>
        @foreach($landingThemes as $key => $label)
            <label class="flex items-center gap-2 text-ui">
                <input type="radio" name="landing_theme" value="{{ $key }}" @checked(old('landing_theme', $landingTheme) === $key)
                       class="border-hairline text-accent focus:ring-accent-soft">
                <span>{{ $label }}</span>
            </label>
        @endforeach
        @error('landing_theme')<p class="field-error">{{ $message }}</p>@enderror
    </fieldset>

    <button type="submit" class="btn-primary">Сохранить</button>
</form>
