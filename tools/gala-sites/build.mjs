/**
 * Сборка сайтов гала-премии: tools/gala-sites/data/{2024,2025,2026}.mjs + общие src/* → папки gala2024, gala2025, gala2026
 * в корне проекта (index.html, assets/, .htaccess, robots.txt, sitemap.xml, README.txt). Node без зависимостей.
 * Запуск из корня проекта:  node tools/gala-sites/build.mjs   (картинки сначала: php tools/gala-sites/prepare-media.php)
 */
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '../..');
const LANGS = ['ru', 'en', 'ro'];
const PLATFORM = 'https://womenhub.innovation.md';
const LINKS = {
  register: `${PLATFORM}/app/account/register`,
  join: `${PLATFORM}/members/join`,
  regulations: `${PLATFORM}/gala/regulations`,
  platform: `${PLATFORM}/`,
  mail: 'womenplatform@gmail.com',
};

// ---------------------------------------------------------------- подписи интерфейса
const UI = {
  nav: {
    about: { ru: 'О церемонии', en: 'About', ro: 'Despre' },
    stages: { ru: 'Что дальше', en: 'What’s next', ro: 'Ce urmează' },
    guests: { ru: 'Гости', en: 'Guests', ro: 'Invitați' },
    gallery: { ru: 'Фото', en: 'Photos', ro: 'Foto' },
    video: { ru: 'Видео', en: 'Video', ro: 'Video' },
    catalog: { ru: 'Журнал', en: 'Magazine', ro: 'Revista' },
    ceremonies: { ru: 'Церемонии', en: 'Ceremonies', ro: 'Ceremonii' },
    join: { ru: 'Присоединиться', en: 'Join', ro: 'Alătură-te' },
  },
  btn: {
    photos: { ru: 'Смотреть фото', en: 'See the photos', ro: 'Vezi fotografiile' },
    video: { ru: 'Смотреть видео', en: 'Watch the video', ro: 'Vezi video-ul' },
    magazine: { ru: 'Листать журнал', en: 'Read the magazine', ro: 'Răsfoiește revista' },
    join: { ru: 'Присоединиться к платформе', en: 'Join the platform', ro: 'Alătură-te platformei' },
    howto: { ru: 'Как стать участницей', en: 'How to become a member', ro: 'Cum devii membră' },
    regulations: { ru: 'Положение о премии', en: 'Award regulations', ro: 'Regulamentul premiului' },
    open: { ru: 'Открыть сайт', en: 'Open the site', ro: 'Deschide site-ul' },
    past: { ru: 'Как это было', en: 'How it was', ro: 'Cum a fost' },
  },
  ceremonies: {
    title: { ru: 'Другие церемонии', en: 'Other ceremonies', ro: 'Alte ceremonii' },
    lead: { ru: 'У каждой церемонии премии «Женщина Года» свой сайт.', en: 'Every “Woman of the Year” ceremony has its own site.', ro: 'Fiecare ceremonie a premiului „Femeia Anului” are propriul site.' },
    upcoming: { ru: 'Анонс: скоро регистрация', en: 'Announcement: registration soon', ro: 'Anunț: în curând înscrieri' },
  },
  join: {
    title: { ru: 'Станьте частью сообщества', en: 'Become part of the community', ro: 'Faceți parte din comunitate' },
    text: {
      ru: 'Заявки на премию принимаются от участниц платформы Women’s Hub. Присоединяйтесь, чтобы узнавать о новых церемониях первой.',
      en: 'Applications for the award are accepted from members of the Women’s Hub platform. Join to be the first to hear about new ceremonies.',
      ro: 'Cererile pentru premiu sunt primite de la membrele platformei Women’s Hub. Alăturați-vă ca să aflați prima despre noile ceremonii.',
    },
  },
  about: {
    title2026: { ru: 'О премии', en: 'About the award', ro: 'Despre premiu' },
    p2026: {
      ru: 'Организатор премии — Агентство инноваций и развития. Условия участия, порядок проведения, Комиссия и критерии оценки описаны в Положении о премии.',
      en: 'The award is organised by the Innovation and Development Agency. Terms of participation, the procedure, the Committee and the evaluation criteria are described in the Award Regulations.',
      ro: 'Premiul este organizat de Agenția pentru Inovare și Dezvoltare. Condițiile de participare, procedura, Comisia și criteriile de evaluare sunt descrise în Regulamentul premiului.',
    },
  },
  foot: {
    org: { ru: 'Организатор: Агентство инноваций и развития', en: 'Organiser: Innovation and Development Agency', ro: 'Organizator: Agenția pentru Inovare și Dezvoltare' },
    platform: { ru: 'Платформа', en: 'Platform', ro: 'Platformă' },
    ceremonies: { ru: 'Церемонии', en: 'Ceremonies', ro: 'Ceremonii' },
    questions: { ru: 'Вопросы', en: 'Questions', ro: 'Întrebări' },
    site: { ru: 'Сайт Women’s Hub', en: 'Women’s Hub site', ro: 'Site-ul Women’s Hub' },
  },
  common: {
    ceremony: { ru: 'Гала Премия', en: 'Gala Award', ro: 'Gala premiilor' },
    siteName: { ru: 'Гала Премия | Women’s Hub', en: 'Gala Award | Women’s Hub', ro: 'Gala premiilor | Women’s Hub' },
    prev: { ru: 'Назад', en: 'Previous', ro: 'Înapoi' },
    next: { ru: 'Вперёд', en: 'Next', ro: 'Înainte' },
    close: { ru: 'Закрыть', en: 'Close', ro: 'Închide' },
    top: { ru: 'Наверх', en: 'Back to top', ro: 'Sus' },
    menu: { ru: 'Меню', en: 'Menu', ro: 'Meniu' },
    down: { ru: 'Вниз', en: 'Scroll down', ro: 'Mai jos' },
    play: { ru: 'Смотреть видео', en: 'Play the video', ro: 'Redă video-ul' },
    onYoutube: { ru: 'Не воспроизводится? Смотреть на YouTube', en: 'Not playing? Watch on YouTube', ro: 'Nu se redă? Vezi pe YouTube' },
  },
};

