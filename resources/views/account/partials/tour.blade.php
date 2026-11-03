{{--
    Подсказки при первом входе: карточки с кнопками «Далее» / «Назад» / «Закрыть». На компьютере карточка стоит рядом с
    разделом меню, который объясняет (раздел подсвечен), на телефоне меню спрятано, поэтому карточка по центру экрана.
    Начинаются с поздравления с пакетом. Показываются сами, пока участница не дошла до конца или не нажала «Закрыть» на этом
    тарифе; после этого POST /tour-seen запоминает это в базе (BotUser::markSeen('tour:тариф')). При переходе на тариф выше
    тур покажется снова, но только с новыми разделами. Кнопка на главной открывает полный тур ($replaySteps, без поздравления).
    Переменные: $tourSteps, $replaySteps (App\Support\CabinetTour::steps), $autoStart.
--}}
<div id="cabinet-tour" class="tour" hidden>
    <div class="tour__spot" aria-hidden="true"></div>
    <div class="tour__card" role="dialog" aria-modal="true" aria-labelledby="tour-title" tabindex="-1">
        <p class="tour__progress" data-tour-progress></p>
        <h2 id="tour-title" class="tour__title" data-tour-title></h2>
        <p class="tour__text" data-tour-text></p>
        <p class="tour__how" data-tour-how></p>
        <div class="tour__actions">
            <button type="button" class="tour__close" data-tour-close>{{ __('account.tour.close') }}</button>
            <span class="tour__nav">
                <button type="button" class="tour__back" data-tour-back>{{ __('account.tour.back') }}</button>
                <button type="button" class="tour__next" data-tour-next></button>
            </span>
        </div>
    </div>
    <form method="POST" action="{{ route('account.tour.seen') }}" data-tour-seen hidden>@csrf</form>
</div>

