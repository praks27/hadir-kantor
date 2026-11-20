@extends('layouts.app')

@section('body')
<div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]">
    <x-app-sidebar />

    <main class="mx-auto w-full max-w-[1500px] px-5 py-6 sm:px-8 lg:px-10 lg:py-9">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
            <div><a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-display text-lg font-extrabold tracking-tight lg:hidden"><span class="grid h-9 w-9 place-items-center rounded-xl bg-[#567765] text-white">H</span> HadirKantor</a><p class="hidden text-sm text-slate-500 lg:block">Administrasi karyawan</p><h1 class="mt-1 font-display text-xl font-bold tracking-tight text-slate-900">@yield('page-title')</h1></div>
            <div class="flex items-center gap-3"><span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold uppercase text-emerald-800">{{ auth()->user()->role }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-500 transition hover:bg-white hover:text-slate-900">Keluar</button></form></div>
        </header>
        <x-app-mobile-menu />
        @yield('content')
    </main>
</div>
@endsection
