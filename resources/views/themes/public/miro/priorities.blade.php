@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);

    // The "what we do" / proof / join-the-community sections used to live on the landing
    // page (/) under #features, #learning, #stories and the closing CTA + logo-wall. They
    // were moved here verbatim — same markup, same landing.css component classes — because
    // this is where that detail actually belongs: elaborating on the platform's priorities.
    $eyebrow = ['ru' => 'О нас', 'en' => 'About us', 'ro' => 'Despre noi'];
    $title = ['ru' => 'Приоритеты платформы', 'en' => 'Our priorities', 'ro' => 'Prioritățile platformei'];
    $intro = ['ru' => 'Мы создаём практическую среду, в которой женщины могут развивать бизнес, находить поддержку и открывать новые возможности.', 'en' => 'We create a practical environment where women can grow their businesses, find support and discover new opportunities.', 'ro' => 'Creăm un mediu practic în care femeile își pot dezvolta afacerile, găsi sprijin și descoperi oportunități noi.'];
    $items = [
        ['ru' => 'Доступ к знаниям и развитию', 'en' => 'Access to knowledge and growth', 'ro' => 'Acces la cunoștințe și dezvoltare'],
        ['ru' => 'Связи между предпринимательницами', 'en' => 'Connections between women entrepreneurs', 'ro' => 'Conexiuni între femeile antreprenoare'],
        ['ru' => 'Поддержка новых проектов и партнёрств', 'en' => 'Support for new projects and partnerships', 'ro' => 'Sprijin pentru proiecte și parteneriate noi'],
    ];
