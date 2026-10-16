import { openCropper } from './cropper';
import { t } from './i18n';

/**
 * Общая загрузка одного изображения: сразу при выборе файла, отдельным запросом
 * (AGENTS.md §14, правило 15). Возвращает относительный путь.
 *
 * @param {?{x:number,y:number,width:number,height:number}} crop
 *        прямоугольник кадрирования в координатах исходника (из openCropper)
 */
export async function uploadImage(file, slot, uploadUrl, crop = null) {
    const body = new FormData();
    body.append('image', file);
    body.append('slot', slot);

    if (crop) {
        body.append('crop[x]', Math.round(crop.x));
        body.append('crop[y]', Math.round(crop.y));
        body.append('crop[width]', Math.round(crop.width));
        body.append('crop[height]', Math.round(crop.height));
    }

    const res = await fetch(uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            Accept: 'application/json',
        },
        body,
    });

    if (!res.ok) {
        throw new Error('upload failed');
    }

    return (await res.json()).path;
}

/**
 * Изменить кадр уже загруженной картинки. Сервер хранит сжатое фото целиком и рамку (см. StoreUploadedImage), поэтому
 * файл заново выбирать не нужно: окно кадрирования открывается на сохранённом фото с прежней рамкой, а после «Применить»
 * сервер вырезает из него новый кадр. Возвращает путь нового кадра или null: пользователь отменил, у картинки нет
 * сохранённого фото (загружена до этой возможности) или сервер отказал; причину в этих случаях видно во всплывающем сообщении.
 *
 * Адреса берутся от адреса загрузки: …/admin/uploads → …/admin/uploads/source-info и …/admin/uploads/recrop.
 *
 * @param {string} path  путь текущего кадра («2026/10/abc.webp»)
 * @param {[number, number]} ratio  пропорции слота
 * @param {string} uploadUrl  адрес загрузки картинок (admin.uploads.store)
 * @param {?(busy: boolean) => void} setBusy  включает и выключает «Обработка…», пока сервер режет новый кадр
 */
export async function editCrop(path, ratio, uploadUrl, setBusy = null) {
    const base = uploadUrl.replace(/\/+$/, '');
    const headers = {
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        Accept: 'application/json',
    };

    let info;
    try {
        const res = await fetch(`${base}/source-info?path=${encodeURIComponent(path)}`, { headers });

        if (res.status === 404) {
            flashError((await res.json().catch(() => ({}))).message
                || t('У этой картинки нет сохранённого фото целиком: перекадрировать её нельзя. Загрузите её заново.'));

            return null;
        }
        if (!res.ok) throw new Error('info failed');

        info = await res.json();
    } catch (e) {
        flashError(t('Не удалось открыть фото для кадрирования.'));

        return null;
    }

    let crop;
    try {
        crop = await openCropper(info.source_url, ratio || info.ratio, { crop: info.crop, title: t('Измените кадр') });
    } catch (e) {
        return null; // отменено
    }

    const body = new FormData();
    body.append('path', path);
    body.append('crop[x]', Math.round(crop.x));
    body.append('crop[y]', Math.round(crop.y));
    body.append('crop[width]', Math.round(crop.width));
    body.append('crop[height]', Math.round(crop.height));

    if (setBusy) setBusy(true);
    try {
        const res = await fetch(`${base}/recrop`, { method: 'POST', headers, body });

        if (!res.ok) throw new Error('recrop failed');

        return (await res.json()).path;
    } catch (e) {
        flashError(t('Не удалось изменить кадр.'));

        return null;
    } finally {
        if (setBusy) setBusy(false);
    }
}

/**
 * Загрузка PDF для блока «Файл (PDF)»: сразу при выборе файла, отдельным запросом.
 * Возвращает { path, name, size }. Если сервер отклонил файл, бросает ошибку с его
 * пояснением (формат, размер) — его же и показываем редактору.
 */
export async function uploadFile(file, uploadUrl) {
    const body = new FormData();
    body.append('file', file);

    const res = await fetch(uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            Accept: 'application/json',
        },
        body,
    });

    // Тело может быть не JSON (например, страница ошибки веб-сервера при слишком большом файле).
    const json = await res.json().catch(() => ({}));

    if (!res.ok) {
        throw new Error(json?.errors?.file?.[0] || 'upload failed');
    }

    return json;
}

export function flashError(text) {
    window.dispatchEvent(new CustomEvent('flash', { detail: { type: 'error', text } }));
}
