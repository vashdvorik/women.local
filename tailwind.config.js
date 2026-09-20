import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // Сканируем только админку: публичные темы women.local живут на собственных
    // статичных CSS-файлах и Tailwind не используют.
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/admin/**/*.blade.php',
        './resources/views/auth/**/*.blade.php',
        './resources/views/layouts/**/*.blade.php',
        './resources/views/components/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    // Классы, собираемые из кусков в PHP/JS, сборщик не видит — держим их здесь.
    safelist: [
        'tab-badge--done',
        'tab-badge--partial',
        'tab-badge--empty',
        'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4',
        'sm:grid-cols-3', 'sm:grid-cols-4',
    ],

    theme: {
        extend: {
            colors: {
                accent:   { DEFAULT: '#0066cc', hover: '#0071e3', soft: '#e8f1fb' },
                canvas:   '#f5f5f7',
                surface:  { DEFAULT: '#ffffff', sunken: '#fafafc' },
                sidebar:  '#ffffff',
                ink:      { DEFAULT: '#1d1d1f', muted: '#6e6e73', faint: '#a1a1a6' },
                'on-accent':        '#ffffff',
                'on-sidebar':       '#1d1d1f',
                'on-sidebar-muted': '#6e6e73',
                hairline: { DEFAULT: '#e0e0e0', soft: '#f0f0f0' },
                danger:   { DEFAULT: '#d70015', soft: '#fdecec' },
                success:  { DEFAULT: '#1d8a3f', soft: '#e8f5ec' },
                warning:  { DEFAULT: '#b25000', soft: '#fdf1e5' },
                neutral:  { DEFAULT: '#6e6e73', soft: '#eeeeef' },
            },
            fontFamily: {
                sans: ['system-ui', '-apple-system', '"Segoe UI"', 'Roboto', 'sans-serif'],
            },
            fontSize: {
                // Две базы плотности: 13px — служебная обвязка (таблицы, меню,
                // кнопки, вкладки, служебные поля форм), 15px — то, что редактор
                // пишет и что уедет на сайт (заголовок и описание материала,
                // текст блока). См. DESIGN.md §3 в education3.
                micro:   ['11px', { lineHeight: '1.2',  letterSpacing: '0.3px'   }],
                caption: ['12px', { lineHeight: '1.4',  letterSpacing: '0'       }],
                ui:      ['13px', { lineHeight: '1.45', letterSpacing: '-0.08px' }],
                'ui-strong': ['13px', { lineHeight: '1.3', letterSpacing: '-0.08px' }],
                reading: ['15px', { lineHeight: '1.5',  letterSpacing: '-0.2px'  }],
                'reading-strong': ['15px', { lineHeight: '1.3', letterSpacing: '-0.2px' }],
                section: ['17px', { lineHeight: '1.3',  letterSpacing: '-0.24px' }],
                page:    ['21px', { lineHeight: '1.25', letterSpacing: '-0.3px'  }],
            },
            fontWeight: { light: '300', normal: '400', semibold: '600', bold: '700' },
            borderRadius: { xs: '6px', sm: '8px', md: '12px', pill: '9999px' },
            maxWidth: { form: '860px', shell: '1180px', prose: '760px' },
            spacing: { sidebar: '260px', header: '60px' },
            transitionDuration: { DEFAULT: '150ms' },
        },
    },

    plugins: [forms],
};
