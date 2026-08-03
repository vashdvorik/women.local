<x-filament-panels::page>
    <style>
        .ai-settings { display: grid; gap: 18px; max-width: 1040px; }
        .ai-settings__intro, .ai-settings__card, .ai-settings__actions { border: 1px solid #e5e7eb; border-radius: 16px; background: #fff; box-shadow: 0 4px 14px rgba(17,24,39,.04); }
        .ai-settings__intro { padding: 22px 24px; background: linear-gradient(135deg, #fff 0%, #f7f4ff 100%); }
        .ai-settings__intro h2 { margin: 0; color: #1f1645; font-size: 21px; font-weight: 700; letter-spacing: -.02em; }
        .ai-settings__intro p { max-width: 720px; margin: 6px 0 0; color: #64748b; font-size: 14px; line-height: 1.5; }
        .ai-settings__card { padding: 22px 24px; }
        .ai-settings__card-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .ai-settings__card-head h3 { margin: 0; color: #1f2937; font-size: 16px; font-weight: 700; }
        .ai-settings__card-head p { margin: 0; color: #94a3b8; font-size: 12px; }
        .ai-settings__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .ai-settings__grid--advanced { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ai-settings__field { display: grid; gap: 7px; color: #334155; font-size: 13px; font-weight: 650; }
        .ai-settings__field--full { grid-column: 1 / -1; }
        .ai-settings__field input, .ai-settings__field select { box-sizing: border-box; width: 100%; min-height: 42px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 11px; background: #fff; color: #111827; font: inherit; font-weight: 400; outline: none; }
        .ai-settings__field input:focus, .ai-settings__field select:focus { border-color: #6d3ca4; box-shadow: 0 0 0 3px rgba(109,60,164,.12); }
        .ai-settings__hint { color: #94a3b8; font-size: 12px; font-weight: 400; line-height: 1.4; }
        .ai-settings__error { color: #dc2626; font-size: 12px; font-weight: 500; }
        .ai-settings__switch { display: flex; align-items: center; gap: 10px; min-height: 42px; padding: 0 12px; border-radius: 10px; background: #f8fafc; color: #334155; font-size: 13px; font-weight: 600; }
        .ai-settings__switch input { width: 16px; height: 16px; accent-color: #6d3ca4; }
        .ai-settings__actions { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 16px 18px; }
        .ai-settings__actions p { max-width: 590px; margin: 0; color: #64748b; font-size: 12px; line-height: 1.45; }
        .ai-settings__buttons { display: flex; flex-wrap: wrap; gap: 10px; }
        @media (max-width: 767px) {
            .ai-settings__intro, .ai-settings__card { padding: 18px; }
            .ai-settings__grid, .ai-settings__grid--advanced { grid-template-columns: 1fr; }
            .ai-settings__field--full { grid-column: auto; }
            .ai-settings__actions { align-items: stretch; flex-direction: column; }
            .ai-settings__buttons > * { flex: 1 1 auto; }
        }
    </style>

    <form wire:submit="save" class="ai-settings">
        <section class="ai-settings__intro">
            <h2>Подключение AI</h2>
            <p>Выберите провайдера, укажите модель и ключ. Первое подключение настроено для DeepSeek V4 Flash, но ниже можно указать любой OpenAI-совместимый сервис.</p>
        </section>

        <section class="ai-settings__card">
            <div class="ai-settings__card-head">
                <h3>Провайдер и доступ</h3>
                <p>Ключ хранится зашифрованно</p>
            </div>
            <div class="ai-settings__grid">
                <label class="ai-settings__field">
                    Провайдер
                    <select wire:model="provider">
                        <option value="deepseek">DeepSeek</option>
                        <option value="openai-compatible">Другой OpenAI-совместимый API</option>
                    </select>
                    @error('provider') <span class="ai-settings__error">{{ $message }}</span> @enderror
                </label>

                <label class="ai-settings__field">
                    Модель
                    <input wire:model="model" type="text" placeholder="deepseek-v4-flash">
                    @error('model') <span class="ai-settings__error">{{ $message }}</span> @enderror
                </label>

                <label class="ai-settings__field ai-settings__field--full">
                    Базовый URL API
                    <input wire:model="baseUrl" type="url" placeholder="https://api.deepseek.com">
                    <span class="ai-settings__hint">К адресу автоматически добавляется <code>/chat/completions</code>, если он ещё не указан.</span>
                    @error('baseUrl') <span class="ai-settings__error">{{ $message }}</span> @enderror
                </label>

                <label class="ai-settings__field ai-settings__field--full">
                    API-ключ
                    <input wire:model="apiKey" type="password" autocomplete="new-password" placeholder="{{ $apiKeyConfigured ? 'Ключ сохранён. Введите значение только для замены.' : 'Введите API-ключ' }}">
                    <span class="ai-settings__hint">{{ $apiKeyConfigured ? 'Сохранённый ключ скрыт. Пустое поле не удалит его.' : 'После сохранения ключ будет зашифрован.' }}</span>
                </label>
            </div>
        </section>

        <section class="ai-settings__card">
            <div class="ai-settings__card-head">
                <h3>Параметры ответа</h3>
                <p>Можно менять без изменения кода</p>
            </div>
            <div class="ai-settings__grid ai-settings__grid--advanced">
                <label class="ai-settings__field">
                    Temperature
                    <input wire:model="temperature" type="number" min="0" max="2" step="0.1">
                    @error('temperature') <span class="ai-settings__error">{{ $message }}</span> @enderror
                </label>

                <label class="ai-settings__field">
                    Максимум токенов
                    <input wire:model="maxTokens" type="number" min="256" max="32768" step="1">
                    @error('maxTokens') <span class="ai-settings__error">{{ $message }}</span> @enderror
                </label>

                <label class="ai-settings__field">
                    Тайм-аут, секунд
                    <input wire:model="timeout" type="number" min="5" max="120" step="1">
                    @error('timeout') <span class="ai-settings__error">{{ $message }}</span> @enderror
                </label>

                <label class="ai-settings__switch ai-settings__field--full">
                    <input wire:model="enabled" type="checkbox">
                    Использовать AI на платформе
                </label>
            </div>
        </section>

        <section class="ai-settings__actions">
            <p>Проверка отправит короткий тестовый запрос. Ключ не будет показан в интерфейсе или уведомлениях.</p>
            <div class="ai-settings__buttons">
                <x-filament::button type="button" color="gray" wire:click="testConnection" wire:loading.attr="disabled">Проверить соединение</x-filament::button>
                <x-filament::button type="submit">Сохранить</x-filament::button>
            </div>
        </section>
    </form>
</x-filament-panels::page>