// ---------------------------------------------------------------- вспомогательное
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const L = (obj) => LANGS.map((l) => `<span data-lang="${l}">${esc(obj[l])}</span>`).join('');
const attr = (obj, l = 'ru') => esc(obj[l]);

function webpSize(file) {
  const b = fs.readFileSync(file);
  const kind = b.toString('ascii', 12, 16);
  if (kind === 'VP8 ') return { w: b.readUInt16LE(26) & 0x3fff, h: b.readUInt16LE(28) & 0x3fff };
  if (kind === 'VP8L') { const v = b.readUInt32LE(21); return { w: (v & 0x3fff) + 1, h: ((v >> 14) & 0x3fff) + 1 }; }
  if (kind === 'VP8X') return { w: (b.readUIntLE(24, 3)) + 1, h: (b.readUIntLE(27, 3)) + 1 };
  throw new Error('Неизвестный WebP: ' + file);
}

const read = (p) => fs.readFileSync(path.join(here, p), 'utf8');
const sha = (s) => crypto.createHash('md5').update(s).digest('hex').slice(0, 8);

const flipCss = read('src/flip.css');
const styleCss = read('src/style.css').replace('/* FLIP_CSS */', flipCss);
const scriptJs = read('src/script.js');
const flipJs = read('src/flip.js');
const pageFlipJs = read('src/page-flip.browser.js');
const V = { css: sha(styleCss), js: sha(scriptJs), flip: sha(flipJs), pf: sha(pageFlipJs) };

// ---------------------------------------------------------------- разделы страницы
function navLinks(site) {
  const items = [];
  if (site.status === 'upcoming') {
    items.push(['about', 'about'], ['stages', 'stages'], ['ceremonies', 'past'], ['video', 'video']);
  } else {
    items.push(['about', 'about'], ['gallery', 'gallery']);
    if (site.video) items.push(['video', 'video']);
    items.push(['catalog', 'catalog']);
  }
  return items.map(([id, key]) => `<a href="#${id}">${L(key === 'past' ? UI.nav.ceremonies : UI.nav[key])}</a>`).join('');
}

