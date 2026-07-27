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
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --miro-primary: #1c1c1e;
            --miro-on-primary: #ffffff;
            --miro-pink: #ffd8f4;
            --miro-coral: #ffc6c6;
            --miro-cream: #fff4c4;
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
        .miro-brand__mark { width: 28px; height: 28px; display: grid; place-items: center; border-radius: 6px; background: var(--miro-pink); color: var(--miro-primary); font-size: 13px; font-weight: 600; }
        .miro-brand__logo { width: 176px; height: 52px; object-fit: contain; }
        .miro-nav__links { display: flex; align-items: center; gap: 28px; color: var(--miro-slate); font-size: 14px; }
        .miro-nav__links a { transition: color .18s ease; }
        .miro-nav__links a:hover, .miro-nav__links a.is-active { color: var(--miro-primary); }
        .miro-nav__links a.is-active { font-weight: 600; }
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
        .miro-button--pink, .miro-button--yellow { background: var(--miro-pink); color: var(--miro-primary); }
        .miro-button--pink:hover, .miro-button--yellow:hover { background: var(--miro-coral); }
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
        .miro-hero__visual::before { content: ""; position: absolute; inset: 18px -24px 8px 24px; border-radius: 52px 0 52px 52px; background: linear-gradient(145deg, var(--miro-pink) 0 18%, transparent 18% 100%), linear-gradient(180deg, transparent 68%, var(--miro-teal) 68% 100%); transform: rotate(-2deg); }
        .miro-hero__image { position: relative; height: 530px; overflow: hidden; border-radius: 48px 0 48px 48px; box-shadow: var(--miro-shadow); }
        .miro-hero__image img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .miro-hero__image::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,.96) 0%, rgba(255,255,255,.7) 10%, rgba(255,255,255,0) 34%), linear-gradient(180deg, rgba(255,255,255,0) 70%, rgba(255,198,198,.28) 87%, rgba(195,250,245,.55) 100%); pointer-events: none; }
        .miro-hero__arc { position: absolute; z-index: 2; right: -5%; bottom: -22px; left: -6%; height: 72px; border-bottom: 5px solid var(--miro-coral); border-radius: 0 0 50% 50%; transform: rotate(-3deg); pointer-events: none; }
        .miro-hero__arc--teal { right: -8%; bottom: -34px; left: 12%; border-bottom-color: var(--miro-pink); border-bottom-width: 8px; }

        .miro-logo-wall { position: relative; overflow: hidden; padding: 40px 0 72px; border-top: 1px solid var(--miro-hairline-soft); border-bottom: 1px solid var(--miro-hairline-soft); }
        .miro-logo-wall::before { content: ""; position: absolute; top: -90px; left: 8%; width: 210px; height: 210px; border: 1px solid var(--miro-pink); border-radius: 50%; opacity: .6; }
        .miro-logo-wall::after { content: ""; position: absolute; right: 8%; bottom: 24px; width: 90px; height: 90px; background-image: radial-gradient(var(--miro-blue) 1.5px, transparent 1.5px); background-size: 14px 14px; opacity: .25; }
        .miro-logo-wall__layout { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, .8fr); align-items: center; gap: 56px; }
        .miro-logo-wall__copy { position: relative; z-index: 2; }
        .miro-logo-wall p { max-width: 540px; margin: 0 0 24px; color: var(--miro-slate); font-size: 17px; line-height: 1.45; }
        .miro-logo-wall__items { display: flex; flex-wrap: wrap; gap: 12px; color: var(--miro-steel); font-size: 14px; font-weight: 500; }
        .miro-logo-wall__items > span { display: inline-flex; align-items: center; max-width: 260px; padding: 9px 14px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: #fff; box-shadow: 0 5px 14px rgba(5,0,56,.04); line-height: 1.25; }
        .miro-logo-wall__items > span:nth-child(1) { background: var(--miro-cream); border-color: rgba(255,198,198,.65); }
        .miro-logo-wall__items > span:nth-child(2) { background: var(--miro-teal); border-color: #a9e9e2; }
        .miro-logo-wall__items > span:nth-child(3) { background: var(--miro-rose); border-color: #f2bce1; }
        .miro-logo-wall__items > span:nth-child(4) { background: var(--miro-coral); border-color: #f0aaaa; }
        .miro-logo-wall__items > span:nth-child(5) { background: var(--miro-orange); border-color: #f4c994; }
        .miro-logo-wall__items > span:nth-child(6) { background: #eef1ff; border-color: #cbd3ff; }
        .miro-logo-wall__visual { position: relative; min-height: 210px; }
        .miro-logo-wall__visual::before { content: ""; position: absolute; top: 6px; right: 4%; width: 170px; height: 170px; border-radius: 50%; background: var(--miro-coral); opacity: .7; }
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
        .miro-feature-card--pink, .miro-feature-card--yellow { background: var(--miro-pink); }
        .miro-feature-card--coral { background: var(--miro-coral); }
        .miro-feature-card--teal { background: var(--miro-teal); }
        .miro-feature-card--rose { background: var(--miro-rose); }
        .miro-feature-card--orange { background: var(--miro-orange); }

        .miro-proof { display: inline-flex; align-items: center; gap: 12px; margin-top: 24px; padding: 10px 16px 10px 10px; border: 1px solid var(--miro-hairline); border-radius: 9999px; background: #fff; box-shadow: 0 8px 20px rgba(5,0,56,.06); color: var(--miro-slate); font-size: 14px; }
        .miro-proof__value { width: 60px; height: 60px; flex: 0 0 60px; display: grid; place-items: center; border-radius: 50%; background: var(--miro-coral); color: var(--miro-primary); font-size: 17px; font-weight: 600; white-space: nowrap; }

        .miro-benefits { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .miro-benefit-card { min-height: 236px; padding: 24px; border: 1px solid var(--miro-hairline-soft); border-radius: 24px; background: #fff; box-shadow: 0 8px 24px rgba(5,0,56,.04); }
        .miro-benefit-card--pink, .miro-benefit-card--yellow { background: var(--miro-pink); }
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
        .miro-step__number { width: 72px; height: 72px; display: grid; place-items: center; margin: 0 auto 20px; border: 7px solid #fff; border-radius: 50%; background: var(--miro-coral); color: var(--miro-primary); box-shadow: 0 0 0 1px var(--miro-hairline-strong); font-size: 20px; font-weight: 600; }
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
        .miro-list__check { flex: 0 0 22px; width: 22px; height: 22px; display: grid; place-items: center; border-radius: 50%; background: var(--miro-teal); color: var(--miro-primary); font-size: 12px; font-weight: 600; }
        .miro-mockup { overflow: hidden; border: 1px solid var(--miro-hairline-soft); border-radius: 16px; background: #fff; box-shadow: var(--miro-shadow); }
        .miro-mockup__bar { height: 42px; display: flex; align-items: center; gap: 6px; padding: 0 16px; border-bottom: 1px solid var(--miro-hairline-soft); }
        .miro-mockup__bar i { width: 8px; height: 8px; border-radius: 50%; background: var(--miro-hairline-strong); }
        .miro-mockup__body { min-height: 330px; padding: 28px; background: var(--miro-surface-soft); }
        .miro-mockup__body--ai { background: linear-gradient(135deg, var(--miro-surface-soft), var(--miro-teal)); }
        .miro-roadmap { display: grid; gap: 12px; }
        .miro-roadmap__row { display: grid; grid-template-columns: 92px repeat(3, 1fr); gap: 8px; align-items: center; }
        .miro-roadmap__label { color: var(--miro-steel); font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .miro-roadmap__cell { min-height: 62px; padding: 12px; border: 1px solid var(--miro-hairline); border-radius: 8px; background: #fff; color: var(--miro-charcoal); font-size: 12px; }
        .miro-roadmap__cell.is-pink, .miro-roadmap__cell.is-yellow { background: var(--miro-pink); border-color: rgba(255,198,198,.65); }
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
        .miro-tag { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; background: var(--miro-cream); color: #050038; font-size: 13px; font-weight: 600; }

        .miro-member-card { display: grid; grid-template-columns: 112px 1fr; gap: 20px; align-items: center; padding: 16px; border: 1px solid var(--miro-hairline-soft); border-radius: 16px; background: #fff; }
        .miro-member-card img { width: 112px; height: 112px; border-radius: 12px; object-fit: cover; }
        .miro-member-card h4 { margin: 0; color: var(--miro-ink-deep); font-size: 18px; font-weight: 500; }
        .miro-member-card__role { margin: 5px 0 0; color: var(--miro-charcoal); font-size: 13px; line-height: 1.35; }
        .miro-member-card__specialization { margin: 8px 0 0; color: var(--miro-blue); font-size: 13px; font-weight: 600; line-height: 1.35; }
        .miro-member-card__tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .miro-profile-tag { display: inline-flex; align-items: center; padding: 5px 9px; border-radius: 9999px; color: var(--miro-primary); font-size: 11px; font-weight: 500; line-height: 1.2; }
        .miro-profile-tag--pink, .miro-profile-tag--yellow { background: var(--miro-cream); }
        .miro-profile-tag--teal { background: var(--miro-teal); color: #187574; }
        .miro-profile-tag--rose { background: var(--miro-rose); }
        .miro-profile-tag--coral { background: var(--miro-coral); }
        .miro-profile-tag--blue { background: #eef1ff; color: var(--miro-blue); }
        .miro-profile-tag--orange { background: var(--miro-orange); }
        .miro-member-card p { margin: 6px 0 0; color: var(--miro-steel); font-size: 14px; }
        .miro-members__cta { display: flex; justify-content: center; margin-top: 32px; }
        .miro-members__legacy { display: none; }
        .miro-members__cta a[href*="account/login"] { display: none; }

        .miro-event-card { overflow: hidden; border: 1px solid var(--miro-hairline-soft); border-radius: 16px; background: #fff; }
        .miro-event-card img { width: 100%; height: 196px; object-fit: cover; }
        .miro-event-card__body { padding: 24px; }
        .miro-event-card__date { margin-top: 14px; color: var(--miro-blue); font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
        .miro-event-card__body h3 { margin-top: 16px; font-size: 22px; }
        .miro-event-card__body p { margin: 10px 0 0; color: var(--miro-slate); }
        .miro-event-card__link { display: inline-flex; align-items: center; margin-top: 20px; padding: 10px 16px; border-radius: 9999px; background: var(--miro-primary); color: #fff; font-size: 13px; font-weight: 500; transition: transform .18s ease, background .18s ease; }
        .miro-event-card__link:hover { background: var(--miro-charcoal); transform: translateY(-1px); }
        .miro-events__footer { display: flex; justify-content: center; margin-top: 32px; }
        .miro-events__all-link { display: inline-flex; align-items: center; min-height: 44px; padding: 11px 22px; border: 1px solid var(--miro-hairline-strong); border-radius: 9999px; color: var(--miro-primary); font-size: 14px; font-weight: 500; transition: border-color .18s ease, background .18s ease; }
        .miro-events__all-link:hover { border-color: var(--miro-primary); background: var(--miro-surface); }

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
        .miro-footer .miro-brand__logo { width: 214px; height: 62px; padding: 7px 10px; border-radius: 12px; background: #fff; }
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
    @include('partials.miro-media-styles')
</head>
<body>
    @include('partials.miro-header', ['miroCurrentPage' => 'home'])
    @if(false)
    <nav class="miro-nav" id="miro-nav">
        <div class="miro-container miro-nav__inner">
            <a href="#top" class="miro-brand">
                <img src="{{ asset('images/brand/logo.webp') }}" alt="Women Entrepreneurs Platform" class="miro-brand__logo">
                <span>Women</span>
            </a>
            <div class="miro-nav__links" id="miro-nav-links">
                <a href="#top"><span data-lang="ru">Главная</span><span data-lang="en">Home</span><span data-lang="ro">Acasă</span></a>
                <a href="#about"><span data-lang="ru">О платформе</span><span data-lang="en">About</span><span data-lang="ro">Despre</span></a>
                <a href="#learning"><span data-lang="ru">Обучение</span><span data-lang="en">Learning</span><span data-lang="ro">Învățare</span></a>
                <a href="{{ route('members') }}"><span data-lang="ru">Участницы</span><span data-lang="en">Members</span><span data-lang="ro">Membre</span></a>
                <a href="{{ route('events') }}"><span data-lang="ru">События</span><span data-lang="en">Events</span><span data-lang="ro">Evenimente</span></a>
                <a href="#opportunities"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></a>
                <a href="{{ route('contact') }}"><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></a>
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
    @endif

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
                            <img src="{{ asset('images/1gDOEvgW6Bbo9rvB-OHSm277Ak0im3tJa.jpg') }}" alt="Women entrepreneurs collaborating around a laptop">
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
                        <img src="{{ asset('/images/333.png') }}" alt="">
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
                    <article class="miro-benefit-card miro-benefit-card--pink">
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
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Need</span><div class="miro-roadmap__cell is-pink">Find a mentor</div><div class="miro-roadmap__cell">New market</div><div class="miro-roadmap__cell">Local partner</div></div>
                                <div class="miro-roadmap__row"><span class="miro-roadmap__label">Action</span><div class="miro-roadmap__cell is-blue">Workshop · 14 Jun</div><div class="miro-roadmap__cell">Ask the community</div><div class="miro-roadmap__cell is-pink">Post an opportunity</div></div>
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
                        <a href="{{ $botUrl }}" target="_blank" rel="noopener" class="miro-button miro-button--pink" style="margin-top: 28px;"><span data-lang="ru">Присоединиться к сообществу</span><span data-lang="en">Join the community</span><span data-lang="ro">Alătură-te comunității</span></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--soft" id="members">
            <div class="miro-container">
                <div class="miro-section__head">
                    <p class="miro-eyebrow"><span data-lang="ru">Профили платформы</span><span data-lang="en">Platform profiles</span><span data-lang="ro">Profilurile platformei</span></p>
                    <h2><span data-lang="ru">Знакомьтесь с нашими экспертами</span><span data-lang="en">Meet registered members and experts</span><span data-lang="ro">Cunoaște participantele și expertele platformei</span></h2>
                    <p><span data-lang="ru">Здесь представлены предприниматели и эксперты, которые уже зарегистрированы на платформе, рассказывают о своей работе и открыты к сотрудничеству.</span><span data-lang="en">Meet women entrepreneurs and experts already registered on the platform, presenting their work and open to collaboration.</span><span data-lang="ro">Descoperă antreprenoarele și expertele deja înregistrate pe platformă, care își prezintă activitatea și sunt deschise colaborării.</span></p>
                </div>
                <div class="miro-grid-2">
                    <article class="miro-member-card">
                        <img src="{{ asset('images/experts/expert-carolina.png') }}" alt="Carolina Bugaiyan" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Каролина Бугаян</span><span data-lang="en">Carolina Bugaiyan</span><span data-lang="ro">Carolina Bugaiyan</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Президент Ассоциации деловых женщин Молдовы (AFAM)</span><span data-lang="en">President of the Association of Women Entrepreneurs in Moldova (AFAM)</span><span data-lang="ro">Președinta Asociației Femeilor de Afaceri din Moldova (AFAM)</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Женское предпринимательство и развитие делового сообщества</span><span data-lang="en">Women’s entrepreneurship &amp; business community development</span><span data-lang="ro">Antreprenoriat feminin și dezvoltarea comunității de business</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--rose">AFAM</span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Партнёрства</span><span data-lang="en">Partnerships</span><span data-lang="ro">Parteneriate</span></span></div>
                            <p><span data-lang="ru">Развивает женское предпринимательство и деловые связи в Молдове.</span><span data-lang="en">Develops women’s entrepreneurship and business connections in Moldova.</span><span data-lang="ro">Dezvoltă antreprenoriatul feminin și conexiunile de business în Moldova.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('images/experts/expert-aurelia.png') }}" alt="Aurelia Salicov" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Аурелия Саликов</span><span data-lang="en">Aurelia Salicov</span><span data-lang="ro">Aurelia Salicov</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Вице-президент Международного бизнес-сообщества в Молдове</span><span data-lang="en">Vice-President of the International Business Society in Moldova</span><span data-lang="ro">Vicepreședinta International Business Society din Moldova</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Международное деловое сотрудничество</span><span data-lang="en">International business cooperation</span><span data-lang="ro">Cooperare internațională de business</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Международные связи</span><span data-lang="en">International relations</span><span data-lang="ro">Relații internaționale</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Партнёрства</span><span data-lang="en">Partnerships</span><span data-lang="ro">Parteneriate</span></span></div>
                            <p><span data-lang="ru">Развивает международное деловое сотрудничество и новые партнёрства.</span><span data-lang="en">Builds international business cooperation and new partnerships.</span><span data-lang="ro">Dezvoltă cooperarea internațională de business și parteneriate noi.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('images/experts/expert-vlada.png') }}" alt="Vlada Lysenko" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Влада Лысенко</span><span data-lang="en">Vlada Lysenko</span><span data-lang="ro">Vlada Lysenko</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Доктор наук, профессор, международный консультант</span><span data-lang="en">Doctor of Sciences, Professor &amp; International Consultant</span><span data-lang="ro">Doctor în științe, profesor și consultant internațional</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Наука, образование и международный консалтинг</span><span data-lang="en">Research, education &amp; international consulting</span><span data-lang="ro">Cercetare, educație și consultanță internațională</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Образование</span><span data-lang="en">Education</span><span data-lang="ro">Educație</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Консалтинг</span><span data-lang="en">Consulting</span><span data-lang="ro">Consultanță</span></span></div>
                            <p><span data-lang="ru">Объединяет академический опыт, образование и международный консалтинг.</span><span data-lang="en">Combines academic experience, education and international consulting.</span><span data-lang="ro">Combină experiența academică, educația și consultanța internațională.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('images/experts/expert-zinaida.png') }}" alt="Zinaida Emelyanova" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Зинаида Емельянова</span><span data-lang="en">Zinaida Emelyanova</span><span data-lang="ro">Zinaida Emelyanova</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Директор Агентства инноваций и развития</span><span data-lang="en">Director of the Agency for Innovation and Development</span><span data-lang="ro">Directoarea Agenției pentru Inovații și Dezvoltare</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Инновации и развитие проектов</span><span data-lang="en">Innovation &amp; project development</span><span data-lang="ro">Inovații și dezvoltarea proiectelor</span></p>
                            <div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Инновации</span><span data-lang="en">Innovation</span><span data-lang="ro">Inovație</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Развитие</span><span data-lang="en">Development</span><span data-lang="ro">Dezvoltare</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Возможности</span><span data-lang="en">Opportunities</span><span data-lang="ro">Oportunități</span></span></div>
                            <p><span data-lang="ru">Развивает инновационные проекты и поддерживает предпринимательские инициативы.</span><span data-lang="en">Develops innovation projects and supports entrepreneurial initiatives.</span><span data-lang="ro">Dezvoltă proiecte inovatoare și susține inițiative antreprenoriale.</span></p>
                        </div>
                    </article>
                </div>
                <div class="miro-grid-2 miro-members__legacy">
                    <article class="miro-member-card">
                        <img src="{{ asset('images/experts/expert-diana.png') }}" alt="Diana Sakirchuk" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Диана Сакирчук</span><span data-lang="en">Diana Sakirchuk</span><span data-lang="ro">Diana Sakirchuk</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Основательница PureCup</span><span data-lang="en">Founder of PureCup</span><span data-lang="ro">Fondatoarea PureCup</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Предпринимательство и развитие продукта</span><span data-lang="en">Entrepreneurship &amp; product development</span><span data-lang="ro">Antreprenoriat și dezvoltarea produsului</span></p>
                            <div class="miro-member-card__tags">
                                <span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Основательница</span><span data-lang="en">Founder</span><span data-lang="ro">Fondatoare</span></span>
                                <span class="miro-profile-tag miro-profile-tag--rose">PureCup</span>
                                <span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Продукт</span><span data-lang="en">Product</span><span data-lang="ro">Produs</span></span>
                            </div>
                            <p><span data-lang="ru">Развивает PureCup и собственный предпринимательский проект.</span><span data-lang="en">Builds PureCup and her own entrepreneurial project.</span><span data-lang="ro">Dezvoltă PureCup și propriul proiect antreprenorial.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('images/experts/expert-olga-melnichuk.png') }}" alt="Olga Melnichuk" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Ольга Мельничук</span><span data-lang="en">Olga Melnichuk</span><span data-lang="ro">Olga Melnichuk</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Соосновательница Business Angels Moldova, исполнительный директор Startup Moldova</span><span data-lang="en">Co-founder of Business Angels Moldova, Executive Director of Startup Moldova</span><span data-lang="ro">Co-fondatoarea Business Angels Moldova, directoarea executivă Startup Moldova</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">Стартапы, инвестиции и предпринимательство</span><span data-lang="en">Startups, investment &amp; entrepreneurship</span><span data-lang="ro">Startupuri, investiții și antreprenoriat</span></p>
                            <div class="miro-member-card__tags">
                                <span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Стартапы</span><span data-lang="en">Startups</span><span data-lang="ro">Startupuri</span></span>
                                <span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Инвестиции</span><span data-lang="en">Investment</span><span data-lang="ro">Investiții</span></span>
                                <span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Сообщества</span><span data-lang="en">Community</span><span data-lang="ro">Comunitate</span></span>
                            </div>
                            <p><span data-lang="ru">Развивает стартап- и инвестиционную экосистему Молдовы.</span><span data-lang="en">Develops Moldova’s startup and investment ecosystem.</span><span data-lang="ro">Dezvoltă ecosistemul de startupuri și investiții din Moldova.</span></p>
                        </div>
                    </article>
                    <article class="miro-member-card">
                        <img src="{{ asset('images/experts/expert-irina.png') }}" alt="Irina Pleshkova" loading="lazy">
                        <div>
                            <h4><span data-lang="ru">Ирина Плешкова</span><span data-lang="en">Irina Pleshkova</span><span data-lang="ro">Irina Pleshkova</span></h4>
                            <p class="miro-member-card__role"><span data-lang="ru">Эксперт по внедрению AI и цифровой эффективности</span><span data-lang="en">Expert in AI adoption and digital efficiency</span><span data-lang="ro">Expertă în implementarea AI și eficiență digitală</span></p>
                            <p class="miro-member-card__specialization"><span data-lang="ru">AI, цифровая трансформация и эффективность</span><span data-lang="en">AI, digital transformation &amp; efficiency</span><span data-lang="ro">AI, transformare digitală și eficiență</span></p>
                            <div class="miro-member-card__tags">
                                <span class="miro-profile-tag miro-profile-tag--coral">AI</span>
                                <span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Цифровизация</span><span data-lang="en">Digital</span><span data-lang="ro">Digital</span></span>
                                <span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Эффективность</span><span data-lang="en">Efficiency</span><span data-lang="ro">Eficiență</span></span>
                            </div>
                            <p><span data-lang="ru">Помогает предпринимателям внедрять AI и цифровые инструменты для роста эффективности.</span><span data-lang="en">Helps entrepreneurs adopt AI and digital tools to improve efficiency.</span><span data-lang="ro">Ajută antreprenorii să adopte AI și instrumente digitale pentru eficiență.</span></p>
                        </div>
                    </article>
                </div>
                <div class="miro-grid-2 miro-members__legacy">
                    <article class="miro-member-card"><img src="{{ asset('images/member-fashion.webp') }}" alt="Fashion and design participant profile" loading="lazy"><div><h4><span data-lang="ru">Участница · Мода и дизайн</span><span data-lang="en">Member · Fashion &amp; design</span><span data-lang="ro">Participantă · Modă și design</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--yellow"><span data-lang="ru">Участница</span><span data-lang="en">Member</span><span data-lang="ro">Participantă</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Дизайн</span><span data-lang="en">Design</span><span data-lang="ro">Design</span></span><span class="miro-profile-tag miro-profile-tag--teal"><span data-lang="ru">Ищет партнёров</span><span data-lang="en">Looking for a partner</span><span data-lang="ro">Caută partener</span></span></div><p><span data-lang="ru">President of the Association of Women Entrepreneurs in Moldova (AFAM).</span><span data-lang="en">Building a product and open to new sales channels and partnerships.</span><span data-lang="ro">Dezvoltă un produs și este deschisă canalelor noi de vânzare și parteneriatelor.</span></p></div></article>
                    <article class="miro-member-card"><img src="{{ asset('images/member-digital.webp') }}" alt="Digital services expert profile" loading="lazy"><div><h4><span data-lang="ru">Каролина Бугаян · Президент Ассоциации </span><span data-lang="en">Expert · Digital services</span><span data-lang="ro">Expertă · Servicii digitale</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--blue"><span data-lang="ru">Бизнесмен</span><span data-lang="en">Marketing</span><span data-lang="ro">Marketing</span></span><span class="miro-profile-tag miro-profile-tag--yellow"><span data-lang="ru">Ищет партнеров</span><span data-lang="en">Offers services</span><span data-lang="ro">Oferă servicii</span></span></div><p><span data-lang="ru">President of the Association of Women Entrepreneurs in Moldova (AFAM).</span><span data-lang="en">Helping businesses become more visible, clear and effective.</span><span data-lang="ro">Ajută afacerile să devină mai vizibile, mai clare și mai eficiente.</span></p></div></article>
                    <article class="miro-member-card"><img src="{{ asset('images/member-agrifood.webp') }}" alt="Agrifood participant profile" loading="lazy"><div><h4><span data-lang="ru">Участница · Агро и продукты</span><span data-lang="en">Member · Agri &amp; food</span><span data-lang="ro">Participantă · Agri și produse</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--teal"><span data-lang="ru">Участница</span><span data-lang="en">Member</span><span data-lang="ro">Participantă</span></span><span class="miro-profile-tag miro-profile-tag--orange"><span data-lang="ru">Агро и продукты</span><span data-lang="en">Agri &amp; food</span><span data-lang="ro">Agri și produse</span></span><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Ищет новые рынки</span><span data-lang="en">Looking for new markets</span><span data-lang="ro">Caută piețe noi</span></span></div><p><span data-lang="ru">Развивает локальный бизнес и ищет устойчивые деловые связи.</span><span data-lang="en">Growing a local business and building sustainable business connections.</span><span data-lang="ro">Dezvoltă o afacere locală și construiește conexiuni de business durabile.</span></p></div></article>
                    <article class="miro-member-card"><img src="{{ asset('images/hero-community.webp') }}" alt="Women entrepreneurs community profile" loading="lazy"><div><h4><span data-lang="ru">Эксперт · Развитие сообщества</span><span data-lang="en">Expert · Community building</span><span data-lang="ro">Expertă · Dezvoltarea comunității</span></h4><div class="miro-member-card__tags"><span class="miro-profile-tag miro-profile-tag--rose"><span data-lang="ru">Эксперт</span><span data-lang="en">Expert</span><span data-lang="ro">Expertă</span></span><span class="miro-profile-tag miro-profile-tag--teal"><span data-lang="ru">Менторство</span><span data-lang="en">Mentorship</span><span data-lang="ro">Mentorat</span></span><span class="miro-profile-tag miro-profile-tag--coral"><span data-lang="ru">Открыта к сотрудничеству</span><span data-lang="en">Open to collaboration</span><span data-lang="ro">Deschisă colaborării</span></span></div><p><span data-lang="ru">Соединяет людей, идеи и возможности для общего результата.</span><span data-lang="en">Connecting people, ideas and opportunities for a shared result.</span><span data-lang="ro">Conectează oameni, idei și oportunități pentru rezultate comune.</span></p></div></article>
                </div>
                <div class="miro-members__cta">
                    <a class="miro-button miro-button--primary" href="{{ route('members') }}"><span data-lang="ru">Найти похожие профили&nbsp;→</span><span data-lang="en">Find similar profiles&nbsp;→</span><span data-lang="ro">Găsește profiluri similare&nbsp;→</span></a>
                    <a class="miro-button miro-button--primary" href="{{ route('account.login') }}"><span data-lang="ru">Найти похожие профили&nbsp;→</span><span data-lang="en">Find similar profiles&nbsp;→</span><span data-lang="ro">Găsește profiluri similare&nbsp;→</span></a>
                </div>
            </div>
        </section>

        <section class="miro-section" id="events">
            <div class="miro-container">
                <div class="miro-section__head miro-section__head--center">
                    <p class="miro-eyebrow"><span data-lang="ru">Новости и возможности</span><span data-lang="en">News &amp; opportunities</span><span data-lang="ro">Noutăți și oportunități</span></p>
                    <h2><span data-lang="ru">Мероприятия, встречи и новые возможности</span><span data-lang="en">Meet, learn and discover new opportunities</span><span data-lang="ro">Întâlnește-te, învață și descoperă oportunități</span></h2>
                    <p><span data-lang="ru">Практические воркшопы, нетворкинг, объявления, гранты и партнёрские возможности — всё, что помогает двигаться дальше.</span><span data-lang="en">Practical workshops, networking, announcements, grants and partner opportunities to help you move forward.</span><span data-lang="ro">Workshopuri practice, networking, anunțuri, granturi și oportunități de parteneriat pentru următorul tău pas.</span></p>
                </div>
                <div class="miro-grid-3">
                    <article class="miro-event-card"><img src="{{ asset('images/news/news-white-noise.jpg') }}" alt="White Noise — where creativity meets entrepreneurship" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background:var(--miro-pink);color:var(--miro-primary)"><span data-lang="ru">Новости</span><span data-lang="en">News</span><span data-lang="ro">Noutăți</span></span><div class="miro-event-card__date">20.05.2026</div><h3><span data-lang="ru">«Белый Шум» — встреча креатива и предпринимательства</span><span data-lang="en">White Noise — where creativity meets entrepreneurship</span><span data-lang="ro">„White Noise” — întâlnirea dintre creativitate și antreprenoriat</span></h3><p><span data-lang="ru">Арт-выставка, где встретились искусство, мода и предпринимательство.</span><span data-lang="en">An art exhibition where art, fashion and entrepreneurship came together.</span><span data-lang="ro">O expoziție de artă în care s-au întâlnit arta, moda și antreprenoriatul.</span></p><a href="https://women.creativity.md/2026/05/20/%d0%b1%d0%b5%d0%bb%d1%8b%d0%b9-%d1%88%d1%83%d0%bc-%d0%b2%d1%81%d1%82%d1%80%d0%b5%d1%87%d0%b0-%d0%ba%d1%80%d0%b5%d0%b0%d1%82%d0%b8%d0%b2%d0%b0-%d0%b8-%d0%bf%d1%80%d0%b5%d0%b4/" target="_blank" rel="noopener" class="miro-event-card__link"><span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span></a></div></article>
                    <article class="miro-event-card"><img src="{{ asset('images/news/news-conference.jpg') }}" alt="International conference for women entrepreneurs" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background:#eef1ff;color:var(--miro-blue)"><span data-lang="ru">Конференция</span><span data-lang="en">Conference</span><span data-lang="ro">Conferință</span></span><div class="miro-event-card__date">20.05.2026</div><h3><span data-lang="ru">Международная конференция для женщин-предпринимателей</span><span data-lang="en">International conference for women entrepreneurs</span><span data-lang="ro">Conferință internațională pentru femei antreprenoare</span></h3><p><span data-lang="ru">Конференция о лидерстве, инновациях и развитии женского предпринимательства.</span><span data-lang="en">A conference about leadership, innovation and women’s entrepreneurship.</span><span data-lang="ro">O conferință despre leadership, inovație și antreprenoriat feminin.</span></p><a href="https://women.creativity.md/2026/05/20/%d0%bc%d0%b5%d0%b6%d0%b4%d1%83%d0%bd%d0%b0%d1%80%d0%be%d0%b4%d0%bd%d0%b0%d1%8f-%d0%ba%d0%be%d0%bd%d1%84%d0%b5%d1%80%d0%b5%d0%bd%d1%86%d0%b8%d1%8f-%d0%b4%d0%bb%d1%8f-%d0%b6%d0%b5%d0%bd%d1%89%d0%b8/" target="_blank" rel="noopener" class="miro-event-card__link"><span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span></a></div></article>
                    <article class="miro-event-card"><img src="{{ asset('images/news/news-networking.jpg') }}" alt="Dream Takes Flight networking event at Glia Impact Hub" loading="lazy"><div class="miro-event-card__body"><span class="miro-tag" style="background:var(--miro-coral);color:var(--miro-primary)"><span data-lang="ru">Нетворкинг</span><span data-lang="en">Networking</span><span data-lang="ro">Networking</span></span><div class="miro-event-card__date">20.05.2026</div><h3><span data-lang="ru">В Glia Impact Hub состоялось нетворкинг-мероприятие</span><span data-lang="en">“Dream Takes Flight” networking event at Glia Impact Hub</span><span data-lang="ro">Evenimentul de networking „Visul își ia zborul” la Glia Impact Hub</span></h3><p><span data-lang="ru">Встреча предпринимательниц, организованная AFAM вместе с партнёрами.</span><span data-lang="en">A gathering of women entrepreneurs organised by AFAM and community partners.</span><span data-lang="ro">O întâlnire a femeilor antreprenoare organizată de AFAM și partenerii comunității.</span></p><a href="https://women.creativity.md/2026/05/20/%d0%b2-glia-impact-hub-%d1%81%d0%be%d1%81%d1%82%d0%be%d1%8f%d0%bb%d0%be%d1%81%d1%8c-%d0%bd%d0%b5%d1%82%d0%b2%d0%be%d1%80%d0%ba%d0%b8%d0%bd%d0%b3-%d0%bc%d0%b5%d1%80%d0%be%d0%bf%d1%80%d0%b8%d1%8f%d1%82/" target="_blank" rel="noopener" class="miro-event-card__link"><span data-lang="ru">Подробнее&nbsp;→</span><span data-lang="en">Read more&nbsp;→</span><span data-lang="ro">Află mai multe&nbsp;→</span></a></div></article>
                </div>
                <div class="miro-events__footer">
                    <a href="{{ route('events') }}" class="miro-events__all-link"><span data-lang="ru">Все новости&nbsp;→</span><span data-lang="en">All news&nbsp;→</span><span data-lang="ro">Toate noutățile&nbsp;→</span></a>
                </div>
            </div>
        </section>

        <section class="miro-section miro-section--surface" id="stories">
            <div class="miro-container">
                <div class="miro-story">
                    <img src="{{ asset('images/story-export.webp') }}" alt="Women entrepreneurs collaborating" loading="lazy">
                    <div class="miro-story__body">
                        <span class="miro-tag" style="width: fit-content; background: var(--miro-pink); color: var(--miro-primary);">Member story</span>
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

    @include('partials.miro-footer')
    @if(false)
    <footer class="miro-footer" id="contact">
        <div class="miro-container">
            <div class="miro-footer__top">
                <div class="miro-footer__brand">
                    <a href="#top" class="miro-brand"><span class="miro-brand__mark">W</span><span>Women Entrepreneurs Platform</span></a>
                    <p><span data-lang="ru">Цифровое пространство для женщин-предпринимательниц из двух берегов.</span><span data-lang="en">A digital space for women entrepreneurs from both banks.</span><span data-lang="ro">Un spațiu digital pentru femeile antreprenoare de pe ambele maluri.</span></p>
                </div>
                <div><h4><span data-lang="ru">Платформа</span><span data-lang="en">Platform</span><span data-lang="ro">Platformă</span></h4><ul><li><a href="#about">About</a></li><li><a href="#features">AI matching</a></li><li><a href="{{ route('members') }}">Members</a></li></ul></div>
                <div><h4><span data-lang="ru">Ресурсы</span><span data-lang="en">Resources</span><span data-lang="ro">Resurse</span></h4><ul><li><a href="#learning">Learning</a></li><li><a href="{{ route('events') }}">Events</a></li><li><a href="#stories">Stories</a></li></ul></div>
                <div><h4><span data-lang="ru">Контакты</span><span data-lang="en">Contact</span><span data-lang="ro">Contact</span></h4><ul><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">@WomenComBot</a></li><li><a href="{{ $managerUrl }}" target="_blank" rel="noopener">Project team</a></li><li><a href="{{ $communityUrl }}" target="_blank" rel="noopener">Community</a></li></ul></div>
                <div><h4><span data-lang="ru">Вход</span><span data-lang="en">Access</span><span data-lang="ro">Acces</span></h4><ul><li><a href="{{ route('account.login') }}"><span data-lang="ru">Кабинет участницы</span><span data-lang="en">Participant cabinet</span><span data-lang="ro">Cabinetul membrei</span></a></li><li><a href="{{ $botUrl }}" target="_blank" rel="noopener">Telegram</a></li></ul></div>
            </div>
            <div class="miro-footer__bottom"><span>© {{ date('Y') }} Women Entrepreneurs Platform</span><span><span data-lang="ru">Сделано для роста через связи</span><span data-lang="en">Made for growth through connection</span><span data-lang="ro">Creat pentru creștere prin conexiuni</span></span></div>
        </div>
    </footer>
    @endif

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
