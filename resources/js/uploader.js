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

export function flashError(text) {
    window.dispatchEvent(new CustomEvent('flash', { detail: { type: 'error', text } }));
}