function heroTitle(site) {
  const words = (l) => {
    const parts = site.title[l].split(' ');
    return parts.map((word, i) => `<span class="w" style="--i:${i}"><span${i === parts.length - 1 ? ' class="year"' : ''}>${esc(word)}</span></span>`).join(' ');
  };
  return `<h1>${LANGS.map((l) => `<span data-lang="${l}">${words(l)}</span>`).join('')}</h1>`;
}

function hero(site) {
  const past = site.status === 'past';
  const actions = past
    ? `<a class="btn" href="#gallery">${L(UI.btn.photos)}</a>
       ${site.video ? `<a class="btn btn--ghost" href="#video">${L(UI.btn.video)}</a>` : ''}
       <a class="btn btn--ghost" href="#catalog">${L(UI.btn.magazine)}</a>`
    : `<a class="btn" href="${LINKS.register}" target="_blank" rel="noopener">${L(UI.btn.join)}</a>
       <a class="btn btn--ghost" href="${LINKS.regulations}" target="_blank" rel="noopener">${L(UI.btn.regulations)}</a>`;
  return `
<header class="hero${site.status === 'upcoming' ? ' hero--announce' : ' hero--past'}" id="top">
  ${site.status === 'upcoming' ? '' : `<div class="hero__bg"><img src="assets/img/hero.webp" alt="" fetchpriority="high" decoding="async"></div>`}
  <div class="hero__shade"></div>
  <div class="hero__glow hero__glow--a"></div>
  <div class="hero__glow hero__glow--b"></div>
  <div class="hero__ring" aria-hidden="true"></div>
  <div class="hero__spot" aria-hidden="true"></div>
  <canvas class="dust" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="hero__content">
      <p class="eyebrow reveal-in" style="--i:0">${L(site.eyebrow)}</p>
      ${heroTitle(site)}
      ${past ? '' : `<div class="hero__meta reveal-in" style="--i:2">
        <span class="chip chip--accent"><i></i>${L(site.badge)}</span>
        <span class="chip">${L(site.date)}</span>
      </div>`}
      <p class="lead reveal-in" style="--i:3">${L(site.lead)}</p>
      ${past ? '' : `<div class="hero__actions reveal-in" style="--i:4">${actions}</div>`}
    </div>
  </div>
  <a class="hero__cue" href="#about" aria-label="${attr(UI.common.down)}"></a>
</header>`;
}

function marquee(site) {
  const unit = `<span class="m">${L(site.title)}</span><span class="m">${L(site.date)}</span><span class="m">${L(UI.common.ceremony)}</span>`;
  const run = unit.repeat(5);
  return `<div class="marquee" aria-hidden="true"><div class="marquee__track">${run}${run}</div></div>`;
}

function factsBlock(site) {
  return `<div class="facts">${site.facts.map((f, i) => `
      <div class="fact reveal reveal--right" style="--i:${i}">
        <div class="fact__num"><span data-count="${f.value}">${f.value}</span>${esc(f.suffix)}</div>
        <div class="fact__label">${L(f.label)}</div>
      </div>`).join('')}</div>`;
}

function about(site) {
  if (site.status === 'upcoming') {
    return `
<section class="section about" id="about">
  <div class="wrap about__grid">
    <div class="about__text">
      <p class="eyebrow reveal">${L(UI.about.title2026)}</p>
      <h2 class="h2 reveal">${L(site.title)}</h2>
      <p class="reveal" style="--i:1">${L(site.lead)}</p>
      <p class="reveal" style="--i:2">${L(UI.about.p2026)}</p>
    </div>
    ${factsBlock(site)}
  </div>
</section>`;
  }
  return `
<section class="section about" id="about">
  <div class="wrap about__grid">
    <div class="about__text">
      <p class="eyebrow reveal">${L(site.story.title)}</p>
      <h2 class="h2 reveal">${L(site.title)}</h2>
      ${site.story.paragraphs.map((p, i) => `<p class="reveal" style="--i:${i + 1}">${L(p)}</p>`).join('')}
    </div>
    ${factsBlock(site)}
  </div>
</section>`;
}

