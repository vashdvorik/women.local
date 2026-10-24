@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
@endphp
<!doctype html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Свяжитесь с командой Women's hub через Telegram и присоединяйтесь к сообществу.">
    <title>Контакты — Women's hub</title>
    <link rel="icon" type="image/png" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro2') . '/images/brand/favicon-w4w.png') }}">
    <link rel="apple-touch-icon" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro2') . '/images/brand/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro2') . '/css/contact.css') }}">
    <link rel="stylesheet" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro2') . '/css/navigation.css') }}">
    <link rel="stylesheet" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro2') . '/css/light.css') }}">
</head>
<body>
    @include('themes.public.miro2.partials.miro-header', ['miroCurrentPage' => 'contact'])

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
                            <div class="miro-contact-card__icon" aria-hidden="true">@</div>
                            <h3><span data-lang="ru">Электронная почта</span><span data-lang="en">Email</span><span data-lang="ro">Email</span></h3>
                            <p><span data-lang="ru">Будем рады идеям и предложениям: о сотрудничестве, партнёрстве, совместных проектах и мероприятиях. Расскажите, чем вы занимаетесь и чем мы могли бы быть друг другу полезны.</span><span data-lang="en">We would love to hear your ideas and proposals: about cooperation, partnerships, joint projects and events. Tell us what you do and how we could be useful to each other.</span><span data-lang="ro">Ne vor bucura ideile și propunerile dvs.: despre colaborare, parteneriate, proiecte comune și evenimente. Spuneți-ne cu ce vă ocupați și cum am putea fi de folos unii altora.</span></p>
                            <div class="miro-contact-card__links">
                                <a href="mailto:{{ config('site.contacts.email') }}">{{ config('site.contacts.email') }}</a>
                            </div>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">✦</div>
                            <h3><span data-lang="ru">Команда разработчиков</span><span data-lang="en">Development team</span><span data-lang="ro">Echipa de dezvoltare</span></h3>
                            <p><span data-lang="ru">Если что-то на сайте или в боте работает не так, как хотелось бы, или у вас есть идея, как сделать платформу удобнее, будем рады вашему сообщению.</span><span data-lang="en">If something on the site or in the bot does not work the way you would like, or you have an idea of how to make the platform more convenient, we would be glad to hear from you.</span><span data-lang="ro">Dacă ceva pe site sau în bot nu funcționează așa cum v-ați dori, sau aveți o idee cum să facem platforma mai comodă, vom fi bucuroși să auzim de la dvs.</span></p>
                            <a href="{{ $managerUrl }}" target="_blank" rel="noopener">@lesnichenkoP&nbsp;→</a>
                        </article>
                        <article class="miro-contact-card">
                            <div class="miro-contact-card__icon" aria-hidden="true">⌖</div>
                            <h3><span data-lang="ru">Адреса и карта</span><span data-lang="en">Addresses and map</span><span data-lang="ro">Adrese și hartă</span></h3>
                            <div class="miro-contact-card__address">
                                <p><span data-lang="ru">г. Тирасполь, ул. Свердлова, 57,<br>MD-3300, Молдова</span><span data-lang="en">57 Sverdlova Street, MD-3300,<br>Tiraspol, Moldova</span><span data-lang="ro">str. Sverdlov 57, MD-3300,<br>Tiraspol, Moldova</span></p>
                                <a href="https://www.google.com/maps/search/?api=1&amp;query=Tiraspol%2C%20Sverdlova%2057" target="_blank" rel="noopener"><span data-lang="ru">Открыть карту&nbsp;→</span><span data-lang="en">Open map&nbsp;→</span><span data-lang="ro">Deschide harta&nbsp;→</span></a>
                            </div>
                            <div class="miro-contact-card__address">
                                <p><span data-lang="ru">г. Кишинёв, ул. Алексея Щусева, 105,<br>MD-2012, Республика Молдова</span><span data-lang="en">105 Alexei Sciusev Street, MD-2012,<br>Chisinau, Republic of Moldova</span><span data-lang="ro">str. Alexei Sciusev 105, MD-2012,<br>Chișinău, Republica Moldova</span></p>
                                <a href="https://www.google.com/maps/search/?api=1&amp;query=Chisinau%2C%20Alexei%20Sciusev%20105" target="_blank" rel="noopener"><span data-lang="ru">Открыть карту&nbsp;→</span><span data-lang="en">Open map&nbsp;→</span><span data-lang="ro">Deschide harta&nbsp;→</span></a>
                            </div>
                        </article>
                    </div>
                </div>

                {{-- «Связаться с нами»: сохраняется в админке («Обращения») и уходит письмом на почту команды, см. ContactController. --}}
                <aside class="miro-contact-form" id="contact-form">
                    <h3><span data-lang="ru">Связаться с нами</span><span data-lang="en">Get in touch</span><span data-lang="ro">Contactează-ne</span></h3>
                    <p class="miro-contact-form__lead"><span data-lang="ru">Расскажите, чем мы можем быть полезны, и мы ответим на почту.</span><span data-lang="en">Tell us how we can help and we will reply by email.</span><span data-lang="ro">Spune-ne cu ce te putem ajuta și îți vom răspunde pe email.</span></p>

                    @if(session('contact_sent'))
                        <p class="miro-contact-form__notice" role="status"><span data-lang="ru">Спасибо! Сообщение отправлено, мы ответим вам на почту.</span><span data-lang="en">Thank you! Your message has been sent and we will reply by email.</span><span data-lang="ro">Mulțumim! Mesajul a fost trimis, îți vom răspunde pe email.</span></p>
                    @else
                        <form method="POST" action="{{ route('contact.send') }}" class="miro-contact-form__form">
                            @csrf
                            <input type="hidden" name="form_token" value="{{ \App\Support\ContactForm::token() }}">
                            {{-- Приманка для ботов: человек это поле не видит и не заполняет. --}}
                            <div class="miro-contact-form__trap" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                            <label class="miro-contact-form__field">
                                <span class="miro-contact-form__label"><span data-lang="ru">Ваше имя</span><span data-lang="en">Your name</span><span data-lang="ro">Numele tău</span></span>
                                <input type="text" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
                            </label>
                            <label class="miro-contact-form__field">
                                <span class="miro-contact-form__label"><span data-lang="ru">Электронная почта</span><span data-lang="en">Email</span><span data-lang="ro">Email</span></span>
                                <input type="email" name="email" value="{{ old('email') }}" required maxlength="191" autocomplete="email" placeholder="name@example.com">
                            </label>
                            <label class="miro-contact-form__field">
                                <span class="miro-contact-form__label"><span data-lang="ru">Телефон</span><span data-lang="en">Phone</span><span data-lang="ro">Telefon</span></span>
                                <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="32" autocomplete="tel" placeholder="+373 ...">
                            </label>
                            <label class="miro-contact-form__field">
                                <span class="miro-contact-form__label"><span data-lang="ru">Организация и должность (по желанию)</span><span data-lang="en">Organisation and position (optional)</span><span data-lang="ro">Organizația și funcția (opțional)</span></span>
                                <input type="text" name="organization" value="{{ old('organization') }}" maxlength="191" autocomplete="organization">
                            </label>
                            <label class="miro-contact-form__field miro-contact-form__field--wide">
                                <span class="miro-contact-form__label"><span data-lang="ru">Сообщение</span><span data-lang="en">Message</span><span data-lang="ro">Mesaj</span></span>
                                <textarea name="message" rows="5" required minlength="5" maxlength="4000">{{ old('message') }}</textarea>
                            </label>

                            @if(session('contact_error') === 'throttled')
                                <p class="miro-contact-form__error" role="alert"><span data-lang="ru">Вы уже отправили несколько сообщений. Попробуйте, пожалуйста, чуть позже или напишите нам на почту.</span><span data-lang="en">You have already sent several messages. Please try again a little later or write to us by email.</span><span data-lang="ro">Ai trimis deja mai multe mesaje. Încearcă puțin mai târziu sau scrie-ne pe email.</span></p>
                            @elseif(session('contact_error') === 'token')
                                <p class="miro-contact-form__error" role="alert"><span data-lang="ru">Не получилось отправить: страница устарела или форма заполнена слишком быстро. Нажмите «Отправить» ещё раз.</span><span data-lang="en">We could not send it: the page has expired or the form was filled in too quickly. Please press “Send” again.</span><span data-lang="ro">Nu s-a putut trimite: pagina a expirat sau formularul a fost completat prea repede. Apasă din nou „Trimite”.</span></p>
                            @elseif(session('contact_error'))
                                <p class="miro-contact-form__error" role="alert"><span data-lang="ru">Проверьте, пожалуйста, имя, почту и сообщение (не короче 5 символов), а в телефоне — только цифры и знак «+».</span><span data-lang="en">Please check your name, email and message (at least 5 characters); the phone number may contain only digits and “+”.</span><span data-lang="ro">Verifică numele, emailul și mesajul (cel puțin 5 caractere); în telefon sunt permise doar cifre și „+”.</span></p>
                            @endif

                            <button type="submit" class="miro-contact-form__submit"><span data-lang="ru">Отправить</span><span data-lang="en">Send</span><span data-lang="ro">Trimite</span></button>
                        </form>
                    @endif
                </aside>
            </div>
        </section>
    </main>

    @include('themes.public.miro2.partials.miro-footer')

</body>
</html>


