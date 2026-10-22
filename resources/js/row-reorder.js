import Alpine from 'alpinejs';

/**
 * Ручной порядок строк таблицы в админке (фотоальбомы): строку перетаскивают за значок слева или двигают кнопками
 * «вверх» и «вниз». После каждого движения порядок id уходит на сервер одним запросом, страница не перезагружается.
 * Тексты («Порядок сохранён», «Не удалось сохранить») приходят из разметки уже переведёнными.
 *
 * Перетаскивание включается только пока нажат значок (draggable у строки выставляется на это время): иначе нельзя было бы
 * выделить текст или нажать на ссылку в строке. На телефонах, где перетаскивание мышью недоступно, остаются кнопки.
 */
Alpine.data('rowReorder', (url, messages = {}) => ({
    dragging: null,
    status: '',

    init() {
        this.refreshButtons();
    },

    rows() {
        return Array.from(this.$refs.body.querySelectorAll('tr[data-id]'));
    },

    arm(event) {
        event.currentTarget.closest('tr').draggable = true;
    },

    disarm(event) {
        if (!this.dragging) {
            event.currentTarget.closest('tr').draggable = false;
        }
    },

    start(event) {
        this.dragging = event.currentTarget;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', event.currentTarget.dataset.id || '');
        requestAnimationFrame(() => this.dragging && this.dragging.classList.add('opacity-40'));
    },

    over(event) {
        if (!this.dragging) {
            return;
        }

        event.preventDefault();

        const row = event.target.closest('tr[data-id]');
        if (!row || row === this.dragging) {
            return;
        }

        const box = row.getBoundingClientRect();
        const after = event.clientY > box.top + box.height / 2;
        row.parentNode.insertBefore(this.dragging, after ? row.nextSibling : row);
    },

    end() {
        if (!this.dragging) {
            return;
        }

        this.dragging.classList.remove('opacity-40');
        this.dragging.draggable = false;
        this.dragging = null;
        this.refreshButtons();
        this.save();
    },

    move(event, step) {
        const row = event.currentTarget.closest('tr');
        const sibling = step < 0 ? row.previousElementSibling : row.nextElementSibling;

        if (!sibling) {
            return;
        }

        step < 0 ? row.parentNode.insertBefore(row, sibling) : row.parentNode.insertBefore(sibling, row);
        this.refreshButtons();
        this.save();
    },

    refreshButtons() {
        const rows = this.rows();

        rows.forEach((row, index) => {
            const up = row.querySelector('[data-move="up"]');
            const down = row.querySelector('[data-move="down"]');

            if (up) up.disabled = index === 0;
            if (down) down.disabled = index === rows.length - 1;
        });
    },

    async save() {
        const ids = this.rows().map((row) => Number(row.dataset.id));
        this.status = 'saving';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ ids }),
            });

            this.status = response.ok ? 'saved' : 'error';
        } catch (error) {
            this.status = 'error';
        }

        if (this.status === 'saved') {
            setTimeout(() => { if (this.status === 'saved') this.status = ''; }, 2500);
        }
    },

    message() {
        return messages[this.status] || '';
    },
}));