function steps(site) {
  return `
<section class="section steps" id="steps">
  <div class="wrap">
    <div class="section__head section__head--center reveal">
      <p class="eyebrow">${L(UI.btn.past)}</p>
      <h2 class="h2">${L(site.title)}</h2>
    </div>
    <div class="steps__grid">${site.steps.map((s, i) => `
      <article class="step reveal" style="--i:${i}"><h3>${L(s.title)}</h3><p>${L(s.text)}</p></article>`).join('')}
    </div>
  </div>
</section>`;
}

function guests(site) {
  const g = site.guests;
  const body = g.list
    ? `<div class="people">${g.list.map((p, i) => `
      <div class="person reveal" style="--i:${i}"><div class="person__mark" aria-hidden="true">${esc(p.name.ru.trim()[0])}</div><div><strong>${L(p.name)}</strong><span class="role">${L(p.role)}</span></div></div>`).join('')}</div>`
    : `<div class="chips">${g.chips.map((c, i) => `<span class="chip reveal reveal--zoom" style="--i:${i % 6}">${L(c)}</span>`).join('')}</div>`;
  return `
<section class="section guests" id="guests">
  <div class="wrap">
    <div class="section__head reveal">
      <p class="eyebrow">${L(site.title)}</p>
      <h2 class="h2">${L(g.title)}</h2>
      <p class="lead">${L(g.intro)}</p>
    </div>
    ${body}
    <blockquote class="quote reveal"><p>${L(site.quote)}</p></blockquote>
  </div>
</section>`;
}

function gallery(site) {
  const g = site.gallery;
  const dir = path.join(root, `gala${site.year}`, 'assets', 'img', g.thumbs);
  const items = [];
  for (let i = 1; i <= g.count; i++) {
    const name = String(i).padStart(2, '0') + '.' + g.ext;
    const { w, h } = webpSize(path.join(dir, name));
    items.push(`<button type="button" class="shot reveal reveal--zoom" style="--i:${i % 3}" data-shot="assets/img/${g.dir}/${name}" data-alt="${attr(site.title)}, ${i}"><img src="assets/img/${g.thumbs}/${name}" width="${w}" height="${h}" loading="lazy" decoding="async" alt="${attr(site.title)}, ${i}"></button>`);
  }
  return `
<section class="section gallery" id="gallery">
  <div class="wrap">
    <div class="section__head section__head--center reveal">
      <p class="eyebrow">${L(site.title)}</p>
      <h2 class="h2">${L(g.title)}</h2>
    </div>
    <div class="masonry">${items.join('')}</div>
  </div>
</section>
<div class="lightbox" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="lightbox__stage"><img class="lightbox__img" alt=""></div>
  <button type="button" class="lightbox__btn lightbox__close" aria-label="${attr(UI.common.close)}">×</button>
  <button type="button" class="lightbox__btn lightbox__prev" aria-label="${attr(UI.common.prev)}">‹</button>
  <button type="button" class="lightbox__btn lightbox__next" aria-label="${attr(UI.common.next)}">›</button>
  <div class="lightbox__count" aria-live="polite"></div>
</div>`;
}

function video(site) {
  const v = site.video;
  const items = v.items ?? [{ youtubeId: v.youtubeId, poster: v.poster }];
  const many = items.length > 1;
  const screens = items.map((it) => `
      <figure class="screen-item reveal reveal--zoom">
        <div class="screen">
          <div class="screen__frame">
            <button type="button" class="screen__poster" data-video="${esc(it.youtubeId)}" data-title="${attr(it.caption ?? v.title)}" aria-label="${attr(UI.common.play)}">
              <img src="${it.poster}" alt="" loading="lazy" decoding="async">
              <span class="screen__play" aria-hidden="true"></span>
            </button>
          </div>
        </div>
        ${it.caption ? `<figcaption class="screen__cap">${L(it.caption)}</figcaption>` : ''}
        <p class="screen__alt"><a href="https://www.youtube.com/watch?v=${esc(it.youtubeId)}" target="_blank" rel="noopener">${L(UI.common.onYoutube)}</a></p>
      </figure>`).join('');
  return `
<section class="section video" id="video">
  <div class="wrap">
    <div class="section__head section__head--center reveal">
      <p class="eyebrow">${L(site.title)}</p>
      <h2 class="h2">${L(v.title)}</h2>
      <p class="lead">${L(v.lead)}</p>
    </div>
    <div class="screens${many ? ' screens--many' : ''}">${screens}
    </div>
  </div>
</section>`;
}

