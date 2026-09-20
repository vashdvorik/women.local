<x-guest-layout>
    <h1 class="text-section font-semibold mb-6">Вход в панель</h1>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div class="space-y-1">
            <label for="email" class="field-label">Почта</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="field-input @error('email') field-input--invalid @enderror">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-1">
            <label for="password" class="field-label">Пароль</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="field-input @error('password') field-input--invalid @enderror">
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-ui text-ink-muted">
            <input type="checkbox" name="remember" class="rounded border-hairline text-accent focus:ring-accent-soft">
            Запомнить меня
        </label>

        <button type="submit" class="btn-primary w-full">Войти</button>
    </form>
</x-guest-layout>
