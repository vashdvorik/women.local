import Alpine from 'alpinejs';
import { uploadImage, flashError } from './uploader';
import { openCropper } from './cropper';

/**
 * Необязательная собственная обложка видео (16:9). Без неё берётся миниатюра
 * с YouTube (AGENTS.md §15).
 */
Alpine.data('videoCover', (initial) => ({
    cover: initial.cover || '',
    uploading: false,

    async upload(event) {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;

        let crop = null;
        try {
            crop = await openCropper(file, initial.ratio || [16, 9]);
        } catch (e) {
            return; // отменено
        }

        this.uploading = true;
        try {
            this.cover = await uploadImage(file, 'video_cover', initial.uploadUrl, crop);
        } catch (e) {
            flashError('Не удалось загрузить изображение.');
        } finally {
            this.uploading = false;
        }
    },
    clear() {
        this.cover = '';
    },
}));
