<x-filament-panels::page>
    <style>
        .ai-providers { display:grid; max-width:1120px; gap:20px; }
        .ai-providers__intro,.ai-provider-card { border:1px solid #e5e7eb; border-radius:18px; background:#fff; box-shadow:0 6px 20px rgba(15,23,42,.05); }
        .ai-providers__intro { padding:24px; background:linear-gradient(135deg,#fff,#f8f7ff); }
        .ai-providers__intro h2,.ai-provider-card h3 { margin:0; color:#18212f; font-weight:750; }
        .ai-providers__intro h2 { font-size:22px; } .ai-providers__intro p { max-width:850px; margin:8px 0 0; color:#64748b; font-size:14px; line-height:1.55; }
        .ai-providers__intro ol { max-width:850px; margin:14px 0 0; padding-left:20px; color:#475569; font-size:13px; line-height:1.7; }

        .ai-providers__group-title { display:flex; align-items:center; gap:10px; margin:6px 0 -4px; color:#392263; font-size:13px; font-weight:750; letter-spacing:.04em; text-transform:uppercase; }
        .ai-providers__group-title span.step { display:inline-grid; place-items:center; width:22px; height:22px; border-radius:999px; background:#6d3ca4; color:#fff; font-size:11px; }
        .ai-providers__group-hint { margin:-8px 0 2px; color:#8491a3; font-size:12.5px; line-height:1.5; }

        .ai-provider-card { overflow:hidden; }
        .ai-provider-card__head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 24px 16px; border-bottom:1px solid #edf0f4; }
        .ai-provider-card__head h3 { font-size:17px; display:flex; align-items:center; gap:9px; }
        .ai-provider-card__head p { margin:5px 0 0; color:#64748b; font-size:13px; line-height:1.45; }
        .ai-provider-card__badge { flex:0 0 auto; padding:6px 11px; border-radius:999px; font-size:12px; font-weight:700; white-space:nowrap; }
        .ai-provider-card__badge--neutral { background:#f0edff; color:#5b31a3; }
        .ai-provider-card__badge--ok { background:#e8f9ee; color:#137a3f; }
        .ai-provider-card__badge--warn { background:#fff4e5; color:#a3540c; }

        .ai-provider-card__body { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; padding:22px 24px; } .ai-provider-card__body--two { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .ai-provider-field { display:grid; gap:7px; color:#334155; font-size:13px; font-weight:650; } .ai-provider-field--full { grid-column:1 / -1; }
        .ai-provider-field input,.ai-provider-field select { box-sizing:border-box; width:100%; min-height:42px; border:1px solid #cbd5e1; border-radius:10px; padding:9px 11px; background:#fff; color:#111827; font:inherit; font-weight:400; outline:none; }
        .ai-provider-field input:focus,.ai-provider-field select:focus { border-color:#6d3ca4; box-shadow:0 0 0 3px rgba(109,60,164,.12); }
        .ai-provider-hint { color:#8491a3; font-size:12px; font-weight:400; line-height:1.4; } .ai-provider-error { color:#dc2626; font-size:12px; font-weight:500; }
        .ai-provider-hint code { padding:1px 5px; border-radius:5px; background:#f1f0f4; color:#392263; font-size:11.5px; }

        .ai-provider-card__footer { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 24px; border-top:1px solid #edf0f4; background:#fafbfc; } .ai-provider-card__footer p { margin:0; color:#8491a3; font-size:12.5px; }
        .ai-notice { margin:0 24px 22px; padding:13px 15px; border-radius:12px; background:#f7f5ff; color:#544476; font-size:13px; line-height:1.5; }
        .ai-notice code { padding:1px 5px; border-radius:5px; background:#eee9fb; }
        .ai-notice strong { color:#392263; } .ai-providers__actions { display:flex; justify-content:flex-end; }
        @media (max-width:800px) {
            .ai-provider-card__body,.ai-provider-card__body--two { grid-template-columns:1fr; } .ai-provider-field--full { grid-column:auto; }
            .ai-provider-card__head,.ai-provider-card__footer { align-items:stretch; flex-direction:column; }
        }

        /* Basic dark-mode safety net in case this panel's appearance switcher is ever enabled. */
        html.dark .ai-providers__intro,html.dark .ai-provider-card { border-color:#2a2f3a; background:#181c24; box-shadow:none; }
        html.dark .ai-providers__intro { background:linear-gradient(135deg,#181c24,#1c1a26); }
        html.dark .ai-providers__intro h2,html.dark .ai-provider-card h3 { color:#f1f2f4; }
        html.dark .ai-providers__intro p,html.dark .ai-provider-card__head p,html.dark .ai-provider-card__footer p,html.dark .ai-providers__group-hint,html.dark .ai-providers__intro ol { color:#9aa3b2; }
        html.dark .ai-provider-card__head,html.dark .ai-provider-card__footer { border-color:#262b35; }
        html.dark .ai-provider-card__footer { background:#15181f; }
        html.dark .ai-provider-field { color:#cbd3e0; }
        html.dark .ai-provider-field input,html.dark .ai-provider-field select { border-color:#333947; background:#12151b; color:#f1f2f4; }
        html.dark .ai-provider-hint code,html.dark .ai-notice code { background:#242038; color:#c9b8ec; }
        html.dark .ai-notice { background:#201c30; color:#c9c0dd; }
        html.dark .ai-notice strong { color:#e2d6fb; }
        html.dark .ai-provider-card__badge--neutral { background:#241f3a; color:#c9b8ec; }
        html.dark .ai-provider-card__badge--ok { background:#123423; color:#5fd996; }
        html.dark .ai-provider-card__badge--warn { background:#3a2a12; color:#f0b166; }
    </style>

    @php
        $embeddingKeyConfigured = $embeddingProvider === 'gemini' ? $geminiApiKeyConfigured : $openRouterApiKeyConfigured;
        $agentKeyConfigured = $agentProvider === 'openrouter' ? $openRouterApiKeyConfigured : $deepSeekApiKeyConfigured;
    @endphp

    <form wire:submit="save" class="ai-providers">
        <section class="ai-providers__intro">
            <h2>Как устроена эта страница</h2>
            <p>Ключ провайдера и то, какая функция им пользуется, — это два разных решения, поэтому они разнесены по разным блокам ниже.</p>
            <ol>
                <li><strong>Шаг 1.</strong> Один раз указываете ключ для каждого провайдера, которым планируете пользоваться (не обязательно для всех сразу).</li>
                <li><strong>Шаг 2.</strong> Для каждой функции платформы (поиск/матчи и AI-помощник) выбираете, какой из настроенных провайдеров и какая именно модель её обслуживает.</li>
            </ol>
            <p>Так можно, например, сменить модель AI-помощника, не трогая поиск, — или наоборот.</p>
        </section>

        <h3 class="ai-providers__group-title"><span class="step">1</span> Ключи провайдеров</h3>
        <p class="ai-providers__group-hint">Ключ нужен только тому провайдеру, которого вы выберете в шаге 2. Настраивать все три не обязательно.</p>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head">
                <div><h3>Gemini</h3><p>Нужен, только если ниже для embeddings выбран Gemini.</p></div>
                <span class="ai-provider-card__badge {{ $geminiApiKeyConfigured ? 'ai-provider-card__badge--ok' : 'ai-provider-card__badge--warn' }}">{{ $geminiApiKeyConfigured ? '✓ Ключ сохранён' : 'Ключ не указан' }}</span>
            </header>
            <div class="ai-provider-card__body ai-provider-card__body--two">
                <label class="ai-provider-field ai-provider-field--full">API-ключ<input wire:model="geminiApiKey" type="password" autocomplete="new-password" placeholder="{{ $geminiApiKeyConfigured ? 'Ключ сохранён. Введите новый только для замены.' : 'Введите API-ключ Gemini' }}">@error('geminiApiKey') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Base URL<input wire:model="geminiBaseUrl" type="url">@error('geminiBaseUrl') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Embedding-модель<input wire:model="geminiModel" type="text">@error('geminiModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field ai-provider-field--full">Тайм-аут, секунд<input wire:model="geminiTimeout" type="number" min="5" max="120">@error('geminiTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <footer class="ai-provider-card__footer"><p>Проверка не сохраняет форму.</p><x-filament::button type="button" color="gray" wire:click="testGeminiConnection">Проверить подключение</x-filament::button></footer>
        </section>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head">
                <div><h3>OpenRouter</h3><p>Один ключ даёт доступ к выбранным embedding- и chat-моделям OpenRouter.</p></div>
                <span class="ai-provider-card__badge {{ $openRouterApiKeyConfigured ? 'ai-provider-card__badge--ok' : 'ai-provider-card__badge--warn' }}">{{ $openRouterApiKeyConfigured ? '✓ Ключ сохранён' : 'Ключ не указан' }}</span>
            </header>
            <div class="ai-provider-card__body ai-provider-card__body--two">
                <label class="ai-provider-field ai-provider-field--full">API-ключ<input wire:model="openRouterApiKey" type="password" autocomplete="new-password" placeholder="{{ $openRouterApiKeyConfigured ? 'Ключ сохранён. Введите новый только для замены.' : 'Введите API-ключ OpenRouter' }}">@error('openRouterApiKey') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Base URL<input wire:model="openRouterBaseUrl" type="url">@error('openRouterBaseUrl') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Тайм-аут, секунд<input wire:model="openRouterTimeout" type="number" min="5" max="120">@error('openRouterTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <footer class="ai-provider-card__footer"><p>Проверка не сохраняет форму.</p><x-filament::button type="button" color="gray" wire:click="testOpenRouterConnection">Проверить подключение</x-filament::button></footer>
        </section>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head">
                <div><h3>DeepSeek</h3><p>Независимый прямой доступ к DeepSeek, в обход OpenRouter.</p></div>
                <span class="ai-provider-card__badge {{ $deepSeekApiKeyConfigured ? 'ai-provider-card__badge--ok' : 'ai-provider-card__badge--warn' }}">{{ $deepSeekApiKeyConfigured ? '✓ Ключ сохранён' : 'Ключ не указан' }}</span>
            </header>
            <div class="ai-provider-card__body ai-provider-card__body--two">
                <label class="ai-provider-field ai-provider-field--full">API-ключ<input wire:model="deepSeekApiKey" type="password" autocomplete="new-password" placeholder="{{ $deepSeekApiKeyConfigured ? 'Ключ сохранён. Введите новый только для замены.' : 'Введите API-ключ DeepSeek' }}">@error('deepSeekApiKey') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Base URL<input wire:model="deepSeekBaseUrl" type="url">@error('deepSeekBaseUrl') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Модель по умолчанию<input wire:model="deepSeekModel" type="text" list="deepseek-model-suggestions"><datalist id="deepseek-model-suggestions"><option value="deepseek-chat"><option value="deepseek-reasoner"></datalist><span class="ai-provider-hint"><code>deepseek-chat</code> — быстрый обычный ответ. <code>deepseek-reasoner</code> — с рассуждениями: медленнее и часть лимита токенов уходит на них.</span>@error('deepSeekModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Тайм-аут, секунд<input wire:model="deepSeekTimeout" type="number" min="5" max="120">@error('deepSeekTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Temperature<input wire:model="deepSeekTemperature" type="number" min="0" max="2" step="0.1">@error('deepSeekTemperature') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Максимум токенов<input wire:model="deepSeekMaxTokens" type="number" min="64" max="32768">@error('deepSeekMaxTokens') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <footer class="ai-provider-card__footer"><p>Проверка не сохраняет форму.</p><x-filament::button type="button" color="gray" wire:click="testDeepSeekConnection">Проверить подключение</x-filament::button></footer>
        </section>

        <h3 class="ai-providers__group-title"><span class="step">2</span> Какие функции что используют</h3>
        <p class="ai-providers__group-hint">Здесь вы назначаете провайдера и модель каждой функции платформы — ключи для них должны быть уже сохранены в шаге 1.</p>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head">
                <div><h3>Поиск и матчи — embeddings</h3><p>Единственный источник векторов для семантического поиска и рекомендаций.</p></div>
                <span class="ai-provider-card__badge {{ $embeddingKeyConfigured ? 'ai-provider-card__badge--ok' : 'ai-provider-card__badge--warn' }}">{{ $embeddingKeyConfigured ? '✓ Активна' : '⚠ Нет ключа для выбранного провайдера' }}</span>
            </header>
            <div class="ai-provider-card__body">
                <label class="ai-provider-field">Провайдер<select wire:model="embeddingProvider"><option value="gemini">Gemini</option><option value="openrouter">OpenRouter</option></select>@error('embeddingProvider') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Embedding-модель<input wire:model="embeddingModel" type="text" list="embedding-model-suggestions" placeholder="baai/bge-m3 или gemini-embedding-001"><datalist id="embedding-model-suggestions"><option value="gemini-embedding-001"><option value="baai/bge-m3"></datalist>@error('embeddingModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Тайм-аут, секунд<input wire:model="embeddingTimeout" type="number" min="5" max="120">@error('embeddingTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Минимальная релевантность поиска<input wire:model="searchMinScore" type="number" min="0" max="1" step="0.01">@error('searchMinScore') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <div class="ai-notice"><strong>Минимальная релевантность:</strong> это порог только для страницы поиска. Чем ниже число, тем больше результатов: <code>0.65</code> — строго, <code>0.55</code> — рекомендуемый старт, <code>0.45</code> — широко. Матчи показывают пять лучших профилей без этого порога.<br><br><strong>Важно:</strong> векторы от разных embedding-моделей несовместимы. После смены провайдера или модели сохраните настройки и полностью пересчитайте профили командой <code>php artisan ai:recompute-embeddings --force</code>. До пересчёта поиск и матчи могут быть некорректными.</div>
        </section>

        <section class="ai-provider-card">
            <header class="ai-provider-card__head">
                <div><h3>AI-помощник</h3><p>Модель чата-помощника в личном кабинете участниц. Не влияет на embeddings.</p></div>
                <span class="ai-provider-card__badge {{ $agentKeyConfigured ? 'ai-provider-card__badge--ok' : 'ai-provider-card__badge--warn' }}">{{ $agentKeyConfigured ? '✓ Активна' : '⚠ Нет ключа для выбранного провайдера' }}</span>
            </header>
            <div class="ai-provider-card__body">
                <label class="ai-provider-field">Провайдер<select wire:model="agentProvider"><option value="openrouter">OpenRouter</option><option value="deepseek">DeepSeek напрямую</option></select>@error('agentProvider') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field ai-provider-field--full">Chat-модель<input wire:model="agentModel" type="text" list="agent-model-suggestions" placeholder="Укажите модель выбранного провайдера"><datalist id="agent-model-suggestions"><option value="deepseek/deepseek-chat"><option value="deepseek/deepseek-chat-v3.1"><option value="deepseek/deepseek-v4-flash"><option value="deepseek-chat"><option value="deepseek-reasoner"></datalist><span class="ai-provider-hint">Для OpenRouter указывайте модель с префиксом вендора, например <code>deepseek/deepseek-chat</code>, а не просто <code>deepseek-chat</code>. Модели с рассуждениями (например <code>deepseek/deepseek-v4-flash</code>) тратят часть лимита токенов на скрытые «размышления» и отвечают заметно медленнее — для быстрого стабильного чата используйте <code>deepseek/deepseek-chat</code> или <code>deepseek/deepseek-chat-v3.1</code>. Для DeepSeek напрямую — <code>deepseek-chat</code> (быстро) или <code>deepseek-reasoner</code> (с рассуждениями).</span>@error('agentModel') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Temperature<input wire:model="agentTemperature" type="number" min="0" max="2" step="0.1">@error('agentTemperature') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Максимум токенов<input wire:model="agentMaxTokens" type="number" min="64" max="32768">@error('agentMaxTokens') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
                <label class="ai-provider-field">Тайм-аут, секунд<input wire:model="agentTimeout" type="number" min="5" max="120">@error('agentTimeout') <span class="ai-provider-error">{{ $message }}</span> @enderror</label>
            </div>
            <div class="ai-notice"><strong>О бесплатных моделях:</strong> их можно использовать для тестов, но не как единственный production-вариант: лимиты и доступность могут измениться.</div>
        </section>

        <div class="ai-providers__actions"><x-filament::button type="submit">Сохранить все настройки</x-filament::button></div>
    </form>
</x-filament-panels::page>