function catalog(site) {
  const c = site.catalog;
  const pages = Array.from({ length: c.pages }, (_, i) => `assets/catalog/${String(i + 1).padStart(3, '0')}.webp`);
  const labels = {
    zoomIn: { ru: 'Приблизить', en: 'Zoom in', ro: 'Mărește' },
    zoomOut: { ru: 'Уменьшить', en: 'Zoom out', ro: 'Micșorează' },
    full: { ru: 'На весь экран', en: 'Full screen', ro: 'Ecran complet' },
    exit: { ru: 'Свернуть', en: 'Exit full screen', ro: 'Restrânge' },
    hint: {
      ru: 'Листайте страницы пальцем влево или вправо. Чтобы прокрутить сайт, проведите вверх или вниз по полям рядом с листом.',
      en: 'Swipe left or right to turn pages. To scroll the site, swipe up or down on the margins beside the page.',
      ro: 'Trageți spre stânga sau spre dreapta pentru a întoarce pagina. Pentru a derula site-ul, trageți în sus sau în jos pe marginile de lângă pagină.',
    },
  };
  return `
<section class="section catalog" id="catalog">
  <div class="wrap">
    <div class="section__head section__head--center reveal">
      <p class="eyebrow">${L(site.title)}</p>
      <h2 class="h2">${L(c.title)}</h2>
    </div>
    <p class="catalog__lead reveal">${L(c.lead)}</p>
    <div class="miro-flip reveal reveal--zoom" data-flip style="--r: ${c.ratio}" data-ratio="${c.ratio}" data-pages="${esc(JSON.stringify(pages))}">
      <div class="miro-flip__stage">
        <div class="miro-flip__viewport" data-flip-viewport><div class="miro-flip__fit"><div class="miro-flip__book" data-flip-book></div></div></div>
        <noscript><a href="${pages[0]}"><img src="${pages[0]}" alt="" class="miro-flip__first"></a></noscript>
      </div>
      <div class="miro-flip__bar" data-flip-bar hidden>
        <button type="button" class="miro-flip__btn" data-flip-prev><span aria-hidden="true">←</span><span class="miro-visually-hidden">${L(UI.common.prev)}</span></button>
        <span class="miro-flip__count" data-flip-count aria-live="polite"></span>
        <button type="button" class="miro-flip__btn" data-flip-next><span aria-hidden="true">→</span><span class="miro-visually-hidden">${L(UI.common.next)}</span></button>
        <button type="button" class="miro-flip__btn miro-flip__btn--text miro-flip__btn--zoom" data-flip-zoom-in><span class="miro-flip__icon" aria-hidden="true">+</span><span class="miro-flip__label">${L(labels.zoomIn)}</span></button>
        <button type="button" class="miro-flip__btn miro-flip__btn--text miro-flip__btn--zoom" data-flip-zoom-out disabled><span class="miro-flip__icon" aria-hidden="true">−</span><span class="miro-flip__label">${L(labels.zoomOut)}</span></button>
        <button type="button" class="miro-flip__btn miro-flip__btn--text" data-flip-full aria-pressed="false"><span data-flip-full-label="enter">${L(labels.full)}</span><span data-flip-full-label="exit" hidden>${L(labels.exit)}</span></button>
      </div>
      <p class="miro-flip__hint" data-flip-hint hidden>${L(labels.hint)}</p>
    </div>
  </div>
</section>`;
}

