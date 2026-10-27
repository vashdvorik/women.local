@php
    $botUrl = route('account.register');
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
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')

</body>
</html>


