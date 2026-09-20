<form method="POST" action="{{ route('admin.settings.update') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <x-forms.field label="Максимальная длинная сторона, px" name="image_max_side" type="number"
                   :value="$maxSide" required
                   hint="От {{ \App\Support\ImageSettings::MAX_SIDE_MIN }} до {{ \App\Support\ImageSettings::MAX_SIDE_MAX }}. По умолчанию {{ \App\Support\ImageSettings::MAX_SIDE_DEFAULT }}." />

    <x-forms.field label="Качество WebP" name="image_quality" type="number"
                   :value="$quality" required
                   hint="От {{ \App\Support\ImageSettings::QUALITY_MIN }} до {{ \App\Support\ImageSettings::QUALITY_MAX }}. По умолчанию {{ \App\Support\ImageSettings::QUALITY_DEFAULT }}." />

    <p class="text-caption text-ink-muted">
        Настройки применяются только к новым загрузкам. Массового пересчёта уже
        загруженных изображений нет.
    </p>

    <button type="submit" class="btn-primary">Сохранить</button>
</form>
