// Сайт «Женщина Года 2026»: анонс (церемония ещё не прошла). Тексты анонса из прежней заглушки; прошлые церемонии показываем по фактам 2024 и 2025.
export default {
  year: 2026,
  domain: 'gala2026.innovation.md',
  accent: 'copper',
  status: 'upcoming',
  title: { ru: 'Женщина Года 2026', en: 'Woman of the Year 2026', ro: 'Femeia Anului 2026' },
  eyebrow: { ru: 'Гала Премия', en: 'Gala Award', ro: 'Gala premiilor' },
  date: { ru: 'Дата будет объявлена', en: 'Date to be announced', ro: 'Data va fi anunțată' },
  badge: { ru: 'Скоро', en: 'Coming soon', ro: 'În curând' },
  description: {
    ru: 'Гала Премия «Женщина Года 2026»: готовим церемонию, на которой мы отметим женщин, чьи достижения вдохновляют. Следите за стартом регистрации.',
    en: 'The “Woman of the Year 2026” Gala Award: we are preparing a ceremony to honour women whose achievements inspire. Follow the start of registration.',
    ro: 'Gala premiilor „Femeia Anului 2026”: pregătim ceremonia în cadrul căreia vom omagia femeile ale căror realizări inspiră. Urmăriți startul înscrierilor.',
  },
  lead: {
    ru: 'Наша команда с вниманием и любовью готовит церемонию, на которой мы отметим женщин, чьи достижения вдохновляют: в бизнесе, обществе, социальной сфере и культуре. Подробности расскажем совсем скоро.',
    en: 'Our team is carefully preparing a ceremony to honour women whose achievements inspire: in business, society, the social sphere and culture. We will share the details very soon.',
    ro: 'Echipa noastră pregătește cu grijă ceremonia în cadrul căreia vom omagia femeile ale căror realizări inspiră: în afaceri, societate, domeniul social și cultură. Vă vom povesti detaliile foarte curând.',
  },
  facts: [
    { value: 2, suffix: '', label: { ru: 'церемонии уже прошли', en: 'ceremonies already held', ro: 'ceremonii deja desfășurate' } },
    { value: 130, suffix: '+', label: { ru: 'гостей на первой церемонии', en: 'guests at the first ceremony', ro: 'invitați la prima ceremonie' } },
    { value: 26, suffix: '', label: { ru: 'номинаций на церемонии 2025', en: 'categories at the 2025 ceremony', ro: 'categorii la ceremonia din 2025' } },
  ],
  stages: {
    title: { ru: 'Что дальше', en: 'What’s next', ro: 'Ce urmează' },
    items: [
      { state: 'now', tag: { ru: 'Сейчас', en: 'Now', ro: 'Acum' }, title: { ru: 'Готовим программу', en: 'Preparing the programme', ro: 'Pregătim programul' }, text: { ru: 'Собираем команду, партнёров и идеи вечера.', en: 'We are putting together the team, the partners and the ideas for the evening.', ro: 'Adunăm echipa, partenerii și ideile pentru seară.' } },
      { state: 'soon', tag: { ru: 'Скоро', en: 'Soon', ro: 'În curând' }, title: { ru: 'Открытие регистрации', en: 'Registration opens', ro: 'Deschiderea înscrierilor' }, text: { ru: 'Заявки принимаются от участниц платформы Women’s Hub.', en: 'Applications are accepted from members of the Women’s Hub platform.', ro: 'Cererile sunt primite de la membrele platformei Women’s Hub.' } },
      { state: 'later', tag: { ru: 'Впереди', en: 'Ahead', ro: 'Urmează' }, title: { ru: 'Церемония награждения', en: 'Award ceremony', ro: 'Ceremonia de decernare' }, text: { ru: 'Вечер, на котором мы отметим женщин, чьи достижения вдохновляют.', en: 'An evening to honour women whose achievements inspire.', ro: 'O seară în care vom omagia femeile ale căror realizări inspiră.' } },
    ],
  },
  past: {
    title: { ru: 'Как это было', en: 'How it was', ro: 'Cum a fost' },
    lead: { ru: 'Две церемонии уже позади: посмотрите фото, видео и журналы прошлых лет.', en: 'Two ceremonies are already behind us: see the photos, the video and the magazines of previous years.', ro: 'Două ceremonii au rămas în urmă: vedeți fotografiile, video-ul și revistele din anii trecuți.' },
    cards: [
      {
        year: 2025,
        url: 'https://gala2025.innovation.md/',
        date: { ru: '12 марта 2026', en: '12 March 2026', ro: '12 martie 2026' },
        title: { ru: 'Женщина Года 2025', en: 'Woman of the Year 2025', ro: 'Femeia Anului 2025' },
        text: { ru: '26 номинаций, посольства шести стран и вечер признания достижений женщин.', en: '26 categories, embassies of six countries and an evening recognising women’s achievements.', ro: '26 de categorii, ambasadele a șase țări și o seară de recunoaștere a realizărilor femeilor.' },
        image: 'assets/img/past-2025-05.webp',
        cover: 'assets/img/cover-2025.webp',
      },
      {
        year: 2024,
        url: 'https://gala2024.innovation.md/',
        date: { ru: '4 марта 2025', en: '4 March 2025', ro: '4 martie 2025' },
        title: { ru: 'Женщина Года 2024', en: 'Woman of the Year 2024', ro: 'Femeia Anului 2024' },
        text: { ru: 'Первая церемония: более 130 гостей и награды в пяти сферах.', en: 'The first ceremony: more than 130 guests and awards in five fields.', ro: 'Prima ceremonie: peste 130 de invitați și premii în cinci domenii.' },
        image: 'assets/img/past-2024-07.webp',
        cover: 'assets/img/cover-2024.webp',
      },
    ],
  },
  strip: [
    'assets/img/past-2025-05.webp', 'assets/img/past-2024-07.webp', 'assets/img/past-2025-01.webp', 'assets/img/past-2024-04.webp',
    'assets/img/past-2025-09.webp', 'assets/img/past-2024-02.webp', 'assets/img/past-2025-08.webp', 'assets/img/past-2025-14.webp',
    'assets/img/past-2025-17.webp', 'assets/img/past-2024-08.webp',
  ],
  video: {
    title: { ru: 'Видео прошлых церемоний', en: 'Videos of the previous ceremonies', ro: 'Video de la ceremoniile anterioare' },
    lead: { ru: 'Обе церемонии в кадре: посмотрите, как это было.', en: 'Both ceremonies on screen: see how it was.', ro: 'Ambele ceremonii în imagini: vedeți cum a fost.' },
    items: [
      { youtubeId: 'j51oFhMdGyI', poster: 'assets/img/past-2024-04.webp', caption: { ru: 'Гала Премия «Женщина Года 2024»', en: 'The “Woman of the Year 2024” Gala Award', ro: 'Gala premiilor „Femeia Anului 2024”' } },
      { youtubeId: 'AcesUVGyAkU', poster: 'assets/img/past-2025-05.webp', caption: { ru: 'Гала Премия «Женщина Года 2025»', en: 'The “Woman of the Year 2025” Gala Award', ro: 'Gala premiilor „Femeia Anului 2025”' } },
    ],
  },
};
