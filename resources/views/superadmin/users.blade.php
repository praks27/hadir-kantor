@extends('superadmin.layout')

@section('title', 'Karyawan & Role')
@section('page-title', 'Karyawan & role pengguna')

@section('content')
<section class="hover-lift mt-7 rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-sm sm:p-8">
    <div class="max-w-2xl"><p class="text-sm font-medium text-emerald-800">Tambah akun</p><h2 class="mt-1 font-display text-xl font-bold">Daftarkan karyawan atau administrator</h2><p class="mt-2 text-sm leading-6 text-slate-500">Pilih role yang sesuai. Akun superadmin yang sudah ada dikelola terpisah dan tidak dapat dibuat dari formulir ini. Jatah cuti tahunan kembali ke 12 hari setiap tahun; perubahan superadmin hanya berlaku untuk tahun yang dipilih.</p></div>
    <form method="POST" action="{{ route('superadmin.users.store') }}" class="mt-6 grid gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-2 xl:grid-cols-4">
        @csrf
        <label for="new-user-name" class="block text-xs font-semibold text-slate-600">Nama lengkap<input id="new-user-name" name="name" value="{{ old('name') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700"></label>
        <label for="new-user-username-preview" class="block text-xs font-semibold text-slate-600">Username otomatis<input id="new-user-username-preview" value="{{ old('username') }}" disabled aria-describedby="new-user-username-help" class="mt-1.5 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-mono font-normal text-slate-600"><input id="new-user-username" type="hidden" name="username" value="{{ old('username') }}"><span id="new-user-username-help" class="mt-1 block font-normal text-slate-400">Dibuat dari nama depan dan angka acak.</span></label>
        <label for="new-user-email" class="block text-xs font-semibold text-slate-600">Email <span class="font-normal text-slate-400">(opsional)</span><input id="new-user-email" type="email" name="email" value="{{ old('email') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700"></label>
        <label for="new-user-nik" class="block text-xs font-semibold text-slate-600">NIK <span class="font-normal text-slate-400">(opsional)</span><input id="new-user-nik" name="nik" value="{{ old('nik') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700"></label>
        <label for="new-user-position" class="block text-xs font-semibold text-slate-600">Jabatan<input id="new-user-position" name="position" value="{{ old('position') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700"></label>
        <label for="new-user-leave-entitlement" class="block text-xs font-semibold text-slate-600">Hak cuti {{ $leaveEntitlementYear }} (hari)<input id="new-user-leave-entitlement" type="number" name="annual_leave_entitlement" value="{{ old('annual_leave_entitlement', 12) }}" min="0" max="366" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700"></label>
        <label for="new-user-department" class="block text-xs font-semibold text-slate-600">Departemen<select id="new-user-department" name="department_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-600 outline-none focus:border-emerald-700"><option value="">Pilih departemen</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></label>
        <label for="new-user-approval-level" class="block text-xs font-semibold text-slate-600">Tingkat approval<select id="new-user-approval-level" name="approval_level" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-600 outline-none focus:border-emerald-700"><option value="">Tanpa tingkat approval</option>@foreach ($approvalLevels as $level => $label)<option value="{{ $level }}" @selected(old('approval_level') === $level)>{{ $label }}</option>@endforeach</select></label>
        <label for="new-user-office" class="block text-xs font-semibold text-slate-600">Lokasi kerja approver<select id="new-user-office" name="office_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-600 outline-none focus:border-emerald-700"><option value="">Tanpa lokasi khusus</option>@foreach ($offices as $workplace)<option value="{{ $workplace->id }}" @selected((string) old('office_id') === (string) $workplace->id)>{{ $workplace->name }}</option>@endforeach</select></label>
        <label for="new-user-role" class="block text-xs font-semibold text-slate-600">Role pengguna<select id="new-user-role" name="role" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-600 outline-none focus:border-emerald-700">@foreach ($roles as $key => $label)<option value="{{ $key }}" @selected(old('role', 'karyawan') === $key)>{{ $label }}</option>@endforeach</select></label>
        <label for="new-user-password" class="block text-xs font-semibold text-slate-600">Kata sandi awal<input id="new-user-password" type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700"><span class="mt-1 block font-normal text-slate-400">Minimal 8 karakter</span></label>
        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-emerald-800 focus:ring-emerald-700"> Akun aktif</label>
        <div class="xl:col-span-3 xl:text-right"><button class="inline-flex items-center gap-2 rounded-xl bg-[#567765] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-900"><x-icon name="users" class="h-4 w-4"/> Buat akun</button></div>
    </form>
</section>

<section class="hover-lift mt-7 rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-sm sm:p-8">
    <div class="max-w-2xl"><p class="text-sm font-medium text-emerald-800">Pengaturan role</p><h2 class="mt-1 font-display text-xl font-bold">Tambah role karyawan</h2><p class="mt-2 text-sm leading-6 text-slate-500">Buat role sesuai kebutuhan organisasi, lalu pilih role itu saat menambahkan atau mengubah akun. Superadmin adalah role sistem dan tidak dapat ditambahkan di sini.</p></div>
    <form method="POST" action="{{ route('superadmin.employee-roles.store') }}" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-start">
        @csrf
        <label class="flex-1 text-sm font-semibold text-slate-700">Nama role
            <input name="name" value="{{ old('name') }}" placeholder="Contoh: Operator SPBU" maxlength="100" required class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-emerald-700">
            @error('name')<span class="mt-1 block text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#567765] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-900 sm:mt-7"><x-icon name="users" class="h-4 w-4"/> Tambah role</button>
    </form>
    <div class="mt-5 flex flex-wrap gap-2">
        @foreach ($roles as $key => $label)
            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900">{{ $label }}</span>
        @endforeach
    </div>
