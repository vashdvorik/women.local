import Alpine from 'alpinejs';

/**
 * Страницы каталога с перелистыванием. Администратор выбирает PDF — браузер сам (pdf.js, подгружается только на этой
 * странице) рисует каждую страницу и отправляет на сервер лёгкой WebP-картинкой, один раз. Посетителю сайта потом уходят
 * только готовые картинки: разбирать PDF на его устройстве не нужно.
 *
 * Состояние: pages — [{path, url}] в порядке листания; в форму уходят пути (скрытое поле pages) и пропорции страницы.
 * Все надписи приходят из разметки уже переведёнными (labels).
 */
const LONG_SIDE = 1600; // длинная сторона готовой картинки, px
const QUALITY = 0.78;
const SPREAD = 1.2; // страница шире этого отношения — разворот из двух страниц

Alpine.data('catalogPages', (initial) => ({
    pages: initial.pages || [],
    ratio: initial.ratio || 0.7071,
    uploadUrl: initial.uploadUrl,
    labels: initial.labels || {},
    busy: false,
    done: 0,
    total: 0,
    error: '',

    get paths() {
        return JSON.stringify(this.pages.map((page) => page.path));
    },

    get progressLabel() {
        return (this.labels.progress || ':done / :total').replace(':done', this.done).replace(':total', this.total);
    },

    remove(index) {
        this.pages.splice(index, 1);
    },

    async convert(event) {
        const file = event.target.files[0];

        if (!file) {
            return;
        }

        this.error = '';
        this.busy = true;
        this.done = 0;
        this.total = 0;

        try {
            const pdfjs = await import('pdfjs-dist/build/pdf.mjs');
            const worker = await import('pdfjs-dist/build/pdf.worker.mjs?url');
            pdfjs.GlobalWorkerOptions.workerSrc = worker.default;

            const pdf = await pdfjs.getDocument({ data: await file.arrayBuffer() }).promise;

            if (pdf.numPages > 300) {
                throw new Error(this.labels.tooMany);
            }

            // Сначала размеры всех страниц: если в PDF есть и обычные страницы, и развороты (две страницы на одном листе),
            // развороты режутся пополам, иначе в книге они были бы сжаты в одну страницу.
            const sizes = [];
            for (let number = 1; number <= pdf.numPages; number++) {
                const view = (await pdf.getPage(number)).getViewport({ scale: 1 });
                sizes.push(view.width / view.height);
            }

            const hasSingles = sizes.some((value) => value <= SPREAD);
            const isSpread = (number) => hasSingles && sizes[number - 1] > SPREAD;
            const single = sizes.find((value) => value <= SPREAD) || sizes[0];

            this.ratio = Math.round(single * 10000) / 10000;
            this.total = sizes.reduce((sum, _value, index) => sum + (isSpread(index + 1) ? 2 : 1), 0);

            const fresh = [];
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            const half = document.createElement('canvas');

            const send = async (source, name) => {
                const blob = await new Promise((resolve) => source.toBlob(resolve, 'image/webp', QUALITY));
                const body = new FormData();
                body.append('file', blob, name);

                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        Accept: 'application/json',
                    },
                    body,
                });

                if (!response.ok) {
                    throw new Error(this.labels.failed);
                }

                fresh.push(await response.json());
                this.done = fresh.length;
            };

            for (let number = 1; number <= pdf.numPages; number++) {
                const page = await pdf.getPage(number);
                const base = page.getViewport({ scale: 1 });
                // Длинная сторона одной страницы (для разворота — высота) приводится к LONG_SIDE
                const side = isSpread(number) ? base.height : Math.max(base.width, base.height);
                const viewport = page.getViewport({ scale: Math.min(3, LONG_SIDE / side) });

                canvas.width = Math.round(viewport.width);
                canvas.height = Math.round(viewport.height);
                context.fillStyle = '#fff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                await page.render({ canvasContext: context, viewport }).promise;
                page.cleanup();

                if (!isSpread(number)) {
                    await send(canvas, `page-${number}.webp`);

                    continue;
                }

                // Разворот: левая и правая половины становятся двумя страницами
                const width = Math.floor(canvas.width / 2);
                half.width = width;
                half.height = canvas.height;

                for (const [index, offset] of [0, width].entries()) {
                    half.getContext('2d').drawImage(canvas, offset, 0, width, canvas.height, 0, 0, width, canvas.height);
                    await send(half, `page-${number}-${index + 1}.webp`);
                }
            }
            await pdf.destroy();
            this.pages = fresh;
        } catch (error) {
            this.error = error && error.message ? error.message : this.labels.failed;
            event.target.value = '';
        } finally {
            this.busy = false;
        }
    },
}));
