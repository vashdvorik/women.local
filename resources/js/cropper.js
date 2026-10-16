import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { t } from './i18n';

/**
 * Инструмент кадрирования. Открывает модальное окно с изображением и рамкой,
 * закреплённой под пропорции слота, и возвращает выбранный прямоугольник в
 * координатах исходного изображения. Дальше сервер обрезает именно его —
 * поэтому увиденное в форме и оказавшееся на сайте совпадает (AGENTS.md §14).
 *
 * Открывает либо только что выбранный файл (File), либо уже сохранённое фото целиком по адресу (строка): так
 * меняют кадр после загрузки. Для сохранённого фото можно передать прежнюю рамку, и окно откроется на ней.
 *
 * @param {File|string} source  файл или адрес сохранённого фото
 * @param {[number, number]} ratio  [ширина, высота] кадра слота
 * @param {{crop?: {x:number,y:number,width:number,height:number}, title?: string}} [options]
 *        crop — рамка, с которой открыть окно (в пикселях фото); title — заголовок окна
 * @returns {Promise<{x:number,y:number,width:number,height:number}>}
 *          resolve — пользователь нажал «Применить»; reject — отменил.
 */
export function openCropper(source, ratio, options = {}) {
    return new Promise((resolve, reject) => {
        const [rw, rh] = ratio || [1, 1];
        const isFile = typeof source !== 'string';
        const url = isFile ? URL.createObjectURL(source) : source;

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop z-[60]';

        const modal = document.createElement('div');
        modal.className = 'modal max-w-[640px] !p-4 flex flex-col gap-3';
        modal.addEventListener('click', (e) => e.stopPropagation());

        const heading = document.createElement('p');
        heading.className = 'text-ui-strong font-semibold text-ink';
        heading.textContent = options.title || t('Обрежьте изображение');

        const stage = document.createElement('div');
        stage.className = 'bg-surface-sunken rounded-sm overflow-hidden';
        stage.style.maxHeight = '60vh';

        const img = document.createElement('img');
        img.src = url;
        img.alt = '';
        img.style.maxWidth = '100%';
        img.style.display = 'block';
        stage.appendChild(img);

        const hint = document.createElement('p');
        hint.className = 'text-caption text-ink-muted';
        hint.textContent = t('Перетащите рамку, потяните за углы, колесо мыши — масштаб.');

        const actions = document.createElement('div');
        actions.className = 'flex justify-end gap-3 pt-1';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'btn-quiet';
        cancelBtn.textContent = t('Отмена');

        const applyBtn = document.createElement('button');
        applyBtn.type = 'button';
        applyBtn.className = 'btn-primary';
        applyBtn.textContent = t('Применить');

        actions.append(cancelBtn, applyBtn);
        modal.append(heading, stage, hint, actions);
        backdrop.appendChild(modal);
        document.body.appendChild(backdrop);

        let cropper = null;
        let settled = false;

        const cleanup = () => {
            if (cropper) cropper.destroy();
            backdrop.remove();
            if (isFile) URL.revokeObjectURL(url);
            document.removeEventListener('keydown', onKey);
        };
        const done = (rect) => {
            if (settled) return;
            settled = true;
            cleanup();
            resolve(rect);
        };
        const cancel = () => {
            if (settled) return;
            settled = true;
            cleanup();
            reject(new Error('cancelled'));
        };
        const onKey = (e) => {
            if (e.key === 'Escape') cancel();
        };

        document.addEventListener('keydown', onKey);
        backdrop.addEventListener('click', cancel);
        cancelBtn.addEventListener('click', cancel);
        applyBtn.addEventListener('click', () => {
            const d = cropper.getData(true); // округлённые натуральные пиксели
            done({
                x: Math.max(0, d.x),
                y: Math.max(0, d.y),
                width: d.width,
                height: d.height,
            });
        });

        img.onload = () => {
            cropper = new Cropper(img, {
                aspectRatio: rw / rh,
                viewMode: 1,
                autoCropArea: 1,
                dragMode: 'move',
                background: false,
                responsive: true,
                zoomOnWheel: true,
                rotatable: false,
                scalable: false,
                checkOrientation: false,
                // Прежняя рамка (при смене кадра сохранённого фото).
                data: options.crop || null,
            });
        };
    });
}
