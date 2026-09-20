<x-layouts.admin :title="'Изменить: '.($profile->full_name ?: 'профиль')">
    <div class="form-column">
        <form method="POST" action="{{ route('admin.profiles.update', $profile) }}" class="card space-y-6">
            @csrf
            @method('PUT')

            <x-forms.error-summary />

            <x-forms.field label="Имя и фамилия" name="full_name" :value="$profile->full_name" required />

            <div class="space-y-1">
                <label for="description" class="field-label">Что представляет</label>
                <textarea id="description" name="description" rows="4"
                          class="field-input @error('description') field-input--invalid @enderror">{{ old('description', $profile->description) }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1">
                <label for="expectation" class="field-label">Что ищет и чем может быть полезна</label>
                <textarea id="expectation" name="expectation" rows="4"
                          class="field-input @error('expectation') field-input--invalid @enderror">{{ old('expectation', $profile->expectation) }}</textarea>
                @error('expectation')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <p class="text-caption text-ink-muted">
                Статус меняется кнопками «Одобрить» и «Отклонить» на странице профиля: они же отправляют
                участнице уведомление в боте.
            </p>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">Сохранить</button>
                <a href="{{ route('admin.profiles.show', $profile) }}" class="btn-quiet">Отмена</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
