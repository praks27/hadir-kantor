<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f6f7f2" id="theme-color-meta">
    <title>@yield('title', 'Hadir Kantor') · HadirKantor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (() => {
            try {
                const savedTheme = localStorage.getItem('hadirkantor-theme');
                const theme = savedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', theme === 'dark');
            } catch (_) {
                document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
            }
        })();
    </script>
    @stack('head')
    <link rel="stylesheet" href="{{ asset('vendor/toastify-js/toastify.css', false) }}">
    <style>
        .toastify { box-sizing: border-box; display: inline-flex; align-items: center; opacity: 0; overflow: hidden; transform-origin: top right; animation: toastify-enter 460ms cubic-bezier(.16,1,.3,1) both; cursor: default; }
        .toastify.on { opacity: 1; }
        .toastify:not(.on) { animation: toastify-exit 220ms ease-in both; }
        .toastify .toast-close { position: absolute; top: 12px; right: 12px; display: grid; width: 32px; height: 32px; place-items: center; padding: 0; border: 0; border-radius: 9px; background: transparent; color: #64748b; font-size: 15px; line-height: 1; cursor: pointer; }
        .toastify .toast-close:hover { background: rgb(148 163 184 / 14%); color: #334155; }
        .toastify .toast-close:focus-visible { outline: 2px solid rgb(86 119 101 / 45%); outline-offset: 1px; }
        .toastify-icon { animation: toastify-icon-pop 460ms cubic-bezier(.2,.85,.25,1.25) 80ms both; }
        .toastify.app-toast-success::before, .toastify.app-toast-error::before, .app-toast-fallback.app-toast-success::before, .app-toast-fallback.app-toast-error::before { content: ''; position: absolute; right: 0; bottom: 0; left: 0; height: 6px; opacity: .2; }
        .toastify.app-toast-success::after, .toastify.app-toast-error::after, .app-toast-fallback.app-toast-success::after, .app-toast-fallback.app-toast-error::after { content: ''; position: absolute; right: 0; bottom: 0; left: 0; height: 6px; transform-origin: left center; animation: toastify-countdown 5s linear both; }
        .app-toast-success::before { background: #4b8d5b; }
        .app-toast-error::before { background: #c34b4b; }
        .app-toast-success::after { background: #4b8d5b; }
        .app-toast-error::after { background: #c34b4b; }
        @keyframes toastify-enter { from { opacity: 0; transform: translate3d(0,-22px,0) scale(.9) rotate(-1deg); } to { opacity: 1; transform: translate3d(0,0,0) scale(1) rotate(0); } }
        @keyframes toastify-exit { from { opacity: 1; transform: translate3d(0,0,0) scale(1); } to { opacity: 0; transform: translate3d(0,-10px,0) scale(.97); } }
        @keyframes toastify-icon-pop { 0% { opacity: 0; transform: scale(.45) rotate(-12deg); } 70% { opacity: 1; transform: scale(1.12) rotate(3deg); } 100% { opacity: 1; transform: scale(1) rotate(0); } }
        @keyframes toastify-countdown { from { transform: scaleX(1); } to { transform: scaleX(0); } }
        @media (prefers-reduced-motion: reduce) { .toastify, .toastify-icon { animation: none; opacity: 1; transform: none; } }
    </style>
    <script defer data-toastify src="{{ asset('vendor/toastify-js/toastify.js', false) }}"></script>
    <script>
        window.toastifyReady = new Promise((resolve) => {
            if (window.Toastify) return resolve(true);

            const script = document.querySelector('[data-toastify]');
            if (!script) return resolve(false);

            script.addEventListener('load', () => resolve(true), { once: true });
            script.addEventListener('error', () => resolve(false), { once: true });
            window.setTimeout(() => resolve(false), 1800);
        });
    </script>
</head>
<body class="min-h-screen bg-[#f6f7f2] font-sans text-slate-800 antialiased">
    <div id="page-loader" class="page-loader" role="status" aria-label="Sedang memuat halaman" aria-live="polite">
        <canvas data-three-loader aria-hidden="true" class="page-loader-canvas"></canvas>
        <div class="page-loader-content">
            <span class="page-loader-mark" aria-hidden="true">H</span>
            <p class="page-loader-title">HadirKantor</p>
            <p class="page-loader-caption">Menyiapkan halaman Anda</p>
            <div class="page-loader-meter">
                <span data-loader-walker class="page-loader-walker" aria-hidden="true">
                    <svg viewBox="0 0 48 68" fill="none">
                        <ellipse class="walker-shadow" cx="24" cy="65" rx="13" ry="2" fill="currentColor" opacity=".16"/>
                        <g class="walker-limb walker-leg walker-leg-a">
                            <path d="M22 42 19 52" stroke="#344941" stroke-width="5" stroke-linecap="round"/>
                            <g class="walker-knee walker-knee-a"><path d="m19 52-4 8" stroke="#344941" stroke-width="4.5" stroke-linecap="round"/><path d="M15 60h7q1 0 1 1v1h-9q-2 0-1-1l2-1Z" fill="#c6a46b"/></g>
                        </g>
                        <g class="walker-limb walker-leg walker-leg-b">
                            <path d="m25 42 5 9" stroke="#40584a" stroke-width="5" stroke-linecap="round"/>
                            <g class="walker-knee walker-knee-b"><path d="m30 51 7 7" stroke="#40584a" stroke-width="4.5" stroke-linecap="round"/><path d="M37 58h5q2 0 2 2v1h-9q-2 0-1-2l3-1Z" fill="#c6a46b"/></g>
                        </g>
                        <g class="walker-upper-body">
                            <path d="M19 27q-3 1-3 5v7l4 1 2-11Z" fill="#b89a68"/>
                            <g class="walker-limb walker-arm walker-arm-a"><path d="m20 26-5 7" stroke="#496653" stroke-width="4.5" stroke-linecap="round"/><g class="walker-elbow walker-elbow-a"><path d="m15 33-2 7" stroke="#496653" stroke-width="4" stroke-linecap="round"/><circle cx="13" cy="41" r="2.2" fill="#edc4a3"/></g></g>
                            <path d="M20 24q5-4 11 0l3 18q-7 4-15 0l1-18Z" fill="currentColor"/>
                            <path d="m25 23 4 3-3 4-3-4 2-3Z" fill="#e4c887"/>
                            <path d="M24 30v11m-3-10h3m3-1h3" stroke="#ffffff" stroke-opacity=".55" stroke-width="1.1" stroke-linecap="round"/>
                            <path d="M29 34h3v4h-3" stroke="#e4c887" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                            <g class="walker-limb walker-arm walker-arm-b"><path d="m29 26 5 7" stroke="#66806c" stroke-width="4.5" stroke-linecap="round"/><g class="walker-elbow walker-elbow-b"><path d="m34 33 2 7" stroke="#66806c" stroke-width="4" stroke-linecap="round"/><circle cx="36" cy="41" r="2.2" fill="#edc4a3"/></g></g>
                            <path d="M23 21h4v4h-4z" fill="#edc4a3"/>
                            <path d="M19 11c0-4 2-7 6-7 4 0 6 3 6 7v2l3 2-3 2c-.4 3-3 5-7 5s-7-3-7-7c0-2 1-3 2-4Z" fill="#edc4a3"/>
                            <path d="M18 14c-1-5 2-10 7-10 4.2 0 7.2 3.3 7 8-2.3-.4-3.5-1.7-4.2-3.2-2.2 2.2-5.3 3.5-9.8 3.2Z" fill="#334b3d"/>
                            <path d="M27 12.5c1.4.2 2 .7 2 1.5" stroke="#263a30" stroke-width="1.4" stroke-linecap="round"/>
                            <path d="m31 13 3 1.5-3 1" stroke="#a97059" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M26 17q1.5 1 3 0" stroke="#a97059" stroke-width=".8" stroke-linecap="round"/>
                        </g>
                    </svg>
                </span>
                <div id="page-loader-progress" class="page-loader-track" role="progressbar" aria-label="Progres memuat halaman" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span data-loader-fill></span></div>
            </div>
        </div>
    </div>
    <script>
        window.hidePageLoader = () => {
            const loader = document.getElementById('page-loader');
            if (!loader) return;
            loader.classList.add('is-hidden');
            loader.setAttribute('aria-hidden', 'true');
            window.dispatchEvent(new Event('page-loader:hidden'));
        };
        window.setTimeout(window.hidePageLoader, 5000);
        window.addEventListener('load', () => {
            if (!window.pageLoaderManaged) window.setTimeout(window.hidePageLoader, 600);
        }, { once: true });
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) window.hidePageLoader();
        });
    </script>
    <button id="theme-toggle" type="button" class="theme-toggle" aria-label="Aktifkan tema gelap" title="Ganti tema">
        <svg class="theme-icon-light" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <svg class="theme-icon-dark" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.2 15.5A8.6 8.6 0 0 1 8.5 3.8 8.7 8.7 0 1 0 20.2 15.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        <span class="theme-label">Tema</span>
    </button>
    @yield('body')
    <x-alerts />
    <script>
        (() => {
            const button = document.getElementById('theme-toggle');
            const meta = document.getElementById('theme-color-meta');
            const applyTheme = (theme) => {
                const isDark = theme === 'dark';
                document.documentElement.classList.toggle('dark', isDark);
                button.setAttribute('aria-label', isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
                meta.setAttribute('content', isDark ? '#111a17' : '#f6f7f2');
            };
            applyTheme(document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            button.addEventListener('click', () => {
                const theme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                applyTheme(theme);
                try { localStorage.setItem('hadirkantor-theme', theme); } catch (_) {}
            });
        })();
    </script>
</body>
</html>
