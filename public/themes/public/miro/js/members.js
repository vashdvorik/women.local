(function () {
    // Progressive "Показать ещё" pagination for the participants grid. The first page is
    // rendered server-side (members.blade.php); everything after it ships as JSON in a
    // hidden <script> tag and is appended here in batches, so photos for cards the visitor
    // hasn't reached yet are never requested. Built to scale toward ~500 participants
    // without needing a backend endpoint.
    var grid = document.getElementById('miro-participants-grid');
    var dataEl = document.getElementById('miro-participants-remaining');
    var loadMoreBtn = document.getElementById('miro-participants-loadmore-btn');
    var countEl = document.getElementById('miro-participants-count');
    if (!grid || !dataEl || !loadMoreBtn) return;

    var remaining = [];
    try {
        remaining = JSON.parse(dataEl.textContent || '[]');
    } catch (e) {
        remaining = [];
    }

    var imageBase = grid.getAttribute('data-image-base') || '';
    var pageSize = parseInt(grid.getAttribute('data-page-size'), 10) || 24;
    var locales = ['ru', 'en', 'ro'];
    var shown = grid.querySelectorAll('.miro-participant-card').length;
    var total = shown + remaining.length;

    function buildLocaleGroup(parent, values) {
        locales.forEach(function (locale) {
            var span = document.createElement('span');
            span.setAttribute('data-lang', locale);
            span.textContent = (values && values[locale]) || '';
            parent.appendChild(span);
        });
    }

    function initials(nameRu) {
        var parts = (nameRu || '').trim().split(/\s+/);
        var a = (parts[0] || '').charAt(0);
        var b = (parts[1] || '').charAt(0);
        return (a + b).toUpperCase();
    }

    function buildCard(participant) {
        var article = document.createElement('article');
        article.className = 'miro-participant-card miro-participant-card--enter';

        if (participant.photo) {
            var img = document.createElement('img');
            img.className = 'miro-participant-card__avatar';
            img.src = imageBase + participant.photo;
            img.alt = (participant.name && participant.name.en) || '';
            img.loading = 'lazy';
            article.appendChild(img);
        } else {
            var placeholder = document.createElement('span');
            placeholder.className = 'miro-participant-card__avatar miro-participant-card__avatar--placeholder';
            placeholder.setAttribute('aria-hidden', 'true');
            placeholder.textContent = initials(participant.name && participant.name.ru);
            article.appendChild(placeholder);
        }

        var body = document.createElement('div');
        body.className = 'miro-participant-card__body';

        var tag = document.createElement('span');
        tag.className = 'miro-participant-card__tag';
        buildLocaleGroup(tag, participant.tag);
        body.appendChild(tag);

        var h3 = document.createElement('h3');
        buildLocaleGroup(h3, participant.name);
        body.appendChild(h3);

        var p = document.createElement('p');
        buildLocaleGroup(p, participant.summary);
        body.appendChild(p);

        article.appendChild(body);
        return article;
    }

    function updateCount() {
        if (!countEl) return;
        countEl.querySelectorAll('[data-template]').forEach(function (span) {
            var template = span.getAttribute('data-template') || '';
            span.textContent = template.replace('{shown}', shown).replace('{total}', total);
        });
    }

    function loadNextPage() {
        var batch = remaining.splice(0, pageSize);
        var fragment = document.createDocumentFragment();
        batch.forEach(function (participant) {
            fragment.appendChild(buildCard(participant));
        });
        grid.appendChild(fragment);
        shown += batch.length;
        updateCount();

        // Let the freshly-inserted cards paint in their "entering" state once, then drop
        // the class on the next frame so the CSS transition actually animates.
        requestAnimationFrame(function () {
            grid.querySelectorAll('.miro-participant-card--enter').forEach(function (card) {
                card.classList.remove('miro-participant-card--enter');
            });
        });

        if (remaining.length === 0) {
            var wrapper = loadMoreBtn.closest('.miro-participants-loadmore');
            if (wrapper) wrapper.style.display = 'none';
        }
    }

    loadMoreBtn.addEventListener('click', loadNextPage);
})();
