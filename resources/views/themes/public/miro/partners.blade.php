@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $partnerGroups = [
        [
            'tone' => 'pink',
            'title' => ['ru' => 'Координаторы платформы', 'en' => 'Platform coordinators', 'ro' => 'Coordonatorii platformei'],
            'description' => ['ru' => 'Организации, которые помогают развивать платформу и объединять женское предпринимательское сообщество.', 'en' => 'Organisations helping to develop the platform and bring the women’s business community together.', 'ro' => 'Organizații care contribuie la dezvoltarea platformei și la reunirea comunității femeilor antreprenoare.'],
            'partners' => [
                ['image' => 'coordinator-ida.png', 'url' => 'https://innovation.md/', 'name' => ['ru' => 'Агентство инноваций и развития', 'en' => 'Agency for Innovation and Development', 'ro' => 'Agenția pentru Inovare și Dezvoltare']],
                ['image' => 'coordinator-creative.png', 'url' => 'https://creativity.md/', 'name' => ['ru' => 'Ассоциация креативных индустрий Приднестровья', 'en' => 'Association of Creative Industries of Transnistria', 'ro' => 'Asociația Industriilor Creative din Transnistria']],
                ['image' => 'coordinator-platform.png', 'url' => 'https://social.innovation.md/', 'name' => ['ru' => 'Платформа социального предпринимательства', 'en' => 'Social Entrepreneurship Platform', 'ro' => 'Platforma antreprenoriatului social']],
            ],
        ],
        [
            'tone' => 'teal',
            'title' => ['ru' => 'Местные партнёры', 'en' => 'Local partners', 'ro' => 'Parteneri locali'],
            'description' => ['ru' => 'Профессиональные сообщества и центры, с которыми мы создаём новые возможности для участниц.', 'en' => 'Professional communities and hubs creating new opportunities for members.', 'ro' => 'Comunități profesionale și huburi care creează oportunități noi pentru membre.'],
            'partners' => [
                ['image' => 'local-eba.png', 'url' => 'https://eba.md/', 'name' => ['ru' => 'European Business Association Moldova', 'en' => 'European Business Association Moldova', 'ro' => 'European Business Association Moldova']],
                ['image' => 'local-afam.png', 'url' => 'https://afam.md/', 'name' => ['ru' => 'AFAM', 'en' => 'AFAM', 'ro' => 'AFAM']],
                ['image' => 'local-glia.png', 'url' => 'https://glia.md/', 'name' => ['ru' => 'Glia Impact Hub', 'en' => 'Glia Impact Hub', 'ro' => 'Glia Impact Hub']],
                ['image' => 'local-progen.png', 'url' => 'https://progen.md/', 'name' => ['ru' => 'Centrul Parteneriat pentru Dezvoltare', 'en' => 'Centrul Parteneriat pentru Dezvoltare', 'ro' => 'Centrul Parteneriat pentru Dezvoltare']],
            ],
        ],
        [
            'tone' => 'coral',
            'title' => ['ru' => 'Международные партнёры', 'en' => 'International partners', 'ro' => 'Parteneri internaționali'],
            'description' => ['ru' => 'Международные организации, поддерживающие развитие, устойчивость и расширение возможностей сообщества.', 'en' => 'International organisations supporting the community’s development, resilience and opportunities.', 'ro' => 'Organizații internaționale care susțin dezvoltarea, reziliența și oportunitățile comunității.'],
            'partners' => [
                ['image' => 'intl-netherlands.png', 'url' => 'https://www.netherlandsandyou.nl/web/moldova', 'name' => ['ru' => 'Королевство Нидерландов', 'en' => 'Kingdom of the Netherlands', 'ro' => 'Regatul Țărilor de Jos']],
                ['image' => 'intl-nrc.png', 'url' => 'https://www.nrc.no/moldova', 'name' => ['ru' => 'Норвежский совет по делам беженцев', 'en' => 'Norwegian Refugee Council', 'ro' => 'Consiliul Norvegian pentru Refugiați']],
                ['image' => 'intl-unwomen.png', 'url' => 'https://moldova.unwomen.org/', 'name' => ['ru' => 'ООН-женщины', 'en' => 'UN Women', 'ro' => 'ONU Femei']],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform — Partners</title>
    <meta name="description" content="Партнёры Women Entrepreneurs Platform — организации и сообщества, которые поддерживают развитие женского предпринимательства.">
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/partners.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'partners'])

    <main class="miro-partners-page">
        <section class="miro-partners-section">
            <div class="miro-container">
                @foreach ($partnerGroups as $group)
                    <section class="miro-partners-group">
                        <div class="miro-partners-group__head">
                            <div class="miro-partners-group__heading">
                                <h2><span data-lang="ru">{{ $group['title']['ru'] }}</span><span data-lang="en">{{ $group['title']['en'] }}</span><span data-lang="ro">{{ $group['title']['ro'] }}</span></h2>
                                <p><span data-lang="ru">{{ $group['description']['ru'] }}</span><span data-lang="en">{{ $group['description']['en'] }}</span><span data-lang="ro">{{ $group['description']['ro'] }}</span></p>
                            </div>
                            <span class="miro-partners-group__count">{{ count($group['partners']) }} <span data-lang="ru">партнёра</span><span data-lang="en">partners</span><span data-lang="ro">parteneri</span></span>
                        </div>
                        <div class="miro-partners-grid">
                            @foreach ($group['partners'] as $partner)
                                <article class="miro-partner-card miro-partner-card--{{ $group['tone'] }}">
                                    <div class="miro-partner-card__logo"><img src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/' . $partner['image']) }}" alt="{{ $partner['name']['en'] }}" loading="lazy"></div>
                                    <div class="miro-partner-card__bottom">
                                        <h3><span data-lang="ru">{{ $partner['name']['ru'] }}</span><span data-lang="en">{{ $partner['name']['en'] }}</span><span data-lang="ro">{{ $partner['name']['ro'] }}</span></h3>
                                        <a href="{{ $partner['url'] }}" target="_blank" rel="noopener noreferrer" class="miro-partner-link"><span data-lang="ru">Сайт</span><span data-lang="en">Website</span><span data-lang="ro">Site</span><span aria-hidden="true">↗</span></a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="miro-partners-cta">
                    <h2><span data-lang="ru">Создавать возможности вместе</span><span data-lang="en">Create opportunities together</span><span data-lang="ro">Creăm oportunități împreună</span></h2>
                    <p><span data-lang="ru">Если вы хотите поддержать женское предпринимательство или предложить участницам новую возможность, давайте познакомимся.</span><span data-lang="en">If you want to support women’s entrepreneurship or offer members a new opportunity, let’s connect.</span><span data-lang="ro">Dacă vrei să susții antreprenoriatul feminin sau să oferi membrelor o oportunitate nouă, hai să ne cunoaștem.</span></p>
                    <div class="miro-partners-cta__actions"><a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary" style="background:var(--miro-pink);color:var(--miro-primary)"><span data-lang="ru">Стать партнёром</span><span data-lang="en">Become a partner</span><span data-lang="ro">Devino partener</span></a><a href="{{ route('about') }}" class="miro-button miro-button--secondary" style="border-color:rgba(255,255,255,.35);color:#fff"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></a></div>
                </section>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')

    <script src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/js/partners.js') }}"></script>
</body>
</html>


