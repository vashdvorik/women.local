<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Design System — Women Entrepreneurs Platform</title>
    @if (is_file(public_path('build/manifest.json')) || is_file(public_path('hot')))
        @vite(['resources/css/design-system.css'])
    @else
        <style>{!! file_get_contents(resource_path('css/design-system.css')) !!}</style>
    @endif
</head>
<body class="ds-page">
    <main>
        <section class="ds-section" style="background: var(--color-white);">
            <div class="ds-container ds-stack" style="gap: var(--space-5);">
                <span class="ds-eyebrow">Women Entrepreneurs Platform</span>
                <h1 class="ds-display">Design System</h1>
                <p class="ds-lead">Единый визуальный и компонентный язык публичного сайта, кабинета участницы и административной зоны.</p>
                <div class="ds-cluster"><span class="ds-badge ds-badge-success">Version 1.1</span><a class="ds-button ds-button-outline ds-button-sm" href="/">Открыть главную</a></div>
            </div>
        </section>

        <section class="ds-section">
            <div class="ds-container">
                <div class="ds-section-header"><span class="ds-eyebrow">01 · Foundation</span><h2 class="ds-h2">Цветовая палитра</h2></div>
                <div class="ds-grid ds-grid-4">
                    @foreach ([
                        ['Deep Plum', '#2B174D', 'var(--color-plum)'],
                        ['Royal Purple', '#4B247A', 'var(--color-purple)'],
                        ['Soft Lavender', '#F3ECFF', 'var(--color-lavender)'],
                        ['Dark Teal', '#087C7B', 'var(--color-dark-teal)'],
                        ['Aqua Tint', '#E8F8F7', 'var(--color-aqua-tint)'],
                        ['Warm Gold', '#E6B94E', 'var(--color-gold)'],
                        ['Ink', '#242033', 'var(--color-ink)'],
                        ['Cloud', '#F8F7FA', 'var(--color-cloud)'],
                    ] as [$name, $hex, $color])
                        <article class="ds-card" style="padding: 0; overflow: hidden;">
                            <div style="height: 112px; background: {{ $color }};"></div>
                            <div style="padding: var(--space-4);"><strong>{{ $name }}</strong><p class="ds-small">{{ $hex }}</p></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="ds-section" style="background: var(--color-white);">
            <div class="ds-container">
                <div class="ds-section-header"><span class="ds-eyebrow">02 · Typography</span><h2 class="ds-h2">Типографика</h2></div>
                <div class="ds-card ds-stack" style="gap: var(--space-8);">
                    <div><span class="ds-small">Display · 64/48</span><p class="ds-display">Сильное сообщество</p></div>
                    <div><span class="ds-small">H1 · 52/40</span><p class="ds-h1">Развивайте бизнес вместе</p></div>
                    <div><span class="ds-small">H2 · 42/34</span><p class="ds-h2">Возможности платформы</p></div>
                    <div><span class="ds-small">H3 · 32/28</span><p class="ds-h3">Ближайшие события</p></div>
                    <div><span class="ds-small">Body · 16</span><p class="ds-body">Основной интерфейсный текст должен оставаться спокойным, ясным и легко читаться на мобильном устройстве.</p></div>
                </div>
            </div>
        </section>

        <section class="ds-section">
            <div class="ds-container">
                <div class="ds-section-header"><span class="ds-eyebrow">03 · Actions</span><h2 class="ds-h2">Кнопки и статусы</h2></div>
                <div class="ds-card ds-stack">
                    <div class="ds-cluster"><button class="ds-button ds-button-primary">Основное действие</button><button class="ds-button ds-button-secondary">Вторичное</button><button class="ds-button ds-button-outline">Контурная</button><button class="ds-button ds-button-ghost">Текстовая</button><button class="ds-button ds-button-danger">Удалить</button><button class="ds-button ds-button-primary" disabled>Недоступно</button></div>
                    <div class="ds-cluster"><span class="ds-badge ds-badge-primary">На проверке</span><span class="ds-badge ds-badge-secondary">Новое</span><span class="ds-badge ds-badge-success">Одобрено</span><span class="ds-badge ds-badge-warning">Нужны изменения</span><span class="ds-badge ds-badge-danger">Отклонено</span></div>
                </div>
            </div>
        </section>

        <section class="ds-section" style="background: var(--color-white);">
            <div class="ds-container">
                <div class="ds-section-header"><span class="ds-eyebrow">04 · Surfaces</span><h2 class="ds-h2">Карточки</h2></div>
                <div class="ds-grid ds-grid-3">
                    <article class="ds-card ds-card-interactive"><div class="ds-card-header"><h3 class="ds-card-title">Обычная карточка</h3><span class="ds-badge ds-badge-secondary">Новое</span></div><p class="ds-body">Для курсов, событий, возможностей и профилей.</p></article>
                    <article class="ds-card ds-card-muted"><div class="ds-card-header"><h3 class="ds-card-title">Спокойная карточка</h3></div><p class="ds-body">Для справочной информации и второстепенных настроек.</p></article>
                    <article class="ds-card ds-card-accent"><div class="ds-card-header"><h3 class="ds-card-title">Акцентная карточка</h3></div><p style="margin:0;color:rgba(255,255,255,.74);">Для CTA и одного важного сообщения в секции.</p></article>
                </div>
            </div>
        </section>

        <section class="ds-section">
            <div class="ds-container">
                <div class="ds-section-header"><span class="ds-eyebrow">05 · Forms</span><h2 class="ds-h2">Формы</h2></div>
                <div class="ds-grid ds-grid-2">
                    <form class="ds-card ds-form">
                        <div class="ds-field"><label class="ds-label" for="ds-name">Название бизнеса <span class="ds-required">*</span></label><input class="ds-input" id="ds-name" placeholder="Введите название"><p class="ds-help">Так название будет отображаться в каталоге.</p></div>
                        <div class="ds-field"><label class="ds-label" for="ds-sector">Сектор</label><select class="ds-select" id="ds-sector"><option>Выберите сектор</option><option>Услуги</option><option>Производство</option></select></div>
                        <div class="ds-field"><label class="ds-label" for="ds-about">О бизнесе</label><textarea class="ds-textarea" id="ds-about" placeholder="Коротко опишите продукты или услуги"></textarea></div>
                        <label class="ds-checkbox"><input type="checkbox"><span>Я подтверждаю согласие на обработку данных.</span></label>
                        <div class="ds-cluster"><button class="ds-button ds-button-primary" type="button">Сохранить и продолжить</button><button class="ds-button ds-button-ghost" type="button">Сохранить черновик</button></div>
                    </form>
                    <div class="ds-stack">
                        <div class="ds-alert ds-alert-info">Информационное сообщение с понятным следующим шагом.</div>
                        <div class="ds-alert ds-alert-success">Изменения успешно сохранены.</div>
                        <div class="ds-alert ds-alert-warning">Для публикации заполните обязательные поля.</div>
                        <div class="ds-alert ds-alert-danger">Не удалось сохранить данные. Попробуйте ещё раз.</div>
                        <div class="ds-field"><label class="ds-label" for="ds-error">Поле с ошибкой</label><input class="ds-input" id="ds-error" aria-invalid="true" value="Некорректное значение"><p class="ds-error">Проверьте формат введённых данных.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ds-section" style="background: var(--color-white);">
            <div class="ds-container">
                <div class="ds-section-header"><span class="ds-eyebrow">06 · Member Area</span><h2 class="ds-h2">Личный кабинет</h2><p class="ds-lead">Светлый, спокойный интерфейс с акцентом на прогресс, рекомендации и ближайшие действия.</p></div>
                <div class="ds-dashboard">
                    <aside class="ds-dashboard-sidebar"><div class="ds-dashboard-brand">Women Platform</div><nav class="ds-dashboard-nav"><a class="active" href="#">Главная</a><a href="#">Профиль</a><a href="#">Каталог</a><a href="#">Обучение</a><a href="#">События</a><a href="#">Наставничество</a></nav></aside>
                    <div class="ds-dashboard-main"><header class="ds-dashboard-topbar"><strong>Добрый день, Анна</strong><span class="ds-badge ds-badge-warning">Профиль заполнен на 72%</span></header><div class="ds-dashboard-content ds-stack" style="gap:var(--space-6);"><div><span class="ds-eyebrow">Обзор</span><h3 class="ds-h3">Ваш следующий шаг</h3></div><div class="ds-dashboard-widgets"><article class="ds-card ds-stat"><span class="ds-small">Прогресс профиля</span><strong>72%</strong><span class="ds-small">Добавьте предложения</span></article><article class="ds-card ds-stat"><span class="ds-small">Рекомендации</span><strong>8</strong><span class="ds-small">Новых за неделю</span></article><article class="ds-card ds-stat"><span class="ds-small">События</span><strong>3</strong><span class="ds-small">В этом месяце</span></article></div><article class="ds-card"><div class="ds-card-header"><h4 class="ds-card-title">Рекомендовано для вас</h4><button class="ds-button ds-button-ghost ds-button-sm">Показать всё</button></div><p class="ds-body">Экспортная программа для малого бизнеса · совпадение по вашим целям и сектору.</p></article></div></div>
                </div>
            </div>
        </section>

        <section class="ds-section">
            <div class="ds-container">
                <div class="ds-section-header"><span class="ds-eyebrow">07 · Administration</span><h2 class="ds-h2">Административная зона</h2><p class="ds-lead">Более плотный и нейтральный интерфейс, оптимизированный для очередей, таблиц и массовых операций.</p></div>
                <div class="ds-admin">
                    <aside class="ds-admin-sidebar"><div class="ds-admin-brand">Platform Admin</div><nav class="ds-admin-nav"><a class="active" href="#">Обзор</a><a href="#">Пользователи</a><a href="#">Модерация</a><a href="#">Контент</a><a href="#">События</a><a href="#">Аналитика</a><a href="#">Аудит</a></nav></aside>
                    <div class="ds-admin-main"><header class="ds-admin-topbar"><strong>Профили на модерации</strong><button class="ds-button ds-button-primary ds-button-sm">Экспорт</button></header><div class="ds-admin-content"><div class="ds-table-wrap"><table class="ds-table"><thead><tr><th>Участница</th><th>Регион</th><th>Отправлено</th><th>Статус</th></tr></thead><tbody><tr><td>Анна Петрова</td><td>Кишинёв</td><td>Сегодня, 10:42</td><td><span class="ds-badge ds-badge-warning">На проверке</span></td></tr><tr><td>Елена Русу</td><td>Северный регион</td><td>Вчера, 16:18</td><td><span class="ds-badge ds-badge-danger">Нужны изменения</span></td></tr><tr><td>Марина Ионеску</td><td>Тирасполь</td><td>Вчера, 11:06</td><td><span class="ds-badge ds-badge-success">Одобрено</span></td></tr></tbody></table></div></div></div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
