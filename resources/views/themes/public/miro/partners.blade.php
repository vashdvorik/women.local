@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
    $partnerGroups = \App\Support\PartnerLogos::groups();
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women's hub — Partners</title>
    <meta name="description" content="Партнёры Women's hub — организации и сообщества, которые поддерживают развитие женского предпринимательства.">
    <link rel="icon" type="image/png" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon-w4w.png') }}">
    <link rel="apple-touch-icon" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/css/partners.css') }}">
    <link rel="stylesheet" href="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'partners'])

    <main class="miro-partners-page">
        <section class="miro-partners-section">
            <div class="miro-container">
                {{-- Знаки без подписей и ссылок: данные и размеры — App\Support\PartnerLogos (те же, что на главной). --}}
                @foreach ($partnerGroups as $index => $group)
                    <section class="miro-partners-group">
                        <div class="miro-partners-group__head">
                            <div class="miro-partners-group__heading">
                                <h2><span data-lang="ru">{{ $group['title']['ru'] }}</span><span data-lang="en">{{ $group['title']['en'] }}</span><span data-lang="ro">{{ $group['title']['ro'] }}</span></h2>
                                <p><span data-lang="ru">{{ $group['description']['ru'] }}</span><span data-lang="en">{{ $group['description']['en'] }}</span><span data-lang="ro">{{ $group['description']['ro'] }}</span></p>
                            </div>
                        </div>
                        <div class="miro-partners-grid">
                            @foreach ($group['logos'] as $logo)
                                <article class="miro-partner-card miro-partner-card--{{ $index === 0 ? 'coral' : 'pink' }}">
                                    <div class="miro-partner-card__logo"><img src="{{ asset_v('themes/public/' . ($publicTheme ?? 'miro') . '/images/partners/home/' . $logo['file'] . '.webp') }}" alt="{{ $logo['alt'] }}" width="{{ \App\Support\PartnerLogos::width($logo['ratio'], 12000, 190) }}" loading="lazy"></div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
                <section class="miro-partners-cta">
                    <h2><span data-lang="ru">Создавать возможности вместе</span><span data-lang="en">Create opportunities together</span><span data-lang="ro">Creăm oportunități împreună</span></h2>
                    <p><span data-lang="ru">Если вы хотите поддержать женское предпринимательство или предложить участницам новую возможность, давайте познакомимся.</span><span data-lang="en">If you want to support women’s entrepreneurship or offer members a new opportunity, let’s connect.</span><span data-lang="ro">Dacă vrei să susții antreprenoriatul feminin sau să oferi membrelor o oportunitate nouă, hai să ne cunoaștem.</span></p>
                    <div class="miro-partners-cta__actions"><a href="{{ $managerUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--pink"><span data-lang="ru">Стать партнёром</span><span data-lang="en">Become a partner</span><span data-lang="ro">Devino partener</span></a><a href="{{ route('about') }}" class="miro-button miro-button--secondary" style="border-color:rgba(255,255,255,.35);color:#fff"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></a></div>
                </section>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')

</body>
</html>


