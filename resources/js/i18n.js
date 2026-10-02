/**
 * Тексты скриптов админки на языке интерфейса. Исходный текст — русский и служит ключом; словарь для другого
 * языка кладёт в страницу layout (window.adminI18n, из lang/<язык>/adminjs.php). На русском словаря нет,
 * поэтому t() возвращает текст как есть. Подстановки: `:size` в тексте заменяется значением из второго аргумента ({ size: 25 }).
 */
export function t(text, vars = {}) {
    const dictionary = window.adminI18n || {};
    let out = Object.prototype.hasOwnProperty.call(dictionary, text) ? dictionary[text] : text;

    for (const [name, value] of Object.entries(vars)) {
        out = out.split(':' + name).join(String(value));
    }

    return out;
}

/** Десятичный разделитель текущего языка интерфейса: запятая по-русски, точка по-английски. */
export function decimalSeparator() {
    return (document.documentElement.lang || 'ru').startsWith('en') ? '.' : ',';
}
