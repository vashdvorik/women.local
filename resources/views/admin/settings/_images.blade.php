<form method="POST" action="{{ route('admin.settings.update') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <x-forms.field :label="__('Максимальная длинная сторона, px')" name="image_max_side" type="number"
                   :value="$maxSide" required
                   :hint="__('От :min до :max. По умолчанию :default.', ['min' => \App\Support\ImageSettings::MAX_SIDE_MIN, 'max' => \App\Support\ImageSettings::MAX_SIDE_MAX, 'default' => \App\Support\ImageSettings::MAX_SIDE_DEFAULT])" />

    <x-forms.field :label="__('Качество WebP')" name="image_quality" type="number"
                   :value="$quality" required
                   :hint="__('От :min до :max. По умолчанию :default.', ['min' => \App\Support\ImageSettings::QUALITY_MIN, 'max' => \App\Support\ImageSettings::QUALITY_MAX, 'default' => \App\Support\ImageSettings::QUALITY_DEFAULT])" />

    <p class="text-caption text-ink-muted">
        {{ __('Настройки применяются только к новым загрузкам. Массового пересчёта уже загруженных изображений нет.') }}
    </p>

    <button type="submit" class="btn-primary">{{ __('Сохранить') }}</button>
</form>
