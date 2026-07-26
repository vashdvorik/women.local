@php
    $accountTheme = $accountTheme ?? 'classic';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('account.cabinet')) — {{ __('account.brand') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }, colors: { brand: { 50:'#f5f3ff',100:'#ede9fe',200:'#ddd6fe',300:'#c4b5fd',400:'#a78bfa',500:'#8b5cf6',600:'#7c3aed',700:'#6d28d9' } } } } }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        :focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; border-radius: 6px; }
        @media (max-width: 1023px) { [x-cloak] { display: none !important; } }

        /* The cabinet theme is independent from the public landing theme. */
        body.account-theme-warm {
            background: #fff7ed !important;
            color: #2f1f1a;
        }

        body.account-theme-warm .bg-brand-50,
        body.account-theme-warm .bg-violet-100 {
            background-color: #ffedd5 !important;
        }

        body.account-theme-warm .bg-brand-600,
        body.account-theme-warm .bg-violet-700,
        body.account-theme-warm [style*="#7c3aed"] {
            background-color: #c2410c !important;
            background-image: linear-gradient(135deg, #c2410c, #9a3412) !important;
        }

        body.account-theme-warm .text-brand-600,
        body.account-theme-warm .text-brand-700,
        body.account-theme-warm .text-violet-700,
        body.account-theme-warm .hover\\:text-brand-600:hover,
        body.account-theme-warm .hover\\:text-violet-700:hover {
            color: #c2410c !important;
        }

        body.account-theme-dark {
            background: #020617 !important;
            color: #e5e7eb;
        }

        body.account-theme-dark .bg-white,
        body.account-theme-dark [class*="bg-white"] {
            background-color: #111827 !important;
            border-color: #334155 !important;
        }

        body.account-theme-dark .bg-gray-50,
        body.account-theme-dark [class*="bg-[#f7f8fa]"] {
            background-color: #020617 !important;
        }

        body.account-theme-dark [class*="text-[#0f172a]"],
        body.account-theme-dark .text-slate-950,
        body.account-theme-dark .text-slate-900 {
            color: #f8fafc !important;
        }

        body.account-theme-dark .text-slate-700,
        body.account-theme-dark .text-slate-600,
        body.account-theme-dark .text-gray-500 {
            color: #cbd5e1 !important;
        }

        body.account-theme-dark .border-gray-100,
        body.account-theme-dark .border-gray-200,
        body.account-theme-dark .border-slate-200,
        body.account-theme-dark .border-slate-300 {
            border-color: #334155 !important;
        }

        body.account-theme-dark .bg-brand-50,
        body.account-theme-dark .bg-violet-100,
        body.account-theme-dark .bg-teal-50 {
            background-color: #1e293b !important;
        }

        body.account-theme-dark .bg-brand-600,
        body.account-theme-dark .bg-violet-700,
        body.account-theme-dark [style*="#7c3aed"] {
            background-color: #8b5cf6 !important;
            background-image: linear-gradient(135deg, #8b5cf6, #4f46e5) !important;
        }

        body.account-theme-dark .text-brand-600,
        body.account-theme-dark .text-brand-700,
        body.account-theme-dark .text-violet-700,
        body.account-theme-dark .text-teal-700,
        body.account-theme-dark .text-teal-800 {
            color: #c4b5fd !important;
        }

        /* Miro cabinet theme: white canvas, black actions and pastel working surfaces. */
        body.account-theme-miro {
            --miro-primary: #1c1c1e;
            --miro-pink: #ffd8f4;
            --miro-cream: #fff4c4;
            --miro-blue: #4262ff;
            --miro-teal: #c3faf5;
            --miro-teal-deep: #187574;
            --miro-coral: #ffc6c6;
            --miro-rose: #ffd8f4;
            --miro-orange: #ffe6cd;
            --miro-canvas: #fafbfc;
            --miro-hairline: #eef0f3;
            background: var(--miro-canvas) !important;
            color: var(--miro-primary) !important;
            font-family: "Roobert PRO", "Noto Sans", Inter, system-ui, sans-serif !important;
        }

        body.account-theme-miro .account-sidebar,
        body.account-theme-miro .account-mobile-header {
            background: #fff !important;
            border-color: var(--miro-hairline) !important;
            box-shadow: none !important;
        }

        body.account-theme-miro .account-brand-mark {
            background: var(--miro-pink) !important;
            background-image: none !important;
            box-shadow: none !important;
            color: var(--miro-primary) !important;
        }

        body.account-theme-miro .account-brand-mark svg { color: var(--miro-primary) !important; }

        body.account-theme-miro .account-user-card {
            border: 1px solid rgba(255,198,198,.65);
            background: var(--miro-pink) !important;
        }

        body.account-theme-miro .account-user-card [style*="#7c3aed"] {
            background: var(--miro-teal) !important;
            background-image: none !important;
            box-shadow: none !important;
            color: var(--miro-teal-deep) !important;
        }

        body.account-theme-miro .account-language-switcher {
            border-color: var(--miro-hairline) !important;
            background: #fafbfc !important;
        }

        body.account-theme-miro .account-language-switcher .bg-brand-600,
        body.account-theme-miro .account-language-switcher .bg-violet-700 {
            background: var(--miro-primary) !important;
            background-image: none !important;
            color: #fff !important;
        }

        body.account-theme-miro .account-main { background: var(--miro-canvas) !important; }

        body.account-theme-miro .bg-white { background-color: #fff !important; }
        body.account-theme-miro .bg-gray-50,
        body.account-theme-miro [class~="bg-[#f7f8fa]"],
        body.account-theme-miro [class~="bg-slate-50"] { background-color: #fafbfc !important; }
        body.account-theme-miro [class~="bg-slate-100"] { background-color: #eef0f3 !important; }
        body.account-theme-miro [class~="bg-brand-50"],
        body.account-theme-miro [class~="bg-violet-100"] { background-color: var(--miro-pink) !important; }
        body.account-theme-miro [class~="bg-amber-50"] { background-color: var(--miro-cream) !important; }
        body.account-theme-miro [class~="bg-teal-50"] { background-color: var(--miro-teal) !important; }
        body.account-theme-miro [class~="bg-orange-100"] { background-color: var(--miro-orange) !important; }

        body.account-theme-miro [class~="bg-brand-600"],
        body.account-theme-miro [class~="bg-violet-700"],
        body.account-theme-miro [class~="bg-slate-950"] {
            background-color: var(--miro-primary) !important;
            background-image: none !important;
            color: #fff !important;
        }

        body.account-theme-miro [class~="bg-orange-600"],
        body.account-theme-miro [class~="bg-amber-500"] {
            background-color: var(--miro-coral) !important;
            background-image: none !important;
            color: var(--miro-primary) !important;
            box-shadow: none !important;
        }

        body.account-theme-miro [class~="bg-teal-700"] {
            background-color: var(--miro-teal) !important;
            color: var(--miro-teal-deep) !important;
        }

        body.account-theme-miro [class*="hover:bg-teal-800"]:hover,
        body.account-theme-miro [class*="hover:bg-orange-700"]:hover,
        body.account-theme-miro [class*="hover:bg-brand-700"]:hover {
            background-color: var(--miro-charcoal, #2c2c34) !important;
            color: #fff !important;
        }

        body.account-theme-miro [class*="text-[#0f172a]"],
        body.account-theme-miro [class~="text-slate-950"],
        body.account-theme-miro [class~="text-slate-900"],
        body.account-theme-miro [class~="text-slate-800"],
        body.account-theme-miro [class~="text-gray-900"] { color: #050038 !important; }

        body.account-theme-miro [class~="text-slate-700"],
        body.account-theme-miro [class~="text-slate-600"],
        body.account-theme-miro [class~="text-gray-700"],
        body.account-theme-miro [class~="text-gray-600"],
        body.account-theme-miro [class~="text-gray-500"] { color: #555a6a !important; }

        body.account-theme-miro [class~="text-gray-400"],
        body.account-theme-miro [class~="text-slate-500"] { color: #6b6f7e !important; }
        body.account-theme-miro [class~="text-brand-600"] { color: var(--miro-blue) !important; }
        body.account-theme-miro [class~="text-brand-700"] { color: var(--miro-primary) !important; }
        body.account-theme-miro [class~="text-teal-700"],
        body.account-theme-miro [class~="text-teal-800"],
        body.account-theme-miro [class~="text-teal-900\/80"],
        body.account-theme-miro [class~="text-teal-950"] { color: var(--miro-teal-deep) !important; }
        body.account-theme-miro [class~="text-orange-700"],
        body.account-theme-miro [class~="text-orange-800"],
        body.account-theme-miro [class~="text-amber-700"],
        body.account-theme-miro [class~="text-amber-900"] { color: #050038 !important; }

        body.account-theme-miro [class~="border-gray-100"],
        body.account-theme-miro [class~="border-gray-200"],
        body.account-theme-miro [class~="border-slate-200"],
        body.account-theme-miro [class~="border-slate-300"] { border-color: var(--miro-hairline) !important; }

        body.account-theme-miro [class*="rounded-[2rem]"] { border-radius: 28px !important; }
        body.account-theme-miro [class*="shadow-sm"],
        body.account-theme-miro [class*="shadow-xl"] { box-shadow: 0 12px 32px rgba(5, 0, 56, .06) !important; }

        /* Shared Miro cabinet primitives. Keep page templates focused on content. */
        body.account-theme-miro .account-sidebar { width: 272px; }
        body.account-theme-miro .account-main { padding: 40px clamp(20px, 4vw, 56px) 64px; }
        body.account-theme-miro .miro-page { width: 100%; max-width: 1180px; margin: 0 auto; }
        body.account-theme-miro .miro-page-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; margin-bottom: 32px; }
        body.account-theme-miro .miro-page-header__copy { max-width: 760px; }
        body.account-theme-miro .miro-eyebrow { margin: 0 0 12px; color: var(--miro-teal-deep); font-size: 11px; font-weight: 600; letter-spacing: .12em; line-height: 1.4; text-transform: uppercase; }
        body.account-theme-miro .miro-page-title { margin: 0; color: #050038; font-size: clamp(32px, 4.5vw, 54px); font-weight: 500; letter-spacing: -.045em; line-height: 1.06; }
        body.account-theme-miro .miro-page-description { margin: 14px 0 0; color: #555a6a; font-size: 16px; line-height: 1.55; }
        body.account-theme-miro .miro-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        body.account-theme-miro .miro-button { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; gap: 8px; border: 1px solid transparent; border-radius: 9999px; padding: 11px 20px; font-size: 14px; font-weight: 500; line-height: 1.3; transition: transform .18s ease, background .18s ease, border-color .18s ease, color .18s ease; }
        body.account-theme-miro .miro-button:hover { transform: translateY(-1px); }
        body.account-theme-miro .miro-button--dark { background: var(--miro-primary); color: #fff; }
        body.account-theme-miro .miro-button--dark:hover { background: #2c2c34; }
        body.account-theme-miro .miro-button--pink { background: var(--miro-pink); color: var(--miro-primary); }
        body.account-theme-miro .miro-button--pink:hover { background: var(--miro-coral); }
        body.account-theme-miro .miro-button--outline { border-color: #c7cad5; background: #fff; color: var(--miro-primary); }
        body.account-theme-miro .miro-button--outline:hover { border-color: var(--miro-primary); }
        body.account-theme-miro .miro-button--text { min-height: auto; padding: 8px 0; color: var(--miro-blue); }
        body.account-theme-miro .miro-card { border: 1px solid var(--miro-hairline); border-radius: 24px; background: #fff; box-shadow: 0 12px 32px rgba(5, 0, 56, .055); }
        body.account-theme-miro .miro-card--soft { background: #fafbfc; box-shadow: none; }
        body.account-theme-miro .miro-card--pink { background: var(--miro-pink); border-color: rgba(255,198,198,.75); box-shadow: none; }
        body.account-theme-miro .miro-card--teal { background: var(--miro-teal); border-color: rgba(15,188,176,.18); box-shadow: none; }
        body.account-theme-miro .miro-card--coral { background: var(--miro-coral); border-color: rgba(255,153,153,.55); box-shadow: none; }
        body.account-theme-miro .miro-card--cream { background: var(--miro-cream); border-color: rgba(255,198,198,.6); box-shadow: none; }
        body.account-theme-miro .miro-card__label { margin: 0 0 10px; color: #6b6f7e; font-size: 11px; font-weight: 600; letter-spacing: .1em; line-height: 1.4; text-transform: uppercase; }
        body.account-theme-miro .miro-card__title { margin: 0; color: #050038; font-size: 22px; font-weight: 500; letter-spacing: -.025em; line-height: 1.2; }
        body.account-theme-miro .miro-card__text { margin: 10px 0 0; color: #555a6a; font-size: 14px; line-height: 1.55; }
        body.account-theme-miro .miro-icon-tile { display: inline-grid; width: 44px; height: 44px; place-items: center; border-radius: 14px; background: var(--miro-pink); color: var(--miro-primary); }
        body.account-theme-miro .miro-icon-tile--teal { background: var(--miro-teal); color: var(--miro-teal-deep); }
        body.account-theme-miro .miro-icon-tile--coral { background: var(--miro-coral); color: var(--miro-primary); }
        body.account-theme-miro .miro-icon-tile--cream { background: var(--miro-cream); color: var(--miro-primary); }
        body.account-theme-miro .miro-empty { border: 1px dashed #c7cad5; border-radius: 28px; background: #fff; padding: clamp(28px, 5vw, 56px); text-align: center; }
        body.account-theme-miro .miro-empty__mark { display: grid; width: 56px; height: 56px; margin: 0 auto 18px; place-items: center; border-radius: 18px; background: var(--miro-teal); color: var(--miro-teal-deep); }
        body.account-theme-miro .miro-empty h2 { margin: 0; color: #050038; font-size: 22px; font-weight: 500; letter-spacing: -.025em; }
        body.account-theme-miro .miro-empty p { max-width: 480px; margin: 10px auto 0; color: #555a6a; font-size: 14px; line-height: 1.55; }
        body.account-theme-miro .miro-alert { display: flex; align-items: flex-start; gap: 14px; border-radius: 20px; padding: 18px 20px; }
        body.account-theme-miro .miro-alert--cream { border: 1px solid rgba(255,198,198,.8); background: var(--miro-cream); color: #050038; }
        body.account-theme-miro .miro-alert--success { border: 1px solid rgba(15,188,176,.22); background: var(--miro-teal); color: var(--miro-teal-deep); }
        body.account-theme-miro .miro-alert--error { border: 1px solid #fbd4d4; background: #fff5f5; color: #7f1d1d; }
        body.account-theme-miro .miro-form-card { max-width: 820px; padding: clamp(22px, 4vw, 36px); }
        body.account-theme-miro .miro-form-field label { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 8px; color: #050038; font-size: 13px; font-weight: 500; }
        body.account-theme-miro .miro-form-field label span { color: #6b6f7e; font-size: 11px; font-weight: 400; }
        body.account-theme-miro .miro-form-field small { display: block; margin-top: 7px; color: #6b6f7e; font-size: 12px; line-height: 1.45; }
        body.account-theme-miro .miro-form-field input,
        body.account-theme-miro .miro-form-field textarea,
        body.account-theme-miro .miro-form-field select { width: 100%; border: 1px solid #c7cad5 !important; border-radius: 12px; padding: 12px 14px; font-size: 14px; line-height: 1.5; }
        body.account-theme-miro .miro-form-field textarea { min-height: 140px; resize: vertical; }
        body.account-theme-miro .miro-form-field input:focus,
        body.account-theme-miro .miro-form-field textarea:focus,
        body.account-theme-miro .miro-form-field select:focus { border-color: var(--miro-blue) !important; box-shadow: 0 0 0 4px rgba(66,98,255,.12) !important; outline: none; }
        body.account-theme-miro .miro-section-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
        body.account-theme-miro .miro-section-heading h2 { margin: 0; color: #050038; font-size: 20px; font-weight: 500; letter-spacing: -.02em; }
        body.account-theme-miro .miro-section-heading p { margin: 0; color: #6b6f7e; font-size: 13px; }
        body.account-theme-miro .miro-list-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-top: 1px solid #eef0f3; padding: 16px 0; }
        body.account-theme-miro .miro-list-row:first-child { border-top: 0; padding-top: 0; }
        body.account-theme-miro .miro-list-row:last-child { padding-bottom: 0; }
        body.account-theme-miro .miro-list-row__label { color: #6b6f7e; font-size: 12px; }
        body.account-theme-miro .miro-list-row__value { color: #050038; font-size: 14px; font-weight: 500; text-align: right; }
        body.account-theme-miro .miro-stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        body.account-theme-miro .miro-stat { min-height: 132px; padding: 20px; }
        body.account-theme-miro .miro-stat__value { margin-top: 16px; color: #050038; font-size: 28px; font-weight: 500; letter-spacing: -.04em; }
        body.account-theme-miro .miro-stat__label { margin-top: 4px; color: #555a6a; font-size: 12px; line-height: 1.4; }
        body.account-theme-miro .miro-profile-hero { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(260px, .8fr); gap: 24px; align-items: stretch; margin-bottom: 28px; }
        body.account-theme-miro .miro-profile-hero__main { position: relative; overflow: hidden; padding: clamp(24px, 4vw, 40px); }
        body.account-theme-miro .miro-profile-hero__main::after { position: absolute; right: -34px; bottom: -48px; width: 180px; height: 180px; border: 1px solid rgba(5,0,56,.12); border-radius: 50%; content: ""; }
        body.account-theme-miro .miro-profile-hero__main > * { position: relative; z-index: 1; }
        body.account-theme-miro .miro-profile-hero__side { padding: 24px; }
        body.account-theme-miro .miro-avatar-xl { width: 82px; height: 82px; overflow: hidden; border: 3px solid rgba(255,255,255,.85); border-radius: 24px; background: var(--miro-teal); box-shadow: 0 8px 18px rgba(5,0,56,.1); }
        body.account-theme-miro .miro-avatar-xl img { width: 100%; height: 100%; object-fit: cover; }
        body.account-theme-miro .miro-avatar-xl__placeholder { display: grid; width: 100%; height: 100%; place-items: center; color: var(--miro-teal-deep); font-size: 30px; font-weight: 500; }
        body.account-theme-miro .miro-mobile-close { display: none; }
        body.account-theme-miro .account-flash { border-radius: 18px; padding: 14px 18px; font-size: 14px; line-height: 1.5; }
        body.account-theme-miro .account-flash--success { border: 1px solid rgba(15,188,176,.22); background: var(--miro-teal); color: var(--miro-teal-deep); }
        body.account-theme-miro .account-flash--error { border: 1px solid #fbd4d4; background: #fff5f5; color: #7f1d1d; }
        body.account-theme-miro .account-site-link { display: flex; align-items: center; gap: 10px; margin: 0 12px 12px; border-radius: 12px; padding: 10px 12px; color: #6b6f7e; font-size: 13px; font-weight: 500; transition: background .18s ease, color .18s ease; }
        body.account-theme-miro .account-site-link:hover { background: var(--miro-teal); color: var(--miro-teal-deep); }

        body.account-theme-miro input,
        body.account-theme-miro textarea,
        body.account-theme-miro select {
            border-color: #c7cad5 !important;
            background: #fff !important;
            color: var(--miro-primary) !important;
        }

        body.account-theme-miro input:focus,
        body.account-theme-miro textarea:focus,
        body.account-theme-miro select:focus {
            border-color: var(--miro-blue) !important;
            box-shadow: 0 0 0 4px rgba(66, 98, 255, .12) !important;
        }

        body.account-theme-miro .miro-directory-page { max-width: 1180px; }
        body.account-theme-miro .miro-directory-header { max-width: 760px; margin-bottom: 32px; }
        body.account-theme-miro .miro-directory-header__eyebrow { margin: 0 0 14px; color: var(--miro-blue); font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        body.account-theme-miro .miro-directory-header h1 { margin: 0; color: #050038; font-size: clamp(34px, 5vw, 56px); font-weight: 500; line-height: 1.08; letter-spacing: -.045em; }
        body.account-theme-miro .miro-directory-header p:last-child { margin: 16px 0 0; color: #555a6a; font-size: 17px; line-height: 1.55; }
        body.account-theme-miro .miro-directory-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        body.account-theme-miro .miro-directory-card { padding: 20px; border: 1px solid #eef0f3; border-radius: 24px; background: #fff; box-shadow: 0 12px 32px rgba(5, 0, 56, .06); }
        body.account-theme-miro .miro-directory-card__profile { display: grid; grid-template-columns: 112px minmax(0, 1fr); gap: 18px; align-items: start; }
        body.account-theme-miro .miro-directory-card__avatar { width: 112px; height: 112px; overflow: hidden; border-radius: 16px; background: linear-gradient(135deg, #c3faf5, #ffe6cd); }
        body.account-theme-miro .miro-directory-card__avatar img { width: 100%; height: 100%; object-fit: cover; }
        body.account-theme-miro .miro-directory-card__avatar-placeholder { display: grid; width: 100%; height: 100%; place-items: center; color: #187574; font-size: 32px; font-weight: 600; }
        body.account-theme-miro .miro-directory-card__role { margin: 0 0 7px; color: #187574; font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
        body.account-theme-miro .miro-directory-card h2 { margin: 0; color: #050038; font-size: 22px; font-weight: 500; line-height: 1.2; }
        body.account-theme-miro .miro-directory-card__specialization { margin: 7px 0 0; color: #6b6f7e; font-size: 14px; }
        body.account-theme-miro .miro-directory-card__tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 16px; }
        body.account-theme-miro .miro-directory-card__tag { display: inline-flex; padding: 5px 9px; border-radius: 9999px; color: #1c1c1e; font-size: 11px; font-weight: 500; line-height: 1.2; }
        body.account-theme-miro .miro-directory-card__tag--yellow { background: var(--miro-cream); }
        body.account-theme-miro .miro-directory-card__tag--cream,
        body.account-theme-miro .miro-directory-card__tag--orange { background: var(--miro-cream); }
        body.account-theme-miro .miro-directory-card__tag--teal { background: #c3faf5; color: #187574; }
        body.account-theme-miro .miro-directory-card__tag--rose { background: #ffd8f4; }
        body.account-theme-miro .miro-directory-card__tag--coral { background: #ffc6c6; }
        body.account-theme-miro .miro-directory-card__tag--blue { background: #eef1ff; color: #4262ff; }
        body.account-theme-miro .miro-directory-card__description { margin: 18px 0 0; color: #555a6a; font-size: 14px; line-height: 1.55; }
        body.account-theme-miro .miro-directory-card__details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 18px; }
        body.account-theme-miro .miro-directory-card__detail { padding: 14px; border-radius: 14px; background: #fafbfc; }
        body.account-theme-miro .miro-directory-card__detail--offer { background: #fff4c4; }
        body.account-theme-miro .miro-directory-card__detail-label { display: block; margin-bottom: 6px; color: #6b6f7e; font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        body.account-theme-miro .miro-directory-card__detail p { margin: 0; color: #1c1c1e; font-size: 13px; line-height: 1.45; }
        body.account-theme-miro .miro-directory-card__contact { display: inline-flex; align-items: center; margin-top: 18px; padding: 10px 16px; border-radius: 9999px; background: #1c1c1e; color: #fff; font-size: 13px; font-weight: 500; transition: transform .18s ease, background .18s ease; }
        body.account-theme-miro .miro-directory-card__contact:hover { background: #2c2c34; transform: translateY(-1px); }
        body.account-theme-miro .miro-directory-empty { padding: 40px; border: 1px dashed #c7cad5; border-radius: 28px; background: #fff; text-align: center; }

        @media (max-width: 767px) {
            body.account-theme-miro .miro-page-header { display: block; }
            body.account-theme-miro .miro-page-header .miro-actions { margin-top: 20px; }
            body.account-theme-miro .miro-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            body.account-theme-miro .miro-profile-hero { grid-template-columns: 1fr; }
            body.account-theme-miro .miro-directory-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 520px) {
            body.account-theme-miro .account-main { padding: 28px 16px 48px; }
            body.account-theme-miro .miro-page-title { font-size: 34px; }
            body.account-theme-miro .miro-actions > * { flex: 1 1 auto; }
            body.account-theme-miro .miro-stat { min-height: 116px; padding: 16px; }
            body.account-theme-miro .miro-stat__value { margin-top: 12px; font-size: 24px; }
            body.account-theme-miro .miro-card { border-radius: 20px; }
            body.account-theme-miro .miro-directory-card__profile { grid-template-columns: 80px minmax(0, 1fr); gap: 14px; }
            body.account-theme-miro .miro-directory-card__avatar { width: 80px; height: 80px; border-radius: 14px; }
            body.account-theme-miro .miro-directory-card h2 { font-size: 19px; }
            body.account-theme-miro .miro-directory-card__details { grid-template-columns: 1fr; }
        }
    </style>
    @stack('head')
</head>
<body class="h-full bg-[#f7f8fa] text-[#0f172a] antialiased account-theme-{{ $accountTheme }}" x-data="{ sidebarOpen: false }">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-black/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

    <div class="flex h-full">
        <aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="account-sidebar fixed inset-y-0 left-0 z-30 flex w-64 shrink-0 flex-col bg-white transition-transform duration-200 ease-out lg:static lg:translate-x-0 lg:z-auto"
               style="box-shadow: 1px 0 0 rgba(0,0,0,.06), 4px 0 24px rgba(0,0,0,.03)">

            <div class="flex shrink-0 items-center gap-3 px-5 pb-5 pt-6">
                <span class="account-brand-mark flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white p-0.5 shadow-sm">
                    <img src="{{ asset('images/brand/favicon.png') }}" alt="Women Entrepreneurs Platform" class="h-full w-full object-contain">
                </span>
                <div class="min-w-0 leading-none">
                    <p class="truncate text-sm font-bold tracking-tight text-[#0f172a]">Women Entrepreneurs Platform</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-widest text-gray-400">of the Two Banks</p>
                </div>
            </div>

            <div class="account-user-card mx-3 mb-4 shrink-0 rounded-2xl p-4" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
                <div class="flex items-center gap-3">
                    @if($accountUser->avatar_path)
                        <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name }}" class="h-10 w-10 shrink-0 rounded-full object-cover">
                    @else
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                            {{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[#0f172a]">{{ explode(' ', (string) $accountUser->full_name)[0] }}</p>
                        @if($accountUser->telegram_username)
                            <p class="mt-0.5 truncate text-xs font-medium text-brand-600">{{ '@' . $accountUser->telegram_username }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="account-language-switcher mx-3 mb-4 flex rounded-full border border-gray-200 bg-gray-50 p-1">
                @foreach(['ru' => 'RU', 'en' => 'EN', 'ro' => 'RO'] as $locale => $label)
                    <a href="{{ route('language.switch', $locale) }}"
                       class="flex-1 rounded-full px-2 py-1 text-center text-xs font-semibold transition {{ app()->getLocale() === $locale ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:text-brand-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <nav class="flex-1 overflow-y-auto px-3">
                <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('account.nav_title') }}</p>
                @php
                    $navItems = [
                        ['route' => 'account.index', 'label' => __('account.nav.home'), 'path' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'account.matches', 'label' => __('account.nav.matches'), 'path' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['route' => 'account.search', 'label' => __('account.nav.search'), 'path' => 'M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z'],
                        ['route' => 'account.people', 'label' => __('account.nav.people'), 'path' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['route' => 'account.opportunities.index', 'label' => __('account.nav.opportunities'), 'path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'account.knowledge', 'label' => __('account.nav.knowledge'), 'path' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ['route' => 'account.profile', 'label' => __('account.nav.profile'), 'path' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ];
                @endphp
                @foreach($navItems as $item)
                    @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                    <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                       class="group mb-0.5 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150 {{ $active ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $active ? 'text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' }}" @if($active) style="background:linear-gradient(135deg,#7c3aed,#4f46e5)" @endif>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['path'] }}"/></svg>
                        </span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="shrink-0 border-t border-gray-100 px-3 py-4">
                <a href="{{ url('/') }}" class="account-site-link">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12h18M3 12l6-6m-6 6 6 6"/></svg>
                    <span>{{ __('account.back_to_site') }}</span>
                </a>
                <form action="{{ route('account.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                        {{ __('account.logout') }}
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="account-mobile-header sticky top-0 z-10 flex h-14 shrink-0 items-center gap-3 border-b border-gray-100 bg-white px-4 lg:hidden">
                <button @click="sidebarOpen = true" class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 transition" aria-label="{{ __('account.open_menu') }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="text-sm font-semibold text-[#0f172a]">@yield('title', __('account.cabinet'))</span>
            </header>

            <main class="account-main flex-1 overflow-y-auto px-6 py-8 lg:px-10">
                @foreach(['success' => 'emerald', 'error' => 'red'] as $key => $color)
                    @if(session($key))
                        <div class="account-flash account-flash--{{ $key === 'success' ? 'success' : 'error' }} mb-6 rounded-xl border border-{{ $color }}-200 bg-{{ $color }}-50 px-4 py-3 text-sm text-{{ $color }}-800">{{ session($key) }}</div>
                    @endif
                @endforeach
                @yield('content')
            </main>
        </div>
    </div>

<script>
    (function () {
        if (!window.Telegram || !window.Telegram.WebApp) return;
        var tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        var logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) logoutForm.closest('div').style.display = 'none';
        document.querySelectorAll('a[href*="t.me/WomenComBot"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                tg.openTelegramLink(link.href);
                tg.close();
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>
