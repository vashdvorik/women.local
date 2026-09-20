{{-- Форма подписки на новости (в подвале сайта). Сообщения — на трёх языках в самой вёрстке;
     контроллер только ставит признак успеха или ошибки (см. SubscribeController). --}}
<div class="miro-subscribe" id="subscribe">
    <div class="miro-subscribe__copy">
        <h4><span data-lang="ru">Новости платформы</span><span data-lang="en">Platform news</span><span data-lang="ro">Noutățile platformei</span></h4>
        <p><span data-lang="ru">Раз в месяц — события, возможности и истории участниц.</span><span data-lang="en">Once a month: events, opportunities and members’ stories.</span><span data-lang="ro">O dată pe lună: evenimente, oportunități și povești ale participantelor.</span></p>
    </div>

    @if(session('subscribed'))
        <p class="miro-subscribe__notice" role="status"><span data-lang="ru">Спасибо! Вы подписаны на новости.</span><span data-lang="en">Thank you! You are subscribed.</span><span data-lang="ro">Mulțumim! Te-ai abonat.</span></p>
    @else
        <form method="POST" action="{{ route('subscribe') }}" class="miro-subscribe__form">
            @csrf
            {{-- Приманка для ботов: человек это поле не видит и не заполняет. --}}
            <div class="miro-subscribe__trap" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="miro-subscribe__row">
                <label class="miro-subscribe__field">
                    <span class="miro-subscribe__label"><span data-lang="ru">Ваша почта</span><span data-lang="en">Your email</span><span data-lang="ro">Adresa de email</span></span>
                    <input type="email" name="email" value="{{ old('email') }}" required maxlength="191" autocomplete="email" placeholder="name@example.com">
                </label>
                <button type="submit" class="miro-button miro-button--primary"><span data-lang="ru">Подписаться</span><span data-lang="en">Subscribe</span><span data-lang="ro">Abonează-te</span></button>
            </div>
            <label class="miro-subscribe__consent">
                <input type="checkbox" name="consent" value="1" required>
                <span><span data-lang="ru">Согласна получать новости платформы на почту</span><span data-lang="en">I agree to receive platform news by email</span><span data-lang="ro">Sunt de acord să primesc noutăți pe email</span></span>
            </label>
            @if(session('subscribe_error'))
                <p class="miro-subscribe__error" role="alert"><span data-lang="ru">Проверьте адрес почты и отметьте согласие.</span><span data-lang="en">Please check the email address and tick the consent box.</span><span data-lang="ro">Verifică adresa de email și bifează acordul.</span></p>
            @endif
        </form>
    @endif
</div>
