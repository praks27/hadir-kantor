@extends('superadmin.layout')

@section('title', 'Karyawan & Role')
@section('page-title', 'Karyawan & role pengguna')

@section('content')
<div class="mt-7 space-y-6">
    <section class="employee-admin-hero rounded-3xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/70 p-5 shadow-sm sm:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0"><p class="text-sm font-semibold text-emerald-800">Administrasi pengguna</p><h2 class="mt-1 font-display text-2xl font-bold tracking-tight text-slate-900">Karyawan dan role</h2><p class="mt-1 text-sm leading-6 text-slate-600">Kelola akun, penempatan, dan hak akses pengguna.</p></div>
            <a href="#form-tambah-akun" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#5b84a8] px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-900"><x-icon name="users" class="h-4 w-4"/> Tambah akun</a>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-white bg-white/90 p-4"><p class="text-xs font-medium text-slate-500">{{ request()->filled('q') ? 'Hasil pencarian' : 'Total pengguna' }}</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($users->total()) }}</p></div>
            <div class="rounded-2xl border border-white bg-white/90 p-4"><p class="text-xs font-medium text-slate-500">Pengaturan akun baru</p><p class="mt-1 text-sm font-bold text-slate-900">Aktif · jatah cuti awal 12 hari</p></div>
        </div>
    </section>

    <section id="form-tambah-akun" class="scroll-mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-5"><p class="text-sm font-semibold text-emerald-800">Tambah akun</p><h2 class="mt-0.5 font-display text-xl font-bold text-slate-900">Buat pengguna baru</h2><p class="mt-1 text-sm text-slate-500">Nama, role, dan kata sandi wajib diisi. Username akan dibuat otomatis.</p></div>
        <form method="POST" action="{{ route('superadmin.users.store') }}" class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @csrf
            <label for="new-user-name" class="min-w-0 text-sm font-semibold text-slate-700">Nama lengkap <span class="text-rose-600">*</span>
                <input id="new-user-name" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3.5 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10">
                @error('name')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-username-preview" class="min-w-0 text-sm font-semibold text-slate-700">Username <span class="text-xs font-normal text-slate-400">otomatis</span>
                <input id="new-user-username-preview" value="{{ old('username') }}" disabled aria-describedby="new-user-username-help" class="mt-1.5 w-full min-w-0 cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-3 text-sm font-mono font-normal text-slate-600">
                <input id="new-user-username" type="hidden" name="username" value="{{ old('username') }}">
                <span id="new-user-username-help" class="mt-1 block text-xs font-normal text-slate-500">Dibuat dari nama depan dan angka unik.</span>
            </label>
            <label for="new-user-nik" class="min-w-0 text-sm font-semibold text-slate-700">NIK <span class="text-xs font-normal text-slate-400">opsional</span>
                <input id="new-user-nik" name="nik" value="{{ old('nik') }}" inputmode="numeric" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3.5 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10">
                @error('nik')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-email" class="min-w-0 text-sm font-semibold text-slate-700">Email <span class="text-xs font-normal text-slate-400">opsional</span>
                <input id="new-user-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="nama@contoh.com" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3.5 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10">
                @error('email')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-position" class="min-w-0 text-sm font-semibold text-slate-700">Jabatan <span class="text-xs font-normal text-slate-400">opsional</span>
                <input id="new-user-position" name="position" value="{{ old('position') }}" placeholder="Contoh: Staf administrasi" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3.5 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10">
                @error('position')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-password" class="min-w-0 text-sm font-semibold text-slate-700">Kata sandi awal <span class="text-rose-600">*</span>
                <input id="new-user-password" type="password" name="password" required autocomplete="new-password" aria-describedby="new-user-password-help" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3.5 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10">
                <span id="new-user-password-help" class="mt-1 block text-xs font-normal text-slate-500">Wajib diisi.</span>
                @error('password')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-department" class="min-w-0 text-sm font-semibold text-slate-700">Departemen <span class="text-xs font-normal text-slate-400">opsional</span>
                <select id="new-user-department" name="department_id" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal text-slate-700"><option value="">Pilih departemen</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select>
                @error('department_id')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-approval-level" class="min-w-0 text-sm font-semibold text-slate-700">Tingkat approval <span class="text-xs font-normal text-slate-400">opsional</span>
                <select id="new-user-approval-level" name="approval_level" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal text-slate-700"><option value="">Tidak ada</option>@foreach ($approvalLevels as $level => $label)<option value="{{ $level }}" @selected(old('approval_level') === $level)>{{ $label }}</option>@endforeach</select>
                @error('approval_level')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-office" class="min-w-0 text-sm font-semibold text-slate-700">Lokasi kerja <span class="text-xs font-normal text-slate-400">untuk approver SPBU/LPG</span>
                <select id="new-user-office" name="office_id" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal text-slate-700"><option value="">Pilih lokasi kerja</option>@foreach ($offices as $workplace)<option value="{{ $workplace->id }}" @selected((string) old('office_id') === (string) $workplace->id)>{{ $workplace->name }}</option>@endforeach</select>
                @error('office_id')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-role" class="min-w-0 text-sm font-semibold text-slate-700">Role pengguna <span class="text-rose-600">*</span>
                <select id="new-user-role" name="role" required class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-normal text-slate-700">@foreach ($roles as $key => $label)<option value="{{ $key }}" @selected(old('role', 'karyawan') === $key)>{{ $label }}</option>@endforeach</select>
                @error('role')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label for="new-user-leave-entitlement" class="min-w-0 text-sm font-semibold text-slate-700">Jatah cuti {{ $leaveEntitlementYear }} (hari)
                <input id="new-user-leave-entitlement" type="number" name="annual_leave_entitlement" value="{{ old('annual_leave_entitlement', 12) }}" min="0" max="366" required class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3.5 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10">
                @error('annual_leave_entitlement')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <label class="flex min-w-0 items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-slate-700 sm:mt-5"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1')) class="h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-800 focus:ring-emerald-700"><span><span class="block font-semibold">Akun aktif</span><span class="text-xs text-slate-500">Dapat langsung digunakan.</span></span></label>
            <div class="flex flex-col justify-end gap-3 sm:col-span-2 xl:col-span-3 sm:flex-row sm:items-center sm:justify-between"><p class="text-xs text-slate-500"><span class="text-rose-600">*</span> Wajib diisi. Approval memerlukan departemen; approver SPBU/LPG juga memerlukan lokasi yang sesuai.</p><button class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-[#5b84a8] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900 sm:w-auto"><x-icon name="users" class="h-4 w-4"/> Buat akun</button></div>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="min-w-0"><p class="text-sm font-semibold text-sky-800">Impor dan ekspor</p><h2 class="mt-0.5 font-display text-xl font-bold text-slate-900">Kelola banyak akun lewat Excel</h2><p class="mt-1 text-sm leading-6 text-slate-500">Template dilengkapi dropdown referensi. Untuk memperbarui akun, gunakan file ekspor dan jangan ubah kolom ID.</p></div>
            <div class="grid w-full gap-2 sm:grid-cols-2 md:w-auto">
                <a href="{{ route('superadmin.users.template') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"><x-icon name="download" class="h-4 w-4"/> Template karyawan</a>
                <a href="{{ route('superadmin.users.excel') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"><x-icon name="download" class="h-4 w-4"/> Ekspor data</a>
            </div>
        </div>
        <form method="POST" action="{{ route('superadmin.users.import') }}" enctype="multipart/form-data" class="mt-5 rounded-2xl bg-slate-50 p-4 sm:p-5">
            @csrf
            <label for="employee-import-file" class="block text-sm font-semibold text-slate-800">Pilih file Excel (.xlsx)</label>
            <div class="mt-2 flex min-w-0 flex-col gap-3 md:flex-row md:items-center">
                <input id="employee-import-file" type="file" name="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required aria-describedby="employee-import-help" class="employee-import-file block min-w-0 flex-1 rounded-xl border border-slate-300 bg-white text-sm text-slate-600 file:mr-3 file:border-0 file:bg-emerald-50 file:px-3 file:py-3 file:text-sm file:font-semibold file:text-emerald-900 hover:file:bg-emerald-100">
                <button class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-[#5b84a8] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900 md:w-auto"><x-icon name="upload" class="h-4 w-4"/> Impor karyawan</button>
            </div>
            <p id="employee-import-help" class="mt-2 text-xs leading-5 text-slate-500">Maksimal 5.000 baris dan 10 MB. Impor dibatalkan seluruhnya jika ada baris yang tidak valid.</p>
            @error('file')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </form>
        @if ($errors->has('import'))
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900" role="alert">
                <p class="font-bold">Impor belum dilakukan. Perbaiki data berikut lalu unggah ulang:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->get('import') as $error)<li>{{ $error }}</li>@endforeach</ul>
                @if (session('import_error_count', 0) > 30)<p class="mt-2 text-xs">Menampilkan 30 dari {{ session('import_error_count') }} kesalahan.</p>@endif
            </div>
        @endif
    </section>

    <section class="min-w-0 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div><p class="text-sm font-semibold text-slate-500">Direktori pengguna</p><h2 class="mt-0.5 font-display text-xl font-bold text-slate-900">Data karyawan</h2><p class="mt-1 text-sm text-slate-500">Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ number_format($users->total()) }} akun.</p></div>
            <form id="user-search-form" method="GET" role="search" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row lg:max-w-lg">
                <label for="user-search" class="sr-only">Cari pengguna</label>
                <div class="relative min-w-0 flex-1"><x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/><input id="user-search" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, username, atau NIK" autocomplete="off" class="w-full min-w-0 rounded-xl border border-slate-300 py-3 pl-9 pr-3 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10"></div>
                @if (request()->filled('q'))<a href="{{ route('superadmin.users') }}" class="rounded-xl px-4 py-3 text-center text-sm font-semibold text-slate-500 hover:bg-slate-100">Reset</a>@endif
            </form>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] border-collapse text-left text-sm">
                    <caption class="sr-only">Daftar karyawan dan administrator</caption>
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr><th scope="col" class="px-4 py-3 font-bold">Nama</th><th scope="col" class="px-4 py-3 font-bold">Username</th><th scope="col" class="px-4 py-3 font-bold">Role</th><th scope="col" class="px-4 py-3 font-bold">Departemen</th><th scope="col" class="px-4 py-3 font-bold">Status</th><th scope="col" class="px-4 py-3 text-right font-bold">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            @php($detailDialogId = 'user-detail-'.$user->id)
                            @php($editDialogId = 'user-edit-'.$user->id)
                            <tr class="user-list-row align-middle transition hover:bg-slate-50/70">
                                <td class="max-w-[280px] px-4 py-4"><div class="flex min-w-0 items-center gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 font-bold text-emerald-900">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span><span class="min-w-0"><span class="block truncate font-semibold text-slate-900">{{ $user->name }}</span><span class="mt-0.5 block truncate text-xs text-slate-500">{{ $user->nik ?: 'NIK belum diisi' }}</span></span></div></td>
                                <td class="px-4 py-4 font-mono text-xs text-slate-600">{{ $user->username }}</td>
                                <td class="px-4 py-4"><span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $roles[$user->role] ?? $user->role }}</span></td>
                                <td class="max-w-[200px] px-4 py-4 text-slate-600"><span class="block truncate">{{ $user->department?->name ?? '—' }}</span></td>
                                <td class="px-4 py-4"><span class="inline-flex rounded-full px-3 py-1.5 text-xs font-bold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>@if (auth()->id() === $user->id)<span class="ml-1 text-xs text-sky-700">Anda</span>@endif</td>
                                <td class="px-4 py-4"><div class="flex justify-end gap-1.5">
                                    <button type="button" data-open-dialog="{{ $detailDialogId }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"><x-icon name="file" class="h-3.5 w-3.5"/> Lihat</button>
                                    <button type="button" data-open-dialog="{{ $editDialogId }}" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"><x-icon name="check" class="h-3.5 w-3.5"/> Edit</button>
                                    @if (auth()->id() !== $user->id)<button type="submit" form="delete-user-{{ $user->id }}" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50"><x-icon name="trash" class="h-3.5 w-3.5"/> Hapus</button>@endif
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center"><p class="font-semibold text-slate-800">Tidak ada data karyawan</p><p class="mt-1 text-sm text-slate-500">Ubah pencarian atau tambahkan akun baru.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <p class="mt-2 text-xs text-slate-500 sm:hidden">Geser tabel ke samping untuk melihat seluruh kolom.</p>
        @if ($users->hasPages())<div class="mt-5 border-t border-slate-100 pt-5">{{ $users->links() }}</div>@endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="max-w-2xl"><p class="text-sm font-semibold text-emerald-800">Pengaturan akses</p><h2 class="mt-0.5 font-display text-xl font-bold text-slate-900">Role pengguna</h2><p class="mt-1 text-sm leading-6 text-slate-500">Role tambahan langsung tersedia pada formulir akun dan dropdown template Excel. Superadmin adalah role sistem.</p></div>
        <form method="POST" action="{{ route('superadmin.employee-roles.store') }}" class="mt-5 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <label for="employee-role-name" class="min-w-0 flex-1 text-sm font-semibold text-slate-700">Nama role
                <input id="employee-role-name" name="name" value="{{ old('name') }}" placeholder="Contoh: Operator SPBU" maxlength="100" required class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3.5 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10">
                @error('name')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
            </label>
            <button class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-[#5b84a8] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900 sm:w-auto"><x-icon name="users" class="h-4 w-4"/> Tambah role</button>
        </form>
        <div class="mt-5 flex flex-wrap gap-2">@foreach ($roles as $key => $label)<span class="max-w-full break-words rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900">{{ $label }} <span class="font-normal text-emerald-700">({{ $key }})</span></span>@endforeach</div>
    </section>
</div>

@foreach ($users as $user)
    <dialog id="user-detail-{{ $user->id }}" class="employee-user-dialog m-auto max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded-3xl p-0 shadow-2xl backdrop:bg-slate-950/50">
        <div class="border-b border-slate-100 p-5 sm:p-6"><div class="flex items-start justify-between gap-4"><div class="min-w-0"><p class="text-sm font-semibold text-emerald-800">Detail pengguna</p><h2 class="mt-1 break-words font-display text-xl font-bold text-slate-900">{{ $user->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ '@'.$user->username }}</p></div><button type="button" data-close-dialog class="shrink-0 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Tutup</button></div></div>
        <dl class="grid gap-x-6 gap-y-5 p-5 sm:grid-cols-2 sm:p-6">
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">NIK</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $user->nik ?: 'Belum diisi' }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt><dd class="mt-1 break-all text-sm font-medium text-slate-900">{{ $user->email ?: 'Belum diisi' }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jabatan</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $user->position ?: 'Belum diisi' }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departemen</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $user->department?->name ?? 'Belum dipilih' }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tingkat approval</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $approvalLevels[$user->approval_level] ?? 'Tidak ada' }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lokasi kerja</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $user->office?->name ?? 'Tidak ada' }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Role</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $roles[$user->role] ?? $user->role }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt><dd class="mt-1 text-sm font-medium {{ $user->is_active ? 'text-emerald-800' : 'text-slate-600' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dibuat</dt><dd class="mt-1 text-sm font-medium text-slate-900">{{ $user->created_at?->format('d M Y, H:i') ?? '—' }}</dd></div>
        </dl>
        <div class="border-t border-slate-100 p-5 text-right sm:p-6"><button type="button" data-close-dialog class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Tutup detail</button></div>
    </dialog>

    <dialog id="user-edit-{{ $user->id }}" class="employee-user-dialog m-auto max-h-[92vh] w-[calc(100%-1rem)] max-w-4xl overflow-y-auto rounded-3xl p-0 shadow-2xl backdrop:bg-slate-950/50 sm:w-[calc(100%-2rem)]">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white/95 p-5 backdrop-blur sm:p-6"><div class="min-w-0"><p class="text-sm font-semibold text-emerald-800">Edit pengguna</p><h2 class="mt-1 break-words font-display text-xl font-bold text-slate-900">{{ $user->name }}</h2></div><button type="button" data-close-dialog class="shrink-0 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Tutup</button></div>
        <form method="POST" action="{{ route('superadmin.users.update', $user) }}" class="grid min-w-0 gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
            @csrf @method('PUT')
            <label class="min-w-0 text-sm font-semibold text-slate-700">Nama lengkap<input name="name" value="{{ $user->name }}" required class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Username<input value="{{ $user->username }}" disabled class="mt-1.5 w-full min-w-0 cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3 py-3 text-sm font-mono font-normal text-slate-500"></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Email<input type="email" name="email" value="{{ $user->email }}" placeholder="nama@contoh.com" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">NIK<input name="nik" value="{{ $user->nik }}" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Jabatan<input name="position" value="{{ $user->position }}" placeholder="Jabatan" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Departemen<select name="department_id" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-normal"><option value="">Tanpa departemen</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected($user->department_id === $department->id)>{{ $department->name }}</option>@endforeach</select></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Tingkat approval<select name="approval_level" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-normal"><option value="">Tidak ada</option>@foreach ($approvalLevels as $level => $label)<option value="{{ $level }}" @selected($user->approval_level === $level)>{{ $label }}</option>@endforeach</select></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Lokasi kerja<select name="office_id" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-normal"><option value="">Tanpa lokasi khusus</option>@foreach ($offices as $workplace)<option value="{{ $workplace->id }}" @selected($user->office_id === $workplace->id)>{{ $workplace->name }}</option>@endforeach</select></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Role pengguna
                @if ($user->isSuperAdmin())<input type="hidden" name="role" value="superadmin"><span class="mt-1.5 block rounded-xl border border-slate-200 bg-slate-100 px-3 py-3 text-sm font-normal text-slate-500">Superadmin (role sistem)</span>@else<select name="role" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-normal">@foreach ($roles as $key => $label)<option value="{{ $key }}" @selected($user->role === $key)>{{ $label }}</option>@endforeach</select>@endif
            </label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Jatah cuti {{ $leaveEntitlementYear }} (hari)<input type="number" name="annual_leave_entitlement" value="{{ $leaveEntitlements[$user->id] ?? 12 }}" min="0" max="366" required class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="min-w-0 text-sm font-semibold text-slate-700">Kata sandi baru <span class="text-xs font-normal text-slate-400">opsional</span><input type="password" name="password" autocomplete="new-password" class="mt-1.5 w-full min-w-0 rounded-xl border border-slate-300 px-3 py-3 text-sm font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="flex min-w-0 items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-slate-700"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-800 focus:ring-emerald-700"> Akun aktif</label>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:col-span-2 sm:flex-row sm:justify-end lg:col-span-3"><button type="button" data-close-dialog class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button><button class="rounded-xl bg-emerald-800 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-900">Simpan perubahan</button></div>
        </form>
    </dialog>

    @if (auth()->id() !== $user->id)
        <form id="delete-user-{{ $user->id }}" method="POST" action="{{ route('superadmin.users.destroy', $user) }}" data-toast-confirm="Hapus akun {{ $user->name }}?" data-toast-confirm-action="Hapus akun">@csrf @method('DELETE')</form>
    @endif
@endforeach

<script>
    (() => {
        const nameInput = document.getElementById('new-user-name');
        const usernameInput = document.getElementById('new-user-username');
        const usernamePreview = document.getElementById('new-user-username-preview');

        if (nameInput && usernameInput && usernamePreview) {
            let previousFirstName = '';
            const updateUsername = () => {
                const firstName = nameInput.value.trim().split(/\s+/)[0] || '';
                if (firstName === previousFirstName) return;
                previousFirstName = firstName;
                const base = firstName.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 45) || 'karyawan';
                const suffix = crypto.getRandomValues(new Uint32Array(1))[0] % 90 + 10;
                usernameInput.value = firstName ? `${base}${suffix}` : '';
                usernamePreview.value = usernameInput.value;
            };
            nameInput.addEventListener('input', updateUsername);
            updateUsername();
        }

        document.querySelectorAll('[data-open-dialog]').forEach((button) => {
            button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal());
        });
        document.querySelectorAll('[data-close-dialog]').forEach((button) => {
            button.addEventListener('click', () => button.closest('dialog')?.close());
        });
        document.querySelectorAll('dialog').forEach((dialog) => {
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });

        const searchForm = document.getElementById('user-search-form');
        const searchInput = document.getElementById('user-search');
        if (searchForm && searchInput) {
            let searchTimer;
            let isComposing = false;
            let submittedQuery = searchInput.value.trim();

            const submitSearch = () => {
                const query = searchInput.value.trim();
                if (query === submittedQuery) return;
                searchInput.value = query;
                submittedQuery = query;
                searchForm.requestSubmit();
            };

            searchInput.addEventListener('compositionstart', () => { isComposing = true; });
            searchInput.addEventListener('compositionend', () => {
                isComposing = false;
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(submitSearch, 350);
            });
            searchInput.addEventListener('input', () => {
                if (isComposing) return;
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(submitSearch, 350);
            });
        }
    })();
</script>
@endsection
