@extends('layouts.app')

@section('title', 'Masuk')

@section('body')
<main class="page-enter grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
    <div id="leave-request-notification" hidden role="alert" aria-live="assertive" class="flash-alert pointer-events-auto fixed inset-x-4 top-4 z-[110] mx-auto flex min-h-[88px] max-w-lg items-start gap-3 rounded-2xl border border-amber-200/80 bg-white/95 p-5 text-slate-900 shadow-[0_22px_60px_rgba(36,56,47,0.2)] backdrop-blur-xl sm:right-6 sm:left-auto sm:mx-0">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-700"><x-icon name="file" class="h-5 w-5"/></span>
        <div class="flex-1 pt-0.5"><p class="text-[15px] font-bold">Pengajuan izin menunggu</p><p class="mt-1 text-[15px] leading-6 text-slate-600">Ada pengajuan izin yang menunggu persetujuan. Silakan masuk untuk melihat pengajuan sesuai wewenang Anda.</p></div>
        <button id="dismiss-leave-notification" type="button" aria-label="Tutup notifikasi" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"><svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true"><path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
    </div>
    <section class="relative hidden overflow-hidden bg-[#567765] p-12 text-white lg:flex lg:flex-col lg:justify-between xl:p-16">
        <div class="soft-float absolute -right-28 -top-28 h-96 w-96 rounded-full border border-white/15"></div>
        <div class="soft-float absolute -right-12 -top-12 h-64 w-64 rounded-full border border-white/15" style="animation-delay:-3s"></div>
        <a href="/" class="relative flex items-center gap-3 font-display text-xl font-extrabold tracking-tight"><span class="grid h-10 w-10 place-items-center rounded-xl bg-[#e2ebdc] text-[#405e4e] shadow-sm">H</span> HadirKantor</a>
        <div class="relative max-w-xl pb-10">
            <span class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[.18em] text-[#edf3e9]"><x-icon name="check" class="h-4 w-4"/> Kehadiran lebih teratur</span>
            <h1 class="font-display text-5xl font-bold leading-[1.12] xl:text-6xl">Hari kerja dimulai dengan <span class="text-[#e2ebdc]">langkah yang tepat.</span></h1>
            <p class="mt-6 max-w-md text-lg leading-8 text-white/70">Satu tempat untuk mencatat kehadiran, melihat riwayat, dan menjalani hari kerja dengan lebih mudah.</p>
        </div>
        <p class="relative text-sm text-white/50">© {{ date('Y') }} HadirKantor · Portal Absensi Karyawan</p>
    </section>
    <section class="flex items-center justify-center px-6 py-12 sm:px-10">
        <div class="w-full max-w-md">
            <a href="/" class="mb-8 flex items-center gap-3 font-display text-lg font-extrabold tracking-tight lg:hidden"><span class="grid h-9 w-9 place-items-center rounded-xl bg-[#567765] text-white shadow-sm">H</span> HadirKantor</a>
            <div class="rounded-[2rem] border border-white/80 bg-white/75 p-7 shadow-[0_24px_70px_rgba(48,68,54,0.09)] backdrop-blur-xl sm:p-9">
            <p class="text-sm font-semibold uppercase tracking-[.16em] text-emerald-800">Selamat datang kembali</p>
            <h2 class="mt-3 font-display text-3xl font-bold tracking-tight text-slate-900">Masuk ke akun Anda</h2>
            <p class="mt-2 text-slate-500">Masuk menggunakan username dan kata sandi Anda.</p>
            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <label class="block text-sm font-semibold text-slate-700">Username
                    <span class="group relative mt-2 block"><x-icon name="users" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-emerald-700"/><input type="text" id="login-username" name="username" value="{{ old('username') }}" autocomplete="username" maxlength="50" required autofocus class="w-full border border-[#e1e7df] bg-[#fbfcf9] py-3.5 pl-12 pr-4 font-normal outline-none placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10" placeholder="Masukkan username"></span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">Kata sandi
                    <span class="group relative mt-2 block"><x-icon name="shield" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-emerald-700"/><input id="login-password" type="password" name="password" autocomplete="current-password" required class="w-full border border-[#e1e7df] bg-[#fbfcf9] py-3.5 pl-12 pr-12 font-normal outline-none placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10" placeholder="Masukkan kata sandi"><button id="toggle-password-visibility" type="button" aria-label="Tampilkan kata sandi" aria-pressed="false" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600/30"><svg data-eye-show viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M2.5 12s3.3-6 9.5-6 9.5 6 9.5 6-3.3 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><svg data-eye-hide viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="hidden h-5 w-5" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.5 10.5 0 0 1 12 5c6.2 0 9.5 7 9.5 7a13.6 13.6 0 0 1-3.2 3.8M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.3 7 9.5 7c1.1 0 2.1-.2 3-.5"/></svg></button></span>
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-500"><input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-800 focus:ring-emerald-700"> Ingat saya</label>
                <button class="group flex w-full items-center justify-center gap-2 rounded-xl bg-[#567765] px-5 py-3.5 font-semibold text-white shadow-lg shadow-emerald-950/10 transition hover:bg-emerald-900 hover:shadow-xl">Masuk ke dashboard <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1"/></button>
            </form>
            <p class="mt-8 text-center text-xs leading-5 text-slate-400">Dengan masuk, Anda menyetujui pencatatan waktu dan lokasi kehadiran sesuai kebijakan perusahaan.</p>
            </div>
        </div>
    </section>
</main>
<script>
    (() => {
        const passwordInput = document.getElementById('login-password');
        const visibilityButton = document.getElementById('toggle-password-visibility');
        const showIcon = visibilityButton.querySelector('[data-eye-show]');
        const hideIcon = visibilityButton.querySelector('[data-eye-hide]');

        visibilityButton.addEventListener('click', () => {
            const shouldShow = passwordInput.type === 'password';
            passwordInput.type = shouldShow ? 'text' : 'password';
            visibilityButton.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            visibilityButton.setAttribute('aria-pressed', String(shouldShow));
            showIcon.classList.toggle('hidden', shouldShow);
            hideIcon.classList.toggle('hidden', !shouldShow);
            passwordInput.focus();
        });
    })();

    (() => {
        let previouslyHadPendingRequest = false;
        let trackedUsername = '';
        let debounceTimer;
        let requestPending = false;
        const usernameInput = document.getElementById('login-username');
        const notification = document.getElementById('leave-request-notification');
        const dismissButton = document.getElementById('dismiss-leave-notification');

        dismissButton.addEventListener('click', () => {
            notification.classList.add('is-leaving');
            window.setTimeout(() => { notification.hidden = true; }, 260);
        });

        const showNotification = () => {
            const message = 'Ada pengajuan izin yang menunggu persetujuan untuk akun dan departemen Anda.';
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(message, 'warning', { focus: true });
                return;
            }

            notification.hidden = false;
            notification.classList.remove('is-leaving');
            notification.tabIndex = -1;
            notification.focus({ preventScroll: true });
        };

        const checkForNewRequests = async () => {
            const username = usernameInput.value.trim().toLowerCase();
            if (username.length < 2 || requestPending) return;
            if (username !== trackedUsername) {
                trackedUsername = username;
                previouslyHadPendingRequest = false;
            }

            requestPending = true;
            try {
                const url = new URL(@json(route('login.leave-notifications', absolute: false)), window.location.origin);
                url.searchParams.set('username', username);
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                if (!response.ok) return;

                const data = await response.json();
                if (username !== usernameInput.value.trim().toLowerCase()) return;
                const hasPendingRequest = data.has_pending === true;
                if (hasPendingRequest && !previouslyHadPendingRequest) {
                    showNotification();
                }
                previouslyHadPendingRequest = hasPendingRequest;
            } catch {
                // Retry at the next polling interval if the connection is temporarily unavailable.
            } finally {
                requestPending = false;
            }
        };

        usernameInput.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(checkForNewRequests, 350);
        });
        checkForNewRequests();
        window.setInterval(checkForNewRequests, 10000);
    })();
</script>
@endsection
