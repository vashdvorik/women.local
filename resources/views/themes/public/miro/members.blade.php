@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);

    // Полный список участниц лежит в resources/data/participants.php: его же выводит карусель на главной.
    $participants = \App\Support\Participants::all();

    $locales = ['ru', 'en', 'ro'];

    // The full roster is expected to grow toward ~500 people, so only the first page is
    // rendered server-side; the rest ships as JSON and is appended client-side in batches
    // by public/themes/public/miro/js/members.js when "Показать ещё" is clicked.
    $participantsPageSize = 24;
    $participantsVisible = array_slice($participants, 0, $participantsPageSize);
    $participantsRemaining = array_slice($participants, $participantsPageSize);
    $participantsTotal = count($participants);
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform — Participants</title>
    <link rel="icon" type="image/png" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/brand/favicon.png') }}">
    <meta name="description" content="Public directory of participants of Women Entrepreneurs Platform.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Prata&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/members.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/css/navigation.css') }}">
</head>
<body>
    @include('themes.public.miro.partials.miro-header', ['miroCurrentPage' => 'members'])

    <main class="miro-members-page">

        <section class="miro-members-section">
            <div class="miro-container">
                <div class="miro-section__head">
                    <h2><span data-lang="ru">Участницы платформы</span><span data-lang="en">Platform participants</span><span data-lang="ro">Participantele platformei</span></h2>
                    <p><span data-lang="ru">Предпринимательницы, которые уже развивают собственное дело при поддержке платформы — от пищевого производства до туризма и ремёсел.</span><span data-lang="en">Entrepreneurs already growing their businesses with the platform’s support — from food production to tourism and crafts.</span><span data-lang="ro">Antreprenoare care își dezvoltă deja afacerea cu sprijinul platformei — de la producție alimentară la turism și meșteșuguri.</span></p>
                </div>

                <div class="miro-participants-grid" id="miro-participants-grid" data-image-base="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images') }}/" data-page-size="{{ $participantsPageSize }}">
                    @foreach($participantsVisible as $participant)
                        <article class="miro-participant-card">
                            @if($participant['photo'])
                                <img class="miro-participant-card__avatar" src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/images/' . $participant['photo']) }}" alt="{{ $participant['name']['en'] }}" loading="lazy">
                            @else
                                @php
                                    $initialsSource = preg_split('/\s+/u', trim($participant['name']['ru']));
                                    $initials = mb_strtoupper(mb_substr($initialsSource[0] ?? '', 0, 1) . mb_substr($initialsSource[1] ?? '', 0, 1));
                                @endphp
                                <span class="miro-participant-card__avatar miro-participant-card__avatar--placeholder" aria-hidden="true">{{ $initials }}</span>
                            @endif
                            <div class="miro-participant-card__body">
                                <span class="miro-participant-card__tag">@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $participant['tag'][$locale] }}</span>@endforeach</span>
                                <h3>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $participant['name'][$locale] }}</span>@endforeach</h3>
                                <p>@foreach($locales as $locale)<span data-lang="{{ $locale }}">{{ $participant['summary'][$locale] }}</span>@endforeach</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if(count($participantsRemaining) > 0)
                    <p class="miro-participants-count" id="miro-participants-count">
                        <span data-lang="ru" data-template="Показано {shown} из {total}">Показано {{ count($participantsVisible) }} из {{ $participantsTotal }}</span><span data-lang="en" data-template="Showing {shown} of {total}">Showing {{ count($participantsVisible) }} of {{ $participantsTotal }}</span><span data-lang="ro" data-template="Se afișează {shown} din {total}">Se afișează {{ count($participantsVisible) }} din {{ $participantsTotal }}</span>
                    </p>
                    <div class="miro-participants-loadmore">
                        <button type="button" id="miro-participants-loadmore-btn" class="miro-button miro-button--secondary">
                            <span data-lang="ru">Показать ещё</span><span data-lang="en">Show more</span><span data-lang="ro">Arată mai mult</span>
                        </button>
                    </div>
                    {{-- Consumed by members.js — kept out of the visible DOM so the browser
                         never requests these participants' photos until they're revealed. --}}
                    <script type="application/json" id="miro-participants-remaining">{!! json_encode($participantsRemaining, JSON_UNESCAPED_UNICODE) !!}</script>
                @endif

                <section class="miro-members-cta">
                    <h2><span data-lang="ru">Зарегистрируйтесь, чтобы связаться</span><span data-lang="en">Register to make the connection</span><span data-lang="ro">Înregistrează-te pentru a lua legătura</span></h2>
                    <p><span data-lang="ru">Создайте профиль на платформе, чтобы находить нужных людей и обращаться к ним напрямую.</span><span data-lang="en">Create your platform profile to find the right people and reach out directly.</span><span data-lang="ro">Creează-ți profilul pentru a găsi oamenii potriviți și a lua legătura direct.</span></p>
                    <div class="miro-members-cta__actions">
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--pink"><span data-lang="ru">Войти в кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Intră în cabinet</span></a>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button" style="border:1px solid rgba(255,255,255,.35);color:#fff"><span data-lang="ru">Присоединиться через Telegram</span><span data-lang="en">Join via Telegram</span><span data-lang="ro">Alătură-te prin Telegram</span></a>
                    </div>
                </section>
            </div>
        </section>
    </main>

    @include('themes.public.miro.partials.miro-footer')
    <script defer src="{{ asset('themes/public/' . ($publicTheme ?? 'miro') . '/js/members.js') }}"></script>
</body>
</html>
