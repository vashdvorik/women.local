import Alpine from 'alpinejs';

/**
 * Сворачивание групп бокового меню. Состояние каждой группы запоминается
 * в localStorage; группа с активной страницей всегда раскрыта.
 */
const GROUPS = ['news', 'opportunities', 'events', 'experts', 'projects', 'media'];
const KEY = 'admin.sidebar.groups';

Alpine.data('sidebarNav', (activeGroup = null) => ({
    open: {},

    init() {
        let saved = {};
        try {
            saved = JSON.parse(localStorage.getItem(KEY) || '{}');
        } catch (e) {
            saved = {};
        }

        GROUPS.forEach((g) => {
            this.open[g] = g in saved ? !!saved[g] : true; // по умолчанию раскрыто
        });

        if (activeGroup && GROUPS.includes(activeGroup)) {
            this.open[activeGroup] = true;
        }
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
