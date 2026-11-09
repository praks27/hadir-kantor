<?php

use App\Http\Controllers\AdminLeaveRequestController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::get('/login/leave-notifications', [AuthController::class, 'leaveNotificationStatus'])
        ->middleware('throttle:30,1')
        ->name('login.leave-notifications');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::middleware(['employee-actions', 'menu-access:leaves'])->group(function () {
        Route::get('/perizinan', [LeaveRequestController::class, 'index'])->name('leaves.index');
        Route::post('/perizinan', [LeaveRequestController::class, 'store'])->name('leaves.store');
        Route::put('/perizinan/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('leaves.update');
        Route::delete('/perizinan/{leaveRequest}', [LeaveRequestController::class, 'cancel'])->name('leaves.cancel');
    });
    Route::middleware(['employee-actions', 'menu-access:attendance'])->group(function () {
        Route::post('/absensi/masuk', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/absensi/pulang', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    });
    Route::get('/perizinan/{leaveRequest}/lampiran', [LeaveRequestController::class, 'downloadAttachment'])->name('leaves.attachment');
    Route::prefix('admin/perizinan')->name('admin.leaves.')->middleware(['approver', 'menu-access:approvals'])->group(function () {
        Route::get('/', [AdminLeaveRequestController::class, 'index'])->name('index');
        Route::put('/{leaveRequest}', [AdminLeaveRequestController::class, 'update'])->name('update');
    });
    Route::prefix('admin/laporan')->name('admin.reports.')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->middleware('menu-access:report_overview')->name('index');
        Route::get('/absensi', [AdminReportController::class, 'attendance'])->middleware('menu-access:report_attendance')->name('attendance');
        Route::get('/absensi/cetak', [AdminReportController::class, 'printAttendance'])->middleware('menu-access:report_attendance')->name('attendance.print');
        Route::get('/absensi/excel', [AdminReportController::class, 'exportAttendance'])->middleware('menu-access:report_attendance')->name('attendance.excel');
        Route::get('/perizinan', [AdminReportController::class, 'leaveRequests'])->middleware('menu-access:report_leaves')->name('leaves');
        Route::get('/perizinan/cetak', [AdminReportController::class, 'printLeaveRequests'])->middleware('menu-access:report_leaves')->name('leaves.print');
        Route::get('/perizinan/excel', [AdminReportController::class, 'exportLeaveRequests'])->middleware('menu-access:report_leaves')->name('leaves.excel');
    });
    Route::prefix('superadmin')->name('superadmin.')->middleware('superadmin')->group(function () {
        Route::get('/departemen', [SuperAdminController::class, 'departments'])->name('departments');
        Route::post('/departemen', [SuperAdminController::class, 'storeDepartment'])->name('departments.store');
        Route::put('/departemen/{department}', [SuperAdminController::class, 'updateDepartment'])->name('departments.update');
        Route::delete('/departemen/{department}', [SuperAdminController::class, 'destroyDepartment'])->name('departments.destroy');
        Route::get('/kantor', [SuperAdminController::class, 'office'])->name('office');
        Route::post('/kantor', [SuperAdminController::class, 'storeOffice'])->name('office.store');
        Route::put('/kantor/{officeSetting}', [SuperAdminController::class, 'updateOffice'])->name('office.update');
        Route::delete('/kantor/{officeSetting}', [SuperAdminController::class, 'destroyOffice'])->name('office.destroy');
        Route::get('/alur-persetujuan', [SuperAdminController::class, 'approvalWorkflows'])->name('workflows');
        Route::put('/alur-persetujuan', [SuperAdminController::class, 'updateApprovalWorkflows'])->name('workflows.update');
        Route::get('/hak-akses-menu', [SuperAdminController::class, 'menuAccess'])->name('menu-access');
        Route::post('/hak-akses-menu', [SuperAdminController::class, 'updateMenuAccess'])->name('menu-access.update');
        Route::get('/pengguna', [SuperAdminController::class, 'users'])->name('users');
        Route::post('/role-karyawan', [SuperAdminController::class, 'storeEmployeeRole'])->name('employee-roles.store');
        Route::post('/pengguna', [SuperAdminController::class, 'storeUser'])->name('users.store');
        Route::put('/pengguna/{user}', [SuperAdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/pengguna/{user}', [SuperAdminController::class, 'destroyUser'])->name('users.destroy');
    });
});
