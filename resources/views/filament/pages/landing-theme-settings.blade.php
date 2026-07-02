<x-filament-panels::page>
<style>
    .theme-settings {
        display: grid;
        gap: 24px;
    }

    .theme-settings__intro {
        border: 1px solid #eadfd4;
        border-radius: 24px;
        padding: 24px;
        background: linear-gradient(135deg, #fff 0%, #fff7ed 100%);
        box-shadow: 0 16px 42px rgba(120, 53, 15, 0.08);
    }

    .theme-settings__intro h2 {
        margin: 0;
        color: #111827;
        font-size: 24px;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .theme-settings__intro p {
        max-width: 760px;
        margin: 8px 0 0;
        color: #6b7280;
        line-height: 1.6;
    }

    .theme-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .theme-card {
        position: relative;
        display: block;
        overflow: hidden;
        border: 2px solid transparent;
        border-radius: 26px;
        background: #fff;
        cursor: pointer;
        box-shadow: 0 12px 34px rgba(17, 24, 39, 0.08);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .theme-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 46px rgba(17, 24, 39, 0.12);
    }

    .theme-card:has(input:checked) {
        border-color: #d97706;
        box-shadow: 0 18px 46px rgba(217, 119, 6, 0.18);
    }

    .theme-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .theme-preview {
        min-height: 220px;
        padding: 20px;
    }

    .theme-preview__nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
    }

    .theme-preview__logo,
    .theme-preview__button {
        border-radius: 999px;
    }

    .theme-preview__logo {
        width: 34px;
        height: 34px;
    }

    .theme-preview__button {
        width: 92px;
        height: 30px;
    }

    .theme-preview__line {
        height: 12px;
        border-radius: 999px;
        margin-bottom: 10px;
    }

    .theme-preview__line.is-lg {
        height: 28px;
        max-width: 78%;
    }

    .theme-preview__photo {
        width: 48%;
        height: 78px;
        margin-top: 20px;
        border-radius: 18px;
        opacity: .9;
    }

    .theme-preview.is-classic {
        background: radial-gradient(circle at 78% 18%, rgba(221, 239, 232, .9), transparent 32%), #faf8f3;
    }

    .theme-preview.is-classic .theme-preview__logo,
    .theme-preview.is-classic .theme-preview__button,
    .theme-preview.is-classic .theme-preview__line.is-lg {
        background: #123c3a;
    }

    .theme-preview.is-classic .theme-preview__line,
    .theme-preview.is-classic .theme-preview__photo {
        background: #ddeee8;
    }

    .theme-preview.is-warm {
        background: radial-gradient(circle at 80% 20%, rgba(254, 215, 170, .8), transparent 34%), #fff7ed;
    }

    .theme-preview.is-warm .theme-preview__logo,
    .theme-preview.is-warm .theme-preview__button,
    .theme-preview.is-warm .theme-preview__line.is-lg {
        background: #9a3412;
    }

    .theme-preview.is-warm .theme-preview__line,
    .theme-preview.is-warm .theme-preview__photo {
        background: #fed7aa;
    }

    .theme-preview.is-dark {
        background: radial-gradient(circle at 80% 20%, rgba(236, 72, 153, .28), transparent 34%), #0f172a;
    }

    .theme-preview.is-dark .theme-preview__logo,
    .theme-preview.is-dark .theme-preview__button,
    .theme-preview.is-dark .theme-preview__line.is-lg {
        background: #f8fafc;
    }

    .theme-preview.is-dark .theme-preview__line,
    .theme-preview.is-dark .theme-preview__photo {
        background: #334155;
    }

    .theme-card__body {
        padding: 18px;
    }

    .theme-card__body strong {
        display: block;
        color: #111827;
        font-size: 16px;
        font-weight: 900;
    }

    .theme-card__body span {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .theme-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 18px;
        background: #fff;
    }

    .theme-actions p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .theme-save {
        border: 0;
        border-radius: 999px;
        padding: 12px 22px;
        background: #d97706;
        color: #fff;
        cursor: pointer;
        font-weight: 800;
        box-shadow: 0 12px 28px rgba(217, 119, 6, .22);
    }

    @media (max-width: 980px) {
        .theme-grid {
            grid-template-columns: 1fr;
        }

        .theme-actions {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

<form wire:submit.prevent="save" class="theme-settings">
    <section class="theme-settings__intro">
        <h2>Выбор дизайна публичного лендинга</h2>
        <p>
            Выберите визуальную тему. Настройка сохраняется в базе данных и применяется для всех посетителей публичной страницы.
            Бизнес-логика, тексты, ссылки Telegram и формы при этом не меняются.
        </p>
    </section>

    <section class="theme-grid">
        @foreach($this->themes() as $key => $label)
            <label class="theme-card">
                <input type="radio" wire:model="theme" value="{{ $key }}">

                <div class="theme-preview is-{{ $key }}">
                    <div class="theme-preview__nav">
                        <div class="theme-preview__logo"></div>
                        <div class="theme-preview__button"></div>
                    </div>
                    <div class="theme-preview__line is-lg"></div>
                    <div class="theme-preview__line" style="width: 62%;"></div>
                    <div class="theme-preview__line" style="width: 48%;"></div>
                    <div class="theme-preview__photo"></div>
                </div>

                <div class="theme-card__body">
                    <strong>{{ $label }}</strong>
                    <span>
                        @if($key === 'classic')
                            Спокойная зелёная тема: базовый вариант для деловой и институциональной подачи.
                        @elseif($key === 'warm')
                            Более тёплая гранатово-песочная тема: мягче, ярче и эмоциональнее.
                        @else
                            Контрастная тёмная тема: премиальная подача для презентаций и донорских показов.
                        @endif
                    </span>
                </div>
            </label>
        @endforeach
    </section>

    <section class="theme-actions">
        <p>Текущая выбранная тема: <strong>{{ $this->themes()[$theme] ?? 'Классическая зелёная' }}</strong></p>
        <button type="submit" class="theme-save">Сохранить тему</button>
    </section>
</form>
</x-filament-panels::page>
