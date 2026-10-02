import Alpine from 'alpinejs';

/**
 * Редактор одного сообщения бота («Кабинеты участниц → Сообщения бота»): вставка переменной в место курсора,
 * возврат исходного текста из resources/data/bot_messages.php и пометка «Изменено», пока текст отличается от эталона.
 */
Alpine.data('botMessage', (text, original) => ({
    text,
    original,

    get changed() {
        return this.text.trim() !== this.original.trim();
    },

    insert(variable) {
        const field = this.$refs.field;
        const start = field.selectionStart ?? this.text.length;
        const end = field.selectionEnd ?? start;

        this.text = this.text.slice(0, start) + variable + this.text.slice(end);

        this.$nextTick(() => {
            field.focus();
            field.setSelectionRange(start + variable.length, start + variable.length);
        });
    },

    restore() {
        this.text = this.original;
    },
}));