@endphp
<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title['ru'] }} — Women Entrepreneurs Platform</title>
    <meta name="description" content="{{ $intro['ru'] }}">
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/public-section.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => null])

    <main class="miro-public-page">
        <section class="miro-public-hero">
            <div class="miro-container miro-public-hero__inner">
                <div class="miro-public-hero__copy">
                    <p class="miro-eyebrow"><span data-lang="ru">{{ $eyebrow['ru'] }}</span><span data-lang="en">{{ $eyebrow['en'] }}</span><span data-lang="ro">{{ $eyebrow['ro'] }}</span></p>
                    <h1><span data-lang="ru">{{ $title['ru'] }}</span><span data-lang="en">{{ $title['en'] }}</span><span data-lang="ro">{{ $title['ro'] }}</span></h1>
                    <p class="miro-public-hero__intro"><span data-lang="ru">{{ $intro['ru'] }}</span><span data-lang="en">{{ $intro['en'] }}</span><span data-lang="ro">{{ $intro['ro'] }}</span></p>
                </div>
                <div class="miro-public-hero__accent" aria-hidden="true"><span></span><span></span><span></span></div>
            </div>
        </section>

        <section class="miro-public-content">
            <div class="miro-container">
                <div class="miro-public-items">
                    @foreach ($items as $item)
                        <article class="miro-public-item">
                            <span class="miro-public-item__number">{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h2><span data-lang="ru">{{ $item['ru'] }}</span><span data-lang="en">{{ $item['en'] }}</span><span data-lang="ro">{{ $item['ro'] }}</span></h2>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="miro-logo-wall">
            <div class="miro-container miro-logo-wall__layout">
                <div class="miro-logo-wall__copy">
                    <div class="miro-logo-wall__items">
                        <span><span data-lang="ru">Видимость бизнеса</span><span data-lang="en">Business visibility</span><span data-lang="ro">Vizibilitatea afacerii</span></span>
                        <span><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></span>
                        <span><span data-lang="ru">Партнёры и рынки</span><span data-lang="en">Partners &amp; markets</span><span data-lang="ro">Partenere și piețe</span></span>
                        <span><span data-lang="ru">Наставничество</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></span>
                        <span><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></span>
                        <span><span data-lang="ru">Запросы и предложения</span><span data-lang="en">Requests &amp; offers</span><span data-lang="ro">Solicitări și oferte</span></span>
                    </div>
                </div>
                <div class="miro-logo-wall__visual" aria-hidden="true">
                    <span class="miro-logo-wall__sticker miro-logo-wall__sticker--brand"><span data-lang="ru">Связи, которые работают</span><span data-lang="en">Connections that move business</span><span data-lang="ro">Conexiuni care dezvoltă afaceri</span></span>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="features">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Возможности</span><span data-lang="en">What you can do</span><span data-lang="ro">Ce poți face</span></p>
                    <h2><span data-lang="ru">От идеи к следующему действию</span><span data-lang="en">From an idea to the next action</span><span data-lang="ro">De la idee la următoarea acțiune</span></h2>
                    <p><span data-lang="ru">Платформа помогает не просто хранить информацию, а превращать её в движение.</span><span data-lang="en">The platform turns information into momentum, not just another place to store it.</span><span data-lang="ro">Platforma transformă informația în mișcare, nu doar într-un alt loc de stocare.</span></p>
                </div>
                <div class="miro-split">
                    <div class="miro-split__copy">
                        <span class="miro-tag"><span data-lang="ru">AI-powered</span><span data-lang="en">AI-powered</span><span data-lang="ro">Cu AI</span></span>
                        <h3 style="margin-top: 18px;"><span data-lang="ru">Найдите контакт, который нужен именно сейчас</span><span data-lang="en">Find the connection you need right now</span><span data-lang="ro">Găsește conexiunea de care ai nevoie acum</span></h3>
                        <p><span data-lang="ru">Опишите свой запрос обычными словами. AI сопоставит его с профилями участниц и покажет близкие варианты.</span><span data-lang="en">Describe your request in your own words. AI matches it with member profiles and surfaces relevant options.</span><span data-lang="ro">Descrie cererea în cuvintele tale. AI o potrivește cu profilurile membrelor.</span></p>
                        <ul class="miro-list">
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Поиск партнёров, экспертов и клиентов</span><span data-lang="en">Find partners, experts and clients</span><span data-lang="ro">Găsește partenere, experte și clienți</span></li>
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Рекомендации на основе профиля</span><span data-lang="en">Recommendations based on your profile</span><span data-lang="ro">Recomandări bazate pe profil</span></li>
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Прямой контакт через Telegram</span><span data-lang="en">Direct contact through Telegram</span><span data-lang="ro">Contact direct prin Telegram</span></li>
                        </ul>
                    </div>
                    <div class="miro-mockup">
                        <div class="miro-mockup__bar"><i></i><i></i><i></i></div>
                        <div class="miro-mockup__body miro-mockup__body--ai">
                            <div class="miro-ai-card">
                                <span class="miro-ai-card__tag">AI match</span>
                                <h4>Export partner</h4>
                                <p>Three relevant profiles found in your community.</p>
                                <div class="miro-ai-card__meter"><span></span></div>
                                <p style="margin-top: 8px; color: var(--miro-blue); font-weight: 500;">86% relevance</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="miro-split miro-split--reverse" style="margin-top: 96px;">
                    <div class="miro-split__copy">
                        <span class="miro-tag" style="background: var(--miro-surface-featured); color: var(--miro-blue);"><span data-lang="ru">Shared workspace</span><span data-lang="en">Shared workspace</span><span data-lang="ro">Spațiu comun</span></span>
                        <h3 style="margin-top: 18px;"><span data-lang="ru">Публикуйте возможности, а не только новости</span><span data-lang="en">Share opportunities, not just updates</span><span data-lang="ro">Distribuie oportunități, nu doar noutăți</span></h3>
                        <p><span data-lang="ru">Проект, встреча, событие или запрос на сотрудничество — публикация сразу попадает в общий поток и Telegram-уведомления участниц.</span><span data-lang="en">A project, meeting, event or collaboration request reaches the shared feed and Telegram notifications.</span><span data-lang="ro">Un proiect, o întâlnire, un eveniment sau o cerere de colaborare ajunge în fluxul comun și în Telegram.</span></p>
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--primary" style="margin-top: 28px;"><span data-lang="ru">Открыть кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Deschide cabinetul</span></a>
                    </div>
                    <div class="miro-mockup">
                        <div class="miro-mockup__bar"><i></i><i></i><i></i></div>
                        <div class="miro-mockup__body">
                            <div class="miro-roadmap">
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Need</span><div class="miro-roadmap__cell is-pink">Find a mentor</div><div class="miro-roadmap__cell">New market</div><div class="miro-roadmap__cell">Local partner</div></div>
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Action</span><div class="miro-roadmap__cell is-blue">Workshop · 14 Jun</div><div class="miro-roadmap__cell">Ask the community</div><div class="miro-roadmap__cell is-pink">Post an opportunity</div></div>
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Result</span><div class="miro-roadmap__cell">New contact</div><div class="miro-roadmap__cell is-blue">Shared learning</div><div class="miro-roadmap__cell">Next step</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section" id="learning">
            <div class="miro-container">
                <div class="miro-split">
                    <div class="miro-image-card">
                        <img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/story-mentor.webp') }}" alt="Mentoring conversation" loading="lazy">
                        <div class="miro-image-card__caption">
                            <span class="miro-tag"><span data-lang="ru">Learning &amp; mentoring</span><span data-lang="en">Learning &amp; mentoring</span><span data-lang="ro">Învățare și mentorat</span></span>
                            <p><span data-lang="ru">Знания становятся полезнее, когда их можно сразу обсудить и применить.</span><span data-lang="en">Knowledge becomes more useful when you can discuss and apply it right away.</span><span data-lang="ro">Cunoștințele devin mai utile când le poți discuta și aplica imediat.</span></p>
                        </div>
                    </div>
                    <div class="miro-split__copy">
                        <p class="miro-eyebrow"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></p>
                        <h2><span data-lang="ru">Учитесь в своём темпе, растите рядом с другими</span><span data-lang="en">Learn at your pace, grow with others</span><span data-lang="ro">Învață în ritmul tău, crește alături de comunitate</span></h2>
                        <p><span data-lang="ru">Материалы, практические встречи, менторство и опыт других предпринимательниц помогают переходить от вопроса к решению.</span><span data-lang="en">Materials, practical sessions, mentoring and peer experience help you move from a question to a solution.</span><span data-lang="ro">Materiale, sesiuni practice, mentorat și experiența comunității te ajută să ajungi de la întrebare la soluție.</span></p>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--pink" style="margin-top: 28px;"><span data-lang="ru">Присоединиться к сообществу</span><span data-lang="en">Join the community</span><span data-lang="ro">Alătură-te comunității</span></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="stories">
            <div class="miro-container">
                <div class="miro-story">
                    <img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/story-export.webp') }}" alt="Women entrepreneurs collaborating" loading="lazy">
                    <div class="miro-story__body">
                        <span class="miro-tag" style="width: fit-content; background: var(--miro-pink); color: var(--miro-primary);">Member story</span>
                        <blockquote><span data-lang="ru">«Нужный контакт оказался не где-то далеко — он уже был внутри сообщества.»</span><span data-lang="en">“The right connection was not far away — it was already inside the community.”</span><span data-lang="ro">„Conexiunea potrivită nu era departe — era deja în comunitate.”</span></blockquote>
                        <p>Women Entrepreneurs Platform member</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section">
            <div class="miro-container">
                <div class="miro-cta">
                    <h2><span data-lang="ru">Готовы собрать свой следующий шаг?</span><span data-lang="en">Ready to bring your next step together?</span><span data-lang="ro">Ești gata să construiești următorul pas?</span></h2>
                    <p><span data-lang="ru">Присоединяйтесь к платформе через Telegram и начните с простого профиля.</span><span data-lang="en">Join through Telegram and start with a simple profile.</span><span data-lang="ro">Alătură-te prin Telegram și începe cu un profil simplu.</span></p>
                    <div class="miro-hero__actions">
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--on-dark"><span data-lang="ru">Начать в Telegram</span><span data-lang="en">Start in Telegram</span><span data-lang="ro">Începe în Telegram</span></a>
                        <a href="{{ route('account.login') }}" class="miro-button" style="border: 1px solid rgba(255,255,255,.35); color: #fff;"><span data-lang="ru">У меня уже есть доступ</span><span data-lang="en">I already have access</span><span data-lang="ro">Am deja acces</span></a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')
</body>
</html>