<script>
(function () {
    var autoSteps = @json($tourSteps);
    var replaySteps = @json($replaySteps);
    var steps = autoSteps;
    var autoStart = @json((bool) $autoStart);
    var labels = { next: @json(__('account.tour.next')), done: @json(__('account.tour.done')), progress: @json(__('account.tour.progress')), how: @json(__('account.home.tile_how')) };
    var root = document.getElementById('cabinet-tour');
    if (!root || (!autoSteps.length && !replaySteps.length)) return;

    var spot = root.querySelector('.tour__spot');
    var card = root.querySelector('.tour__card');
    var title = root.querySelector('[data-tour-title]');
    var text = root.querySelector('[data-tour-text]');
    var how = root.querySelector('[data-tour-how]');
    var progress = root.querySelector('[data-tour-progress]');
    var back = root.querySelector('[data-tour-back]');
    var next = root.querySelector('[data-tour-next]');
    var index = 0;
    var saved = false;

    // Раздел меню видно только на широком экране: на телефоне карточка по центру без подсветки
    function targetOf(step) {
        if (!step.target || window.innerWidth < 1024) return null;
        var el = document.querySelector(step.target);
        if (!el) return null;
        // Раздел может лежать в свёрнутой группе меню «Ещё»: раскрываем её, чтобы его было видно
        var group = el.closest('details');
        if (group && !group.open) group.open = true;
        var box = el.getBoundingClientRect();
        return box.width > 0 && box.height > 0 ? el : null;
    }

    // Карточка стоит там, где она помещается целиком: справа от элемента, иначе слева, иначе под ним, иначе над ним.
    // Возвращает false, если места рядом с элементом нет нигде (широкий элемент на низком экране): тогда карточка прижата
    // к низу и может его закрыть, и вызывающий подтягивает элемент к верху экрана и расставляет заново. Всегда внутри окна.
    function place(box, pad) {
        var fits = true;
        var gap = 16;
        var margin = 16;
        var w = card.offsetWidth;
        var h = card.offsetHeight;
        var vw = window.innerWidth;
        var vh = window.innerHeight;
        var clampX = function (x) { return Math.max(margin, Math.min(vw - w - margin, x)); };
        var clampY = function (y) { return Math.max(margin, Math.min(vh - h - margin, y)); };
        var middleY = box.top + box.height / 2 - h / 2;
        var middleX = box.left + box.width / 2 - w / 2;
        var left;
        var top;

        if (box.right + pad + gap + w + margin <= vw) {
            left = box.right + pad + gap; top = clampY(middleY);
        } else if (box.left - pad - gap - w - margin >= 0) {
            left = box.left - pad - gap - w; top = clampY(middleY);
        } else if (box.bottom + pad + gap + h + margin <= vh) {
            left = clampX(middleX); top = box.bottom + pad + gap;
        } else if (box.top - pad - gap - h - margin >= 0) {
            left = clampX(middleX); top = box.top - pad - gap - h;
        } else {
            left = clampX(middleX); top = vh - h - margin;
            fits = false;
        }

        card.style.left = left + 'px';
        card.style.top = top + 'px';

        return fits;
    }

    // Подсветка вокруг элемента
    function frame(el, pad) {
        var box = el.getBoundingClientRect();
        spot.hidden = false;
        spot.style.top = (box.top - pad) + 'px';
        spot.style.left = (box.left - pad) + 'px';
        spot.style.width = (box.width + pad * 2) + 'px';
        spot.style.height = (box.height + pad * 2) + 'px';

        return box;
    }

    function show(n) {
        index = Math.max(0, Math.min(steps.length - 1, n));
        var step = steps[index];

        title.textContent = step.title;
        text.textContent = step.text;
        how.textContent = step.how ? labels.how + ' ' + step.how : '';
        how.hidden = !step.how;
        progress.textContent = labels.progress.replace(':n', index + 1).replace(':total', steps.length);
        back.hidden = index === 0;
        next.textContent = index === steps.length - 1 ? labels.done : labels.next;

        var el = targetOf(step);
        if (el) {
            var pad = 6;
            el.scrollIntoView({ block: 'nearest' });
            root.classList.remove('tour--center');

            if (!place(frame(el, pad), pad)) {
                // Рядом места нет: подтягиваем элемент к верху экрана, чтобы карточка встала под ним и ничего не закрыла
                el.scrollIntoView({ block: 'start' });
                place(frame(el, pad), pad);
            }
        } else {
            spot.hidden = true;
            root.classList.add('tour--center');
            card.style.left = '';
            card.style.top = '';
        }

        next.focus({ preventScroll: true });
    }

    function open(set) {
        steps = set;
        root.hidden = false;
        show(0);
    }

    // Закрыли или дошли до конца: запоминаем один раз, чтобы подсказки сами больше не появлялись
    function close() {
        root.hidden = true;
        if (saved) return;
        saved = true;
        var form = root.querySelector('[data-tour-seen]');
        try { fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }); } catch (e) {}
    }

    next.addEventListener('click', function () { index === steps.length - 1 ? close() : show(index + 1); });
    back.addEventListener('click', function () { show(index - 1); });
    root.querySelector('[data-tour-close]').addEventListener('click', close);
    document.querySelectorAll('[data-tour-start]').forEach(function (button) { button.addEventListener('click', function () { open(replaySteps); }); });

    document.addEventListener('keydown', function (event) {
        if (root.hidden) return;
        if (event.key === 'Escape') close();
        else if (event.key === 'ArrowRight') next.click();
        else if (event.key === 'ArrowLeft' && index > 0) show(index - 1);
    });
    window.addEventListener('resize', function () { if (!root.hidden) show(index); });

    if (autoStart && autoSteps.length) {
        // После оплаты поверх главной стоит окно «Оплата прошла»: подсказки начинаются, когда его закроют.
        var payPopup = document.querySelector('[data-payment-popup]');
        if (payPopup && !payPopup.hidden) {
            document.addEventListener('payment-popup:closed', function () { window.setTimeout(function () { open(autoSteps); }, 300); }, { once: true });
        } else {
            window.setTimeout(function () { open(autoSteps); }, 500);
        }
    }
})();
</script>
