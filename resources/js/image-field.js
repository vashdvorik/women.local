import Alpine from 'alpinejs';
import { uploadImage, flashError } from './uploader';
import { openCropper } from './cropper';

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
            flashError('Не удалось загрузить изображение.');
        } finally {
            this.uploading = false;
        }
    },
    clear() {
        this.path = '';
    },
}));
