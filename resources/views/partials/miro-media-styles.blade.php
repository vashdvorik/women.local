<style>
    .miro-nav__dropdown { position: relative; display: flex; align-items: center; }
    .miro-nav__dropdown-trigger { display: inline-flex; align-items: center; gap: 7px; padding: 0; border: 0; background: transparent; color: inherit; cursor: pointer; font: inherit; }
    .miro-nav__dropdown-trigger::after { content: '⌄'; margin-top: -3px; font-size: 14px; line-height: 1; transition: transform .18s ease; }
    .miro-nav__dropdown.is-open .miro-nav__dropdown-trigger::after { transform: rotate(180deg); }
    .miro-nav__dropdown-menu { position: absolute; top: calc(100% + 15px); left: 50%; display: grid; min-width: 190px; padding: 8px; border: 1px solid var(--miro-hairline, #e0e2e8); border-radius: 16px; background: #fff; box-shadow: 0 18px 42px rgba(28, 28, 30, .12); opacity: 0; visibility: hidden; transform: translate(-50%, -6px); transition: opacity .18s ease, transform .18s ease, visibility .18s ease; }
    .miro-nav__dropdown-menu::before { content: ''; position: absolute; top: -6px; left: 50%; width: 11px; height: 11px; border-top: 1px solid var(--miro-hairline, #e0e2e8); border-left: 1px solid var(--miro-hairline, #e0e2e8); background: #fff; transform: translateX(-50%) rotate(45deg); }
    .miro-nav__dropdown.is-open .miro-nav__dropdown-menu, .miro-nav__dropdown:focus-within .miro-nav__dropdown-menu { opacity: 1; visibility: visible; transform: translate(-50%, 0); }
    .miro-nav__dropdown-menu a, .miro-nav__dropdown-item { position: relative; z-index: 1; display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 16px; padding: 10px 12px; border: 0; border-radius: 10px; background: transparent; color: var(--miro-ink, #1c1c1e); font: 500 14px/1.3 var(--miro-font, 'Noto Sans', sans-serif); text-align: left; text-decoration: none; }
    .miro-nav__dropdown-menu a:hover, .miro-nav__dropdown-item:hover { background: var(--miro-pink, #ffd8f4); }
    .miro-nav__dropdown-item { cursor: default; }
    .miro-nav__dropdown-item__status { color: var(--miro-steel, #6b6f7e); font-size: 11px; font-weight: 500; }

    @media (max-width: 1023px) {
        .miro-nav__dropdown { display: block; }
        .miro-nav.is-open .miro-nav__dropdown-trigger { width: 100%; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid var(--miro-hairline-soft, #eef0f3); }
        .miro-nav.is-open .miro-nav__dropdown-menu, .miro-nav.is-open .miro-nav__dropdown:focus-within .miro-nav__dropdown-menu { position: static; display: none; min-width: 0; padding: 6px 0 8px 12px; border: 0; border-radius: 0; box-shadow: none; opacity: 1; visibility: visible; transform: none; }
        .miro-nav.is-open .miro-nav__dropdown.is-open .miro-nav__dropdown-menu { display: grid; }
        .miro-nav.is-open .miro-nav__dropdown-menu::before { display: none; }
        .miro-nav.is-open .miro-nav__dropdown-menu a, .miro-nav.is-open .miro-nav__dropdown-menu .miro-nav__dropdown-item { padding: 11px 12px; border-bottom: 0; }
    }
</style>
