import Alpine from 'alpinejs';

/**
 * Редактор блока «текст»: жирный, курсив, подчёркнутый, зачёркнутый,
 * подзаголовки H2/H3, цитата, два вида списков, ссылка, очистка форматирования,
 * отмена/повтор и режим правки HTML-исходника.
 *
 * Результат хранится как HTML и на выводе очищается санитайзером по белому
 * списку тегов (AGENTS.md §5.5). Для произвольного кода без обработки —
 * отдельный блок «HTML-код».
 */
Alpine.data('richText', (getHtml, setHtml) => ({
    mode: 'visual', // visual | source

    // Модальное окно ссылки: свой диалог вместо стандартного window.prompt()
    // браузера — тот не даёт выбрать, открывать ли ссылку в новом окне.
    linkModalOpen: false,
    linkMode: 'insert', // insert (вставка новой) | wrap (обернуть выделение) | edit (правка существующей)
    linkUrl: '',
    linkText: '',
    linkNewTab: false,
    _linkAnchor: null,
    _savedRange: null,

    init() {
        this.renderArea();
        this.$refs.area.addEventListener('input', () => this.sync());
        this.$refs.area.addEventListener('blur', () => this.sync());
    },

    renderArea() {
        if (this.$refs.area) {
            this.$refs.area.innerHTML = getHtml() || '';
        }
    },
    sync() {
        setHtml(this.$refs.area.innerHTML);
    },

    exec(command, value = null) {
        this.$refs.area.focus();
        document.execCommand(command, false, value);
        this.sync();
    },
    formatBlock(tag) {
        this.exec('formatBlock', tag);
    },

    // Ищет <a>, внутри которого сейчас находится курсор/выделение (если есть).
    anchorAtCursor() {
        const sel = window.getSelection();
        if (!sel || !sel.anchorNode) return null;
        const el = sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement;
        const a = el ? el.closest('a') : null;
        return a && this.$refs.area.contains(a) ? a : null;
    },

    link() {
        this.$refs.area.focus();
        const sel = window.getSelection();
        const range = sel && sel.rangeCount ? sel.getRangeAt(0) : null;
        this._savedRange = range ? range.cloneRange() : null;
        this._linkAnchor = this.anchorAtCursor();

        if (this._linkAnchor) {
            this.linkMode = 'edit';
            this.linkUrl = this._linkAnchor.getAttribute('href') || '';
            this.linkText = this._linkAnchor.textContent;
            this.linkNewTab = this._linkAnchor.target === '_blank';
        } else if (range && !range.collapsed) {
            this.linkMode = 'wrap';
            this.linkUrl = '';
            this.linkText = range.toString();
            this.linkNewTab = false;
        } else {
            this.linkMode = 'insert';
            this.linkUrl = '';
            this.linkText = '';
            this.linkNewTab = false;
        }

        this.linkModalOpen = true;
    },

    closeLinkModal() {
        this.linkModalOpen = false;
        this._linkAnchor = null;
        this._savedRange = null;
    },

    applyLinkTarget(a) {
        if (this.linkNewTab) {
            a.setAttribute('target', '_blank');
            a.setAttribute('rel', 'noopener noreferrer');
        } else {
            a.removeAttribute('target');
            a.removeAttribute('rel');
        }
    },

    applyLink() {
        const url = this.linkUrl.trim();
        if (!url) return;

        this.$refs.area.focus();

        if (this.linkMode === 'edit' && this._linkAnchor) {
            this._linkAnchor.setAttribute('href', url);
            const text = this.linkText.trim();
            if (text) this._linkAnchor.textContent = text;
            this.applyLinkTarget(this._linkAnchor);
        } else {
            const sel = window.getSelection();
            sel.removeAllRanges();
            if (this._savedRange) sel.addRange(this._savedRange);

            if (this.linkMode === 'wrap') {
                document.execCommand('createLink', false, url);
            } else {
                const text = (this.linkText.trim() || url)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                document.execCommand('insertHTML', false, `<a href="${url.replace(/"/g, '&quot;')}">${text}</a>`);
            }

            // execCommand оставляет курсор внутри только что созданной ссылки —
            // находим её, чтобы проставить target/rel.
            const created = this.anchorAtCursor();
            if (created) this.applyLinkTarget(created);
        }

        this.closeLinkModal();
        this.sync();
    },

    toggleSource() {
        if (this.mode === 'visual') {
            this.sync();
            this.mode = 'source';
        } else {
            this.mode = 'visual';
            this.$nextTick(() => this.renderArea());
        }
    },
}));