function otherYears(site, all) {
  const cards = all.filter((s) => s.year !== site.year).map((s, i) => {
    return `
      <a class="year-card reveal" style="--i:${i}" href="https://${s.domain}/">
        <img src="${s.cardImage}" alt="" loading="lazy" decoding="async">
        <div class="year-card__body">
          <p class="year-card__date">${L(s.status === 'past' ? s.date : UI.ceremonies.upcoming)}</p>
          <h3>${L(s.title)}</h3>
          <p>${L(s.status === 'past' ? s.lead : s.badgeText)}</p>
          <span class="year-card__go">${L(UI.btn.open)}</span>
        </div>
      </a>`;
  });
  return `
<section class="section years" id="ceremonies">
  <div class="wrap">
    <div class="section__head section__head--center reveal">
      <p class="eyebrow">${L(UI.ceremonies.title)}</p>
      <h2 class="h2">${L(UI.ceremonies.title)}</h2>
      <p class="lead">${L(UI.ceremonies.lead)}</p>
    </div>
    <div class="years__grid">${cards.join('')}</div>
  </div>
</section>`;
}

function stages(site) {
  const s = site.stages;
  return `
<section class="section stages" id="stages">
  <div class="wrap">
    <div class="section__head section__head--center reveal">
      <p class="eyebrow">${L(s.title)}</p>
      <h2 class="h2">${L(s.title)}</h2>
    </div>
    <div class="stages__grid">${s.items.map((it, i) => `
      <article class="stage stage--${it.state} reveal" style="--i:${i}"><span class="stage__tag">${L(it.tag)}</span><h3>${L(it.title)}</h3><p>${L(it.text)}</p></article>`).join('')}
    </div>
  </div>
</section>`;
}

function pastBlock(site) {
  const p = site.past;
  return `
<section class="section years" id="past">
  <div class="wrap">
    <div class="section__head section__head--center reveal">
      <p class="eyebrow">${L(p.title)}</p>
      <h2 class="h2">${L(p.title)}</h2>
      <p class="lead">${L(p.lead)}</p>
    </div>
    <div class="years__grid">${p.cards.map((c, i) => `
      <a class="year-card reveal" style="--i:${i}" href="${c.url}">
        <img src="${c.image}" alt="" loading="lazy" decoding="async">
        <img class="year-card__cover" src="${c.cover}" alt="" loading="lazy" decoding="async">
        <div class="year-card__body">
          <p class="year-card__date">${L(c.date)}</p>
          <h3>${L(c.title)}</h3>
          <p>${L(c.text)}</p>
          <span class="year-card__go">${L(UI.btn.open)}</span>
        </div>
      </a>`).join('')}
    </div>
  </div>
</section>
<div class="strip" aria-hidden="true"><div class="strip__track">${[...site.strip, ...site.strip].map((src) => { const { w, h } = webpSize(path.join(root, 'gala2026', src)); return `<img src="${src}" width="${w}" height="${h}" alt="" loading="lazy" decoding="async">`; }).join('')}</div></div>`;
}

function joinBlock(site) {
  const text = site.join?.text ?? UI.join.text;
  const title = site.join?.title ?? UI.join.title;
  return `
<section class="section join" id="join">
  <div class="wrap">
    <div class="join__box reveal">
      <h2 class="h2">${L(title)}</h2>
      <p>${L(text)}</p>
      <div class="join__actions">
        <a class="btn btn--light" href="${LINKS.register}" target="_blank" rel="noopener">${L(UI.btn.join)}</a>
        <a class="btn btn--ghost" href="${LINKS.join}" target="_blank" rel="noopener">${L(UI.btn.howto)}</a>
        <a class="btn btn--ghost" href="${LINKS.regulations}" target="_blank" rel="noopener">${L(UI.btn.regulations)}</a>
      </div>
    </div>
  </div>
</section>`;
}

function footer(site, all) {
  return `
<footer class="foot">
  <div class="wrap">
    <div class="foot__grid">
      <div>
        <p class="foot__title">${L(site.title)}</p>
        <p>${L(UI.foot.org)}</p>
      </div>
      <div>
        <h4>${L(UI.foot.ceremonies)}</h4>
        <ul>${all.map((s) => `<li><a href="https://${s.domain}/">${L(s.title)}</a></li>`).join('')}</ul>
      </div>
      ${site.status !== 'upcoming' ? '' : `<div>
        <h4>${L(UI.foot.platform)}</h4>
        <ul>
          <li><a href="${LINKS.platform}" target="_blank" rel="noopener">${L(UI.foot.site)}</a></li>
          <li><a href="${LINKS.regulations}" target="_blank" rel="noopener">${L(UI.btn.regulations)}</a></li>
          <li><a href="mailto:${LINKS.mail}">${LINKS.mail}</a></li>
        </ul>
      </div>`}
    </div>
    <p class="foot__copy">© ${L(site.title)}</p>
  </div>
</footer>
<button type="button" class="totop" aria-label="${attr(UI.common.top)}">↑</button>`;
}

