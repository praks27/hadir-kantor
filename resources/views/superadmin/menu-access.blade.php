@extends('superadmin.layout')

@section('page-title', 'Hak akses menu')

@section('content')
<section class="mt-7 grid min-w-0 gap-6 xl:grid-cols-2">
    <article class="min-w-0 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-8">
        <p class="text-sm font-medium text-emerald-800">Pengaturan umum</p>
        <h2 class="mt-1 font-display text-xl font-bold text-slate-900">Default berdasarkan role</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Aturan ini berlaku untuk semua pengguna pada role yang dipilih, kecuali pengguna yang memiliki aturan khusus. Laporan dipisah menjadi ringkasan, pengajuan izin, dan absensi.</p>

        <form method="GET" action="{{ route('superadmin.menu-access') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
            <label class="flex-1 text-sm font-semibold text-slate-700">Role
                <select name="role" class="mt-2 w-full rounded-xl border border-slate-200 bg-[#f9fbfe] px-4 py-3 text-sm font-normal" onchange="this.form.submit()">
                    @foreach ($roles as $key => $label)
                        <option value="{{ $key }}" @selected($selectedRole === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <noscript><button class="self-end rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold">Pilih role</button></noscript>
        </form>

        <form method="POST" action="{{ route('superadmin.menu-access.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="scope" value="role">
            <input type="hidden" name="role" value="{{ $selectedRole }}">
            @foreach ($menus as $key => $label)
                <label class="flex min-w-0 cursor-pointer items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-emerald-300 hover:bg-emerald-50/30">
                    <input type="checkbox" name="permissions[{{ $key }}]" value="1" @checked($rolePermissions[$key] ?? false) class="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 text-emerald-800 focus:ring-emerald-700">
                    <span class="min-w-0 flex-1"><span class="block break-words text-sm font-bold text-slate-800">{{ $label }}</span><span class="mt-1.5 block break-words text-xs leading-5 text-slate-600">{{ $menuDescriptions[$key] }} Default untuk role {{ strtolower($roles[$selectedRole]) }}.</span></span>
                </label>
            @endforeach
            <button class="inline-flex w-full items-center justify-center rounded-xl bg-[#5b84a8] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900 sm:w-auto">Simpan pengaturan umum</button>
        </form>
    </article>

    <article class="min-w-0 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-8">
        <p class="text-sm font-medium text-emerald-800">Pengaturan khusus</p>
        <h2 class="mt-1 font-display text-xl font-bold text-slate-900">Atur per karyawan</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Aturan khusus karyawan mengalahkan pengaturan role. Pilih Ikuti pengaturan umum untuk menu yang ingin mengikuti role, atau gunakan tombol di bawah untuk menghapus semua override akun.</p>

        <form method="GET" action="{{ route('superadmin.menu-access') }}" class="mt-5 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-end">
            <input type="hidden" name="role" value="{{ $selectedRole }}">
            <label class="flex-1 text-sm font-semibold text-slate-700">Karyawan
                <select name="user" class="mt-2 w-full min-w-0 rounded-xl border border-slate-200 bg-[#f9fbfe] px-4 py-3 text-sm font-normal">
                    <option value="">Pilih karyawan</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($selectedUser?->id === $user->id)>{{ $user->name }} · {{ $user->username }} ({{ $roles[$user->role] ?? ucfirst($user->role) }})</option>
                    @endforeach
                </select>
            </label>
            <button class="w-full shrink-0 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">Pilih</button>
        </form>

        @if ($selectedUser)
            <form method="POST" action="{{ route('superadmin.menu-access.update') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="scope" value="user">
                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                @foreach ($menus as $key => $label)
                    @php($override = $userOverrides[$key] ?? null)
                    <label class="block min-w-0 rounded-2xl border border-slate-200 bg-white p-5 text-sm font-bold text-slate-800">{{ $label }}<span class="mt-1.5 block break-words text-xs font-normal leading-5 text-slate-600">{{ $menuDescriptions[$key] }}</span>
                        <select name="permissions[{{ $key }}]" class="mt-3 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-normal">
                            <option value="" @selected($override === null)>Ikuti pengaturan umum</option>
                            <option value="1" @selected($override === true || $override === 1)>Izinkan</option>
                            <option value="0" @selected($override === false || $override === 0)>Blokir</option>
                        </select>
                    </label>
                @endforeach
                <div class="flex flex-col gap-2 sm:flex-row">
                    <button class="inline-flex w-full items-center justify-center rounded-xl bg-[#5b84a8] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900 sm:w-auto">Simpan pengaturan khusus</button>
                    <button name="reset_to_role" value="1" formnovalidate class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">Ikuti semua pengaturan role</button>
                </div>
            </form>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center text-sm text-slate-500">Pilih karyawan untuk mengatur hak akses khusus.</div>
        @endif
    </article>
</section>
<p class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900">Menu yang diizinkan dapat dibuka melalui navigasi maupun URL langsung. Akses laporan mengikuti pilihan di atas untuk semua role. Menu persetujuan tetap memerlukan jabatan approver dan tahap persetujuan yang sesuai.</p>
@endsection
