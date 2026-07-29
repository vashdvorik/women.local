(function () {
        var dropdown = document.querySelector('[data-media-dropdown]');
        if (!dropdown) return;
        var trigger = dropdown.querySelector('.miro-nav__dropdown-trigger');
        function setOpen(open) {
            dropdown.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            setOpen(!dropdown.classList.contains('is-open'));
        });
        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target)) setOpen(false);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { setOpen(false); trigger.focus(); }
        });
    })();