// ---------------------------------------------------------------- страница целиком
function page(site, all) {
  const titles = Object.fromEntries(LANGS.map((l) => [l, `${site.title[l]} — ${UI.common.siteName[l]}`]));
  const url = `https://${site.domain}/`;
  const body = site.status === 'upcoming'
    ? [hero(site), marquee(site), about(site), stages(site), pastBlock(site), video(site)]
    : [hero(site), marquee(site), about(site), steps(site), gallery(site), site.video ? video(site) : '', catalog(site), otherYears(site, all)];

  return `<!DOCTYPE html>
<html lang="ru" data-year="${site.year}" data-accent="${site.accent}" class="no-js">
<head>
<meta charset="UTF-8">
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${esc(titles.ru)}</title>
<meta name="description" content="${attr(site.description)}">
<meta name="theme-color" content="#FBF3EE">
<link rel="canonical" href="${url}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Women’s Hub">
<meta property="og:title" content="${esc(titles.ru)}">
<meta property="og:description" content="${attr(site.description)}">
<meta property="og:url" content="${url}">
<meta property="og:image" content="${url}assets/img/og.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/png" href="assets/img/favicon-w4w.png">
<link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Prata&display=swap" rel="stylesheet">
${site.status === 'upcoming' ? '' : `<link rel="preload" as="image" href="assets/img/hero.webp">`}
<link rel="stylesheet" href="assets/style.css?v=${V.css}">
</head>
<body>
<div class="progress" aria-hidden="true"></div>
<nav class="nav" aria-label="${attr(UI.common.menu)}">
  <div class="wrap nav__inner">
    <div class="nav__links" id="nav-links">${navLinks(site)}</div>
    <div class="nav__tools">
      <div class="lang" role="group" aria-label="Language">${LANGS.map((l) => `<button type="button" data-set-lang="${l}">${l.toUpperCase()}</button>`).join('')}</div>
      ${site.status === 'upcoming' ? `<a class="btn nav__cta" href="${LINKS.register}" target="_blank" rel="noopener">${L(UI.nav.join)}</a>` : ''}
      <button type="button" class="nav__burger" aria-label="${attr(UI.common.menu)}" aria-expanded="false" aria-controls="nav-links"><span></span><span></span><span></span></button>
    </div>
  </div>
</nav>
<main>
${body.join('\n')}
</main>
${footer(site, all)}
<script type="application/json" id="gala-i18n">${JSON.stringify({ title: titles }).replace(/</g, '\\u003c')}</script>
<script src="assets/script.js?v=${V.js}"></script>
${site.status === 'past' ? `<script src="assets/page-flip.browser.js?v=${V.pf}" defer></script>\n<script src="assets/flip.js?v=${V.flip}" defer></script>` : ''}
</body>
</html>
`;
}

// ---------------------------------------------------------------- файлы сайта
const htaccess = `# Главная — index.html, запасная — index.php (он выдаёт ту же страницу). Список файлов папки не показываем.
DirectoryIndex index.html index.php
Options -Indexes

<IfModule mod_mime.c>
    AddType image/webp .webp
    AddType application/javascript .js
</IfModule>

# Сжатие и долгий кеш для картинок, стилей и скриптов (адреса стилей и скриптов с ?v=… меняются при правке)
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml
</IfModule>
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType text/html "access plus 5 minutes"
</IfModule>
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
`;

// Запасной вход для хостинга, который ищет только index.php и не открывает index.html
const indexPhp = `<?php

// Запасной вход для хостинга, который ищет только index.php и не открывает index.html: выдаёт ту же страницу.
// Если хостинг и так открывает index.html, этот файл не мешает (стоит в списке после него, см. .htaccess).
header('Content-Type: text/html; charset=utf-8');
readfile(__DIR__.'/index.html');
`;

