@extends('layouts.app')

@section('title', 'Lupa kata sandi')

@section('body')
<main class="flex min-h-screen items-center justify-center px-5 py-12 sm:px-8">
    <section class="w-full max-w-md rounded-[2rem] border border-white/80 bg-white/85 p-7 shadow-[0_24px_70px_rgba(48,68,54,0.09)] sm:p-9">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-800">← Kembali ke halaman masuk</a>
        <p class="mt-8 text-sm font-semibold uppercase tracking-[.16em] text-emerald-800">Pemulihan akun</p>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-slate-900">Lupa kata sandi?</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan email yang terdaftar pada akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>

        @if (session('status'))
            <p role="status" class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif
        @error('email')
            <p role="alert" class="mt-5 rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
            @csrf
            <label class="block text-sm font-semibold text-slate-700">Email
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="mt-2 w-full rounded-xl border border-slate-300 bg-[#f9fbfe] px-4 py-3.5 font-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10" placeholder="nama@contoh.com">
            </label>
            <button class="w-full rounded-xl bg-[#5b84a8] px-5 py-3.5 font-semibold text-white transition hover:bg-emerald-900">Kirim tautan pemulihan</button>
        </form>
        <p class="mt-6 text-xs leading-5 text-slate-400">Email harus sudah terdaftar pada akun Anda. Jika belum memiliki email di akun, hubungi administrator untuk memperbaruinya.</p>
    </section>
</main>
@endsection
