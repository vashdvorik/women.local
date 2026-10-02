import Alpine from 'alpinejs';
import { uploadImage, uploadFile, flashError } from './uploader';
import { openCropper } from './cropper';

/**
 * Редактор новости и возможности. Все три языка — в одной форме и уходят одним
 * запросом (AGENTS.md §12). Структура блоков общая для всех языков: добавление,
 * удаление и перестановка применяются ко всем языкам сразу, переводится только
 * текст внутри; пути к изображениям тоже общие.
 */

const LOCALES = ['ru', 'ro', 'en'];

function uid() {
    return (crypto.randomUUID && crypto.randomUUID()) ||
        'b-' + Math.random().toString(36).slice(2, 14);
}

const CELLS = { image: 1, gallery_2: 2, gallery_3: 3, gallery_4: 4 };

function blankData(type) {
    if (type === 'text' || type === 'embed') return { html: '' };
    if (type === 'heading') return { text: '', level: 'h2' };
    if (type === 'image') return { path: null };
    if (type === 'file') return { path: null, title: '', name: null, size: null };
    return { images: Array(CELLS[type]).fill(null) };
}

function makeBlock(type) {
    return { uid: uid(), type, data: blankData(type), _busy: false };
}

/**
 * Общая часть редакторов с блоками (публикация, возможность, новость): три языка, структура блоков
 * одна на все языки, загрузка картинок и PDF. Вкладки, черновик и публикация — не здесь.
 */
function blockEditor(initial) {
    return {
        blocks: initial.blocks, // { ru:[...], ro:[...], en:[...] }
        kinds: initial.kinds,   // допустимые типы блоков
        ratios: initial.ratios || {}, // слот → [ширина, высота] кадра

        /** @param {boolean} seed  положить в пустой материал один текстовый блок */
        initBlocks(seed = true) {
            LOCALES.forEach((l) => {
                if (!Array.isArray(this.blocks[l])) this.blocks[l] = [];
            });
            // В новом материале уже стоит один пустой текстовый блок; в новости текст необязателен.
            if (seed && this.blocks.ru.length === 0) {
                this.addBlock('text', { silent: true });
            }
        },

        // ----- операции над структурой (для всех языков сразу) -----
        addBlock(type, opts = {}) {
            const base = makeBlock(type);
            LOCALES.forEach((l) => {
                this.blocks[l].push(l === 'ru'
                    ? base
                    : { uid: base.uid, type, data: JSON.parse(JSON.stringify(base.data)) });
            });
            if (!opts.silent) this.scrollToBlock(this.blocks.ru.length - 1);
        },
        duplicate(i) {
            const newUid = uid();
            LOCALES.forEach((l) => {
                const copy = JSON.parse(JSON.stringify(this.blocks[l][i]));
                copy.uid = newUid;
                this.blocks[l].splice(i + 1, 0, copy);
            });
            this.scrollToBlock(i + 1);
        },
        remove(i) {
            LOCALES.forEach((l) => this.blocks[l].splice(i, 1));
        },
        move(i, dir) {
            const j = i + dir;
            if (j < 0 || j >= this.blocks.ru.length) return;
            LOCALES.forEach((l) => {
                const arr = this.blocks[l];
                [arr[i], arr[j]] = [arr[j], arr[i]];
            });
        },
        setLevel(i, level) {
            LOCALES.forEach((l) => { this.blocks[l][i].data.level = level; });
        },
        // «HTML-код» — структурный блок: одинаков для всех языков.
        setEmbedHtml(i, html) {
            LOCALES.forEach((l) => { this.blocks[l][i].data.html = html; });
        },

        // Прокрутить к блоку так, чтобы он оказался в поле зрения после добавления
        // или дублирования. Один и тот же блок отрисован в трёх языковых вкладках
        // под одинаковым id — берём ту копию, что сейчас видна. Прокрутка мгновенная:
        // плавная анимация scrollIntoView срывается перерисовкой соседних блоков и
        // вовсе не выполняется, когда вкладка браузера неактивна.
        scrollToBlock(i) {
            const block = this.blocks.ru[i];
            if (!block) return;
            this.$nextTick(() => {
                const target = [...document.querySelectorAll(`[id="block-${block.uid}"]`)]
                    .find((el) => el.offsetParent !== null);
                if (target) target.scrollIntoView({ block: 'center' });
            });
        },

        // ----- загрузка изображений (сразу при выборе файла) -----
        // Сначала пользователь кадрирует картинку под пропорции слота, затем она
        // уходит на сервер отдельным запросом.
        async upload(event, slot, apply, setBusy = null) {
            const file = event.target.files[0];
            event.target.value = '';
            if (!file) return;

            let crop = null;
            try {
                crop = await openCropper(file, this.ratios[slot]);
            } catch (e) {
                return; // пользователь отменил кадрирование
            }

            if (setBusy) setBusy(true);
            try {
                const path = await uploadImage(file, slot, initial.uploadUrl, crop);
                apply(path);
            } catch (e) {
                flashError('Не удалось загрузить изображение.');
            } finally {
                if (setBusy) setBusy(false);
            }
        },
        setBlockImage(i, path, cell = null) {
            LOCALES.forEach((l) => {
                const b = this.blocks[l][i];
                if (b.type === 'image') b.data.path = path;
                else b.data.images[cell] = path;
            });
        },

        // ----- «Файл (PDF)»: каталог, брошюра -----
        // Файл один на все языки; название — на каждой вкладке своё. Как и картинка, PDF уходит
        // на сервер сразу при выборе. Формат и размер проверяются здесь заранее, чтобы не ждать
        // загрузки большого файла ради отказа, и ещё раз на сервере.
        fileMaxMb: initial.fileMaxMb || 25,
        async uploadPdf(event, i, setBusy) {
            const file = event.target.files[0];
            event.target.value = '';
            if (!file) return;

            const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
            if (!isPdf) {
                flashError('Нужен файл в формате PDF.');
                return;
            }
            if (file.size > this.fileMaxMb * 1024 * 1024) {
                flashError(`Файл слишком большой: допустимо до ${this.fileMaxMb} МБ.`);
                return;
            }

            setBusy(true);
            try {
                this.setBlockFile(i, await uploadFile(file, initial.uploadFileUrl));
            } catch (e) {
                flashError(e.message && e.message !== 'upload failed'
                    ? e.message
                    : `Не удалось загрузить файл. Проверьте, что это PDF не больше ${this.fileMaxMb} МБ.`);
            } finally {
                setBusy(false);
            }
        },
        // meta = { path, name, size } или null (убрать файл). Название не трогаем, кроме одного
        // случая: на русской вкладке оно пустое — подставляем имя файла без «.pdf».
        setBlockFile(i, meta) {
            LOCALES.forEach((l) => {
                const d = this.blocks[l][i].data;
                d.path = meta ? meta.path : null;
                d.name = meta ? meta.name : null;
                d.size = meta ? meta.size : null;
            });
            const ru = this.blocks.ru[i].data;
            if (meta && !(ru.title || '').trim() && meta.name) {
                ru.title = meta.name.replace(/\.pdf$/i, '');
            }
        },
        fileSize(bytes) {
            if (!bytes) return '';
            if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + ' КБ';
            return (bytes / (1024 * 1024)).toFixed(1).replace('.', ',') + ' МБ';
        },
    };
}