function readme(site) {
  return `Женщина Года ${site.year}: сайт ${site.status === 'upcoming' ? '(анонс)' : ''} https://${site.domain}/
=====================================================================

Самостоятельный статичный сайт: чистые HTML, CSS и JS. Laravel, PHP и node на сервере не нужны.
Папка лежит в git вместе с основным проектом: после «git pull» на хостинге достаточно направить домен ${site.domain}
в эту папку (cPanel → «Домены»/«Поддомены» → корень документов = путь к папке gala${site.year}). Больше ничего настраивать не нужно.

Состав:
    index.html, assets/ (style.css, script.js, img/, ${site.status === 'past' ? 'catalog/, flip.js, page-flip.browser.js' : 'картинки прошлых церемоний'}), .htaccess, robots.txt, sitemap.xml

Как менять
----------
Файлы index.html и assets/style.css, script.js СОБИРАЮТСЯ скриптом: править их вручную не нужно (правки пропадут при следующей сборке).
Тексты и факты: tools/gala-sites/data/${site.year}.mjs (каждая фраза на ru, en, ro).
Общий дизайн и анимации: tools/gala-sites/src/style.css и script.js (единые для трёх сайтов).
Сборка из корня проекта:   node tools/gala-sites/build.mjs
Картинки (фото церемоний, страницы журналов) подготавливает:   php tools/gala-sites/prepare-media.php
После сборки закоммитьте папки gala2024, gala2025, gala2026.
`;
}

// ---------------------------------------------------------------- запуск
const sites = [];
for (const year of [2026, 2025, 2024]) sites.push((await import(`./data/${year}.mjs`)).default);

// Картинки для карточек «другие церемонии» на сайтах прошлых лет
const cardFile = (year) => ({ 2024: 'assets/img/card-2024.webp', 2025: 'assets/img/card-2025.webp', 2026: 'assets/img/card-2026.webp' })[year];
for (const s of sites) { s.cardImage = cardFile(s.year); }
sites.find((s) => s.year === 2026).badgeText = { ru: 'Готовим программу. Следите за стартом регистрации.', en: 'We are preparing the programme. Follow the start of registration.', ro: 'Pregătim programul. Urmăriți startul înscrierilor.' };

const today = new Date().toISOString().slice(0, 10);

for (const site of sites) {
  const dir = path.join(root, `gala${site.year}`);
  fs.mkdirSync(path.join(dir, 'assets'), { recursive: true });
  fs.writeFileSync(path.join(dir, 'index.html'), page(site, [...sites].sort((a, b) => b.year - a.year)));
  fs.writeFileSync(path.join(dir, 'assets', 'style.css'), styleCss);
  fs.writeFileSync(path.join(dir, 'assets', 'script.js'), scriptJs);
  if (site.status === 'past') {
    fs.writeFileSync(path.join(dir, 'assets', 'flip.js'), flipJs);
    fs.writeFileSync(path.join(dir, 'assets', 'page-flip.browser.js'), pageFlipJs);
  } else {
    for (const f of ['flip.js', 'page-flip.browser.js']) fs.rmSync(path.join(dir, 'assets', f), { force: true });
  }
  fs.writeFileSync(path.join(dir, '.htaccess'), htaccess);
  fs.writeFileSync(path.join(dir, 'index.php'), indexPhp);
  fs.writeFileSync(path.join(dir, 'robots.txt'), `User-agent: *\nDisallow:\n\nSitemap: https://${site.domain}/sitemap.xml\n`);
  fs.writeFileSync(path.join(dir, 'sitemap.xml'), `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n  <url><loc>https://${site.domain}/</loc><lastmod>${today}</lastmod><changefreq>monthly</changefreq></url>\n</urlset>\n`);
  fs.writeFileSync(path.join(dir, 'README.txt'), readme(site));
  const size = (function walk(d) { return fs.readdirSync(d, { withFileTypes: true }).reduce((n, e) => n + (e.isDirectory() ? walk(path.join(d, e.name)) : fs.statSync(path.join(d, e.name)).size), 0); })(dir);
  console.log(`gala${site.year}: ${(size / 1048576).toFixed(1)} МБ`);
}
