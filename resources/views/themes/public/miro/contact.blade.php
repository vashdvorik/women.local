@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
@endphp
<!doctype html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Свяжитесь с командой Women Entrepreneurs Platform через Telegram и присоединяйтесь к сообществу.">
    <title>Контакты — Women Entrepreneurs Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/contact.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'contact'])

    <main class="miro-contact-page">


        <section class="miro-contact-section">
            <div class="miro-container miro-contact-layout">
                <div>
                    <h2><span data-lang="ru">Напишите туда, где вам удобно</span><span data-lang="en">Reach us where it feels easiest</span><span data-lang="ro">Scrie-ne acolo unde îți este mai ușor</span></h2>
                    <p class="miro-contact-lead"><span data-lang="ru">Мы собрали основные точки входа: бот для быстрых действий, команда проекта для сотрудничества и сообщество для новостей и общения.</span><span data-lang="en">We’ve gathered the main ways to connect: a bot for quick actions, the project team for collaboration, and the community for news and conversation.</span><span data-lang="ro">Am adunat principalele modalități de contact: un bot pentru acțiuni rapide, echipa proiectului pentru colaborare și comunitatea pentru noutăți și conversații.</span></p>
                    <div class="miro-contact-cards">
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">@</div>
                            <h3><span data-lang="ru">Telegram-бот</span><span data-lang="en">Telegram bot</span><span data-lang="ro">Bot Telegram</span></h3>
                            <p><span data-lang="ru">Регистрация, вход и уведомления платформы.</span><span data-lang="en">Registration, access and platform notifications.</span><span data-lang="ro">Înregistrare, acces și notificări ale platformei.</span></p>
                            <a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot&nbsp;→</a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">✦</div>
                            <h3><span data-lang="ru">Команда проекта</span><span data-lang="en">Project team</span><span data-lang="ro">Echipa proiectului</span></h3>
                            <p><span data-lang="ru">Вопросы о платформе, партнёрстве и сотрудничестве.</span><span data-lang="en">Questions about the platform, partnerships and collaboration.</span><span data-lang="ro">Întrebări despre platformă, parteneriate și colaborare.</span></p>
                            <a href="{{ $managerUrl }}" target="_blank" rel="noopener">@lesnichenkoP&nbsp;→</a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">↗</div>
                            <h3><span data-lang="ru">Сообщество</span><span data-lang="en">Community</span><span data-lang="ro">Comunitate</span></h3>
                            <p><span data-lang="ru">Новости, приглашения и новые знакомства.</span><span data-lang="en">News, invitations and new connections.</span><span data-lang="ro">Noutăți, invitații și conexiuni noi.</span></p>
                            <a href="{{ $communityUrl }}" target="_blank" rel="noopener"><span data-lang="ru">Открыть сообщество&nbsp;→</span><span data-lang="en">Open the community&nbsp;→</span><span data-lang="ro">Deschide comunitatea&nbsp;→</span></a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">⌂</div>
                            <h3><span data-lang="ru">Телефон</span><span data-lang="en">Phone</span><span data-lang="ro">Telefon</span></h3>
                            <p><span data-lang="ru">Свяжитесь с командой по рабочим вопросам.</span><span data-lang="en">Contact the team about project and office matters.</span><span data-lang="ro">Contactează echipa pentru întrebări despre proiect și oficiu.</span></p>
                            <a href="tel:+37377798317">+373 777 983 17&nbsp;→</a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">@</div>
                            <h3><span data-lang="ru">Электронная почта</span><span data-lang="en">Email</span><span data-lang="ro">Email</span></h3>
                            <p><span data-lang="ru">Напишите нам по общим и проектным вопросам.</span><span data-lang="en">Write to us about general and project questions.</span><span data-lang="ro">Scrie-ne pentru întrebări generale și despre proiect.</span></p>
                            <div class="miro-contact-card__links">
                                <a href="mailto:women.tiras.hub@gmail.com">women.tiras.hub@gmail.com</a>
                                <a href="mailto:elena.sinika@innovation.md">elena.sinika@innovation.md</a>
                            </div>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">⌖</div>
                            <h3><span data-lang="ru">Адрес и карта</span><span data-lang="en">Address and map</span><span data-lang="ro">Adresă și hartă</span></h3>
                            <div class="miro-contact-card__map" aria-hidden="true"><span></span></div>
                            <p><span data-lang="ru">г. Тирасполь, ул. Свердлова, 57<br>Приднестровье, MD-3300</span><span data-lang="en">57 Sverdlova Street, Tiraspol<br>Transnistria, MD-3300</span><span data-lang="ro">str. Sverdlov 57, Tiraspol<br>Transnistria, MD-3300</span></p>
                            <a href="https://www.google.com/maps/search/?api=1&amp;query=Tiraspol%2C%20Sverdlova%2057" target="_blank" rel="noopener"><span data-lang="ru">Открыть карту&nbsp;→</span><span data-lang="en">Open map&nbsp;→</span><span data-lang="ro">Deschide harta&nbsp;→</span></a>
                        </article>
                    </div>
                </div>

                <aside class="miro-contact-topics">
                    <h3><span data-lang="ru">Что можно обсудить?</span><span data-lang="en">What can we discuss?</span><span data-lang="ro">Ce putem discuta?</span></h3>
                    <ul>
                        <li><span data-lang="ru">Участие и профиль на платформе</span><span data-lang="en">Participation and your platform profile</span><span data-lang="ro">Participarea și profilul pe platformă</span></li>
                        <li><span data-lang="ru">Партнёрство и поддержка проекта</span><span data-lang="en">Partnerships and project support</span><span data-lang="ro">Parteneriate și susținerea proiectului</span></li>
                        <li><span data-lang="ru">Событие, новость или возможность</span><span data-lang="en">An event, news item or opportunity</span><span data-lang="ro">Un eveniment, o știre sau o oportunitate</span></li>
                        <li><span data-lang="ru">Обучение, наставничество и связи</span><span data-lang="en">Learning, mentoring and connections</span><span data-lang="ro">Învățare, mentorat și conexiuni</span></li>
                    </ul>
                    <div class="miro-contact-cta">
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-contact-button miro-contact-button--primary"><span data-lang="ru">Написать в Telegram&nbsp;→</span><span data-lang="en">Message us on Telegram&nbsp;→</span><span data-lang="ro">Scrie-ne pe Telegram&nbsp;→</span></a>
                        <a href="{{ route('about') }}" class="miro-contact-button miro-contact-button--secondary"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></a>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')

    <script src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/js/contact.js') }}"></script>
</body>
</html>


