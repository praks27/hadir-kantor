@extends('layouts.app')

@section('title', 'Atur ulang kata sandi')

@section('body')
<main class="flex min-h-screen items-center justify-center px-5 py-12 sm:px-8">
    <section class="w-full max-w-md rounded-[2rem] border border-white/80 bg-white/85 p-7 shadow-[0_24px_70px_rgba(48,68,54,0.09)] sm:p-9">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-800">← Kembali ke halaman masuk</a>
        <p class="mt-8 text-sm font-semibold uppercase tracking-[.16em] text-emerald-800">Pemulihan akun</p>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-slate-900">Buat kata sandi baru</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan kata sandi baru untuk akun Anda.</p>

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-xl bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label class="block text-sm font-semibold text-slate-700">Email
                <input type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required class="mt-2 w-full rounded-xl border border-slate-300 bg-[#fbfcf9] px-4 py-3.5 font-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Kata sandi baru
                <input type="password" name="password" autocomplete="new-password" required class="mt-2 w-full rounded-xl border border-slate-300 bg-[#fbfcf9] px-4 py-3.5 font-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Ulangi kata sandi baru
                <input type="password" name="password_confirmation" autocomplete="new-password" required class="mt-2 w-full rounded-xl border border-slate-300 bg-[#fbfcf9] px-4 py-3.5 font-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10">
            </label>
            <button class="w-full rounded-xl bg-[#567765] px-5 py-3.5 font-semibold text-white transition hover:bg-emerald-900">Simpan kata sandi baru</button>
        </form>
    </section>
</main>
@endsection
