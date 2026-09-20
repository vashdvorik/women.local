import Alpine from 'alpinejs';
import { uploadImage, flashError } from './uploader';
import { openCropper } from './cropper';

/**
 * Редактор фотоальбома. Вкладки устроены иначе: первая — «Фото», потому что
 * фотографии общие для всех языков (AGENTS.md §15). Языковые вкладки несут
 * только заголовок и описание. Конструктор — только блоки с изображениями.
 */
const LOCALES = ['ru', 'ro', 'en'];
const CELLS = { image: 1, gallery_2: 2, gallery_3: 3, gallery_4: 4 };

function uid() {
    return (crypto.randomUUID && crypto.randomUUID()) || 'b-' + Math.random().toString(36).slice(2, 14);
}

Alpine.data('albumEditor', (initial, cancelUrl) => ({
    activeTab: 'photos',
    intent: 'save',
    submitting: false,
    dirty: false,
    ready: false,
    cancelUrl: cancelUrl || '/admin',

    slug: initial.slug || '',
    publishedAt: initial.published_at || '',
    cover: initial.cover || '',
    coverUploading: false,
    fields: initial.fields,
    blocks: initial.blocks || [],
    ratios: initial.ratios || {},
    localeNames: { ru: 'Русский', ro: 'Română', en: 'English' },

    init() {
        this.$watch('blocks', () => { this.serialized = JSON.stringify(this.blocks); this.touch(); }, { deep: true });
        this.$watch('fields', () => this.touch(), { deep: true });
        ['slug', 'publishedAt', 'cover'].forEach((k) => this.$watch(k, () => this.touch()));
        this.serialized = JSON.stringify(this.blocks);

        window.addEventListener('beforeunload', (e) => {
            if (this.dirty && !this.submitting) { e.preventDefault(); e.returnValue = ''; }
        });
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                this.submit('save');
                document.getElementById('album-form')?.requestSubmit();
            }
        });
        this.$nextTick(() => { this.ready = true; });
    },

    touch() {
        if (this.ready) this.dirty = true;
    },
    submit(intent) {
        this.intent = intent;
        this.submitting = true;
    },
    cancel() {
        if (this.dirty && ! window.confirm('Есть несохранённые изменения. Покинуть страницу?')) return;
        this.submitting = true;
        window.location.href = this.cancelUrl;
    },

    serialized: '[]',

    addBlock(type) {
        const data = type === 'image' ? { path: null } : { images: Array(CELLS[type]).fill(null) };
        this.blocks.push({ uid: uid(), type, data });
        this.scrollToBlock(this.blocks.length - 1);
    },
    duplicate(i) {
        const copy = JSON.parse(JSON.stringify(this.blocks[i]));
        copy.uid = uid();
        this.blocks.splice(i + 1, 0, copy);
        this.scrollToBlock(i + 1);
    },
    remove(i) { this.blocks.splice(i, 1); },
    move(i, dir) {
        const j = i + dir;
        if (j < 0 || j >= this.blocks.length) return;
        [this.blocks[i], this.blocks[j]] = [this.blocks[j], this.blocks[i]];
    },
    // Мгновенная прокрутка к блоку: плавная анимация scrollIntoView срывается
    // перерисовкой соседних блоков и не работает в неактивной вкладке браузера.
    scrollToBlock(i) {
        const block = this.blocks[i];
        if (!block) return;
        this.$nextTick(() => {
            const el = document.getElementById('ablock-' + block.uid);
            if (el) el.scrollIntoView({ block: 'center' });
        });
    },

    async uploadCover(event) {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;

        let crop = null;
        try {
            crop = await openCropper(file, this.ratios.album);
        } catch (e) {
            return; // отменено
        }

        this.coverUploading = true;
        try {
            this.cover = await uploadImage(file, 'album', initial.uploadUrl, crop);
        } catch (e) {
            flashError('Не удалось загрузить изображение.');
        } finally {
            this.coverUploading = false;
        }
    },
    async uploadBlock(event, i, cell) {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;
        const block = this.blocks[i];
        const slot = block.type === 'image' ? 'image' : block.type;

        let crop = null;
        try {
            crop = await openCropper(file, this.ratios[slot]);
        } catch (e) {
            return; // отменено
        }

        block._busy = true;
        try {
            const path = await uploadImage(file, slot, initial.uploadUrl, crop);
            if (block.type === 'image') block.data.path = path;
            else block.data.images[cell] = path;
        } catch (e) {
            flashError('Не удалось загрузить изображение.');
        } finally {
            block._busy = false;
        }
    },
    photoCount() {
        return this.blocks.reduce((n, b) => n + (b.type === 'image'
            ? (b.data.path ? 1 : 0)
            : b.data.images.filter(Boolean).length), 0);
    },
}));
