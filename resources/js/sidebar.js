import Alpine from 'alpinejs';

/**
 * Сворачивание групп бокового меню (Участницы, Пакеты и оплата, Контент, Общение, Настройки и вложенная «Медиатека»).
 * Состояние каждой группы запоминается в localStorage. При первом заходе раскрыта только группа текущей страницы,
 * чтобы меню было коротким; группа с активной страницей раскрыта всегда. Активных групп может быть несколько
 * (страница фотоальбомов лежит в «Медиатеке», а та в «Контенте»), поэтому их можно передать списком.
 */
const GROUPS = ['people', 'money', 'content', 'media', 'comm', 'settings'];
const KEY = 'admin.sidebar.groups.v2';

Alpine.data('sidebarNav', (active = null) => ({
    open: {},

    init() {
        const actives = Array.isArray(active) ? active : (active ? [active] : []);

        let saved = null;
        try {
            saved = JSON.parse(localStorage.getItem(KEY) || 'null');
        } catch (e) {
            saved = null;
        }

        GROUPS.forEach((g) => {
            this.open[g] = saved && g in saved ? !!saved[g] : actives.includes(g);
        });

        actives.forEach((g) => {
            if (GROUPS.includes(g)) {
                this.open[g] = true;
            }
        });
    },

    toggle(group) {
        this.open[group] = !this.open[group];
        try {
            localStorage.setItem(KEY, JSON.stringify(this.open));
        } catch (e) {
            /* приватный режим — просто не сохраняем */
        }
    },
}));
