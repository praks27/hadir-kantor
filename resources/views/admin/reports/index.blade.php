@extends('admin.layout')

@section('page-title', 'Ringkasan laporan')

@section('content')
<section class="mt-7 space-y-6">
    <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
        <div><h2 class="font-display text-lg font-bold">Pilih periode laporan</h2><p class="mt-1 text-sm text-slate-500">Ringkasan dan detail laporan mengikuti rentang tanggal serta kantor yang dipilih.</p></div>
        <form method="GET" action="{{ route('admin.reports.index') }}" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.3fr_auto]">
            <label class="text-xs font-semibold text-slate-500">Dari tanggal<input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-100"></label>
            <label class="text-xs font-semibold text-slate-500">Sampai tanggal<input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-100"></label>
            <label class="text-xs font-semibold text-slate-500">Lokasi kerja<select name="office_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-100"><option value="">Semua lokasi</option>@foreach ($offices as $office)<option value="{{ $office->id }}" @selected((string) $filters['office_id'] === (string) $office->id)>{{ $office->name }}</option>@endforeach</select></label>
            <button class="self-end rounded-xl bg-[#567765] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-900">Terapkan filter</button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">Karyawan aktif</p><p class="mt-2 font-display text-3xl font-bold text-slate-900">{{ number_format($employeeCount) }}</p><p class="mt-1 text-xs text-slate-400">Seluruh lokasi kerja</p></article>
        <article class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">Catatan absensi</p><p class="mt-2 font-display text-3xl font-bold text-slate-900">{{ number_format($attendanceCount) }}</p><p class="mt-1 text-xs text-slate-400">{{ number_format($lateCount) }} terlambat pada periode ini</p></article>
        <article class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">Pengajuan izin</p><p class="mt-2 font-display text-3xl font-bold text-slate-900">{{ number_format($leaveCount) }}</p><p class="mt-1 text-xs text-slate-400">{{ number_format($pendingLeaveCount) }} menunggu · {{ number_format($approvedLeaveCount) }} disetujui</p></article>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        @if ($canViewAttendanceReport)
            <article class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm"><div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-800"><x-icon name="clock" class="h-5 w-5"/></span><div><h2 class="font-display text-lg font-bold">Absensi karyawan</h2><p class="text-sm text-slate-500">Rekap kehadiran, jam masuk, dan status.</p></div></div><div class="mt-5 flex flex-wrap gap-2"><a href="{{ route('admin.reports.attendance', request()->query()) }}" class="rounded-xl bg-[#567765] px-4 py-2.5 text-sm font-semibold text-white">Buka laporan</a><a target="_blank" href="{{ route('admin.reports.attendance.print', request()->query()) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Cetak / Simpan PDF</a><a href="{{ route('admin.reports.attendance.excel', request()->query()) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Ekspor Excel</a></div></article>
        @endif
        @if ($canViewLeaveReport)
            <article class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm"><div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-50 text-amber-800"><x-icon name="calendar" class="h-5 w-5"/></span><div><h2 class="font-display text-lg font-bold">Pengajuan izin karyawan</h2><p class="text-sm text-slate-500">Rekap cuti, sakit, dinas, dan status keputusan.</p></div></div><div class="mt-5 flex flex-wrap gap-2"><a href="{{ route('admin.reports.leaves', request()->query()) }}" class="rounded-xl bg-[#567765] px-4 py-2.5 text-sm font-semibold text-white">Buka laporan</a><a target="_blank" href="{{ route('admin.reports.leaves.print', request()->query()) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Cetak / Simpan PDF</a><a href="{{ route('admin.reports.leaves.excel', request()->query()) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Ekspor Excel</a></div></article>
        @endif
    </div>
</section>
@endsection
