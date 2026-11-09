@php
    $viewer = auth()->user();
    $isSuperAdmin = $viewer->isSuperAdmin();
    $showAttendance = ! $isSuperAdmin && $viewer->canAccessMenu('attendance');
    $showLeaves = ! $isSuperAdmin && $viewer->canAccessMenu('leaves');
    $showApprovals = $viewer->canApproveLeaveRequests() && $viewer->canAccessMenu('approvals');
    $showReportOverview = $viewer->canAccessMenu('report_overview');
    $showLeaveReport = $viewer->canAccessMenu('report_leaves');
    $showAttendanceReport = $viewer->canAccessMenu('report_attendance');
    $pendingLeaveCount = $showApprovals ? app(\App\Services\LeaveApprovalWorkflowService::class)->actionableCount($viewer) : 0;
    $linkClass = 'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors';
    $activeClass = 'bg-emerald-50 font-semibold text-emerald-900';
    $inactiveClass = 'text-slate-500 hover:bg-slate-50 hover:text-slate-900';
@endphp

<aside class="no-print hidden w-[260px] shrink-0 border-r border-slate-200/80 bg-white p-6 lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:overflow-y-auto">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-display text-lg font-extrabold tracking-tight"><span class="grid h-10 w-10 place-items-center rounded-xl bg-[#567765] text-white shadow-sm">H</span> HadirKantor</a>

    <p class="mb-3 mt-10 px-3 text-[11px] font-bold uppercase tracking-[.18em] text-slate-400">Menu utama</p>
    <a href="{{ route('dashboard') }}" class="{{ $linkClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}"><x-icon name="dashboard" class="h-4 w-4"/> Dashboard</a>
    @if ($showAttendance)
        <a href="{{ route('dashboard') }}#riwayat" class="mt-1 {{ $linkClass }} {{ request()->routeIs('dashboard') && request()->getRequestUri() !== '/dashboard' ? $activeClass : $inactiveClass }}"><x-icon name="clock" class="h-4 w-4"/> Riwayat absensi</a>
    @endif
    @if ($showLeaves)
        <a href="{{ route('leaves.index') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('leaves.*') ? $activeClass : $inactiveClass }}"><x-icon name="file" class="h-4 w-4"/> Pengajuan perizinan</a>
    @endif
    @if ($showApprovals)
        <a href="{{ route('admin.leaves.index') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('admin.leaves.*') ? $activeClass : $inactiveClass }}"><x-icon name="check" class="h-4 w-4"/> Persetujuan izin @if ($pendingLeaveCount > 0)<span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">{{ $pendingLeaveCount }}</span>@endif</a>
    @endif

    @if ($showReportOverview || $showLeaveReport || $showAttendanceReport)
        <p class="mb-2 mt-8 px-3 text-[11px] font-bold uppercase tracking-[.18em] text-slate-400">Laporan</p>
        @if ($showReportOverview)<a href="{{ route('admin.reports.index') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.reports.index') ? $activeClass : $inactiveClass }}"><x-icon name="file" class="h-4 w-4"/> Ringkasan laporan</a>@endif
        @if ($showLeaveReport)<a href="{{ route('admin.reports.leaves') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('admin.reports.leaves*') ? $activeClass : $inactiveClass }}"><x-icon name="calendar" class="h-4 w-4"/> Pengajuan izin karyawan</a>@endif
        @if ($showAttendanceReport)<a href="{{ route('admin.reports.attendance') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('admin.reports.attendance*') ? $activeClass : $inactiveClass }}"><x-icon name="clock" class="h-4 w-4"/> Absensi karyawan</a>@endif
    @endif

    @if ($isSuperAdmin)
        <p class="mb-2 mt-8 px-3 text-[11px] font-bold uppercase tracking-[.18em] text-slate-400">Administrasi</p>
        <a href="{{ route('superadmin.office') }}" class="{{ $linkClass }} {{ request()->routeIs('superadmin.office*') ? $activeClass : $inactiveClass }}"><x-icon name="office" class="h-4 w-4"/> Lokasi & jam kerja</a>
        <a href="{{ route('superadmin.departments') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('superadmin.departments*') ? $activeClass : $inactiveClass }}"><x-icon name="users" class="h-4 w-4"/> Departemen</a>
        <a href="{{ route('superadmin.users') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('superadmin.users*') ? $activeClass : $inactiveClass }}"><x-icon name="users" class="h-4 w-4"/> Karyawan & role</a>
        <a href="{{ route('superadmin.menu-access') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('superadmin.menu-access*') ? $activeClass : $inactiveClass }}"><x-icon name="shield" class="h-4 w-4"/> Hak akses menu</a>
        <a href="{{ route('superadmin.workflows') }}" class="mt-1 {{ $linkClass }} {{ request()->routeIs('superadmin.workflows*') ? $activeClass : $inactiveClass }}"><x-icon name="file" class="h-4 w-4"/> Alur persetujuan izin</a>
    @endif

    <div class="mt-auto rounded-2xl bg-[#f2f7ec] p-4"><p class="text-xs font-semibold text-emerald-900">Menu sesuai akses Anda</p><p class="mt-1 text-xs leading-5 text-slate-500">Navigasi tetap berada di tempat yang sama saat Anda berpindah halaman.</p></div>
</aside>
