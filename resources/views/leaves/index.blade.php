@extends('layouts.app')

@section('title', 'Pengajuan Perizinan')

@section('body')
<main class="page-enter mx-auto max-w-6xl px-5 py-6 sm:px-8 lg:px-10 lg:py-10">
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div><a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-display text-lg font-extrabold tracking-tight"><span class="grid h-10 w-10 place-items-center rounded-xl bg-[#567765] text-white shadow-sm">H</span> HadirKantor</a><p class="mt-3 flex items-center gap-2 text-sm text-slate-500"><x-icon name="file" class="h-4 w-4"/> Perizinan karyawan</p></div>
        <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-emerald-50">← Kembali ke dashboard</a>
    </header>

    <section class="mt-7 grid gap-6 lg:grid-cols-[.85fr_1.15fr]">
        <div class="hover-lift rounded-3xl bg-gradient-to-br from-[#648472] to-[#4d6d5b] p-6 text-white shadow-lg shadow-emerald-950/10 sm:p-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-[#edf3e9]"><x-icon name="calendar" class="h-4 w-4"/> Pengajuan karyawan</span>
            <h1 class="mt-5 font-display text-3xl font-bold">Ajukan izin dengan mudah.</h1>
            <p class="mt-3 text-sm leading-6 text-white/75">Pilih jenis perizinan, lengkapi tanggal dan alasan. Pantau setiap tahap persetujuan atasan melalui riwayat pengajuan.</p>
            <div class="mt-7 grid grid-cols-2 gap-3 text-sm"><div class="rounded-2xl border border-white/10 bg-white/10 p-4"><x-icon name="clock" class="h-5 w-5 text-[#e2ebdc]"/><p class="mt-3 font-semibold">Proses transparan</p><p class="mt-1 text-xs leading-5 text-white/65">Status pengajuan terlihat di halaman ini.</p></div><div class="rounded-2xl border border-white/10 bg-white/10 p-4"><x-icon name="shield" class="h-5 w-5 text-[#e2ebdc]"/><p class="mt-3 font-semibold">Lampiran privat</p><p class="mt-1 text-xs leading-5 text-white/65">Dokumen hanya dapat dilihat pemohon dan admin.</p></div></div>
        </div>

        <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data" class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
            @csrf
            <div class="mb-6"><p class="text-sm font-medium text-emerald-800">Formulir perizinan</p><h2 class="mt-1 font-display text-2xl font-bold text-slate-900">Detail pengajuan</h2></div>
            @error('approval')<p class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ $message }}</p>@enderror
            @error('supervisor_id')<p class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</p>@enderror
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Lokasi kerja
                    <select id="office-id" name="office_id" required class="mt-2 w-full border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"><option value="">Pilih lokasi kerja</option>@foreach ($offices as $workplace)<option value="{{ $workplace->id }}" @selected((string) old('office_id') === (string) $workplace->id)>{{ $workplace->name }}</option>@endforeach</select>
                </label>
                @if ($supervisors->isNotEmpty())<label id="supervisor-wrap" class="{{ isset($supervisorChoiceOfficeIds[old('office_id')]) ? '' : 'hidden' }} block text-sm font-semibold text-slate-700 sm:col-span-2">Atasan langsung
                    <select id="supervisor-id" name="supervisor_id" class="mt-2 w-full border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600"><option value="">Pilih atasan langsung</option>@foreach ($supervisors as $supervisor)<option value="{{ $supervisor->id }}" @selected((string) old('supervisor_id') === (string) $supervisor->id)>{{ $supervisor->name }}</option>@endforeach</select>
                </label>@endif
                <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Jenis perizinan
                    <select id="leave-type" name="leave_type" required class="mt-2 w-full border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"><option value="">Pilih jenis perizinan</option>@foreach ($types as $type)<option value="{{ $type }}" @selected(old('leave_type') === $type)>{{ $type }}</option>@endforeach</select>
                </label>
                <label id="special-type-wrap" class="{{ old('leave_type') === 'Cuti khusus' ? '' : 'hidden' }} block text-sm font-semibold text-slate-700 sm:col-span-2">Jenis cuti khusus
                    <select id="special-type" name="special_type" class="mt-2 w-full border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"><option value="">Pilih alasan cuti khusus</option>@foreach ($specialTypes as $specialType)<option value="{{ $specialType }}" @selected(old('special_type') === $specialType)>{{ $specialType }}</option>@endforeach</select>
                </label>
                <div id="date-range-wrap" class="grid gap-5 sm:col-span-2 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">Tanggal mulai<input type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-2 w-full border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"></label>
                    <label class="block text-sm font-semibold text-slate-700">Tanggal selesai<input type="date" name="end_date" value="{{ old('end_date') }}" required class="mt-2 w-full border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10"></label>
                </div>
                <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Alasan pengajuan<textarea name="reason" rows="4" minlength="10" maxlength="2000" required placeholder="Jelaskan keperluan pengajuan Anda (minimal 10 karakter)" class="mt-2 w-full resize-y border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-700/10">{{ old('reason') }}</textarea></label>
                <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Lampiran pendukung <span class="font-normal text-slate-400">(opsional, PDF/JPG/PNG maks. 5 MB)</span><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="mt-2 block w-full rounded-xl border border-slate-200 bg-[#fbfcf9] p-2 text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-800 hover:file:bg-emerald-100"></label>
            </div>
            <div class="mt-7 flex justify-end"><button class="group inline-flex items-center gap-2 rounded-xl bg-[#567765] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-900 hover:shadow-lg"><x-icon name="file" class="h-4 w-4"/> Kirim pengajuan <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1"/></button></div>
        </form>
    </section>

    <section class="mt-8 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
        <div class="flex items-end justify-between gap-4"><div><p class="text-sm font-medium text-slate-500">Catatan pribadi</p><h2 class="mt-1 font-display text-xl font-bold">Riwayat pengajuan</h2></div><span class="text-xs text-slate-400">{{ $requests->total() }} pengajuan</span></div>
        @if ($requests->isEmpty())
            <div class="mt-6 rounded-2xl bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">Belum ada pengajuan perizinan.</div>
        @else
            <div class="mt-5 overflow-x-auto rounded-2xl bg-slate-50/70 p-2 sm:p-3">
                <table class="w-full min-w-[900px] border-separate border-spacing-y-2 text-left text-sm">
                    <thead class="text-xs font-semibold uppercase tracking-wide text-slate-400"><tr><th class="px-4 pb-1">Pengajuan</th><th class="px-4 pb-1">Periode</th><th class="px-4 pb-1">Lokasi & alasan</th><th class="px-4 pb-1">Tahap approval</th><th class="px-4 pb-1">Lampiran</th><th class="px-4 pb-1">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($requests as $item)
                            @php($canEditRequest = $item->status === 'Menunggu' && (int) $item->current_approval_step === 1 && ! $item->approvals->contains(fn ($stage) => $stage->reviewed_at !== null))
                            <tr class="align-top text-slate-600">
                                <td class="rounded-l-2xl border-y border-l border-slate-200 bg-white px-4 py-4"><p class="font-bold text-slate-800">{{ $item->leave_type }}@if ($item->special_type)<span class="block text-xs font-medium text-slate-500">{{ $item->special_type }}</span>@endif</p><span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $item->status === 'Disetujui' ? 'bg-emerald-50 text-emerald-800' : ($item->status === 'Ditolak' ? 'bg-rose-50 text-rose-700' : ($item->status === 'Dibatalkan' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-700')) }}">{{ $item->status }}</span></td>
                                <td class="whitespace-nowrap border-y border-slate-200 bg-white px-4 py-4"><p class="font-semibold text-slate-700">{{ $item->start_date->translatedFormat('d M Y') }}</p><p class="mt-1 text-xs text-slate-500">s/d {{ $item->end_date->translatedFormat('d M Y') }}</p><p class="mt-2 text-xs text-slate-400">{{ $item->start_date->diffInDays($item->end_date) + 1 }} hari</p></td>
                                <td class="border-y border-slate-200 bg-white px-4 py-4"><p class="font-semibold text-emerald-800">{{ $item->office?->name ?? 'Lokasi tidak tersedia' }}</p><p class="mt-1 max-w-xs whitespace-pre-line text-xs leading-5 text-slate-500">{{ $item->reason }}</p>@if ($item->review_note)<p class="mt-2 rounded-lg bg-slate-50 px-2.5 py-2 text-xs text-slate-600"><span class="font-semibold">Catatan:</span> {{ $item->review_note }}</p>@endif</td>
                                <td class="border-y border-slate-200 bg-white px-4 py-4"><div class="flex min-w-48 flex-col gap-2">@foreach ($item->approvals as $stage)<div class="rounded-xl border px-3 py-2 text-xs {{ $stage->status === 'Disetujui' ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : ($stage->status === 'Ditolak' ? 'border-rose-100 bg-rose-50 text-rose-700' : 'border-slate-200 bg-slate-50 text-slate-600') }}"><p class="font-bold">{{ $stage->sequence }}. {{ \App\Models\LeaveApprovalWorkflow::LEVELS[$stage->approver_level] ?? $stage->approver_level }} <span class="font-medium">· {{ $stage->status }}</span></p>@if ($stage->reviewer)<p class="mt-1 opacity-75">{{ $stage->reviewer->name }}</p>@elseif ($stage->approver)<p class="mt-1 opacity-75">Untuk {{ $stage->approver->name }}</p>@endif</div>@endforeach</div></td>
                                <td class="border-y border-slate-200 bg-white px-4 py-4">@if ($item->attachment_path)<a href="{{ route('leaves.attachment', $item) }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100"><x-icon name="file" class="h-4 w-4"/> Unduh</a>@else<span class="text-xs text-slate-400">Tidak ada</span>@endif</td>
                                <td class="rounded-r-2xl border-y border-r border-slate-200 bg-white px-4 py-4"><div class="flex min-w-36 flex-col gap-2">@if ($canEditRequest)<button type="button" data-edit-request="{{ $item->id }}" data-update-action="{{ route('leaves.update', $item) }}" data-office="{{ $item->office_id }}" data-type="{{ $item->leave_type }}" data-special="{{ $item->special_type }}" data-start="{{ $item->start_date->format('Y-m-d') }}" data-end="{{ $item->end_date->format('Y-m-d') }}" data-reason="{{ $item->reason }}" data-supervisor="{{ $item->approvals->firstWhere('approver_level', 'supervisor')?->approver_user_id }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-900 transition hover:-translate-y-0.5 hover:bg-emerald-100"><x-icon name="edit" class="h-4 w-4"/> Edit</button>@endif @if ($item->status === 'Menunggu')<form method="POST" action="{{ route('leaves.cancel', $item) }}" data-toast-confirm="Batalkan pengajuan izin ini?" data-toast-confirm-action="Ya, batalkan">@csrf @method('DELETE')<button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:-translate-y-0.5 hover:bg-rose-100"><x-icon name="trash" class="h-4 w-4"/> Batalkan</button></form>@endif @if (! $canEditRequest && $item->status !== 'Menunggu')<span class="text-xs text-slate-400">Tidak ada tindakan</span>@endif</div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $requests->links() }}</div>
        @endif
    </section>

    <div id="edit-request-modal" class="fixed inset-0 z-[90] hidden items-start justify-center overflow-y-auto overscroll-contain bg-slate-950/50 px-3 py-3 opacity-0 backdrop-blur-sm transition-opacity duration-200 sm:items-center sm:p-5" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
        <button type="button" data-close-edit class="absolute inset-0 cursor-default" aria-label="Tutup modal edit"></button>
        <section class="relative my-auto flex max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-white/70 bg-white shadow-2xl transition duration-200 sm:max-h-[calc(100dvh-2.5rem)] sm:rounded-3xl" data-edit-card>
            <header class="flex shrink-0 items-start justify-between gap-3 bg-gradient-to-r from-[#567765] to-[#6c8c79] px-4 py-4 text-white sm:gap-4 sm:px-8 sm:py-5"><div class="min-w-0"><p class="text-[11px] font-semibold uppercase tracking-[.16em] text-white/70 sm:text-xs">Perbarui pengajuan</p><h2 id="edit-modal-title" class="mt-1 font-display text-lg font-bold sm:text-xl">Edit pengajuan izin</h2><p class="mt-1 text-xs leading-5 text-white/75 sm:text-sm">Perubahan hanya tersedia sebelum approval dimulai.</p></div><button type="button" data-close-edit aria-label="Tutup" class="shrink-0 rounded-xl bg-white/15 p-2 text-white transition hover:bg-white/25"><svg viewBox="0 0 20 20" fill="none" class="h-5 w-5"><path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button></header>
            <form id="edit-request-form" method="POST" action="{{ route('leaves.update', ['leaveRequest' => '__ID__']) }}" enctype="multipart/form-data" class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-7">
                @csrf @method('PUT')
                <input type="hidden" id="edit-request-id" name="edit_request_id" value="{{ old('edit_request_id') }}">
                @if ($errors->hasBag('edit')) @foreach ($errors->getBag('edit')->all() as $error)<p class="mb-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $error }}</p>@endforeach @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Lokasi kerja<select id="edit-office-id" name="office_id" required class="mt-2 w-full rounded-xl border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600">@foreach ($offices as $workplace)<option value="{{ $workplace->id }}">{{ $workplace->name }}</option>@endforeach</select></label>
                    @if ($supervisors->isNotEmpty())<label id="edit-supervisor-wrap" class="hidden block text-sm font-semibold text-slate-700 sm:col-span-2">Atasan langsung
                        <select id="edit-supervisor-id" name="supervisor_id" class="mt-2 w-full rounded-xl border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600"><option value="">Pilih atasan langsung</option>@foreach ($supervisors as $supervisor)<option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>@endforeach</select>
                    </label>@endif
                    <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Jenis perizinan<select id="edit-leave-type" name="leave_type" required class="mt-2 w-full rounded-xl border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600"><option value="">Pilih jenis perizinan</option>@foreach ($types as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></label>
                    <label id="edit-special-wrap" class="hidden block text-sm font-semibold text-slate-700 sm:col-span-2">Jenis cuti khusus<select id="edit-special-type" name="special_type" class="mt-2 w-full rounded-xl border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600"><option value="">Pilih alasan cuti khusus</option>@foreach ($specialTypes as $specialType)<option value="{{ $specialType }}">{{ $specialType }}</option>@endforeach</select></label>
                    <div id="edit-date-range-wrap" class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                        <label class="block min-w-0 text-sm font-semibold text-slate-700">Tanggal mulai<input id="edit-start-date" type="date" name="start_date" required class="mt-2 block w-full min-w-0 rounded-xl border border-slate-200 bg-[#fbfcf9] px-3 py-3 text-sm font-normal outline-none focus:border-emerald-600 sm:px-4"></label>
                        <label class="block min-w-0 text-sm font-semibold text-slate-700">Tanggal selesai<input id="edit-end-date" type="date" name="end_date" required class="mt-2 block w-full min-w-0 rounded-xl border border-slate-200 bg-[#fbfcf9] px-3 py-3 text-sm font-normal outline-none focus:border-emerald-600 sm:px-4"></label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Alasan<textarea id="edit-reason" name="reason" rows="4" minlength="10" maxlength="2000" required class="mt-2 w-full resize-y rounded-xl border border-slate-200 bg-[#fbfcf9] px-4 py-3 text-sm font-normal outline-none focus:border-emerald-600"></textarea></label>
                    <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Lampiran baru <span class="font-normal text-slate-400">(opsional, menggantikan lampiran lama)</span><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="mt-2 block w-full rounded-xl border border-slate-200 bg-[#fbfcf9] p-2 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-emerald-800"></label>
                </div>
                <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:mt-7 sm:flex-row sm:justify-end sm:gap-3"><button type="button" data-close-edit class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 sm:w-auto sm:py-2.5">Batal</button><button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#567765] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-900 sm:w-auto sm:py-2.5"><x-icon name="check" class="h-4 w-4"/> Simpan perubahan</button></div>
            </form>
        </section>
    </div>

</main>
<script>
    const leaveType = document.getElementById('leave-type');
    const specialWrap = document.getElementById('special-type-wrap');
    const specialType = document.getElementById('special-type');
    const dateRangeWrap = document.getElementById('date-range-wrap');
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const supervisorChoiceOfficeIds = @json($supervisorChoiceOfficeIds);
    const supervisorWrap = document.getElementById('supervisor-wrap');
    const supervisorSelect = document.getElementById('supervisor-id');
    function updateSupervisorChoice(officeId, wrap, select) {
        if (!wrap || !select) return;
        const eligibleIds = supervisorChoiceOfficeIds[officeId] || [];
        wrap.classList.toggle('hidden', eligibleIds.length < 2);
        select.required = eligibleIds.length > 1;
        [...select.options].forEach((option) => {
            option.hidden = option.value !== '' && !eligibleIds.includes(Number(option.value));
            option.disabled = option.hidden;
        });
        if (select.value && !eligibleIds.includes(Number(select.value))) select.value = '';
    }
    const officeSelect = document.getElementById('office-id');
    officeSelect.addEventListener('change', () => updateSupervisorChoice(officeSelect.value, supervisorWrap, supervisorSelect));
    updateSupervisorChoice(officeSelect.value, supervisorWrap, supervisorSelect);
    function updateSpecialType() {
        const isSpecial = leaveType.value === 'Cuti khusus';
        specialWrap.classList.toggle('hidden', !isSpecial);
        specialType.required = isSpecial;
        if (!isSpecial) specialType.value = '';
    }
    function updateDateRange() {
        const isOutsideDuty = leaveType.value === 'Dinas luar';
        dateRangeWrap.classList.toggle('hidden', isOutsideDuty);
        startDate.required = !isOutsideDuty;
        endDate.required = !isOutsideDuty;
        startDate.disabled = isOutsideDuty;
        endDate.disabled = isOutsideDuty;
    }
    leaveType.addEventListener('change', updateSpecialType);
    leaveType.addEventListener('change', updateDateRange);
    updateSpecialType();
    updateDateRange();

    const editModal = document.getElementById('edit-request-modal');
    // Keep fixed modals outside the animated page container so they anchor to the viewport.
    document.body.appendChild(editModal);
    const editCard = editModal.querySelector('[data-edit-card]');
    const editForm = document.getElementById('edit-request-form');
    const editSpecialWrap = document.getElementById('edit-special-wrap');
    const editSpecialType = document.getElementById('edit-special-type');
    const editDateRangeWrap = document.getElementById('edit-date-range-wrap');
    const editStartDate = document.getElementById('edit-start-date');
    const editEndDate = document.getElementById('edit-end-date');
    const editSupervisorWrap = document.getElementById('edit-supervisor-wrap');
    const editSupervisorSelect = document.getElementById('edit-supervisor-id');
    let lastTrigger = null;

    function showModal(modal, card) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        });
    }

    function hideModal(modal, card) {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        card.classList.remove('scale-100');
        card.classList.add('scale-95');
        window.setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (editModal.classList.contains('hidden')) document.body.classList.remove('overflow-hidden');
            lastTrigger?.focus();
        }, 200);
    }

    function toggleEditSpecialType() {
        const isSpecial = document.getElementById('edit-leave-type').value === 'Cuti khusus';
        editSpecialWrap.classList.toggle('hidden', !isSpecial);
        editSpecialType.required = isSpecial;
        if (!isSpecial) editSpecialType.value = '';
    }

    function toggleEditDateRange() {
        const isOutsideDuty = document.getElementById('edit-leave-type').value === 'Dinas luar';
        editDateRangeWrap.classList.toggle('hidden', isOutsideDuty);
        editStartDate.required = !isOutsideDuty;
        editEndDate.required = !isOutsideDuty;
        editStartDate.disabled = isOutsideDuty;
        editEndDate.disabled = isOutsideDuty;
    }

    document.querySelectorAll('[data-edit-request]').forEach((button) => {
        button.addEventListener('click', () => {
            lastTrigger = button;
            editForm.action = button.dataset.updateAction;
            document.getElementById('edit-request-id').value = button.dataset.editRequest;
            document.getElementById('edit-office-id').value = button.dataset.office;
            if (editSupervisorSelect) editSupervisorSelect.value = button.dataset.supervisor || '';
            updateSupervisorChoice(button.dataset.office, editSupervisorWrap, editSupervisorSelect);
            document.getElementById('edit-leave-type').value = button.dataset.type;
            editSpecialType.value = button.dataset.special || '';
            document.getElementById('edit-start-date').value = button.dataset.start;
            document.getElementById('edit-end-date').value = button.dataset.end;
            document.getElementById('edit-reason').value = button.dataset.reason;
            toggleEditSpecialType();
            toggleEditDateRange();
            showModal(editModal, editCard);
            document.getElementById('edit-office-id').focus({ preventScroll: true });
        });
    });

    document.getElementById('edit-leave-type').addEventListener('change', toggleEditSpecialType);
    document.getElementById('edit-leave-type').addEventListener('change', toggleEditDateRange);
    document.getElementById('edit-office-id').addEventListener('change', (event) => updateSupervisorChoice(event.target.value, editSupervisorWrap, editSupervisorSelect));
    document.querySelectorAll('[data-close-edit]').forEach((button) => button.addEventListener('click', () => hideModal(editModal, editCard)));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !editModal.classList.contains('hidden')) hideModal(editModal, editCard);
    });

    @if ($errors->hasBag('edit') && $errors->getBag('edit')->any())
        const oldEditId = @json(old('edit_request_id'));
        const oldEditButton = [...document.querySelectorAll('[data-edit-request]')].find((button) => button.dataset.editRequest === String(oldEditId));
        if (oldEditButton) {
            oldEditButton.click();
            document.getElementById('edit-office-id').value = @json(old('office_id')) || '';
            if (editSupervisorSelect) editSupervisorSelect.value = @json(old('supervisor_id')) || '';
            updateSupervisorChoice(document.getElementById('edit-office-id').value, editSupervisorWrap, editSupervisorSelect);
            document.getElementById('edit-leave-type').value = @json(old('leave_type')) || '';
            editSpecialType.value = @json(old('special_type')) || '';
            document.getElementById('edit-start-date').value = @json(old('start_date')) || '';
            document.getElementById('edit-end-date').value = @json(old('end_date')) || '';
            document.getElementById('edit-reason').value = @json(old('reason')) || '';
            toggleEditSpecialType();
            toggleEditDateRange();
        }
    @endif
</script>
@endsection
