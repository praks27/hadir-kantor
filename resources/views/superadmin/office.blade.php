@extends('superadmin.layout')

@section('title', 'Pengaturan Kantor')
@section('page-title', 'Lokasi & jam kerja')

@section('content')
<section class="mt-7 grid gap-5 xl:grid-cols-[.7fr_1.3fr]">
    <div class="hover-lift rounded-3xl bg-gradient-to-br from-[#648472] to-[#4d6d5b] p-6 text-white sm:p-8">
        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-[#d5f5a0]">Pengaturan global</span>
        <h2 class="mt-5 font-display text-2xl font-bold">Kelola semua lokasi kerja.</h2>
        <p class="mt-3 text-sm leading-6 text-white/70">Setiap kantor memiliki koordinat, radius absensi, dan jadwalnya sendiri. Karyawan memilih kantor saat absensi masuk dan mengajukan izin.</p>
        <div class="mt-7 rounded-2xl border border-white/15 bg-white/10 p-4"><p class="flex items-center gap-2 text-xs text-white/70"><x-icon name="pin" class="h-4 w-4"/> Koordinat terpasang</p><p class="mt-2 font-mono text-sm">{{ $setting->latitude }}, {{ $setting->longitude }}</p><p class="mt-2 text-xs text-white/70">Radius {{ $setting->radius_meters }} meter</p></div>
    </div>
    <form method="POST" action="{{ $editingOffice ? route('superadmin.office.update', $editingOffice) : route('superadmin.office.store') }}" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8">
        @csrf
        @if ($editingOffice) @method('PUT') @endif
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3"><div><p class="text-sm font-medium text-emerald-800">{{ $editingOffice ? 'Perbarui lokasi' : 'Lokasi baru' }}</p><h2 class="mt-1 font-display text-2xl font-bold text-slate-900">{{ $editingOffice ? 'Edit '.$setting->name : 'Tambah lokasi kerja' }}</h2></div>@if ($editingOffice)<a href="{{ route('superadmin.office') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">Batal edit</a>@endif</div>
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Nama lokasi kerja<input name="name" value="{{ old('name', $setting->name) }}" required maxlength="120" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Jenis lokasi kerja<select name="workplace_type" required class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 font-normal outline-none focus:border-emerald-700"><option value="kantor" @selected(old('workplace_type', $setting->workplace_type ?? 'kantor') === 'kantor')>Kantor</option><option value="spbu" @selected(old('workplace_type', $setting->workplace_type ?? 'kantor') === 'spbu')>SPBU / Lapangan</option><option value="lpg" @selected(old('workplace_type', $setting->workplace_type ?? 'kantor') === 'lpg')>Agen LPG</option></select><span class="mt-1 block text-xs font-normal text-slate-500">Jenis ini menentukan pilihan alur persetujuan izin.</span></label>
            <label class="block text-sm font-semibold text-slate-700">Latitude<input id="office-latitude" type="number" name="latitude" step="any" min="-90" max="90" value="{{ old('latitude', $setting->latitude) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="block text-sm font-semibold text-slate-700">Longitude<input id="office-longitude" type="number" name="longitude" step="any" min="-180" max="180" value="{{ old('longitude', $setting->longitude) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="block text-sm font-semibold text-slate-700">Radius absensi (meter)<input id="office-radius" type="number" name="radius_meters" min="10" max="10000" value="{{ old('radius_meters', $setting->radius_meters) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="block text-sm font-semibold text-slate-700">Toleransi keterlambatan (menit)<input type="number" name="grace_minutes" min="0" max="180" value="{{ old('grace_minutes', $setting->grace_minutes) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="block text-sm font-semibold text-slate-700">Jam masuk<input type="time" name="start_time" value="{{ old('start_time', substr((string) $setting->start_time, 0, 5)) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            <label class="block text-sm font-semibold text-slate-700">Jam pulang<input type="time" name="end_time" value="{{ old('end_time', substr((string) $setting->end_time, 0, 5)) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 font-normal outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"></label>
            @php($days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])
            @php($selectedDays = old('work_days', $setting->work_days ?? []))
            <fieldset class="sm:col-span-2"><legend class="text-sm font-semibold text-slate-700">Hari kerja</legend><div class="mt-3 flex flex-wrap gap-2">@foreach ($days as $day)<label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-900"><input type="checkbox" name="work_days[]" value="{{ $day }}" @checked(in_array($day, $selectedDays, true)) class="rounded border-slate-300 text-emerald-800 focus:ring-emerald-700">{{ $day }}</label>@endforeach</div></fieldset>
        </div>
        <div class="mt-7 flex justify-end"><button class="inline-flex items-center gap-2 rounded-xl bg-[#567765] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900"><x-icon name="check" class="h-4 w-4"/> {{ $editingOffice ? 'Simpan perubahan' : 'Tambah kantor' }}</button></div>
    </form>
</section>

<section class="mt-7 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3"><div><p class="text-sm font-medium text-slate-500">Daftar lokasi</p><h2 class="mt-1 font-display text-xl font-bold">Lokasi kerja terdaftar</h2></div><span class="text-xs text-slate-400">{{ $offices->count() }} lokasi</span></div>
    @if ($offices->isEmpty())
        <div class="rounded-2xl bg-slate-50 px-5 py-8 text-center text-sm text-slate-500">Belum ada kantor. Tambahkan lokasi pertama menggunakan formulir di atas.</div>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($offices as $workplace)
                <article class="rounded-2xl border border-slate-200 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="font-display text-lg font-bold text-slate-900">{{ $workplace->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ ['kantor' => 'Kantor', 'spbu' => 'SPBU / Lapangan', 'lpg' => 'Agen LPG'][$workplace->workplace_type] ?? 'Kantor' }} · Radius absensi {{ $workplace->radius_meters }} meter</p></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $workplace->attendances_count + $workplace->leave_requests_count }} catatan</span></div>
                    <p class="mt-4 text-sm text-slate-600">Koordinat: <span class="font-mono text-xs">{{ $workplace->latitude }}, {{ $workplace->longitude }}</span></p>
                    <p class="mt-1 text-sm text-slate-600">Jam kerja: {{ substr((string) $workplace->start_time, 0, 5) }}–{{ substr((string) $workplace->end_time, 0, 5) }} · Toleransi {{ $workplace->grace_minutes }} menit</p>
                    <p class="mt-1 text-sm text-slate-600">Hari kerja: {{ implode(', ', $workplace->work_days ?? []) }}</p>
                    <div class="mt-5 flex flex-wrap gap-2"><a href="{{ route('superadmin.office', ['edit' => $workplace->id]) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Edit lokasi</a>
                        @if ($offices->count() > 1 && $workplace->attendances_count === 0 && $workplace->leave_requests_count === 0)
                            <form method="POST" action="{{ route('superadmin.office.destroy', $workplace) }}" data-toast-confirm="Hapus lokasi kerja ini?" data-toast-confirm-action="Hapus lokasi">@csrf @method('DELETE')<button class="rounded-lg px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Hapus</button></form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection
