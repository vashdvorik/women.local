import Alpine from 'alpinejs';
import { uploadImage, editCrop, flashError } from './uploader';
import { openCropper } from './cropper';
import { t } from './i18n';

/**
 * Одно необязательное изображение с кадрированием под слот.
 * Используется в формах отзыва и проекта (video-cover.js — то же для видео).
 */
Alpine.data('imageField', (initial) => ({
    path: initial.path || '',
    uploading: false,

    async upload(event) {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;

        let crop = null;
        try {
            crop = await openCropper(file, initial.ratio);
        } catch (e) {
            return; // отменено
        }

        this.uploading = true;
        try {
            this.path = await uploadImage(file, initial.slot, initial.uploadUrl, crop);
        } catch (e) {
            flashError(t('Не удалось загрузить изображение.'));
        } finally {
            this.uploading = false;
        }
    },
    // Изменить кадр загруженной картинки, не выбирая файл заново.
    async recrop() {
        const next = await editCrop(this.path, initial.ratio, initial.uploadUrl, (busy) => { this.uploading = busy; });
        if (next) this.path = next;
    },
    clear() {
        this.path = '';
    },
}));