Alpine.data('articleEditor', (initial, cancelUrl) => ({
    ...blockEditor(initial),

    activeTab: 'ru',
    intent: 'save',
    submitting: false,   // идёт легитимная отправка формы — не мешать beforeunload
    dirty: false,        // есть несохранённые изменения
    ready: false,
    cancelUrl: cancelUrl || '/admin',

    slug: initial.slug || '',
    author: initial.author || '',
    publishedAt: initial.published_at || '',
    seo: initial.seo || { ru: {}, ro: {}, en: {} },
    deadlineAt: initial.deadline_at || '',
    tagId: initial.tag_id || '',
    cover: initial.cover || '',
    coverUploading: false,

    fields: initial.fields, // { ru:{title,excerpt}, ... }

    tags: initial.tags || [],
    localeNames: { ru: 'Русский', ro: 'Română', en: 'English' },

    init() {
        this.initBlocks();
        this.$watch('blocks', () => { this.serialize(); this.touch(); }, { deep: true });
        this.$watch('fields', () => { this.recalcBadges(); this.touch(); }, { deep: true });
        ['slug', 'author', 'publishedAt', 'deadlineAt', 'tagId', 'cover'].forEach((k) =>
            this.$watch(k, () => this.touch()));
        this.$watch('seo', () => this.touch(), { deep: true });

        this.serialize();
        this.recalcBadges();

        // Предупредить о несохранённых изменениях при уходе со страницы.
        window.addEventListener('beforeunload', (e) => {
            if (this.dirty && !this.submitting) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Ctrl/Cmd + S — сохранить черновик.
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                this.submit('save');
                document.getElementById('article-form')?.requestSubmit();
            }
        });

        this.$nextTick(() => { this.ready = true; });
    },

    touch() {
        if (this.ready) this.dirty = true;
    },

    // Публикация возможна только с русским заголовком, кратким описанием и
    // датой не в будущем. Пока условия не выполнены — кнопка «Опубликовать»
    // заблокирована, чтобы не ловить ошибку на сервере.
    get canPublish() {
        const f = this.fields.ru || {};
        const filled = (f.title || '').trim() !== '' && (f.excerpt || '').trim() !== '';
        const dateOk = !this.publishedAt || new Date(this.publishedAt) <= new Date();
        return filled && dateOk;
    },

    submit(intent) {
        this.intent = intent;
        this.submitting = true;
    },

    cancel() {
        if (this.dirty && ! window.confirm('Есть несохранённые изменения. Покинуть страницу?')) {
            return;
        }
        this.submitting = true; // подавить предупреждение beforeunload
        window.location.href = this.cancelUrl;
    },

    // ----- сериализация в скрытые поля -----
    serialized: { ru: '[]', ro: '[]', en: '[]' },
    serialize() {
        LOCALES.forEach((l) => {
            this.serialized[l] = JSON.stringify(this.blocks[l]);
        });
    },

    // ----- индикаторы заполненности вкладок -----
    badges: { ru: 'empty', ro: 'empty', en: 'empty' },
    recalcTimer: null,
    recalcBadges() {
        clearTimeout(this.recalcTimer);
        this.recalcTimer = setTimeout(() => {
            this.badges.ru = this.primaryBadge();
            this.badges.ro = this.secondaryBadge('ro');
            this.badges.en = this.secondaryBadge('en');
        }, 250);
    },
    blockText(b) {
        if (b.type === 'text') return b.data.html.replace(/<[^>]*>/g, '').trim();
        if (b.type === 'heading') return (b.data.text || '').trim();
        // Название файла переводится, сам файл — нет.
        if (b.type === 'file') return (b.data.title || '').trim();
        return '';
    },
    blockHasContent(b) {
        // Название без файла — не содержимое: на странице такой блок ничего не покажет.
        if (b.type === 'file') return !!b.data.path;
        if (this.blockText(b)) return true;
        if (b.type === 'embed') return !!(b.data.html || '').trim();
        if (b.type === 'image') return !!b.data.path;
        if (b.data.images) return b.data.images.some(Boolean);
        return false;
    },
    primaryBadge() {
        const f = this.fields.ru;
        const hasContent = this.blocks.ru.some((b) => this.blockHasContent(b));
        return (f.title || '').trim() && (f.excerpt || '').trim() && hasContent ? 'done' : 'empty';
    },
    secondaryBadge(locale) {
        const ru = this.fields.ru;
        const tr = this.fields[locale];
        let expected = 0, filled = 0;
        ['title', 'excerpt'].forEach((k) => {
            if ((ru[k] || '').trim()) {
                expected++;
                if ((tr[k] || '').trim()) filled++;
            }
        });
        this.blocks.ru.forEach((b, i) => {
            if (!this.blockText(b)) return;
            expected++;
            const counterpart = this.blocks[locale][i];
            if (counterpart && this.blockText(counterpart)) filled++;
        });
        const anyInput = filled > 0 || (tr.title || '').trim() || (tr.excerpt || '').trim() ||
            this.blocks[locale].some((b) => this.blockText(b));
        if (!anyInput) return 'empty';
        return expected > 0 && filled >= expected ? 'done' : 'partial';
    },

    get titleForHeader() {
        return (this.fields.ru.title || '').trim();
    },
}));

/**
 * Текст страницы новости: те же блоки, что у публикаций, но внутри плоской формы карточки. Блоки уходят
 * в скрытые поля translations[язык][content] JSON-строкой (сервер разбирает её до валидации).
 */
Alpine.data('eventEditor', (initial) => ({
    ...blockEditor(initial),

    serialized: { ru: '[]', ro: '[]', en: '[]' },

    init() {
        this.initBlocks(false);
        this.$watch('blocks', () => this.serialize(), { deep: true });
        this.serialize();
    },

    serialize() {
        LOCALES.forEach((l) => {
            this.serialized[l] = JSON.stringify(this.blocks[l]);
        });
    },
}));