</section>

<section class="hover-lift mt-7 rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-sm sm:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-sm font-medium text-slate-500">Akses pengguna</p><h2 class="mt-1 font-display text-xl font-bold">Kelola karyawan dan role</h2></div><form method="GET" class="flex gap-2"><label class="relative"><span class="sr-only">Cari pengguna</span><x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/><input name="q" value="{{ request('q') }}" placeholder="Cari nama, username, NIK" class="min-w-0 border border-slate-200 bg-[#fbfcf9] py-2.5 pl-9 pr-3 text-sm outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"></label><button aria-label="Cari pengguna" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-emerald-50"><x-icon name="search" class="h-4 w-4"/></button></form></div>
    <div class="mt-6 space-y-4">
        @forelse ($users as $user)
                <article class="rounded-2xl border border-slate-200/90 bg-white p-4 transition-colors hover:border-emerald-200 sm:p-5">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><div><h3 class="font-semibold text-slate-900">{{ $user->name }} <span class="ml-1 text-xs font-normal text-slate-400">{{ $user->nik ?: 'Tanpa NIK' }}</span></h3><p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p></div><div class="flex items-center gap-2"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>@if (auth()->id() === $user->id)<span class="text-xs font-semibold text-emerald-800">Akun Anda</span>@endif</div></div>
                <form method="POST" action="{{ route('superadmin.users.update', $user) }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">@csrf @method('PUT')
                    <input name="name" value="{{ $user->name }}" required aria-label="Nama" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-700">
                    <input value="{{ $user->username }}" disabled aria-label="Username" class="cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                    <input type="email" name="email" value="{{ $user->email }}" aria-label="Email (opsional)" placeholder="Email (opsional)" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-700">
                    <input name="nik" value="{{ $user->nik }}" aria-label="NIK" placeholder="NIK" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-700">
                    <input name="position" value="{{ $user->position }}" aria-label="Jabatan" placeholder="Jabatan" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-700">
                    <label class="block text-xs font-semibold text-slate-500">Hak cuti {{ $leaveEntitlementYear }} (hari)<input type="number" name="annual_leave_entitlement" value="{{ $leaveEntitlements[$user->id] ?? 12 }}" min="0" max="366" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-normal text-slate-700 outline-none focus:border-emerald-700"></label>
                    <select name="department_id" aria-label="Departemen" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-emerald-700"><option value="">Tanpa departemen</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected($user->department_id === $department->id)>{{ $department->name }}</option>@endforeach</select>
                    <select name="approval_level" aria-label="Tingkat approval" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-emerald-700"><option value="">Tanpa tingkat approval</option>@foreach ($approvalLevels as $level => $label)<option value="{{ $level }}" @selected($user->approval_level === $level)>{{ $label }}</option>@endforeach</select>
                    <select name="office_id" aria-label="Lokasi kerja approver" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-emerald-700"><option value="">Tanpa lokasi khusus</option>@foreach ($offices as $workplace)<option value="{{ $workplace->id }}" @selected($user->office_id === $workplace->id)>{{ $workplace->name }}</option>@endforeach</select>
                    @if ($user->isSuperAdmin())
                        <input type="hidden" name="role" value="superadmin">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-500">Superadmin (role sistem)</div>
                    @else
                        <select name="role" aria-label="Role" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none focus:border-emerald-700">@foreach ($roles as $key => $label)<option value="{{ $key }}" @selected($user->role === $key)>{{ $label }}</option>@endforeach</select>
                    @endif
                    <input type="password" name="password" aria-label="Kata sandi baru" placeholder="Kata sandi baru (opsional)" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-700">
                    <div class="flex flex-wrap items-center justify-between gap-3 sm:col-span-2 xl:col-span-4">
                        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="rounded border-slate-300 text-emerald-800 focus:ring-emerald-700"> Akun aktif</label>
                        <button class="rounded-lg bg-emerald-800 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-900">Simpan perubahan</button>
                    </div>
                </form>
                @if (auth()->id() !== $user->id)
                    <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}" data-toast-confirm="Hapus akun pengguna ini?" data-toast-confirm-action="Hapus akun" class="mt-3 flex justify-end">@csrf @method('DELETE')<button class="inline-flex items-center gap-2 rounded-lg border border-rose-200 px-4 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50"><x-icon name="trash" class="h-3.5 w-3.5"/> Hapus akun</button></form>
                @endif
            </article>
        @empty
            <div class="rounded-2xl bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">Tidak ada pengguna yang cocok dengan pencarian.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</section>
<script>
    (() => {
        const nameInput = document.getElementById('new-user-name');
        const usernameInput = document.getElementById('new-user-username');
        const usernamePreview = document.getElementById('new-user-username-preview');
        if (!nameInput || !usernameInput || !usernamePreview) return;

        let previousFirstName = '';
        const updateUsername = () => {
            const firstName = nameInput.value.trim().split(/\s+/)[0] || '';
            if (firstName === previousFirstName) return;
            previousFirstName = firstName;

            const base = firstName.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
                .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 45) || 'karyawan';
            const randomNumber = crypto.getRandomValues(new Uint32Array(1))[0] % 90 + 10;
            usernameInput.value = firstName ? `${base}${randomNumber}` : '';
            usernamePreview.value = usernameInput.value;
        };

        nameInput.addEventListener('input', updateUsername);
        updateUsername();
    })();
</script>
@endsection
