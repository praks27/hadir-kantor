@extends('superadmin.layout')

@section('title', 'Departemen')
@section('page-title', 'Pengelolaan departemen')

@section('content')
<section class="mt-7 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
    <div class="max-w-2xl">
        <p class="text-sm font-medium text-emerald-800">Struktur organisasi</p>
        <h2 class="mt-1 font-display text-xl font-bold text-slate-900">Tambah departemen</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Departemen yang ditambahkan akan langsung tersedia saat mengatur akun pengguna dan alur persetujuan kantor.</p>
    </div>
    <form method="POST" action="{{ route('superadmin.departments.store') }}" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-start">
        @csrf
        <label class="block flex-1 text-sm font-semibold text-slate-700">Nama departemen
            <input name="name" value="{{ old('name') }}" maxlength="100" required placeholder="Contoh: Keuangan" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700">
            @error('name')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#567765] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-900 sm:mt-7"><x-icon name="check" class="h-4 w-4"/> Tambah departemen</button>
    </form>
</section>

<section class="mt-7 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div><p class="text-sm font-medium text-slate-500">Data departemen</p><h2 class="mt-1 font-display text-xl font-bold text-slate-900">Daftar departemen</h2></div>
        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800">{{ $departments->count() }} departemen</span>
    </div>
    <div class="mt-5 space-y-3">
        @forelse ($departments as $department)
            <article class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <form method="POST" action="{{ route('superadmin.departments.update', $department) }}" class="flex flex-col gap-2 sm:flex-row">
                        @csrf @method('PUT')
                        <label class="sr-only" for="department-{{ $department->id }}">Nama departemen</label>
                        <input id="department-{{ $department->id }}" name="name" value="{{ $department->name }}" maxlength="100" required class="min-w-0 flex-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-emerald-700">
                        <button class="rounded-xl border border-emerald-200 px-4 py-2.5 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Simpan nama</button>
                    </form>
                    <p class="mt-2 text-xs text-slate-500">{{ $department->users_count }} akun · {{ $department->leave_approval_workflows_count }} alur khusus</p>
                </div>
                <form method="POST" action="{{ route('superadmin.departments.destroy', $department) }}" data-toast-confirm="Hapus departemen {{ $department->name }}?" data-toast-confirm-action="Hapus departemen">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50"><x-icon name="trash" class="h-4 w-4"/> Hapus</button>
                </form>
            </article>
        @empty
            <p class="rounded-2xl bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">Belum ada departemen.</p>
        @endforelse
    </div>
</section>
@endsection
