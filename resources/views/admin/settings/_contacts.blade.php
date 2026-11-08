<form method="POST" action="{{ route('admin.settings.contacts') }}" class="card space-y-6">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <p class="text-caption text-ink-muted">
        {{ __('Сюда приходят письма из формы «Связаться с нами» на странице «Контакты». Этот же адрес показан посетителям на странице «Контакты» и внизу сайта.') }}
    </p>

    <x-forms.field :label="__('Почта команды')" name="contact_email" type="email"
                   :value="old('contact_email', $contactEmail)" required maxlength="191"
                   :hint="__('Например: team@example.com. Обращения из формы в любом случае сохраняются в разделе «Обращения».')" />

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-primary">{{ __('Сохранить') }}</button>
    </div>
</form>

<form method="POST" action="{{ route('admin.settings.contacts.test') }}" class="card space-y-4 mt-6">
    @csrf

    <p class="text-caption text-ink-muted">
        {{ __('Проверка отправки: на сохранённый адрес уйдёт короткое тестовое письмо. Если оно не придёт, смотрите «Отслеживание доставки» на хостинге и папку «Спам».') }}
    </p>

    <button type="submit" class="btn-secondary">{{ __('Отправить проверочное письмо') }}</button>
</form>
