<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeSetting;
use App\Services\LeaveApprovalWorkflowService;
use App\Services\LeaveBalanceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(LeaveApprovalWorkflowService $workflows, LeaveBalanceService $leaveBalance): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $canViewAttendance = ! $isSuperAdmin && $user->canAccessMenu('attendance');
        $canViewLeaves = ! $isSuperAdmin && $user->canAccessMenu('leaves');
        $canViewApprovals = $user->canApproveLeaveRequests() && $user->canAccessMenu('approvals');
        $canViewReports = $user->canAccessMenu('report_overview');
        $today = now()->toDateString();
        $attendance = $canViewAttendance ? Attendance::with('office')->where('user_id', Auth::id())->where('attendance_date', $today)->first() : null;
        $history = $canViewAttendance ? Attendance::with('office')->where('user_id', Auth::id())->latest('attendance_date')->limit(7)->get() : collect();
        $offices = $canViewAttendance ? OfficeSetting::query()->orderBy('name')->get() : collect();
        $office = $attendance?->office;
        $pendingLeaveCount = $canViewApprovals
            ? $workflows->actionableCount($user)
            : 0;
        $leaveBalanceSummary = $canViewLeaves ? $leaveBalance->summary($user, (int) now()->year) : null;
        if ($pendingLeaveCount > 0) {
            session()->flash('leave_notification', 'Ada pengajuan izin yang masih menunggu persetujuan Anda.');
        }

        return view('dashboard', compact('attendance', 'history', 'office', 'offices', 'pendingLeaveCount', 'leaveBalanceSummary', 'isSuperAdmin', 'canViewAttendance', 'canViewLeaves', 'canViewApprovals', 'canViewReports'));
    }
}
