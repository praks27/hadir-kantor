@extends('layouts.app')

@section('title', 'Dashboard')

@section('body')
<div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]">
        <x-app-sidebar />
    <main class="mx-auto w-full max-w-7xl px-5 py-6 sm:px-8 lg:px-10 lg:py-9">
        <header class="flex items-center justify-between border-b border-slate-200/80 pb-5">
            <div class="flex items-center gap-3 lg:hidden"><span class="grid h-9 w-9 place-items-center rounded-xl bg-[#567765] font-bold text-white">H</span><span class="font-display font-extrabold">HadirKantor</span></div>
            <div class="hidden lg:block"><p class="text-sm text-slate-500">Portal karyawan</p><p class="font-semibold text-slate-800">Ringkasan kehadiran</p></div>
            <div class="flex items-center gap-3"><div class="hidden text-right sm:block"><p class="text-sm font-semibold">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">{{ auth()->user()->position ?: 'Karyawan' }}</p></div><div class="grid h-10 w-10 place-items-center rounded-full bg-[#ddecce] font-bold text-emerald-950">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div><form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-white hover:text-slate-900">Keluar</button></form></div>
        </header>

        <x-app-mobile-menu />

        <section class="mt-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div><p class="flex items-center gap-2 text-sm font-semibold text-emerald-800"><x-icon name="calendar" class="h-4 w-4"/>{{ now()->translatedFormat('l, d F Y') }}</p><h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Halo, {{ explode(' ', auth()->user()->name)[0] }}</h1><p class="mt-2 text-slate-500">Semoga hari kerja Anda berjalan lancar.</p></div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"><span class="mr-2 inline-block h-2 w-2 rounded-full bg-emerald-500"></span><span class="font-medium text-slate-600">Waktu lokal</span><span class="ml-2 font-bold tabular-nums text-slate-900" id="clock">{{ now()->format('H:i:s') }}</span></div>
        </section>

        <section class="mt-8 grid gap-5 {{ $canViewAttendance ? 'xl:grid-cols-[1.35fr_.65fr]' : 'xl:grid-cols-1' }}">
            @if ($canViewAttendance)
            <div class="interactive-3d hover-lift overflow-hidden rounded-3xl bg-gradient-to-br from-[#648472] to-[#4d6d5b] p-6 text-white shadow-xl shadow-emerald-950/10 sm:p-8">
                <canvas data-three-hero aria-hidden="true" class="three-hero-canvas"></canvas>
                <div class="flex items-start justify-between gap-4"><div><span class="inline-flex items-center gap-1.5 rounded-full bg-white/12 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-[#edf3e9]"><x-icon name="pin" class="h-3.5 w-3.5"/>{{ $office?->name ?? 'Pilih lokasi kerja' }} · Radius {{ $office?->radius_meters ?? '-' }}{{ $office ? ' m' : '' }}</span><h2 class="mt-5 font-display text-2xl font-bold">{{ $attendance ? 'Kehadiran tercatat' : 'Belum ada catatan' }}</h2><p class="mt-2 max-w-md text-sm leading-6 text-white/75">{{ $attendance ? 'Terima kasih, aktivitas kehadiran Anda hari ini sudah diperbarui.' : 'Pilih lokasi kerja dan catat kehadiran saat Anda berada di area kantor.' }}</p></div><div class="hidden h-14 w-14 place-items-center rounded-2xl bg-[#e2ebdc] text-[#405e4e] sm:grid"><x-icon name="check" class="h-7 w-7"/></div></div>
                <div class="my-7 grid grid-cols-2 gap-3 sm:max-w-md"><div class="rounded-2xl border border-white/10 bg-white/[.07] p-4"><p class="text-xs text-white/55">Jam masuk</p><p class="mt-2 font-display text-2xl font-bold">{{ $attendance?->check_in?->format('H:i') ?? '--:--' }}</p></div><div class="rounded-2xl border border-white/10 bg-white/[.07] p-4"><p class="text-xs text-white/55">Jam pulang</p><p class="mt-2 font-display text-2xl font-bold">{{ $attendance?->check_out?->format('H:i') ?? '--:--' }}</p></div></div>
                <div class="flex flex-wrap gap-3">
                    @if (! $attendance)
                        <form method="POST" action="{{ route('attendance.check-in') }}" id="check-in-form" class="flex flex-wrap items-end gap-3">@csrf<input type="hidden" name="latitude"><input type="hidden" name="longitude"><label class="block min-w-52 text-xs font-semibold text-white/75">Lokasi kerja<select name="office_id" required class="mt-2 w-full rounded-xl border border-white/20 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:ring-4 focus:ring-white/20"><option value="">Pilih lokasi kerja</option>@foreach ($offices as $workplace)<option value="{{ $workplace->id }}" @selected((string) old('office_id') === (string) $workplace->id)>{{ $workplace->name }}</option>@endforeach</select></label>@if ($offices->isEmpty())<p class="text-sm text-white/80">Lokasi kerja belum tersedia. Hubungi superadmin.</p>@endif<button type="button" @disabled($offices->isEmpty()) onclick="submitAttendance('check-in-form')" class="group inline-flex items-center gap-2 rounded-xl bg-[#e2ebdc] px-5 py-3 text-sm font-bold text-[#405e4e] transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-60">Catat masuk <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1"/></button></form>
                    @elseif (! $attendance->check_out)
                        <form method="POST" action="{{ route('attendance.check-out') }}" id="check-out-form">@csrf<input type="hidden" name="latitude"><input type="hidden" name="longitude"><button type="button" onclick="submitAttendance('check-out-form')" class="group inline-flex items-center gap-2 rounded-xl bg-[#e2ebdc] px-5 py-3 text-sm font-bold text-[#405e4e] transition hover:bg-white">Catat pulang <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1"/></button></form>
                    @else
                        <span class="rounded-xl bg-white/10 px-5 py-3 text-sm font-semibold text-white/75">Absensi hari ini selesai</span>
                    @endif
                    <p id="location-message" class="self-center text-xs text-white/55"></p>
                </div>
            </div>
            @endif
            <div class="hover-lift rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-sm sm:p-7"><div class="flex items-center justify-between"><div><p class="text-sm font-medium text-slate-500">Profil karyawan</p><h2 class="mt-1 font-display text-xl font-bold">Informasi Anda</h2></div><span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif</span></div><div class="mt-6 space-y-4"><div class="border-b border-slate-100 pb-3"><p class="text-xs text-slate-400">Nama lengkap</p><p class="mt-1 text-sm font-semibold">{{ auth()->user()->name }}</p></div><div class="border-b border-slate-100 pb-3"><p class="text-xs text-slate-400">NIK karyawan</p><p class="mt-1 text-sm font-semibold">{{ auth()->user()->nik ?: 'Belum diatur' }}</p></div><div class="border-b border-slate-100 pb-3"><p class="text-xs text-slate-400">Departemen</p><p class="mt-1 text-sm font-semibold">{{ auth()->user()->department?->name ?: 'Belum ditentukan' }}</p></div><div><p class="text-xs text-slate-400">Jabatan</p><p class="mt-1 text-sm font-semibold">{{ auth()->user()->position ?: 'Belum diatur' }}</p></div></div></div>
        </section>

        @if ($leaveBalanceSummary)
        <section class="mt-7 rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div><p class="text-sm font-semibold text-emerald-800">Hak cuti tahunan {{ $leaveBalanceSummary['year'] }}</p><p class="mt-1 text-sm text-slate-500">Pengajuan cuti yang menunggu persetujuan ikut mengurangi sisa sementara. Izin lain dan cuti khusus tidak memotong saldo ini.</p></div>
                <div class="flex items-baseline gap-2"><span class="font-display text-4xl font-bold text-slate-900">{{ $leaveBalanceSummary['remaining'] }}</span><span class="text-sm font-semibold text-slate-500">hari tersisa dari {{ $leaveBalanceSummary['entitlement'] }} hari</span></div>
            </div>
            @php($leaveUsagePercent = $leaveBalanceSummary['entitlement'] > 0 ? min(100, (int) round($leaveBalanceSummary['used'] / $leaveBalanceSummary['entitlement'] * 100)) : 0)
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-emerald-50"><div class="h-full rounded-full bg-[#567765] transition-all" style="width: {{ $leaveUsagePercent }}%"></div></div>
            <p class="mt-2 text-right text-xs text-slate-400">{{ $leaveBalanceSummary['used'] }} hari terpakai atau sedang diajukan</p>
        </section>
        @endif

        @if ($canViewLeaves || $canViewApprovals)
        <section class="mt-7 flex flex-col justify-between gap-4 rounded-3xl border border-emerald-100 bg-emerald-50/70 p-5 sm:flex-row sm:items-center sm:p-6">
            <div class="flex items-start gap-4"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-emerald-800 shadow-sm"><x-icon name="file" class="h-5 w-5"/></span><div><h2 class="font-display font-bold text-slate-800">Perizinan karyawan</h2><p class="mt-1 text-sm text-slate-500">Ajukan cuti, izin, sakit, libur pengganti, cuti khusus, atau dinas luar.</p></div></div>
            <div class="flex flex-wrap gap-2">@if ($canViewLeaves)<a href="{{ route('leaves.index') }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:bg-emerald-100">Ajukan / lihat status</a>@endif @if ($canViewApprovals)<a href="{{ route('admin.leaves.index') }}" class="rounded-xl bg-[#567765] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900">Tinjau pengajuan{{ $pendingLeaveCount ? ' ('.$pendingLeaveCount.')' : '' }}</a>@endif</div>
        </section>
        @endif

        @if ($canViewAttendance)
        <section id="riwayat" class="hover-lift mt-8 rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-sm sm:p-8"><div class="flex items-end justify-between"><div><p class="text-sm font-medium text-slate-500">Aktivitas terbaru</p><h2 class="mt-1 font-display text-xl font-bold">Riwayat absensi</h2></div><span class="text-xs font-medium text-slate-400">7 catatan terakhir</span></div>
            @if ($history->isEmpty())
                <div class="mt-6 rounded-2xl bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">Belum ada riwayat kehadiran.</div>
            @else
                <div class="mt-6 overflow-x-auto"><table class="w-full min-w-[680px] text-left text-sm"><thead class="text-xs uppercase tracking-wider text-slate-400"><tr><th class="pb-3 font-semibold">Tanggal</th><th class="pb-3 font-semibold">Kantor</th><th class="pb-3 font-semibold">Masuk</th><th class="pb-3 font-semibold">Pulang</th><th class="pb-3 font-semibold">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($history as $item)<tr><td class="py-4 font-semibold text-slate-700">{{ $item->attendance_date->translatedFormat('d M Y') }}</td><td class="py-4 text-slate-600">{{ $item->office?->name ?? 'Kantor tidak tersedia' }}</td><td class="py-4 text-slate-600">{{ $item->check_in?->format('H:i') ?? '--:--' }}</td><td class="py-4 text-slate-600">{{ $item->check_out?->format('H:i') ?? 'Belum pulang' }}</td><td class="py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->status === 'Terlambat' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-800' }}">{{ $item->status }}</span></td></tr>@endforeach</tbody></table></div>
            @endif
        </section>
        @endif
        <footer class="py-8 text-center text-xs text-slate-400">Data kehadiran tersimpan aman dan hanya dapat diakses oleh pihak berwenang.</footer>
    </main>
</div>
<script>
    const clock = document.getElementById('clock');
    setInterval(() => { if (clock) clock.textContent = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).format(new Date()); }, 1000);
    function submitAttendance(formId) {
        const form = document.getElementById(formId);
        const message = document.getElementById('location-message');
        if (!navigator.geolocation) { message.textContent = 'Browser tidak mendukung lokasi.'; return; }
        message.textContent = 'Sedang mengambil lokasi...';
        navigator.geolocation.getCurrentPosition((position) => {
            form.elements.latitude.value = position.coords.latitude;
            form.elements.longitude.value = position.coords.longitude;
            form.submit();
        }, () => { message.textContent = 'Izin lokasi diperlukan untuk mencatat kehadiran.'; }, { enableHighAccuracy: true, timeout: 10000 });
    }
</script>
@endsection
