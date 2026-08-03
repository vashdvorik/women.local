<x-filament-panels::page>
    <style>
        .ai-providers { display:grid; max-width:1120px; gap:20px; }
        .ai-providers__intro,.ai-provider-card { border:1px solid #e5e7eb; border-radius:18px; background:#fff; box-shadow:0 6px 20px rgba(15,23,42,.05); }
        .ai-providers__intro { padding:24px; background:linear-gradient(135deg,#fff,#f8f7ff); }
        .ai-providers__intro h2,.ai-provider-card h3 { margin:0; color:#18212f; font-weight:750; }
        .ai-providers__intro h2 { font-size:22px; } .ai-providers__intro p { max-width:850px; margin:8px 0 0; color:#64748b; font-size:14px; line-height:1.55; }
        .ai-provider-card { overflow:hidden; } .ai-provider-card__head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:22px 24px 18px; border-bottom:1px solid #edf0f4; }
        .ai-provider-card__head h3 { font-size:17px; } .ai-provider-card__head p,.ai-provider-card__footer p { margin:5px 0 0; color:#64748b; font-size:13px; line-height:1.45; }
        .ai-provider-card__badge { flex:0 0 auto; padding:6px 10px; border-radius:999px; background:#f0edff; color:#5b31a3; font-size:12px; font-weight:700; }
        .ai-provider-card__body { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; padding:22px 24px; } .ai-provider-card__body--two { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .ai-provider-field { display:grid; gap:7px; color:#334155; font-size:13px; font-weight:650; } .ai-provider-field--full { grid-column:1 / -1; }
        .ai-provider-field input,.ai-provider-field select { box-sizing:border-box; width:100%; min-height:42px; border:1px solid #cbd5e1; border-radius:10px; padding:9px 11px; background:#fff; color:#111827; font:inherit; font-weight:400; outline:none; }
        .ai-provider-field input:focus,.ai-provider-field select:focus { border-color:#6d3ca4; box-shadow:0 0 0 3px rgba(109,60,164,.12); }
        .ai-provider-hint { color:#8491a3; font-size:12px; font-weight:400; line-height:1.4; } .ai-provider-error { color:#dc2626; font-size:12px; font-weight:500; }
        .ai-provider-card__footer { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 24px; border-top:1px solid #edf0f4; background:#fafbfc; } .ai-provider-card__footer p { margin:0; }
        .ai-notice { margin:0 24px 22px; padding:13px 15px; border-radius:12px; background:#f7f5ff; color:#544476; font-size:13px; line-height:1.5; }
        .ai-notice strong { color:#392263; } .ai-providers__actions { display:flex; justify-content:flex-end; }
        @media (max-width:800px) { .ai-provider-card__body,.ai-provider-card__body--two { grid-template-columns:1fr; } .ai-provider-field--full { grid-column:auto; } .ai-provider-card__head,.ai-provider-card__footer { align-items:stretch; flex-direction:column; } }
    </style>

    <form wire:submit="save" class="ai-providers">
        <section class="ai-providers__intro">
            <h2>AI-провайдеры и назначение моделей</h2>
            <p>Ключи провайдеров и настройки функций хранятся отдельно. Это позволяет менять модель AI-помощника без влияния на поиск, а провайдера embeddings — без изменения логики кабинета.</p>
        </section>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head"><div><h3>1. Ключи и подключение провайдеров</h3><p>Введите ключ один раз. После сохранения он шифруется и больше не отображается.</p></div><span class="ai-provider-card__badge">Независимые ключи</span></header>
            <div class="ai-provider-card__body ai-provider-card__body--two">
                <label class="ai-provider-field ai-provider-field--full">Gemini API-ключ<input wire:model="geminiApiKey" type="password" autocomplete="new-password" placeholder="{{ $geminiApiKeyConfigured ? 'Ключ сохранён. Введите новый только для замены.' : 'Введите API-ключ Gemini' }}"><span class="ai-provider-hint">Используется только при выборе Gemini для embeddings.</span>@error('geminiApiKey') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Gemini Base URL<input wire:model="geminiBaseUrl" type="url">@error('geminiBaseUrl') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Gemini embedding-модель<input wire:model="geminiModel" type="text">@error('geminiModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Gemini тайм-аут, секунд<input wire:model="geminiTimeout" type="number" min="5" max="120">@error('geminiTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>

                <label class="ai-provider-field ai-provider-field--full">OpenRouter API-ключ<input wire:model="openRouterApiKey" type="password" autocomplete="new-password" placeholder="{{ $openRouterApiKeyConfigured ? 'Ключ сохранён. Введите новый только для замены.' : 'Введите API-ключ OpenRouter' }}"><span class="ai-provider-hint">Один ключ даёт доступ к выбранным embedding- и chat-моделям OpenRouter.</span>@error('openRouterApiKey') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">OpenRouter Base URL<input wire:model="openRouterBaseUrl" type="url">@error('openRouterBaseUrl') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">OpenRouter тайм-аут, секунд<input wire:model="openRouterTimeout" type="number" min="5" max="120">@error('openRouterTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>

                <label class="ai-provider-field ai-provider-field--full">DeepSeek API-ключ<input wire:model="deepSeekApiKey" type="password" autocomplete="new-password" placeholder="{{ $deepSeekApiKeyConfigured ? 'Ключ сохранён. Введите новый только для замены.' : 'Введите API-ключ DeepSeek' }}"><span class="ai-provider-hint">Независимый прямой доступ к DeepSeek для AI-помощника или резервного сценария.</span>@error('deepSeekApiKey') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">DeepSeek Base URL<input wire:model="deepSeekBaseUrl" type="url">@error('deepSeekBaseUrl') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">DeepSeek модель по умолчанию<input wire:model="deepSeekModel" type="text">@error('deepSeekModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">DeepSeek тайм-аут, секунд<input wire:model="deepSeekTimeout" type="number" min="5" max="120">@error('deepSeekTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">DeepSeek temperature<input wire:model="deepSeekTemperature" type="number" min="0" max="2" step="0.1">@error('deepSeekTemperature') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">DeepSeek максимум токенов<input wire:model="deepSeekMaxTokens" type="number" min="64" max="32768">@error('deepSeekMaxTokens') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <footer class="ai-provider-card__footer"><p>Проверка не сохраняет форму и не меняет профили участников.</p><div class="flex flex-wrap gap-2"><x-filament::button type="button" color="gray" wire:click="testGeminiConnection">Проверить Gemini</x-filament::button><x-filament::button type="button" color="gray" wire:click="testOpenRouterConnection">Проверить OpenRouter</x-filament::button><x-filament::button type="button" color="gray" wire:click="testDeepSeekConnection">Проверить DeepSeek</x-filament::button></div></footer>
        </section>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head"><div><h3>2. Поиск и матчи — embeddings</h3><p>Выберите единственный источник векторов для семантического поиска и рекомендаций.</p></div><span class="ai-provider-card__badge">Рабочая функция</span></header>
            <div class="ai-provider-card__body">
                <label class="ai-provider-field">Провайдер<select wire:model="embeddingProvider"><option value="gemini">Gemini</option><option value="openrouter">OpenRouter</option></select>@error('embeddingProvider') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Embedding-модель<input wire:model="embeddingModel" type="text" placeholder="baai/bge-m3 или gemini-embedding-001">@error('embeddingModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Тайм-аут, секунд<input wire:model="embeddingTimeout" type="number" min="5" max="120">@error('embeddingTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Минимальная релевантность поиска<input wire:model="searchMinScore" type="number" min="0" max="1" step="0.01">@error('searchMinScore') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <div class="ai-notice"><strong>Минимальная релевантность:</strong> это порог только для страницы поиска. Чем ниже число, тем больше результатов: <code>0.65</code> — строго, <code>0.55</code> — рекомендуемый старт, <code>0.45</code> — широко. Матчи показывают пять лучших профилей без этого порога.<br><br><strong>Важно:</strong> векторы от разных embedding-моделей несовместимы. После смены провайдера или модели сохраните настройки и полностью пересчитайте профили командой <code>php artisan ai:recompute-embeddings --force</code>. До пересчёта поиск и матчи могут быть некорректными.</div>
        </section>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head"><div><h3>3. AI-помощник</h3><p>Настройка модели для будущего чата-помощника в кабинете. Она не влияет на embeddings.</p></div><span class="ai-provider-card__badge">Подготовлено к подключению</span></header>
            <div class="ai-provider-card__body">
                <label class="ai-provider-field">Провайдер<select wire:model="agentProvider"><option value="openrouter">OpenRouter</option><option value="deepseek">DeepSeek напрямую</option></select>@error('agentProvider') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field ai-provider-field--full">Chat-модель<input wire:model="agentModel" type="text" placeholder="Укажите модель выбранного провайдера">@error('agentModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Temperature<input wire:model="agentTemperature" type="number" min="0" max="2" step="0.1">@error('agentTemperature') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Максимум токенов<input wire:model="agentMaxTokens" type="number" min="64" max="32768">@error('agentMaxTokens') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Тайм-аут, секунд<input wire:model="agentTimeout" type="number" min="5" max="120">@error('agentTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <div class="ai-notice"><strong>О бесплатных моделях:</strong> их можно использовать для тестов, но не как единственный production-вариант: лимиты и доступность могут измениться. Для каждой будущей AI-функции будет храниться свой провайдер и модель; добавление новой функции не изменит уже работающие embeddings.</div>
        </section>

        <div class="ai-providers__actions"><x-filament::button type="submit">Сохранить все настройки</x-filament::button></div>
    </form>
</x-filament-panels::page>
