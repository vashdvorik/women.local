@php
    $botUrl = 'https://t.me/WomenComBot';
    $managerUrl = 'https://t.me/lesnichenkoP';
    $communityUrl = config('nutgram.community_url', $botUrl);
@endphp

<!DOCTYPE html>
<html lang="ru" class="miro-page scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Entrepreneurs Platform</title>
    <meta name="description" content="Пространство для женщин-предпринимательниц: обучение, контакты, AI-рекомендации и возможности роста.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --miro-primary: #1c1c1e;
            --miro-on-primary: #ffffff;
            --miro-yellow: #ffd02f;
            --miro-yellow-deep: #fcb900;
            --miro-yellow-light: #fff4c4;
            --miro-blue: #4262ff;
            --miro-coral: #ffc6c6;
            --miro-rose: #ffd8f4;
            --miro-teal: #c3faf5;
            --miro-orange: #ffe6cd;
            --miro-canvas: #ffffff;
            --miro-surface: #f7f8fa;
            --miro-surface-soft: #fafbfc;
            --miro-hairline: #e0e2e8;
            --miro-hairline-soft: #eef0f3;
            --miro-hairline-strong: #c7cad5;
            --miro-ink-deep: #050038;
            --miro-ink: #1c1c1e;
            --miro-charcoal: #2c2c34;
            --miro-slate: #555a6a;
            --miro-steel: #6b6f7e;
            --miro-muted: #a5a8b5;
            --miro-shadow: rgba(5, 0, 56, .08) 0 12px 32px -4px;
            --miro-font: "Roobert PRO", "Noto Sans", -apple-system, BlinkMacSystemFont, sans-serif;
        }

        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        body { margin: 0; background: var(--miro-canvas); color: var(--miro-ink); font-family: var(--miro-font); font-size: 16px; line-height: 1.5; }
        a { color: inherit; text-decoration: none; }
        button, a { -webkit-tap-highlight-color: transparent; }
        img { display: block; max-width: 100%; }
        html:not([lang="ru"]) [data-lang="ru"],
        html:not([lang="en"]) [data-lang="en"],
        html:not([lang="ro"]) [data-lang="ro"] { display: none !important; }
        :focus-visible { outline: 2px solid var(--miro-blue); outline-offset: 3px; border-radius: 6px; }

        .miro-container { width: min(1280px, calc(100% - 64px)); margin: 0 auto; }
        .miro-nav { position: sticky; top: 0; z-index: 30; min-height: 68px; border-bottom: 1px solid var(--miro-hairline-soft); background: rgba(255,255,255,.94); backdrop-filter: blur(16px); }
        .miro-nav__inner { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .miro-brand { display: inline-flex; align-items: center; gap: 10px; white-space: nowrap; font-size: 16px; font-weight: 600; letter-spacing: -.02em; }
        .miro-brand__mark { width: 28px; height: 28px; display: grid; place-items: center; border-radius: 6px; background: var(--miro-yellow); color: var(--miro-primary); font-size: 13px; font-weight: 600; }
        .miro-nav__links { display: flex; align-items: center; gap: 28px; color: var(--miro-slate); font-size: 14px; }
        .miro-nav__links a { transition: color .18s ease; }
        .miro-nav__links a:hover { color: var(--miro-primary); }
        .miro-nav__actions { display: flex; align-items: center; gap: 10px; }
        .miro-languages { display: inline-flex; align-items: center; gap: 2px; margin-right: 8px; padding: 3px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: var(--miro-surface); }
        .miro-languages button { border: 0; border-radius: 9999px; padding: 5px 7px; background: transparent; color: var(--miro-steel); cursor: pointer; font: 500 11px/1 var(--miro-font); }
        .miro-languages button.is-active { background: var(--miro-primary); color: #fff; }
        .miro-mobile-toggle { display: none; width: 40px; height: 40px; flex: 0 0 40px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: #fff; color: var(--miro-primary); cursor: pointer; }
        .miro-nav__mobile-menu { display: none; }

        .miro-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 12px 24px; border-radius: 9999px; font-size: 14px; font-weight: 500; line-height: 1.3; transition: transform .18s ease, background .18s ease, border-color .18s ease; }
        .miro-button:hover { transform: translateY(-1px); }
        .miro-button--primary { background: var(--miro-primary); color: #fff; }
        .miro-button--primary:hover { background: var(--miro-charcoal); }
        .miro-button--secondary { border: 1px solid var(--miro-hairline-strong); background: transparent; color: var(--miro-ink); }
        .miro-button--secondary:hover { border-color: var(--miro-primary); }
        .miro-button--yellow { background: var(--miro-yellow); color: var(--miro-primary); }
        .miro-button--yellow:hover { background: var(--miro-yellow-deep); }
        .miro-button--on-dark { background: #fff; color: var(--miro-primary); }
        .miro-button--small { min-height: 40px; padding: 10px 18px; }

        .miro-hero { padding: 76px 0 72px; text-align: left; }
        .miro-eyebrow { margin: 0 0 20px; color: var(--miro-blue); font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .miro-hero h1 { max-width: 920px; margin: 0; color: var(--miro-ink-deep); font-size: clamp(46px, 7vw, 80px); font-weight: 500; line-height: 1.05; letter-spacing: -.055em; }
        .miro-hero__subtitle { max-width: 620px; margin: 28px 0 0; color: var(--miro-slate); font-size: 18px; line-height: 1.5; }
        .miro-hero__actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-top: 32px; }
        .miro-hero__note { margin: 14px 0 0; color: var(--miro-muted); font-size: 13px; }

        .miro-hero--image { overflow: hidden; }
        .miro-hero__grid { display: grid; grid-template-columns: minmax(0, .86fr) minmax(0, 1.14fr); align-items: center; gap: 48px; }
        .miro-hero__content { position: relative; z-index: 2; }
        .miro-hero--image h1 { max-width: 600px; font-size: clamp(44px, 5.8vw, 76px); }
        .miro-hero--image .miro-eyebrow { color: var(--miro-primary); }
        .miro-hero--image .miro-hero__subtitle { max-width: 520px; }
        .miro-hero--image .miro-hero__actions { justify-content: flex-start; }
        .miro-hero__visual { position: relative; min-height: 530px; margin-right: -32px; }
        .miro-hero__visual::before { content: ""; position: absolute; inset: 18px -24px 8px 24px; border-radius: 52px 0 52px 52px; background: linear-gradient(145deg, var(--miro-yellow) 0 18%, transparent 18% 100%), linear-gradient(180deg, transparent 68%, var(--miro-teal) 68% 100%); transform: rotate(-2deg); }
        .miro-hero__image { position: relative; height: 530px; overflow: hidden; border-radius: 48px 0 48px 48px; box-shadow: var(--miro-shadow); }
        .miro-hero__image img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .miro-hero__image::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,.96) 0%, rgba(255,255,255,.7) 10%, rgba(255,255,255,0) 34%), linear-gradient(180deg, rgba(255,255,255,0) 70%, rgba(255,208,47,.18) 87%, rgba(195,250,245,.55) 100%); pointer-events: none; }
        .miro-hero__arc { position: absolute; z-index: 2; right: -5%; bottom: -22px; left: -6%; height: 72px; border-bottom: 5px solid var(--miro-yellow); border-radius: 0 0 50% 50%; transform: rotate(-3deg); pointer-events: none; }
        .miro-hero__arc--teal { right: -8%; bottom: -34px; left: 12%; border-bottom-color: var(--miro-teal); border-bottom-width: 8px; }

        .miro-logo-wall { position: relative; overflow: hidden; padding: 40px 0 72px; border-top: 1px solid var(--miro-hairline-soft); border-bottom: 1px solid var(--miro-hairline-soft); }
        .miro-logo-wall::before { content: ""; position: absolute; top: -90px; left: 8%; width: 210px; height: 210px; border: 1px solid var(--miro-yellow); border-radius: 50%; opacity: .6; }
        .miro-logo-wall::after { content: ""; position: absolute; right: 8%; bottom: 24px; width: 90px; height: 90px; background-image: radial-gradient(var(--miro-blue) 1.5px, transparent 1.5px); background-size: 14px 14px; opacity: .25; }
        .miro-logo-wall__layout { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, .8fr); align-items: center; gap: 56px; }
        .miro-logo-wall__copy { position: relative; z-index: 2; }
        .miro-logo-wall p { max-width: 540px; margin: 0 0 24px; color: var(--miro-slate); font-size: 17px; line-height: 1.45; }
        .miro-logo-wall__items { display: flex; flex-wrap: wrap; gap: 12px; color: var(--miro-steel); font-size: 14px; font-weight: 500; }
        .miro-logo-wall__items > span { display: inline-flex; align-items: center; max-width: 260px; padding: 9px 14px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: #fff; box-shadow: 0 5px 14px rgba(5,0,56,.04); line-height: 1.25; }
        .miro-logo-wall__items > span:nth-child(1) { background: var(--miro-yellow-light); border-color: #f1d46c; }
        .miro-logo-wall__items > span:nth-child(2) { background: var(--miro-teal); border-color: #a9e9e2; }
        .miro-logo-wall__items > span:nth-child(3) { background: var(--miro-rose); border-color: #f2bce1; }
        .miro-logo-wall__items > span:nth-child(4) { background: var(--miro-coral); border-color: #f0aaaa; }
        .miro-logo-wall__items > span:nth-child(5) { background: var(--miro-orange); border-color: #f4c994; }
        .miro-logo-wall__items > span:nth-child(6) { background: #eef1ff; border-color: #cbd3ff; }
        .miro-logo-wall__visual { position: relative; min-height: 210px; }
        .miro-logo-wall__visual::before { content: ""; position: absolute; top: 6px; right: 4%; width: 170px; height: 170px; border-radius: 50%; background: var(--miro-yellow); opacity: .7; }
        .miro-logo-wall__visual::after { content: ""; position: absolute; right: -2%; bottom: 0; width: 150px; height: 110px; border-radius: 50% 50% 28px 28px; background: var(--miro-teal); opacity: .8; }
        .miro-logo-wall__photo { position: absolute; z-index: 1; top: 10px; left: 12%; width: min(280px, 72%); height: 190px; overflow: hidden; border: 8px solid #fff; border-radius: 30px 30px 30px 8px; box-shadow: var(--miro-shadow); transform: rotate(-3deg); }
        .miro-logo-wall__photo img { width: 100%; height: 100%; object-fit: cover; object-position: 36% center; }
        .miro-logo-wall__sticker { position: absolute; z-index: 2; right: 2%; bottom: 18px; display: inline-flex; align-items: center; min-height: 40px; padding: 8px 14px; border-radius: 8px; background: var(--miro-primary); color: #fff; font-size: 12px; font-weight: 500; box-shadow: 0 8px 18px rgba(5,0,56,.14); transform: rotate(4deg); }

        .miro-section { padding: 96px 0; }
        .miro-section--surface { background: var(--miro-surface); }
        .miro-section--soft { background: var(--miro-surface-soft); }
        .miro-section__head { max-width: 720px; margin-bottom: 40px; }
        .miro-section__head--center { margin-right: auto; margin-left: auto; text-align: center; }
        .miro-section h2 { margin: 0; color: var(--miro-ink-deep); font-size: clamp(34px, 5vw, 60px); font-weight: 500; line-height: 1.1; letter-spacing: -.045em; }
        .miro-section h3 { margin: 0; color: var(--miro-ink-deep); font-size: 28px; font-weight: 500; line-height: 1.25; letter-spacing: -.025em; }
        .miro-section__head p { margin: 18px 0 0; color: var(--miro-slate); font-size: 18px; }
        .miro-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .miro-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .miro-feature-card { min-height: 300px; padding: 32px; border-radius: 28px; }
        .miro-feature-card__icon { width: 44px; height: 44px; display: grid; place-items: center; margin-bottom: 56px; border-radius: 50%; background: rgba(255,255,255,.72); color: var(--miro-primary); font-size: 18px; font-weight: 600; }
        .miro-feature-card p { margin: 14px 0 0; color: var(--miro-charcoal); line-height: 1.55; }
        .miro-feature-card--yellow { background: var(--miro-yellow); }
        .miro-feature-card--coral { background: var(--miro-coral); }
        .miro-feature-card--teal { background: var(--miro-teal); }
        .miro-feature-card--rose { background: var(--miro-rose); }
        .miro-feature-card--orange { background: var(--miro-orange); }

        .miro-proof { display: inline-flex; align-items: center; gap: 12px; margin-top: 24px; padding: 10px 16px 10px 10px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: #fff; box-shadow: 0 8px 20px rgba(5,0,56,.06); color: var(--miro-slate); font-size: 14px; }
        .miro-proof__value { width: 60px; height: 60px; flex: 0 0 60px; display: grid; place-items: center; border-radius: 50%; background: var(--miro-teal); color: var(--miro-primary); font-size: 17px; font-weight: 600; white-space: nowrap; }

        .miro-benefits { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .miro-benefit-card { min-height: 236px; padding: 24px; border: 1px solid var(--miro-hairline-soft); border-radius: 24px; background: #fff; box-shadow: 0 8px 24px rgba(5,0,56,.04); }
        .miro-benefit-card--yellow { background: var(--miro-yellow); }
        .miro-benefit-card--coral { background: var(--miro-coral); }
        .miro-benefit-card--teal { background: var(--miro-teal); }
        .miro-benefit-card--rose { background: var(--miro-rose); }
        .miro-benefit-card--orange { background: var(--miro-orange); }
        .miro-benefit-card--surface { background: var(--miro-surface); }
        .miro-benefit-card__icon { width: 46px; height: 46px; display: grid; place-items: center; margin-bottom: 32px; border-radius: 50%; background: rgba(255,255,255,.76); color: var(--miro-primary); font-size: 19px; font-weight: 600; }
        .miro-benefit-card h3 { margin: 0; color: var(--miro-ink-deep); font-size: 21px; font-weight: 500; line-height: 1.2; }
        .miro-benefit-card p { margin: 10px 0 0; color: var(--miro-charcoal); font-size: 14px; line-height: 1.5; }

        .miro-steps { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
        .miro-steps::before { content: ""; position: absolute; top: 36px; right: 12%; left: 12%; border-top: 2px dashed var(--miro-hairline-strong); }
        .miro-step { position: relative; z-index: 1; text-align: center; }
        .miro-step__number { width: 72px; height: 72px; display: grid; place-items: center; margin: 0 auto 20px; border: 7px solid #fff; border-radius: 50%; background: var(--miro-yellow); color: var(--miro-primary); box-shadow: 0 0 0 1px var(--miro-hairline-strong); font-size: 20px; font-weight: 600; }
        .miro-step:nth-child(2) .miro-step__number { background: var(--miro-rose); }
        .miro-step:nth-child(3) .miro-step__number { background: var(--miro-teal); }
        .miro-step:nth-child(4) .miro-step__number { background: var(--miro-orange); }
        .miro-step h3 { margin: 0; color: var(--miro-ink-deep); font-size: 20px; font-weight: 500; line-height: 1.2; }
        .miro-step p { margin: 10px auto 0; max-width: 220px; color: var(--miro-slate); font-size: 14px; line-height: 1.5; }

        .miro-offers { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .miro-offer { min-height: 154px; padding: 22px 18px; border: 1px solid var(--miro-hairline-soft); border-radius: 18px; background: #fff; box-shadow: 0 8px 20px rgba(5,0,56,.04); }
        .miro-offer__icon { width: 36px; height: 36px; display: grid; place-items: center; margin-bottom: 20px; border: 1px solid var(--miro-hairline-strong); border-radius: 50%; color: var(--miro-blue); font-size: 15px; font-weight: 600; }
        .miro-offer h3 { margin: 0; color: var(--miro-ink-deep); font-size: 16px; font-weight: 500; line-height: 1.25; }
        .miro-offer p { margin: 7px 0 0; color: var(--miro-steel); font-size: 13px; line-height: 1.4; }

        .miro-split { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: center; gap: 72px; }
        .miro-split--reverse > :first-child { order: 2; }
        .miro-split__copy p { max-width: 540px; margin: 20px 0 0; color: var(--miro-slate); font-size: 17px; }
        .miro-list { display: grid; gap: 14px; margin: 28px 0 0; padding: 0; list-style: none; }
        .miro-list li { display: flex; gap: 12px; align-items: flex-start; color: var(--miro-charcoal); }
        .miro-list__check { flex: 0 0 22px; width: 22px; height: 22px; display: grid; place-items: center; border-radius: 50%; background: var(--miro-yellow); color: var(--miro-primary); font-size: 12px; font-weight: 600; }
        .miro-mockup { overflow: hidden; border: 1px solid var(--miro-hairline-soft); border-radius: 16px; background: #fff; box-shadow: var(--miro-shadow); }
        .miro-mockup__bar { height: 42px; display: flex; align-items: center; gap: 6px; padding: 0 16px; border-bottom: 1px solid var(--miro-hairline-soft); }
        .miro-mockup__bar i { width: 8px; height: 8px; border-radius: 50%; background: var(--miro-hairline-strong); }
        .miro-mockup__body { min-height: 330px; padding: 28px; background: var(--miro-surface-soft); }
        .miro-mockup__body--ai { background: linear-gradient(135deg, var(--miro-surface-soft), var(--miro-teal)); }
        .miro-roadmap { display: grid; gap: 12px; }
        .miro-roadmap__row { display: grid; grid-template-columns: 92px repeat(3, 1fr); gap: 8px; align-items: center; }
        .miro-roadmap__label { color: var(--miro-steel); font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .miro-roadmap__cell { min-height: 62px; padding: 12px; border: 1px solid var(--miro-hairline); border-radius: 8px; background: #fff; color: var(--miro-charcoal); font-size: 12px; }
        .miro-roadmap__cell.is-yellow { background: var(--miro-yellow-light); border-color: #f1d46c; }
        .miro-roadmap__cell.is-blue { background: #eef1ff; border-color: #cbd3ff; }
        .miro-ai-card { max-width: 370px; margin: 20px auto 0; padding: 22px; border: 1px solid rgba(24,117,116,.18); border-radius: 16px; background: rgba(255,255,255,.84); box-shadow: 0 8px 24px rgba(5,0,56,.06); }
        .miro-ai-card__tag { display: inline-flex; padding: 4px 10px; border-radius: 9999px; background: #e9e7ff; color: var(--miro-blue); font-size: 12px; font-weight: 600; }
        .miro-ai-card h4 { margin: 16px 0 8px; color: var(--miro-ink-deep); font-size: 22px; font-weight: 500; }
        .miro-ai-card p { margin: 0; color: var(--miro-slate); font-size: 14px; }
        .miro-ai-card__meter { height: 8px; margin-top: 18px; overflow: hidden; border-radius: 9999px; background: #e5e8ef; }
        .miro-ai-card__meter span { display: block; width: 86%; height: 100%; border-radius: inherit; background: var(--miro-blue); }

        .miro-image-card { overflow: hidden; border-radius: 28px; background: #fff; box-shadow: var(--miro-shadow); }
        .miro-image-card img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
        .miro-image-card__caption { padding: 24px; }
        .miro-image-card__caption p { margin: 10px 0 0; color: var(--miro-slate); }
        .miro-tag { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; background: var(--miro-yellow-light); color: #746019; font-size: 13px; font-weight: 600; }

        .miro-member-card { display: grid; grid-template-columns: 112px 1fr; gap: 20px; align-items: center; padding: 16px; border: 1px solid var(--miro-hairline-soft); border-radius: 16px; background: #fff; }
        .miro-member-card img { width: 112px; height: 112px; border-radius: 12px; object-fit: cover; }
        .miro-member-card h4 { margin: 0; color: var(--miro-ink-deep); font-size: 18px; font-weight: 500; }
        .miro-member-card p { margin: 6px 0 0; color: var(--miro-steel); font-size: 14px; }
        .miro-member-card__link { display: inline-flex; margin-top: 14px; color: var(--miro-blue); font-size: 14px; font-weight: 500; }

        .miro-event-card { overflow: hidden; border: 1px solid var(--miro-hairline-soft); border-radius: 16px; background: #fff; }
        .miro-event-card img { width: 100%; height: 196px; object-fit: cover; }
        .miro-event-card__body { padding: 24px; }
        .miro-event-card__body h3 { margin-top: 16px; font-size: 22px; }
        .miro-event-card__body p { margin: 10px 0 0; color: var(--miro-slate); }

        .miro-story { display: grid; grid-template-columns: 1fr 1fr; overflow: hidden; border-radius: 28px; background: var(--miro-primary); color: #fff; }
        .miro-story img { width: 100%; height: 100%; min-height: 360px; object-fit: cover; }
        .miro-story__body { display: flex; flex-direction: column; justify-content: center; padding: 48px; }
        .miro-story__body blockquote { margin: 20px 0 24px; color: #fff; font-size: clamp(24px, 3vw, 36px); font-weight: 500; line-height: 1.2; letter-spacing: -.025em; }
        .miro-story__body p { margin: 0; color: var(--miro-muted); }

        .miro-cta { padding: 64px 32px; border-radius: 32px; background: var(--miro-primary); color: #fff; text-align: center; }
        .miro-cta h2 { max-width: 720px; margin: 0 auto; color: #fff; }
        .miro-cta p { max-width: 560px; margin: 18px auto 0; color: var(--miro-muted); font-size: 18px; }
        .miro-cta .miro-hero__actions { margin-top: 28px; }

        .miro-footer { padding: 64px 0 28px; background: var(--miro-primary); color: #fff; }
        .miro-footer__top { display: grid; grid-template-columns: 1.4fr repeat(4, 1fr); gap: 32px; padding-bottom: 56px; }
        .miro-footer__brand p { max-width: 250px; margin: 18px 0 0; color: var(--miro-muted); font-size: 14px; }
        .miro-footer h4 { margin: 0 0 14px; font-size: 16px; font-weight: 500; }
        .miro-footer ul { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; color: var(--miro-muted); font-size: 14px; }
        .miro-footer li a:hover { color: #fff; }
        .miro-footer__bottom { display: flex; justify-content: space-between; gap: 20px; padding-top: 22px; border-top: 1px solid rgba(255,255,255,.12); color: var(--miro-muted); font-size: 12px; }

        @media (max-width: 1023px) {
            .miro-container { width: min(100% - 40px, 760px); }
            .miro-hero__grid { grid-template-columns: 1fr; gap: 40px; }
            .miro-hero__visual { min-height: 440px; margin-right: -20px; }
            .miro-hero__image { height: 440px; }
            .miro-nav__links, .miro-nav__actions > .miro-languages, .miro-nav__actions > .miro-button { display: none; }
            .miro-mobile-toggle { display: grid; place-items: center; }
            .miro-nav.is-open .miro-nav__links { position: absolute; top: 68px; left: 0; right: 0; display: grid; gap: 0; padding: 12px 20px 20px; border-bottom: 1px solid var(--miro-hairline); background: #fff; }
            .miro-nav.is-open .miro-nav__links a { padding: 14px 0; border-bottom: 1px solid var(--miro-hairline-soft); }
            .miro-nav.is-open .miro-nav__mobile-menu { display: grid; gap: 10px; padding-top: 14px; }
            .miro-nav.is-open .miro-nav__mobile-menu .miro-languages { width: max-content; margin: 0 0 4px; }
            .miro-nav.is-open .miro-nav__mobile-menu .miro-button { width: 100%; }
            .miro-grid-3 { grid-template-columns: 1fr; }
            .miro-logo-wall__layout { grid-template-columns: 1fr 1fr; gap: 28px; }
            .miro-benefits { grid-template-columns: repeat(2, 1fr); }
            .miro-offers { grid-template-columns: repeat(2, 1fr); }
            .miro-split { grid-template-columns: 1fr; gap: 40px; }
            .miro-split--reverse > :first-child { order: initial; }
            .miro-footer__top { grid-template-columns: repeat(3, 1fr); }
            .miro-footer__brand { grid-column: 1 / -1; }
        }

        @media (max-width: 767px) {
            .miro-container { width: min(100% - 32px, 540px); }
            .miro-nav__inner { gap: 12px; }
            .miro-brand { font-size: 14px; }
            .miro-languages { margin-right: 0; }
            .miro-hero { padding: 72px 0 48px; }
            .miro-hero--image h1 { font-size: 48px; }
            .miro-hero__subtitle, .miro-section__head p, .miro-cta p { font-size: 16px; }
            .miro-logo-wall__layout { grid-template-columns: 1fr; gap: 28px; }
            .miro-logo-wall__visual { min-height: 200px; }
            .miro-logo-wall__visual::before { right: 10%; }
            .miro-logo-wall__photo { left: 8%; }
            .miro-hero__visual { min-height: 360px; margin-right: -16px; }
            .miro-hero__image { height: 360px; border-radius: 34px 0 34px 34px; }
            .miro-hero__visual::before { inset: 12px -14px 4px 12px; border-radius: 38px 0 38px 38px; }
            .miro-hero__arc { height: 48px; border-bottom-width: 4px; }
            .miro-hero__arc--teal { border-bottom-width: 6px; }
            .miro-section { padding: 64px 0; }
            .miro-section h2 { font-size: 38px; }
            .miro-grid-2 { grid-template-columns: 1fr; }
            .miro-benefits, .miro-steps, .miro-offers { grid-template-columns: 1fr; }
            .miro-steps { gap: 32px; }
            .miro-steps::before { top: 36px; right: auto; bottom: 36px; left: 50%; border-top: 0; border-left: 2px dashed var(--miro-hairline-strong); }
            .miro-feature-card { min-height: 260px; padding: 24px; }
            .miro-feature-card__icon { margin-bottom: 40px; }
            .miro-roadmap__row { grid-template-columns: 66px repeat(3, 1fr); }
            .miro-roadmap__cell { min-height: 76px; padding: 8px; font-size: 10px; }
            .miro-member-card { grid-template-columns: 80px 1fr; gap: 14px; }
            .miro-member-card img { width: 80px; height: 80px; }
            .miro-story { grid-template-columns: 1fr; }
            .miro-story img { min-height: 240px; max-height: 280px; }
            .miro-story__body { padding: 28px; }
            .miro-footer__top { grid-template-columns: repeat(2, 1fr); }
            .miro-footer__bottom { flex-direction: column; }
        }

        @media (max-width: 479px) {
            .miro-hero--image h1 { font-size: 36px; }
            .miro-footer__top { grid-template-columns: 1fr 1fr; gap: 28px 16px; }
        }
    </style>
</head>
<body>
    <nav class="miro-nav" id="miro-nav">
        <div class="miro-container miro-nav__inner">
            <a href="#top" class="miro-brand">
                <span class="miro-brand__mark">W</span>
                <span>Women</span>
            </a>
            <div class="miro-nav__links" id="miro-nav-links">
                <a href="#top"><span data-lang="ru">Главная</span><span data-lang="en">Home</span><span data-lang="ro">Acasă</span></a>
                <a href="#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
                <a href="#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
                <a href="#members"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a>
                <a href="#events"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a>
                <a href="#opportunities"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a>
                <a href="#contact"><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></a>
                <div class="miro-nav__mobile-menu">
                    <div class="miro-languages" aria-label="Language switcher">
                        <button type="button" data-locale="ru">RU</button>
                        <button type="button" data-locale="en">EN</button>
                        <button type="button" data-locale="ro">RO</button>
                    </div>
                    <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary">
                        <span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span>
                    </a>
                    <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary">
                        <span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span>
                    </a>
                </div>
            </div>
            <div class="miro-nav__actions">
                <div class="miro-languages" aria-label="Language switcher">
                    <button type="button" data-locale="ru">RU</button>
                    <button type="button" data-locale="en">EN</button>
                    <button type="button" data-locale="ro">RO</button>
                </div>
                <a href="{{ route('account.login') }}" class="miro-button miro-button--secondary miro-button--small">
                    <span data-lang="ru">Войти</span><span data-lang="en">Log in</span><span data-lang="ro">Intră</span>
                </a>
                <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary miro-button--small">
                    <span data-lang="ru">Присоединиться</span><span data-lang="en">Get started</span><span data-lang="ro">Începe</span>
                </a>
                <button type="button" class="miro-mobile-toggle" id="miro-mobile-toggle" aria-label="Menu" aria-expanded="false" aria-controls="miro-nav-links">☰</button>
            </div>
        </div>
    </nav>

    <main id="top">
        <section class="miro-hero miro-hero--image">
            <div class="miro-container">
                <div class="miro-hero__grid">
                    <div class="miro-hero__content">
                        <h1>
                            <span data-lang="ru">ОНЛАЙН-ПЛАТФОРМА<br>ЖЕНСКОГО<br>БИЗНЕСА</span>
                            <span data-lang="en">ONLINE PLATFORM<br>FOR WOMEN<br>ENTREPRENEURS</span>
                            <span data-lang="ro">PLATFORMĂ ONLINE<br>PENTRU FEMEI<br>ANTREPRENOARE</span>
                        </h1>
                        <p class="miro-hero__subtitle">
                            <span data-lang="ru">Цифровое пространство для обучения, нетворкинга, менторства и роста бизнеса в регионе.</span>
                            <span data-lang="en">A digital space for learning, networking, mentorship, and business growth across the region.</span>
                            <span data-lang="ro">Un spațiu digital pentru învățare, networking, mentorat și creșterea afacerilor în regiune.</span>
                        </p>
                        <div class="miro-hero__actions">
                            <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--primary">
                                <span data-lang="ru">Присоединиться к платформе</span><span data-lang="en">Join the Platform</span><span data-lang="ro">Alătură-te platformei</span>
                            </a>
                            <a href="#learning" class="miro-button miro-button--secondary">
                                <span data-lang="ru">Изучить обучение</span><span data-lang="en">Explore Learning</span><span data-lang="ro">Explorează învățarea</span>
                            </a>
                        </div>
                        <div class="miro-proof">
                            <span class="miro-proof__value">500+</span>
                            <span data-lang="ru">женщин уже объединены в сообщество</span><span data-lang="en">women already connected through the community</span><span data-lang="ro">de femei deja conectate în comunitate</span>
                        </div>
                    </div>
                    <div class="miro-hero__visual" aria-label="Women entrepreneurs collaborating">
                        <div class="miro-hero__image">
                            <img src="{{ asset('images/hero-community.webp') }}" alt="Women entrepreneurs collaborating around a laptop">
                        </div>
                        <span class="miro-hero__arc"></span>
                        <span class="miro-hero__arc miro-hero__arc--teal"></span>
                    </div>
                </div>

            </div>
        </section>

        <section class="miro-logo-wall">
            <div class="miro-container miro-logo-wall__layout">
                <div class="miro-logo-wall__copy">
                    <p><span data-lang="ru">Цифровое пространство для развития бизнеса с обоих берегов</span><span data-lang="en">One digital space for women entrepreneurs across both banks</span><span data-lang="ro">Un spațiu digital pentru femei antreprenoare de pe ambele maluri</span></p>
                    <div class="miro-logo-wall__items">
                        <span><span data-lang="ru">Видимость бизнеса</span><span data-lang="en">Business visibility</span><span data-lang="ro">Vizibilitatea afacerii</span></span>
                        <span><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></span>
                        <span><span data-lang="ru">Партнёры и рынки</span><span data-lang="en">Partners &amp; markets</span><span data-lang="ro">Partenere și piețe</span></span>
                        <span><span data-lang="ru">Наставничество</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></span>
                        <span><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></span>
                        <span><span data-lang="ru">Запросы и предложения</span><span data-lang="en">Requests &amp; offers</span><span data-lang="ro">Solicitări și oferte</span></span>
                    </div>
                </div>
                <div class="miro-logo-wall__visual" aria-hidden="true">
                    <div class="miro-logo-wall__photo">
                        <img src="{{ asset('images/story-mentor.webp') }}" alt="">
                    </div>
                    <span class="miro-logo-wall__sticker"><span data-lang="ru">Связи, которые работают</span><span data-lang="en">Connections that move business</span><span data-lang="ro">Conexiuni care dezvoltă afaceri</span></span>
                </div>
            </div>
        </section>

        <section class="miro-section" id="benefits">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Зачем присоединяться</span><span data-lang="en">Why join?</span><span data-lang="ro">De ce să te alături?</span></p>
                    <h2><span data-lang="ru">Ключевые преимущества платформы</span><span data-lang="en">Key benefits for your next step</span><span data-lang="ro">Beneficii pentru următorul tău pas</span></h2>
                    <p><span data-lang="ru">Всё необходимое для обучения, полезных знакомств и роста бизнеса — в одном понятном пространстве.</span><span data-lang="en">Everything you need to learn, connect and grow your business in one clear space.</span><span data-lang="ro">Tot ce ai nevoie pentru a învăța, a te conecta și a-ți dezvolta afacerea într-un singur spațiu clar.</span></p>
                </div>
                <div class="miro-benefits">
                    <article class="miro-benefit-card miro-benefit-card--yellow">
                        <div class="miro-benefit-card__icon">↗</div>
                        <h3><span data-lang="ru">Центр обучения</span><span data-lang="en">Learning Hub</span><span data-lang="ro">Hub de învățare</span></h3>
                        <p><span data-lang="ru">Онлайн-курсы, видео и практические модули для бизнеса.</span><span data-lang="en">Online courses, videos and practical business modules.</span><span data-lang="ro">Cursuri online, videoclipuri și module practice pentru afaceri.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--teal">
                        <div class="miro-benefit-card__icon">◎</div>
                        <h3><span data-lang="ru">Каталог участниц</span><span data-lang="en">Member Directory</span><span data-lang="ro">Directorul membrelor</span></h3>
                        <p><span data-lang="ru">Находите предпринимательниц и легко открывайте полезные связи.</span><span data-lang="en">Discover women-led businesses and connect easily.</span><span data-lang="ro">Descoperă afaceri conduse de femei și conectează-te ușor.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--rose">
                        <div class="miro-benefit-card__icon">✦</div>
                        <h3><span data-lang="ru">AI-подбор</span><span data-lang="en">AI Matching</span><span data-lang="ro">Potrivire AI</span></h3>
                        <p><span data-lang="ru">Умные рекомендации партнёров, менторов и возможностей.</span><span data-lang="en">Smart recommendations for partners, mentors and opportunities.</span><span data-lang="ro">Recomandări inteligente pentru partenere, mentori și oportunități.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--coral">
                        <div class="miro-benefit-card__icon">◫</div>
                        <h3><span data-lang="ru">События и возможности</span><span data-lang="en">Events &amp; Opportunities</span><span data-lang="ro">Evenimente și oportunități</span></h3>
                        <p><span data-lang="ru">Тренинги, форумы, гранты и встречи для сотрудничества.</span><span data-lang="en">Trainings, forums, grants and meetings for collaboration.</span><span data-lang="ro">Traininguri, forumuri, granturi și întâlniri pentru colaborare.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--orange">
                        <div class="miro-benefit-card__icon">↔</div>
                        <h3><span data-lang="ru">Менторство</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></h3>
                        <p><span data-lang="ru">Получайте советы от опытных предпринимательниц и эксперток.</span><span data-lang="en">Access advice from experienced women entrepreneurs.</span><span data-lang="ro">Primește sfaturi de la antreprenoare cu experiență.</span></p>
                    </article>
                    <article class="miro-benefit-card miro-benefit-card--surface">
                        <div class="miro-benefit-card__icon">➤</div>
                        <h3><span data-lang="ru">Уведомления Telegram</span><span data-lang="en">Telegram Alerts</span><span data-lang="ro">Alerte Telegram</span></h3>
                        <p><span data-lang="ru">Будьте в курсе новостей и получайте персональные уведомления.</span><span data-lang="en">Stay updated with news and tailored notifications.</span><span data-lang="ro">Rămâi la curent cu noutățile și notificările personalizate.</span></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--soft" id="how-it-works">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Как это работает</span><span data-lang="en">How it works</span><span data-lang="ro">Cum funcționează</span></p>
                    <h2><span data-lang="ru">Четыре шага от идеи к действию</span><span data-lang="en">Four steps from idea to action</span><span data-lang="ro">Patru pași de la idee la acțiune</span></h2>
                </div>
                <div class="miro-steps">
                    <article class="miro-step">
                        <div class="miro-step__number">01</div>
                        <h3><span data-lang="ru">Зарегистрируйтесь</span><span data-lang="en">Register</span><span data-lang="ro">Înregistrează-te</span></h3>
                        <p><span data-lang="ru">Быстро присоединитесь к нашему сообществу.</span><span data-lang="en">Sign up quickly and join our community.</span><span data-lang="ro">Înscrie-te rapid și alătură-te comunității.</span></p>
                    </article>
                    <article class="miro-step">
                        <div class="miro-step__number">02</div>
                        <h3><span data-lang="ru">Создайте профиль</span><span data-lang="en">Create your profile</span><span data-lang="ro">Creează-ți profilul</span></h3>
                        <p><span data-lang="ru">Расскажите о себе и своём бизнесе.</span><span data-lang="en">Tell us about yourself and your business.</span><span data-lang="ro">Spune-ne despre tine și afacerea ta.</span></p>
                    </article>
                    <article class="miro-step">
                        <div class="miro-step__number">03</div>
                        <h3><span data-lang="ru">Учитесь и знакомьтесь</span><span data-lang="en">Learn &amp; connect</span><span data-lang="ro">Învață și conectează-te</span></h3>
                        <p><span data-lang="ru">Используйте материалы и знакомьтесь с участницами и экспертами.</span><span data-lang="en">Access resources and connect with members and experts.</span><span data-lang="ro">Accesează resurse și conectează-te cu membre și experți.</span></p>
                    </article>
                    <article class="miro-step">
                        <div class="miro-step__number">04</div>
                        <h3><span data-lang="ru">Растите бизнес</span><span data-lang="en">Grow your business</span><span data-lang="ro">Crește-ți afacerea</span></h3>
                        <p><span data-lang="ru">Находите возможности, сотрудничайте и развивайтесь.</span><span data-lang="en">Find opportunities, collaborate and grow.</span><span data-lang="ro">Găsește oportunități, colaborează și crește.</span></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="opportunities">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Возможности платформы</span><span data-lang="en">What the platform offers</span><span data-lang="ro">Ce oferă platforma</span></p>
                    <h2><span data-lang="ru">Инструменты для движения вперёд</span><span data-lang="en">Tools to keep moving forward</span><span data-lang="ro">Instrumente pentru a merge mai departe</span></h2>
                </div>
                <div class="miro-offers">
                    <article class="miro-offer"><div class="miro-offer__icon">◎</div><h3><span data-lang="ru">Личный профиль</span><span data-lang="en">Personal profile</span><span data-lang="ro">Profil personal</span></h3><p><span data-lang="ru">Покажите свой опыт и запрос.</span><span data-lang="en">Show your experience and needs.</span><span data-lang="ro">Arată-ți experiența și nevoile.</span></p></article>
                    <article class="miro-offer"><div class="miro-offer__icon">□</div><h3><span data-lang="ru">Витрина бизнеса</span><span data-lang="en">Business showcase</span><span data-lang="ro">Prezentarea afacerii</span></h3><p><span data-lang="ru">Расскажите о продукте и услугах.</span><span data-lang="en">Present your products and services.</span><span data-lang="ro">Prezintă produsele și serviciile.</span></p></article>
                    <article class="miro-offer"><div class="miro-offer__icon">△</div><h3><span data-lang="ru">Учебные модули</span><span data-lang="en">Training modules</span><span data-lang="ro">Module de instruire</span></h3><p><span data-lang="ru">Практические знания для бизнеса.</span><span data-lang="en">Practical knowledge for business.</span><span data-lang="ro">Cunoștințe practice pentru afaceri.</span></p></article>
                    <article class="miro-offer"><div class="miro-offer__icon">↔</div><h3><span data-lang="ru">Нетворкинг</span><span data-lang="en">Networking</span><span data-lang="ro">Networking</span></h3><p><span data-lang="ru">Полезные связи и обмен опытом.</span><span data-lang="en">Useful connections and peer learning.</span><span data-lang="ro">Conexiuni utile și schimb de experiență.</span></p></article>
                    <article class="miro-offer"><div class="miro-offer__icon">✦</div><h3><span data-lang="ru">Менторство</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></h3><p><span data-lang="ru">Поддержка опытных эксперток.</span><span data-lang="en">Support from experienced experts.</span><span data-lang="ro">Sprijin de la experți cu experiență.</span></p></article>
                    <article class="miro-offer"><div class="miro-offer__icon">+</div><h3><span data-lang="ru">Запросы бизнеса</span><span data-lang="en">Business requests</span><span data-lang="ro">Solicitări de business</span></h3><p><span data-lang="ru">Публикуйте задачи и ищите решения.</span><span data-lang="en">Share challenges and find solutions.</span><span data-lang="ro">Distribuie provocări și găsește soluții.</span></p></article>
                    <article class="miro-offer"><div class="miro-offer__icon">▦</div><h3><span data-lang="ru">Календарь событий</span><span data-lang="en">Events calendar</span><span data-lang="ro">Calendarul evenimentelor</span></h3><p><span data-lang="ru">Планируйте обучение и встречи.</span><span data-lang="en">Plan learning and meetings.</span><span data-lang="ro">Planifică învățarea și întâlnirile.</span></p></article>
                    <article class="miro-offer"><div class="miro-offer__icon">≡</div><h3><span data-lang="ru">Библиотека ресурсов</span><span data-lang="en">Resource library</span><span data-lang="ro">Biblioteca de resurse</span></h3><p><span data-lang="ru">Собранные материалы для роста.</span><span data-lang="en">Curated materials for growth.</span><span data-lang="ro">Materiale selectate pentru creștere.</span></p></article>
                </div>
            </div>
        </section>

        <section class="miro-section" id="about">
            <div class="miro-container">
                <div class="miro-section__head">
                    <p class="miro-eyebrow"><span data-lang="ru">О платформе</span><span data-lang="en">About the platform</span><span data-lang="ro">Despre platformă</span></p>
                    <h2><span data-lang="ru">Ваше сообщество — это рабочее пространство для роста</span><span data-lang="en">Your community is a workspace for growth</span><span data-lang="ro">Comunitatea ta este un spațiu pentru creștere</span></h2>
                    <p><span data-lang="ru">Участницы приходят с разными задачами, но находят здесь одну понятную точку опоры: людей, знания и практические возможности.</span><span data-lang="en">Members arrive with different challenges and find one clear place to move forward: people, knowledge and practical opportunities.</span><span data-lang="ro">Membrele vin cu provocări diferite și găsesc un punct clar de sprijin: oameni, cunoștințe și oportunități practice.</span></p>
                </div>
                <div class="miro-grid-3">
                    <article class="miro-feature-card miro-feature-card--yellow">
                        <div class="miro-feature-card__icon">01</div>
                        <h3><span data-lang="ru">Видеть картину целиком</span><span data-lang="en">See the whole picture</span><span data-lang="ro">Vezi imaginea completă</span></h3>
                        <p><span data-lang="ru">Профиль, запросы, возможности и события собраны в одном понятном цифровом пространстве.</span><span data-lang="en">Your profile, needs, opportunities and events live in one clear digital space.</span><span data-lang="ro">Profilul, nevoile, oportunitățile și evenimentele într-un singur spațiu digital.</span></p>
                    </article>
                    <article class="miro-feature-card miro-feature-card--coral">
                        <div class="miro-feature-card__icon">02</div>
                        <h3><span data-lang="ru">Соединяться точнее</span><span data-lang="en">Connect with purpose</span><span data-lang="ro">Conectează-te cu scop</span></h3>
                        <p><span data-lang="ru">AI помогает найти участниц с близкой экспертизой, запросом или форматом сотрудничества.</span><span data-lang="en">AI helps you find members with relevant expertise, needs or collaboration formats.</span><span data-lang="ro">AI te ajută să găsești membre cu expertiză și nevoi relevante.</span></p>
                    </article>
                    <article class="miro-feature-card miro-feature-card--teal">
                        <div class="miro-feature-card__icon">03</div>
                        <h3><span data-lang="ru">Двигаться вместе</span><span data-lang="en">Move forward together</span><span data-lang="ro">Creșteți împreună</span></h3>
                        <p><span data-lang="ru">От первого знакомства до партнёрства, обучения и новых клиентов — каждый шаг становится действием.</span><span data-lang="en">From a first introduction to partnership, learning and new clients — every step becomes action.</span><span data-lang="ro">De la prima conexiune la parteneriat, învățare și clienți noi.</span></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="features">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Возможности</span><span data-lang="en">What you can do</span><span data-lang="ro">Ce poți face</span></p>
                    <h2><span data-lang="ru">От идеи к следующему действию</span><span data-lang="en">From an idea to the next action</span><span data-lang="ro">De la idee la următoarea acțiune</span></h2>
                    <p><span data-lang="ru">Платформа помогает не просто хранить информацию, а превращать её в движение.</span><span data-lang="en">The platform turns information into momentum, not just another place to store it.</span><span data-lang="ro">Platforma transformă informația în mișcare, nu doar într-un alt loc de stocare.</span></p>
                </div>
                <div class="miro-split">
                    <div class="miro-split__copy">
                        <span class="miro-tag"><span data-lang="ru">AI-powered</span><span data-lang="en">AI-powered</span><span data-lang="ro">Cu AI</span></span>
                        <h3 style="margin-top: 18px;"><span data-lang="ru">Найдите контакт, который нужен именно сейчас</span><span data-lang="en">Find the connection you need right now</span><span data-lang="ro">Găsește conexiunea de care ai nevoie acum</span></h3>
                        <p><span data-lang="ru">Опишите свой запрос обычными словами. AI сопоставит его с профилями участниц и покажет близкие варианты.</span><span data-lang="en">Describe your request in your own words. AI matches it with member profiles and surfaces relevant options.</span><span data-lang="ro">Descrie cererea în cuvintele tale. AI o potrivește cu profilurile membrelor.</span></p>
                        <ul class="miro-list">
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Поиск партнёров, экспертов и клиентов</span><span data-lang="en">Find partners, experts and clients</span><span data-lang="ro">Găsește partenere, experte și clienți</span></li>
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Рекомендации на основе профиля</span><span data-lang="en">Recommendations based on your profile</span><span data-lang="ro">Recomandări bazate pe profil</span></li>
                            <li><span class="miro-list__check">✓</span><span data-lang="ru">Прямой контакт через Telegram</span><span data-lang="en">Direct contact through Telegram</span><span data-lang="ro">Contact direct prin Telegram</span></li>
                        </ul>
                    </div>
                    <div class="miro-mockup">
                        <div class="miro-mockup__bar"><i></i><i></i><i></i></div>
                        <div class="miro-mockup__body miro-mockup__body--ai">
                            <div class="miro-ai-card">
                                <span class="miro-ai-card__tag">AI match</span>
                                <h4>Export partner</h4>
                                <p>Three relevant profiles found in your community.</p>
                                <div class="miro-ai-card__meter"><span></span></div>
                                <p style="margin-top: 8px; color: #4262ff; font-weight: 500;">86% relevance</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="miro-split miro-split--reverse" style="margin-top: 96px;">
                    <div class="miro-split__copy">
                        <span class="miro-tag" style="background: #eef1ff; color: var(--miro-blue);"><span data-lang="ru">Shared workspace</span><span data-lang="en">Shared workspace</span><span data-lang="ro">Spațiu comun</span></span>
                        <h3 style="margin-top: 18px;"><span data-lang="ru">Публикуйте возможности, а не только новости</span><span data-lang="en">Share opportunities, not just updates</span><span data-lang="ro">Distribuie oportunități, nu doar noutăți</span></h3>
                        <p><span data-lang="ru">Проект, встреча, событие или запрос на сотрудничество — публикация сразу попадает в общий поток и Telegram-уведомления участниц.</span><span data-lang="en">A project, meeting, event or collaboration request reaches the shared feed and Telegram notifications.</span><span data-lang="ro">Un proiect, o întâlnire, un eveniment sau o cerere de colaborare ajunge în fluxul comun și în Telegram.</span></p>
                        <a href="{{ route('account.login') }}" class="miro-button miro-button--primary" style="margin-top: 28px;"><span data-lang="ru">Открыть кабинет</span><span data-lang="en">Open the cabinet</span><span data-lang="ro">Deschide cabinetul</span></a>
                    </div>
                    <div class="miro-mockup">
                        <div class="miro-mockup__bar"><i></i><i></i><i></i></div>
                        <div class="miro-mockup__body">
                            <div class="miro-roadmap">
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Need</span><div class="miro-roadmap__cell is-yellow">Find a mentor</div><div class="miro-roadmap__cell">New market</div><div class="miro-roadmap__cell">Local partner</div></div>
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Action</span><div class="miro-roadmap__cell is-blue">Workshop · 14 Jun</div><div class="miro-roadmap__cell">Ask the community</div><div class="miro-roadmap__cell is-yellow">Post an opportunity</div></div>
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Result</span><div class="miro-roadmap__cell">New contact</div><div class="miro-roadmap__cell is-blue">Shared learning</div><div class="miro-roadmap__cell">Next step</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section" id="learning">
            <div class="miro-container">
                <div class="miro-split">
                    <div class="miro-image-card">
                        <img src="{{ asset('images/story-mentor.webp') }}" alt="Mentoring conversation" loading="lazy">
                        <div class="miro-image-card__caption">
                            <span class="miro-tag"><span data-lang="ru">Learning & mentoring</span><span data-lang="en">Learning & mentoring</span><span data-lang="ro">Învățare și mentorat</span></span>
                            <p><span data-lang="ru">Знания становятся полезнее, когда их можно сразу обсудить и применить.</span><span data-lang="en">Knowledge becomes more useful when you can discuss and apply it right away.</span><span data-lang="ro">Cunoștințele devin mai utile când le poți discuta și aplica imediat.</span></p>
                        </div>
                    </div>
                    <div class="miro-split__copy">
                        <p class="miro-eyebrow"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></p>
                        <h2><span data-lang="ru">Учитесь в своём темпе, растите рядом с другими</span><span data-lang="en">Learn at your pace, grow with others</span><span data-lang="ro">Învață în ritmul tău, crește alături de comunitate</span></h2>
                        <p><span data-lang="ru">Материалы, практические встречи, менторство и опыт других предпринимательниц помогают переходить от вопроса к решению.</span><span data-lang="en">Materials, practical sessions, mentoring and peer experience help you move from a question to a solution.</span><span data-lang="ro">Materiale, sesiuni practice, mentorat și experiența comunității te ajută să ajungi de la întrebare la soluție.</span></p>
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--yellow" style="margin-top: 28px;"><span data-lang="ru">Присоединиться к сообществу</span><span data-lang="en">Join the community</span><span data-lang="ro">Alătură-te comunității</span></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--soft" id="members">
            <div class="miro-container">
                <div class="miro-section__head">
                    <p class="miro-eyebrow"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></p>
                    <h2><span data-lang="ru">Разные бизнесы. Общий импульс.</span><span data-lang="en">Different businesses. Shared momentum.</span><span data-lang="ro">Afaceri diferite. Același impuls.</span></h2>
                </div>
                <div class="miro-grid-2">
                    <article class="miro-member-card"><img src="{{ asset('images/member-fashion.webp') }}" alt="Fashion business owner" loading="lazy"><div><h4>Fashion & design</h4><p><span data-lang="ru">Создаёт продукт, ищет новые каналы продаж и партнёров.</span><span data-lang="en">Building a product, looking for new sales channels and partners.</span><span data-lang="ro">Construiește un produs și caută canale și parteneri noi.</span></p><a class="miro-member-card__link" href="{{ route('account.login') }}"><span data-lang="ru">Найти похожие профили →</span><span data-lang="en">Find similar profiles →</span><span data-lang="ro">Găsește profiluri similare →</span></a></div></article>
                    <article class="miro-member-card"><img src="{{ asset('images/member-digital.webp') }}" alt="Digital business owner" loading="lazy"><div><h4>Digital services</h4><p><span data-lang="ru">Помогает бизнесам становиться заметнее и эффективнее.</span><span data-lang="en">Helping businesses become more visible and effective.</span><span data-lang="ro">Ajută afacerile să devină mai vizibile și eficiente.</span></p><a class="miro-member-card__link" href="{{ route('account.login') }}"><span data-lang="ru">Открыть каталог участниц →</span><span data-lang="en">Open the member directory →</span><span data-lang="ro">Deschide catalogul membrelor →</span></a></div></article>
                    <article class="miro-member-card"><img src="{{ asset('images/member-agrifood.webp') }}" alt="Agrifood business owner" loading="lazy"><div><h4>Agri & food</h4><p><span data-lang="ru">Развивает локальный бизнес и ищет устойчивые связи.</span><span data-lang="en">Growing a local business and building sustainable connections.</span><span data-lang="ro">Dezvoltă o afacere locală și construiește conexiuni durabile.</span></p><a class="miro-member-card__link" href="{{ route('account.login') }}"><span data-lang="ru">Посмотреть возможности →</span><span data-lang="en">See opportunities →</span><span data-lang="ro">Vezi oportunitățile →</span></a></div></article>
                    <article class="miro-member-card"><img src="{{ asset('images/hero-community.webp') }}" alt="Women entrepreneurs community" loading="lazy"><div><h4>Community builders</h4><p><span data-lang="ru">Соединяют людей, идеи и энергию для общего результата.</span><span data-lang="en">Connecting people, ideas and energy for a shared result.</span><span data-lang="ro">Conectează oameni, idei și energie pentru rezultate comune.</span></p><a class="miro-member-card__link" href="{{ $botUrl }}" target="_blank" rel="noopener"><span data-lang="ru">Стать частью →</span><span data-lang="en">Become part of it →</span><span data-lang="ro">Devino parte →</span></a></div></article>
                </div>
            </div>
        </section>

        <section class="miro-section" id="events">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></p>
                    <h2><span data-lang="ru">Встречайтесь не только онлайн</span><span data-lang="en">Make the connection real</span><span data-lang="ro">Transformă conexiunea în realitate</span></h2>
                    <p><span data-lang="ru">Практические воркшопы, нетворкинг и события, где можно познакомиться с людьми за пределами экрана.</span><span data-lang="en">Practical workshops, networking and events that bring people together beyond the screen.</span><span data-lang="ro">Workshopuri, networking și evenimente care aduc oamenii împreună dincolo de ecran.</span></p>
                </div>
                <div class="miro-grid-3">
                    <article class="miro-event-card"><img src="{{ asset('images/event-workshop.webp') }}" alt="Business workshop" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag"><span data-lang="ru">Workshop</span><span data-lang="en">Workshop</span><span data-lang="ro">Workshop</span></span><h3>Business lab</h3><p><span data-lang="ru">Разложить задачу на понятные шаги вместе с экспертами.</span><span data-lang="en">Break a business challenge into clear steps with experts.</span><span data-lang="ro">Transformă o provocare de business în pași clari.</span></p></div></article>
                    <article class="miro-event-card"><img src="{{ asset('images/event-networking.webp') }}" alt="Networking event" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background: #eef1ff; color: var(--miro-blue);"><span data-lang="ru">Networking</span><span data-lang="en">Networking</span><span data-lang="ro">Networking</span></span><h3>Meet & connect</h3><p><span data-lang="ru">Найти людей, с которыми хочется продолжить разговор после встречи.</span><span data-lang="en">Meet people you want to keep talking to after the event.</span><span data-lang="ro">Cunoaște oameni cu care vrei să continui conversația.</span></p></div></article>
                    <article class="miro-event-card"><img src="{{ asset('images/event-esg.webp') }}" alt="ESG event" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background: var(--miro-teal); color: #187574;"><span data-lang="ru">Impact</span><span data-lang="en">Impact</span><span data-lang="ro">Impact</span></span><h3>Ideas with impact</h3><p><span data-lang="ru">Обсудить устойчивый рост, партнёрства и пользу для сообщества.</span><span data-lang="en">Explore sustainable growth, partnerships and community impact.</span><span data-lang="ro">Explorează creșterea durabilă și impactul pentru comunitate.</span></p></div></article>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="stories">
            <div class="miro-container">
                <div class="miro-story">
                    <img src="{{ asset('images/story-export.webp') }}" alt="Women entrepreneurs collaborating" loading="lazy">
                    <div class="miro-story__body">
                        <span class="miro-tag" style="width: fit-content; background: var(--miro-yellow); color: var(--miro-primary);">Member story</span>
                        <blockquote><span data-lang="ru">«Нужный контакт оказался не где-то далеко — он уже был внутри сообщества.»</span><span data-lang="en">“The right connection was not far away — it was already inside the community.”</span><span data-lang="ro">„Conexiunea potrivită nu era departe — era deja în comunitate.”</span></blockquote>
                        <p>Women Entrepreneurs Platform member</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section">
            <div class="miro-container">
                <div class="miro-cta">
                    <h2><span data-lang="ru">Готовы собрать свой следующий шаг?</span><span data-lang="en">Ready to bring your next step together?</span><span data-lang="ro">Ești gata să construiești următorul pas?</span></h2>
                    <p><span data-lang="ru">Присоединяйтесь к платформе через Telegram и начните с простого профиля.</span><span data-lang="en">Join through Telegram and start with a simple profile.</span><span data-lang="ro">Alătură-te prin Telegram și începe cu un profil simplu.</span></p>
                    <div class="miro-hero__actions">
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--on-dark"><span data-lang="ru">Начать в Telegram</span><span data-lang="en">Start in Telegram</span><span data-lang="ro">Începe în Telegram</span></a>
                        <a href="{{ route('account.login') }}" class="miro-button" style="border: 1px solid rgba(255,255,255,.35); color: #fff;"><span data-lang="ru">У меня уже есть доступ</span><span data-lang="en">I already have access</span><span data-lang="ro">Am deja acces</span></a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="miro-footer" id="contact">
        <div class="miro-container">
            <div class="miro-footer__top">
                <div class="miro-footer__brand">
                    <a href="#top" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women Entrepreneurs Platform</span></a>
                    <p><span data-lang="ru">Цифровое пространство для женщин-предпринимательниц из двух берегов.</span><span data-lang="en">A digital space for women entrepreneurs from both banks.</span><span data-lang="ro">Un spațiu digital pentru femeile antreprenoare de pe ambele maluri.</span></p>
                </div>
                <div><h4><span data-lang="ru">Платформа</span><span data-lang="en">Platform</span><span data-lang="ro">Platformă</span></h4><ul><li><a href="#about">About</a></li><li><a href="#features">AI matching</a></li><li><a href="#members">Members</a></li></ul></div>
                <div><h4><span data-lang="ru">Ресурсы</span><span data-lang="en">Resources</span><span data-lang="ro">Resurse</span></h4><ul><li><a href="#learning">Learning</a></li><li><a href="#events">Events</a></li><li><a href="#stories">Stories</a></li></ul></div>
                <div><h4><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></h4><ul><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot</a></li><li><a href="{{ $managerUrl }}" target="_blank" rel="noopener">Project team</a></li><li><a href="{{ $communityUrl }}" target="_blank" rel="noopener">Community</a></li></ul></div>
                <div><h4><span data-lang="ru">Вход</span><span data-lang="en">Access</span><span data-lang="ro">Acces</span></h4><ul><li><a href="{{ route('account.login') }}"><span data-lang="ru">Кабинет участницы</span><span data-lang="en">Participant cabinet</span><span data-lang="ro">Cabinetul membrei</span></a></li><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">Telegram</a></li></ul></div>
            </div>
            <div class="miro-footer__bottom"><span>© {{ date('Y') }} Women Entrepreneurs Platform</span><span><span data-lang="ru">Сделано для роста через связи</span><span data-lang="en">Made for growth through connection</span><span data-lang="ro">Creat pentru creștere prin conexiuni</span></span></div>
        </div>
    </footer>

    <script>
        (function () {
            var supported = ['ru', 'en', 'ro'];
            var saved = localStorage.getItem('miro_lang');
            var browser = (navigator.language || '').slice(0, 2);
            var locale = supported.indexOf(saved) >= 0 ? saved : supported.indexOf(browser) >= 0 ? browser : 'ru';
            var root = document.documentElement;
            var nav = document.getElementById('miro-nav');
            var toggle = document.getElementById('miro-mobile-toggle');

            function setLocale(next) {
                if (supported.indexOf(next) < 0) next = 'ru';
                locale = next;
                root.lang = locale;
                localStorage.setItem('miro_lang', locale);
                document.querySelectorAll('[data-locale]').forEach(function (button) {
                    button.classList.toggle('is-active', button.getAttribute('data-locale') === locale);
                });
            }

            document.querySelectorAll('[data-locale]').forEach(function (button) {
                button.addEventListener('click', function () { setLocale(button.getAttribute('data-locale')); });
            });

            if (toggle) {
                toggle.addEventListener('click', function () {
                    var isOpen = nav.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            document.querySelectorAll('.miro-nav__links a').forEach(function (link) {
                link.addEventListener('click', function () {
                    nav.classList.remove('is-open');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                });
            });

            setLocale(locale);

            if (window.Telegram && window.Telegram.WebApp) {
                window.Telegram.WebApp.ready();
            }
        })();
    </script>
</body>
</html>
