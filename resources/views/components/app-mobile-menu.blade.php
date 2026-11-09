@php
    $viewer = auth()->user();
    $isSuperAdmin = $viewer->isSuperAdmin();
    $showLeaves = ! $isSuperAdmin && $viewer->canAccessMenu('leaves');
    $showApprovals = $viewer->canApproveLeaveRequests() && $viewer->canAccessMenu('approvals');
    $showReportOverview = $viewer->canAccessMenu('report_overview');
    $showLeaveReport = $viewer->canAccessMenu('report_leaves');
    $showAttendanceReport = $viewer->canAccessMenu('report_attendance');
    $pendingLeaveCount = $showApprovals ? app(\App\Services\LeaveApprovalWorkflowService::class)->actionableCount($viewer) : 0;
@endphp

<details class="mobile-menu no-print relative mt-4 lg:hidden">
    <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm"><span class="flex items-center gap-2"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>Menu navigasi</span><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></summary>
    <div class="mt-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('dashboard') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Dashboard</a>
            @if ($showLeaves)<a href="{{ route('leaves.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('leaves.*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Pengajuan perizinan</a>@endif
            @if ($showApprovals)<a href="{{ route('admin.leaves.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.leaves.*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Persetujuan izin @if ($pendingLeaveCount > 0)<span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">{{ $pendingLeaveCount }}</span>@endif</a>@endif
            @if ($showReportOverview || $showLeaveReport || $showAttendanceReport)
                @if ($showReportOverview)<a href="{{ route('admin.reports.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.reports.index') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Ringkasan laporan</a>@endif
                @if ($showLeaveReport)<a href="{{ route('admin.reports.leaves') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.reports.leaves*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Pengajuan izin karyawan</a>@endif
                @if ($showAttendanceReport)<a href="{{ route('admin.reports.attendance') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.reports.attendance*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Absensi karyawan</a>@endif
            @endif
            @if ($isSuperAdmin)
                <a href="{{ route('superadmin.office') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('superadmin.office*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Lokasi & jam kerja</a>
                <a href="{{ route('superadmin.departments') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('superadmin.departments*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Departemen</a>
                <a href="{{ route('superadmin.users') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('superadmin.users*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Karyawan & role</a>
                <a href="{{ route('superadmin.menu-access') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('superadmin.menu-access*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Hak akses menu</a>
                <a href="{{ route('superadmin.workflows') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('superadmin.workflows*') ? 'bg-emerald-50 text-emerald-900' : 'bg-slate-50 text-slate-700' }}">Alur persetujuan izin</a>
            @endif
        </div>
    </div>
</details>
