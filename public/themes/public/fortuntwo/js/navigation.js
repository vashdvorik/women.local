(function () {
    var dropdowns = Array.prototype.slice.call(document.querySelectorAll('[data-nav-dropdown]'));
    var mobileToggle = document.getElementById('miro-mobile-toggle');
    var nav = document.getElementById('miro-nav');

    function closeDropdown(dropdown) {
        var trigger = dropdown.querySelector('.miro-nav__dropdown-trigger');
        dropdown.classList.remove('is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    function openDropdown(dropdown) {
        dropdowns.forEach(function (item) {
            if (item !== dropdown) closeDropdown(item);
        });
        var trigger = dropdown.querySelector('.miro-nav__dropdown-trigger');
        dropdown.classList.add('is-open');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
    }

    dropdowns.forEach(function (dropdown) {
        var trigger = dropdown.querySelector('.miro-nav__dropdown-trigger');
        if (!trigger) return;
        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            if (dropdown.classList.contains('is-open')) closeDropdown(dropdown);
            else openDropdown(dropdown);
        });
    });

    document.addEventListener('click', function (event) {
        dropdowns.forEach(function (dropdown) {
            if (!dropdown.contains(event.target)) closeDropdown(dropdown);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        dropdowns.forEach(closeDropdown);
    });

    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            mobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
})();
