@extends('admin.layout')

@section('title', 'Persetujuan Perizinan')
@section('page-title', 'Persetujuan perizinan')

@section('content')
<section class="mt-7 rounded-3xl bg-gradient-to-br from-[#648472] to-[#4d6d5b] p-6 text-white shadow-lg shadow-emerald-950/10 sm:flex sm:items-end sm:justify-between sm:p-8">
    <div><span class="inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-[#edf3e9]"><x-icon name="file" class="h-4 w-4"/> Administrasi karyawan</span><h1 class="mt-4 font-display text-3xl font-bold">Tinjau pengajuan izin.</h1><p class="mt-2 max-w-xl text-sm leading-6 text-white/75">Periksa rentang tanggal dan alasan, lalu berikan keputusan beserta catatan jika diperlukan.</p></div>
    <div class="mt-5 rounded-2xl border border-white/15 bg-white/10 px-5 py-4 sm:mt-0"><p class="text-xs text-white/70">Menunggu ditinjau</p><p class="mt-1 font-display text-3xl font-bold">{{ $pendingCount }}</p></div>
</section>

<section class="mt-7 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-sm font-medium text-slate-500">Daftar pengajuan</p><h2 class="mt-1 font-display text-xl font-bold">Semua karyawan</h2></div><form method="GET" class="flex items-center gap-2"><label for="status-filter" class="text-sm font-medium text-slate-500">Status</label><select id="status-filter" name="status" onchange="this.form.submit()" class="border border-slate-200 bg-[#fbfcf9] px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10"><option value="">Semua status</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></form></div>
    @if ($requests->isEmpty())
        <div class="mt-6 rounded-2xl bg-slate-50 px-5 py-12 text-center"><div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-800"><x-icon name="check" class="h-6 w-6"/></div><p class="mt-3 font-semibold text-slate-700">Belum ada pengajuan pada filter ini.</p><p class="mt-1 text-sm text-slate-500">Pengajuan baru akan muncul di sini.</p></div>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($requests as $item)
                @php($activeApproval = $item->approvals->firstWhere('sequence', $item->current_approval_step))
                @php($canAct = $item->status === 'Menunggu' && $activeApproval?->status === 'Menunggu' && (int) $activeApproval?->approver_user_id === (int) auth()->id() && $activeApproval?->approver_level === auth()->user()->approval_level && $item->user->department_id === auth()->user()->department_id && (! str_starts_with((string) auth()->user()->approval_level, 'spbu_') || $item->office_id === auth()->user()->office_id) && $item->user_id !== auth()->id())
                <article class="rounded-2xl border border-slate-200/80 bg-white p-5 transition-colors hover:border-emerald-200 sm:p-6">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">{{ $item->leave_type }}</span>@if ($item->special_type)<span class="rounded-full bg-[#f2f3ec] px-3 py-1 text-xs font-semibold text-slate-600">{{ $item->special_type }}</span>@endif<span class="rounded-full px-3 py-1 text-xs font-bold {{ $item->status === 'Disetujui' ? 'bg-emerald-50 text-emerald-800' : ($item->status === 'Ditolak' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">{{ $item->status }}</span></div>
                            <h3 class="mt-3 font-display text-lg font-bold text-slate-900">{{ $item->user->name }} <span class="font-sans text-sm font-normal text-slate-400">· {{ $item->user->username }} · {{ $item->user->department?->name ?: 'Tanpa departemen' }}</span></h3>
                            <p class="mt-1 text-sm font-medium text-emerald-800">Lokasi kerja: {{ $item->office?->name ?? 'Tidak tersedia' }}</p>
                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-500"><span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="h-4 w-4"/>{{ $item->start_date->translatedFormat('d M Y') }} – {{ $item->end_date->translatedFormat('d M Y') }}</span><span>{{ $item->start_date->diffInDays($item->end_date) + 1 }} hari</span><span>Diajukan {{ $item->created_at->translatedFormat('d M Y, H:i') }}</span></div>
                            @if ($item->status === 'Menunggu' && $activeApproval)<p class="mt-3 text-sm font-semibold text-amber-700">Menunggu tahap {{ $activeApproval->sequence }} · {{ $approvalLabels[$activeApproval->approver_level] ?? $activeApproval->approver_level }}</p>@endif
                            <p class="mt-4 whitespace-pre-line rounded-xl bg-[#f8f9f5] px-4 py-3 text-sm leading-6 text-slate-600">{{ $item->reason }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-4">@if ($item->attachment_path)<a href="{{ route('leaves.attachment', $item) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-800 hover:text-emerald-950"><x-icon name="file" class="h-4 w-4"/> Unduh lampiran</a>@endif @if ($item->reviewer)<span class="text-xs text-slate-400">Diproses oleh {{ $item->reviewer->name }}@if ($item->reviewed_at) · {{ $item->reviewed_at->translatedFormat('d M Y, H:i') }}@endif</span>@endif</div>
                            <div class="mt-4 flex flex-wrap gap-2">@foreach ($item->approvals as $stage)<span class="rounded-xl border px-3 py-2 text-xs {{ $stage->status === 'Disetujui' ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : ($stage->status === 'Ditolak' ? 'border-rose-100 bg-rose-50 text-rose-700' : 'border-slate-200 bg-slate-50 text-slate-600') }}"><span class="font-bold">{{ $stage->sequence }}. {{ $approvalLabels[$stage->approver_level] ?? $stage->approver_level }}</span><span class="ml-1">{{ $stage->status }}</span>@if ($stage->reviewer)<span class="mt-1 block text-[11px] opacity-75">{{ $stage->reviewer->name }}</span>@endif</span>@endforeach</div>
                            @if ($item->review_note && $item->status !== 'Menunggu')<p class="mt-3 text-sm text-slate-500"><span class="font-semibold">Catatan keputusan:</span> {{ $item->review_note }}</p>@endif
                        </div>
                        @if ($canAct)
                            <form method="POST" action="{{ route('admin.leaves.update', $item) }}" class="w-full rounded-2xl bg-[#f8f9f5] p-4 lg:max-w-xs">@csrf @method('PUT')
                                <label class="block text-xs font-semibold text-slate-600">Keputusan<select name="status" required class="mt-2 w-full border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10"><option value="Disetujui">Setujui pengajuan</option><option value="Ditolak">Tolak pengajuan</option></select></label>
                                <label class="mt-3 block text-xs font-semibold text-slate-600">Catatan untuk karyawan<textarea name="review_note" rows="3" maxlength="1000" placeholder="Opsional" class="mt-2 w-full resize-y border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal outline-none placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-700/10"></textarea></label>
                                <button class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#567765] px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-900"><x-icon name="check" class="h-4 w-4"/> Simpan keputusan</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $requests->links() }}</div>
    @endif
</section>
@endsection
