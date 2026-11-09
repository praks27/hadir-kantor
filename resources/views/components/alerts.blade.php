<div id="app-toast-fallbacks" class="pointer-events-none fixed inset-x-4 top-4 z-[100] mx-auto flex max-w-lg flex-col gap-3 sm:right-6 sm:left-auto sm:mx-0" aria-label="Notifikasi"></div>

<style>
    @keyframes delete-confirm-toast-in { from { opacity: 0; transform: scale(.94) translateY(12px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    @keyframes delete-confirm-toast-out { from { opacity: 1; transform: scale(1) translateY(0); } to { opacity: 0; transform: scale(.97) translateY(8px); } }
    .delete-confirm-fallback { animation: delete-confirm-toast-in 260ms cubic-bezier(.2,.8,.2,1.08) both; }
    .delete-confirm-fallback.is-closing { animation: delete-confirm-toast-out 160ms ease-in both; }
    .toastify.delete-confirm-toast { animation: delete-confirm-toast-in 260ms cubic-bezier(.2,.8,.2,1.08) both; transform-origin: center; }
    .toastify.delete-confirm-toast:not(.on) { animation: delete-confirm-toast-out 160ms ease-in both; }
    @media (prefers-reduced-motion: reduce) {
        .toastify.delete-confirm-toast { animation-duration: 1ms !important; }
    }
</style>

<script>
    (() => {
        const fallbackHost = document.getElementById('app-toast-fallbacks');
        const tones = {
            success: {
                background: '#f4faf4', border: '#cfe5d1', color: '#244d31', iconBackground: '#e5f3e7', iconColor: '#26703a',
                dark: { background: '#1c3024', border: '#456b4c', color: '#c4e8c9', iconBackground: '#294333', iconColor: '#a8d5b1' },
            },
            error: {
                background: '#fff7f7', border: '#f3cccc', color: '#762c2c', iconBackground: '#fde8e8', iconColor: '#c33d3d',
                dark: { background: '#382328', border: '#74424b', color: '#fecdd3', iconBackground: '#512d35', iconColor: '#fda4af' },
            },
            warning: {
                background: '#fffdf7', border: '#f3dba1', color: '#58451b', iconBackground: '#fff1c7', iconColor: '#a66b08',
                dark: { background: '#342d1c', border: '#69572d', color: '#f7dfaa', iconBackground: '#493c21', iconColor: '#f2d391' },
            },
            info: {
                background: '#f7fafc', border: '#d7e3ed', color: '#2f4858', iconBackground: '#e6f0f7', iconColor: '#387494',
                dark: { background: '#1f2c34', border: '#3e5b6b', color: '#c8e2ef', iconBackground: '#293e49', iconColor: '#91c9df' },
            },
        };

        const tonePalette = (tone) => {
            const palette = tones[tone] ?? tones.info;
            return document.documentElement.classList.contains('dark') ? palette.dark : palette;
        };

        const iconPaths = {
            success: '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.5 2.5L16.5 9"/>',
            error: '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/>',
            warning: '<path d="m10.3 4.3-7 12.1a1.8 1.8 0 0 0 1.6 2.7h14.2a1.8 1.8 0 0 0 1.6-2.7l-7-12.1a1.9 1.9 0 0 0-3.4 0Z"/><path d="M12 9v4m0 3h.01"/>',
            info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/>',
        };

        const createToastIcon = (tone) => {
            const palette = tonePalette(tone);
            const icon = document.createElement('span');
            icon.className = 'toastify-icon grid h-11 w-11 shrink-0 place-items-center rounded-xl';
            icon.style.backgroundColor = palette.iconBackground;
            icon.style.color = palette.iconColor;
            icon.setAttribute('aria-hidden', 'true');
            icon.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">${iconPaths[tone] ?? iconPaths.info}</svg>`;
            return icon;
        };

        const createToastContent = (message, tone) => {
            const content = document.createElement('div');
            content.className = 'flex w-full items-start gap-3';
            content.appendChild(createToastIcon(tone));

            const text = document.createElement('p');
            text.className = 'flex-1 whitespace-pre-line pt-1.5 pr-2 text-[15px] font-semibold leading-6';
            text.textContent = message;
            content.appendChild(text);
            return content;
        };

        const showFallbackToast = (message, tone) => {
            const palette = tonePalette(tone);
            const toast = document.createElement('div');
            toast.setAttribute('role', tone === 'error' ? 'alert' : 'status');
            toast.setAttribute('aria-live', tone === 'error' ? 'assertive' : 'polite');
            toast.tabIndex = -1;
            toast.className = `app-toast-fallback app-toast-${tone} flash-alert pointer-events-auto relative flex min-h-[88px] items-start gap-3 overflow-hidden rounded-2xl border bg-white/95 p-5 pr-14 text-[15px] font-semibold leading-6 shadow-[0_22px_60px_rgba(36,56,47,0.2)] backdrop-blur-xl`;
            toast.style.borderColor = palette.border;
            toast.style.color = palette.color;
            toast.style.backgroundColor = palette.background;
            let autoCloseTimer = null;
            let dismissed = false;

            const dismiss = () => {
                if (dismissed) return;
                dismissed = true;
                window.clearTimeout(autoCloseTimer);
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 260);
            };

            const dismissButton = document.createElement('button');
            dismissButton.type = 'button';
            dismissButton.className = 'absolute right-3 top-3 grid h-8 w-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-700/30';
            dismissButton.setAttribute('aria-label', 'Tutup notifikasi');
            dismissButton.innerHTML = '<svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true"><path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
            dismissButton.addEventListener('click', dismiss);
            toast.append(createToastContent(message, tone), dismissButton);
            fallbackHost.appendChild(toast);
            if (tone === 'success' || tone === 'error') {
                autoCloseTimer = window.setTimeout(dismiss, 5000);
            }
            return toast;
        };

        window.showAppToast = async (message, tone = 'info', options = {}) => {
            if (!window.Toastify && window.toastifyReady) await window.toastifyReady;

            if (typeof window.Toastify === 'function') {
                const palette = tonePalette(tone);
                const toast = window.Toastify({
                    node: createToastContent(String(message), tone),
                    duration: 0,
                    close: true,
                    gravity: 'top',
                    position: 'right',
                    stopOnFocus: false,
                    ariaLive: tone === 'error' ? 'assertive' : 'polite',
                    className: `app-toast app-toast-${tone}`,
                    style: {
                        position: 'fixed',
                        top: '16px',
                        right: '16px',
                        zIndex: '2147483647',
                        width: 'min(460px, calc(100vw - 32px))',
                        maxWidth: 'calc(100vw - 32px)',
                        minHeight: '88px',
                        padding: '20px 52px 20px 20px',
                        borderRadius: '18px',
                        border: `1px solid ${palette.border}`,
                        background: palette.background,
                        color: palette.color,
                        boxShadow: '0 22px 60px rgba(36,56,47,.2)',
                        fontSize: '15px',
                        lineHeight: '1.6',
                    },
                }).showToast();
                if ((tone === 'success' || tone === 'error') && toast.toastElement) {
                    let dismissed = false;
                    const autoCloseTimer = window.setTimeout(() => {
                        if (!dismissed) toast.hideToast();
                    }, 5000);
                    toast.toastElement.querySelector('.toast-close')?.addEventListener('click', () => {
                        dismissed = true;
                        window.clearTimeout(autoCloseTimer);
                    }, { once: true });
                }
                if (options.focus && toast.toastElement) {
                    toast.toastElement.tabIndex = -1;
                    toast.toastElement.setAttribute('role', 'alert');
                    window.requestAnimationFrame(() => toast.toastElement?.focus({ preventScroll: true }));
                }
                return;
            }

            const toast = showFallbackToast(String(message), tone);
            if (options.focus) window.requestAnimationFrame(() => toast?.focus({ preventScroll: true }));
        };

        let activeConfirmToast = null;
        let activeConfirmFallback = null;
        let pendingDeleteForm = null;
        let returnFocus = null;

        const closeConfirmModal = () => {
            activeConfirmToast?.hideToast();
            if (activeConfirmFallback) {
                const fallback = activeConfirmFallback;
                fallback.classList.add('is-closing');
                window.setTimeout(() => fallback.remove(), 160);
            }
            window.stopDeleteConfirmScene?.();
            activeConfirmToast = null;
            activeConfirmFallback = null;
            pendingDeleteForm = null;
            document.body.classList.remove('overflow-hidden');
            returnFocus?.focus({ preventScroll: true });
            returnFocus = null;
        };

        const openConfirmModal = async (form, submitter) => {
            if (!window.Toastify && window.toastifyReady) await window.toastifyReady;
            const useToastify = typeof window.Toastify === 'function';

            pendingDeleteForm = form;
            returnFocus = submitter;
            const overlay = document.createElement('div');
            overlay.className = useToastify
                ? 'relative flex h-full w-full items-center justify-center overflow-hidden bg-slate-950/65 p-4 backdrop-blur-sm'
                : 'delete-confirm-fallback fixed inset-0 z-[2147483647] flex items-center justify-center overflow-hidden bg-slate-950/65 p-4 backdrop-blur-sm';
            overlay.innerHTML = `
                <canvas data-delete-confirm-canvas aria-hidden="true" class="pointer-events-none absolute inset-0 z-0 h-full w-full"></canvas>
                <button type="button" data-confirm-backdrop class="absolute inset-0 z-10 cursor-default" aria-label="Tutup konfirmasi"></button>
                <section class="relative z-20 w-full max-w-md rounded-3xl border border-white/70 bg-white/90 p-6 shadow-2xl backdrop-blur-xl sm:p-8" role="alertdialog" aria-modal="true" aria-labelledby="delete-confirm-title" aria-describedby="delete-confirm-description">
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" class="h-7 w-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/></svg></span>
                    <h2 id="delete-confirm-title" class="mt-4 text-center font-display text-xl font-bold text-slate-900">Hapus data ini?</h2>
                    <p id="delete-confirm-description" class="mt-2 text-center text-sm leading-6 text-slate-500"></p>
                    <p class="mt-2 text-center text-xs font-medium text-rose-600">Data yang dihapus tidak dapat dipulihkan.</p>
                    <div class="mt-6 flex flex-col-reverse justify-center gap-3 sm:flex-row">
                        <button type="button" data-confirm-cancel class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Kembali</button>
                        <button type="button" data-confirm-delete class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-600/20"><svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/></svg><span data-confirm-action></span></button>
                    </div>
                </section>`;
            overlay.querySelector('#delete-confirm-description').textContent = form.dataset.toastConfirm;
            overlay.querySelector('[data-confirm-action]').textContent = form.dataset.toastConfirmAction || 'Hapus';

            if (useToastify) {
                activeConfirmToast = window.Toastify({
                    node: overlay,
                    duration: 0,
                    gravity: 'bottom',
                    position: 'center',
                    className: 'delete-confirm-toast',
                    ariaLive: 'assertive',
                    style: {
                        position: 'fixed', inset: '0', display: 'flex', alignItems: 'center', justifyContent: 'center',
                        width: '100vw', height: '100vh', maxWidth: '100vw', margin: '0', padding: '0',
                        overflow: 'visible', borderRadius: '0', background: 'transparent', boxShadow: 'none',
                        cursor: 'default', zIndex: '2147483647',
                    },
                }).showToast();
                // Toastify repositions each toast after display; restore the full-screen modal bounds.
                activeConfirmToast.toastElement.style.inset = '0';
            } else {
                activeConfirmFallback = overlay;
                overlay.style.position = 'fixed';
                overlay.style.inset = '0';
                overlay.style.zIndex = '2147483647';
                document.body.appendChild(overlay);
            }
            document.body.classList.add('overflow-hidden');
            window.startDeleteConfirmScene?.(overlay.querySelector('[data-delete-confirm-canvas]'));

            const cancelButton = overlay.querySelector('[data-confirm-cancel]');
            const deleteButton = overlay.querySelector('[data-confirm-delete]');
            cancelButton.addEventListener('click', closeConfirmModal);
            overlay.querySelector('[data-confirm-backdrop]').addEventListener('click', closeConfirmModal);
            deleteButton.addEventListener('click', () => {
                if (!pendingDeleteForm) return;
                const formToSubmit = pendingDeleteForm;
                deleteButton.disabled = true;
                closeConfirmModal();
                HTMLFormElement.prototype.submit.call(formToSubmit);
            });
            cancelButton.focus({ preventScroll: true });
        };

        document.addEventListener('keydown', (event) => {
            if (!activeConfirmToast && !activeConfirmFallback) return;
            if (event.key === 'Escape') closeConfirmModal();
            if (event.key === 'Tab') {
                const overlay = activeConfirmToast?.toastElement?.querySelector('.flex.h-full') ?? activeConfirmFallback;
                const cancel = overlay?.querySelector('[data-confirm-cancel]');
                const confirm = overlay?.querySelector('[data-confirm-delete]');
                event.preventDefault();
                (document.activeElement === cancel ? confirm : cancel)?.focus();
            }
        });

        document.querySelectorAll('form[data-toast-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                openConfirmModal(form, event.submitter);
            });
        });

        const statusMessage = @json(session('status'));
        const leaveNotification = @json(session('leave_notification'));
        const errorMessages = @json($errors->all());
        if (statusMessage) window.showAppToast(statusMessage, 'success');
        if (errorMessages.length) window.showAppToast(errorMessages.join('\n'), 'error');
        if (leaveNotification) window.showAppToast(leaveNotification, 'warning');
    })();
</script>
