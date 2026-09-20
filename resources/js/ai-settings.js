import Alpine from 'alpinejs';

/**
 * Кнопки «Проверить подключение» на вкладке «ИИ». Отправляют текущие значения
 * формы (в том числе ещё не сохранённые) на сервер; тот делает пробный запрос
 * к провайдеру и возвращает { ok, message }. Ничего не сохраняется.
 */
Alpine.data('aiProviders', (testBase) => ({
    testing: null,
    results: {},

    async test(provider, form) {
        this.testing = provider;
        this.results[provider] = null;

        try {
            const body = new FormData(form);
            body.delete('_method'); // форма сохранения — PUT, а проверка — обычный POST

            const res = await fetch(`${testBase}/${provider}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body,
            });

            this.results[provider] = await res.json();
        } catch (e) {
            this.results[provider] = { ok: false, message: 'Не удалось выполнить запрос.' };
        } finally {
            this.testing = null;
        }
    },
}));
