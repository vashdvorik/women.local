@php
    // Поле «API-ключ»: ключ не показывается, пустое поле оставляет сохранённый.
    $keyPlaceholder = fn (bool $set, string $name) => $set
        ? 'Ключ сохранён. Введите новый только для замены.'
        : 'Введите API-ключ '.$name;
@endphp

<form method="POST" action="{{ route('admin.cabinets.settings.ai.update') }}" class="space-y-6"
      x-data="aiProviders(@js(url('admin/cabinets/settings/ai/test')))">
    @csrf
    @method('PUT')

    <x-forms.error-summary />

    <div class="card space-y-2 text-ui text-ink-muted">
        <h2 class="text-section font-semibold text-ink">Как устроена эта вкладка</h2>
        <p>Ключ провайдера и то, какая функция им пользуется, — два разных решения, поэтому они разнесены по блокам.</p>
        <ol class="list-decimal pl-5 space-y-1 marker:font-semibold">
            <li><b class="text-ink">Ключи провайдеров.</b> Один раз указываете ключ для каждого провайдера, которым планируете пользоваться (не обязательно для всех).</li>
            <li><b class="text-ink">Функции.</b> Для поиска с матчами и для AI-помощника выбираете провайдера и модель из настроенных.</li>
        </ol>
        <p>Так можно сменить модель помощника, не трогая поиск, и наоборот. Ключи хранятся зашифрованными и в панели больше не показываются.</p>
    </div>

    <h3 class="form-section-title">1. Ключи провайдеров</h3>

    {{-- ---------- Gemini ---------- --}}
    <section class="card space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h4 class="text-ui-strong font-semibold">Gemini</h4>
                <p class="field-hint">Нужен, только если ниже для embeddings выбран Gemini.</p>
            </div>
            @if($geminiKeySet)<span class="badge badge--published">Ключ сохранён</span>@else<span class="badge badge--pending">Ключ не указан</span>@endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-forms.field label="API-ключ" name="gemini_api_key" type="password" autocomplete="new-password"
                               :placeholder="$keyPlaceholder($geminiKeySet, 'Gemini')" />
            </div>
            <x-forms.field label="Base URL" name="gemini_base_url" type="url" :value="$gemini['base_url']" required />
            <x-forms.field label="Embedding-модель" name="gemini_model" :value="$gemini['model']" required />
            <x-forms.field label="Тайм-аут, секунд" name="gemini_timeout" type="number" min="5" max="120" :value="$gemini['timeout']" required />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn-secondary" @click="test('gemini', $root)" :disabled="testing !== null">
                <span x-show="testing !== 'gemini'">Проверить подключение</span>
                <span x-show="testing === 'gemini'" x-cloak>Проверяем…</span>
            </button>
            <span class="text-caption text-ink-muted">Проверка не сохраняет форму.</span>
        </div>
        <div x-show="results.gemini" x-cloak class="rounded-sm p-3 text-ui"
             :class="results.gemini && results.gemini.ok ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger'">
            <span x-text="results.gemini && results.gemini.message"></span>
        </div>
    </section>

    {{-- ---------- OpenRouter ---------- --}}
    <section class="card space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h4 class="text-ui-strong font-semibold">OpenRouter</h4>
                <p class="field-hint">Один ключ даёт доступ к embedding- и chat-моделям OpenRouter.</p>
            </div>
            @if($openRouter['api_key_configured'])<span class="badge badge--published">Ключ сохранён</span>@else<span class="badge badge--pending">Ключ не указан</span>@endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-forms.field label="API-ключ" name="openrouter_api_key" type="password" autocomplete="new-password"
                               :placeholder="$keyPlaceholder($openRouter['api_key_configured'], 'OpenRouter')" />
            </div>
            <x-forms.field label="Base URL" name="openrouter_base_url" type="url" :value="$openRouter['base_url']" required />
            <x-forms.field label="Тайм-аут, секунд" name="openrouter_timeout" type="number" min="5" max="120" :value="$openRouter['timeout']" required />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn-secondary" @click="test('openrouter', $root)" :disabled="testing !== null">
                <span x-show="testing !== 'openrouter'">Проверить подключение</span>
                <span x-show="testing === 'openrouter'" x-cloak>Проверяем…</span>
            </button>
            <span class="text-caption text-ink-muted">Проверка не сохраняет форму.</span>
        </div>
        <div x-show="results.openrouter" x-cloak class="rounded-sm p-3 text-ui"
             :class="results.openrouter && results.openrouter.ok ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger'">
            <span x-text="results.openrouter && results.openrouter.message"></span>
        </div>
    </section>

    {{-- ---------- DeepSeek ---------- --}}
    <section class="card space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h4 class="text-ui-strong font-semibold">DeepSeek</h4>
                <p class="field-hint">Прямой доступ к DeepSeek в обход OpenRouter.</p>
            </div>
            @if($deepSeek['api_key_configured'])<span class="badge badge--published">Ключ сохранён</span>@else<span class="badge badge--pending">Ключ не указан</span>@endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-forms.field label="API-ключ" name="deepseek_api_key" type="password" autocomplete="new-password"
                               :placeholder="$keyPlaceholder($deepSeek['api_key_configured'], 'DeepSeek')" />
            </div>
            <x-forms.field label="Base URL" name="deepseek_base_url" type="url" :value="$deepSeek['base_url']" required />
            <x-forms.field label="Модель по умолчанию" name="deepseek_model" list="deepseek-models" :value="$deepSeek['model']" required
                           hint="deepseek-chat — быстрый обычный ответ; deepseek-reasoner — с рассуждениями: медленнее, часть лимита токенов уходит на них." />
            <datalist id="deepseek-models"><option value="deepseek-chat"></option><option value="deepseek-reasoner"></option></datalist>
            <x-forms.field label="Тайм-аут, секунд" name="deepseek_timeout" type="number" min="5" max="120" :value="$deepSeek['timeout']" required />
            <x-forms.field label="Temperature" name="deepseek_temperature" type="number" min="0" max="2" step="0.1" :value="$deepSeek['temperature']" required />
            <x-forms.field label="Максимум токенов" name="deepseek_max_tokens" type="number" min="64" max="32768" :value="$deepSeek['max_tokens']" required />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn-secondary" @click="test('deepseek', $root)" :disabled="testing !== null">
                <span x-show="testing !== 'deepseek'">Проверить подключение</span>
                <span x-show="testing === 'deepseek'" x-cloak>Проверяем…</span>
            </button>
            <span class="text-caption text-ink-muted">Проверка не сохраняет форму.</span>
        </div>
        <div x-show="results.deepseek" x-cloak class="rounded-sm p-3 text-ui"
             :class="results.deepseek && results.deepseek.ok ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger'">
            <span x-text="results.deepseek && results.deepseek.message"></span>
        </div>
    </section>

    <h3 class="form-section-title">2. Какие функции что используют</h3>

    {{-- ---------- Поиск и матчи ---------- --}}
    <section class="card space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h4 class="text-ui-strong font-semibold">Поиск и матчи — embeddings</h4>
                <p class="field-hint">Единственный источник векторов для семантического поиска и рекомендаций.</p>
            </div>
            @if($embeddingKeySet)<span class="badge badge--published">Активна</span>@else<span class="badge badge--pending">Нет ключа для выбранного провайдера</span>@endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-forms.field label="Провайдер" name="embedding_provider">
                <select id="embedding_provider" name="embedding_provider" class="field-input">
                    @foreach(['gemini' => 'Gemini', 'openrouter' => 'OpenRouter'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('embedding_provider', $embedding['provider']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-forms.field>
            <x-forms.field label="Embedding-модель" name="embedding_model" list="embedding-models" :value="$embedding['model']" required
                           placeholder="baai/bge-m3 или gemini-embedding-001" />
            <datalist id="embedding-models"><option value="gemini-embedding-001"></option><option value="baai/bge-m3"></option></datalist>
            <x-forms.field label="Тайм-аут, секунд" name="embedding_timeout" type="number" min="5" max="120" :value="$embedding['timeout']" required />
            <x-forms.field label="Минимальная релевантность поиска" name="search_min_score" type="number" min="0" max="1" step="0.01" :value="$searchMinScore" required />
        </div>
        <p class="rounded-sm bg-surface-sunken p-3 text-caption text-ink-muted">
            <b class="text-ink">Минимальная релевантность</b> — порог только для страницы поиска. Чем ниже число, тем больше результатов:
            0.65 — строго, 0.55 — рекомендуемый старт, 0.45 — широко. Матчи показывают пять лучших профилей без этого порога.<br><br>
            <b class="text-ink">Важно:</b> векторы разных embedding-моделей несовместимы. После смены провайдера или модели сохраните
            настройки и полностью пересчитайте профили командой <code>php artisan ai:recompute-embeddings --force</code>.
            До пересчёта поиск и матчи могут работать некорректно.
        </p>
    </section>

    {{-- ---------- AI-помощник ---------- --}}
    <section class="card space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h4 class="text-ui-strong font-semibold">AI-помощник</h4>
                <p class="field-hint">Модель чата-помощника в личном кабинете участниц. Не влияет на embeddings.</p>
            </div>
            @if($agentKeySet)<span class="badge badge--published">Активна</span>@else<span class="badge badge--pending">Нет ключа для выбранного провайдера</span>@endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-forms.field label="Провайдер" name="agent_provider">
                <select id="agent_provider" name="agent_provider" class="field-input">
                    @foreach(['openrouter' => 'OpenRouter', 'deepseek' => 'DeepSeek напрямую'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('agent_provider', $agent['provider']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-forms.field>
            <x-forms.field label="Chat-модель" name="agent_model" list="agent-models" :value="$agent['model']" required
                           placeholder="Укажите модель выбранного провайдера"
                           hint="Для OpenRouter указывайте модель с префиксом вендора, например deepseek/deepseek-chat. Модели с рассуждениями (deepseek/deepseek-v4-flash) отвечают медленнее: для быстрого стабильного чата подойдут deepseek/deepseek-chat или deepseek/deepseek-chat-v3.1. Для DeepSeek напрямую — deepseek-chat или deepseek-reasoner." />
            <datalist id="agent-models">
                <option value="deepseek/deepseek-chat"></option><option value="deepseek/deepseek-chat-v3.1"></option>
                <option value="deepseek/deepseek-v4-flash"></option><option value="deepseek-chat"></option><option value="deepseek-reasoner"></option>
            </datalist>
            <x-forms.field label="Temperature" name="agent_temperature" type="number" min="0" max="2" step="0.1" :value="$agent['temperature']" required />
            <x-forms.field label="Максимум токенов" name="agent_max_tokens" type="number" min="64" max="32768" :value="$agent['max_tokens']" required />
            <x-forms.field label="Тайм-аут, секунд" name="agent_timeout" type="number" min="5" max="120" :value="$agent['timeout']" required />
        </div>
        <p class="text-caption text-ink-muted">
            Бесплатные модели подходят для тестов, но не как единственный вариант для боевого сайта: лимиты и доступность могут измениться.
        </p>
    </section>

    <button type="submit" class="btn-primary">Сохранить все настройки</button>
</form>
