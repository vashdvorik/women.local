<x-filament-panels::page>
<style>
    .account-theme-settings {
        display: grid;
        gap: 24px;
    }

    .account-theme-settings__intro {
        border: 1px solid #ddd6fe;
        border-radius: 24px;
        padding: 24px;
        background: linear-gradient(135deg, #fff 0%, #f5f3ff 100%);
        box-shadow: 0 16px 42px rgba(79, 70, 229, 0.08);
    }

    .account-theme-settings__intro h2 {
        margin: 0;
        color: #111827;
        font-size: 24px;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .account-theme-settings__intro p {
        max-width: 820px;
        margin: 8px 0 0;
        color: #6b7280;
        line-height: 1.6;
    }

    .account-theme-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .account-theme-card {
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

    .account-theme-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 46px rgba(17, 24, 39, 0.12);
    }

    .account-theme-card:has(input:checked) {
        border-color: #7c3aed;
        box-shadow: 0 18px 46px rgba(124, 58, 237, 0.18);
    }

    .account-theme-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .account-theme-preview {
        min-height: 220px;
        padding: 20px;
    }

    .account-theme-preview__sidebar,
    .account-theme-preview__header,
    .account-theme-preview__card,
    .account-theme-preview__line {
        border-radius: 12px;
    }

    .account-theme-preview__sidebar {
        float: left;
        width: 26%;
        height: 180px;
    }

    .account-theme-preview__content {
        margin-left: 32%;
    }

    .account-theme-preview__header {
        height: 26px;
        margin-bottom: 22px;
    }

    .account-theme-preview__card {
        height: 68px;
        margin-bottom: 12px;
    }

    .account-theme-preview__line {
        height: 9px;
        margin: 12px 0;
        opacity: .65;
    }

    .account-theme-preview.is-classic { background: #f7f8fa; }
    .account-theme-preview.is-classic .account-theme-preview__sidebar,
    .account-theme-preview.is-classic .account-theme-preview__header { background: #7c3aed; }
    .account-theme-preview.is-classic .account-theme-preview__card { background: #fff; border: 1px solid #ede9fe; }
    .account-theme-preview.is-classic .account-theme-preview__line { background: #c4b5fd; }

    .account-theme-preview.is-miro { background: #fafbfc; }
    .account-theme-preview.is-miro .account-theme-preview__sidebar { background: #1c1c1e; }
    .account-theme-preview.is-miro .account-theme-preview__header { background: #ffd8f4; }
    .account-theme-preview.is-miro .account-theme-preview__card { background: #fff; border: 1px solid #eef0f3; }
    .account-theme-preview.is-miro .account-theme-preview__line { background: #c3faf5; }

    .account-theme-preview.is-fortun { background: #fafbfc; }
    .account-theme-preview.is-fortun .account-theme-preview__sidebar { background: #1c1c1e; }
    .account-theme-preview.is-fortun .account-theme-preview__header { background: #ffd8f4; }
    .account-theme-preview.is-fortun .account-theme-preview__card { background: #fff; border: 1px solid #eef0f3; }
    .account-theme-preview.is-fortun .account-theme-preview__line { background: #c3faf5; }

    .account-theme-card__body {
        padding: 18px;
    }

    .account-theme-card__body strong {
        display: block;
        color: #111827;
        font-size: 16px;
        font-weight: 900;
    }

    .account-theme-card__body span {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .account-theme-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 18px;
        background: #fff;
    }

    .account-theme-actions p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .account-theme-save {
        border: 0;
        border-radius: 999px;
        padding: 12px 22px;
        background: #7c3aed;
        color: #fff;
        cursor: pointer;
        font-weight: 800;
        box-shadow: 0 12px 28px rgba(124, 58, 237, .22);
    }

    @media (max-width: 900px) {
        .account-theme-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 760px) {
        .account-theme-actions { align-items: stretch; flex-direction: column; }
    }
</style>

@php
    $descriptions = [
        'classic' => 'Текущий кабинет: светлый интерфейс с фиолетовой навигацией и акцентами.',
        'miro' => 'Белое рабочее пространство с чёрными CTA, жёлтыми акцентами и пастельными карточками.',
    ];
@endphp

<form wire:submit.prevent="save" class="account-theme-settings">
    <section class="account-theme-settings__intro">
        <h2>Внешний вид кабинета участницы</h2>
        <p>
            Эта настройка относится только к `/app/account`: публичный лендинг управляется отдельно на странице «Тема лендинга».
        </p>
    </section>

    <section class="account-theme-grid">
        @foreach($this->themes() as $key => $label)
            <label class="account-theme-card">
                <input type="radio" wire:model="theme" value="{{ $key }}">

                <div class="account-theme-preview is-{{ $key }}">
                    <div class="account-theme-preview__sidebar"></div>
                    <div class="account-theme-preview__content">
                        <div class="account-theme-preview__header"></div>
                        <div class="account-theme-preview__card"></div>
                        <div class="account-theme-preview__card"></div>
                        <div class="account-theme-preview__line" style="width: 70%;"></div>
                        <div class="account-theme-preview__line" style="width: 48%;"></div>
                    </div>
                </div>

                <div class="account-theme-card__body">
                    <strong>{{ $label }}</strong>
                    <span>{{ $descriptions[$key] ?? 'Тема кабинета участницы.' }}</span>
                </div>
            </label>
        @endforeach
    </section>

    <section class="account-theme-actions">
        <p>Текущая тема: <strong>{{ $this->themes()[$theme] ?? $this->themes()['classic'] }}</strong></p>
        <button type="submit" class="account-theme-save">Сохранить тему</button>
    </section>
</form>
</x-filament-panels::page>
