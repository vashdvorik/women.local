import Alpine from 'alpinejs';

/**
 * Цвет тега выбирается свободно. Рядом стоят <input type="color"> и поле HEX,
 * синхронизированные здесь. Цвет текста на плашке вычисляется из яркости фона —
 * той же формулой, что и на сайте (AGENTS.md §15, правило 27).
 */
Alpine.data('tagColor', (initialColor, initialName = '') => ({
    hex: initialColor || '#0066cc',
    ruName: initialName,

    get valid() {
        return /^#[0-9a-fA-F]{6}$/.test(this.hex);
    },
    get normalized() {
        return this.valid ? this.hex.toLowerCase() : '#0066cc';
    },
    get textColor() {
        const [r, g, b] = [1, 3, 5].map((i) => parseInt(this.normalized.slice(i, i + 2), 16));
        return (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.6 ? '#1d1d1f' : '#ffffff';
    },
    setFromPicker(value) {
        this.hex = value.toLowerCase();
    },
    setFromText(value) {
        let v = value.trim();
        if (v && v[0] !== '#') v = '#' + v;
        this.hex = v;
    },
}));
