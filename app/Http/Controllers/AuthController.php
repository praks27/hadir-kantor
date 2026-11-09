<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LeaveApprovalWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function leaveNotificationStatus(Request $request, LeaveApprovalWorkflowService $workflows): JsonResponse
    {
        $username = mb_strtolower(trim((string) $request->query('username', '')));
        if ($username === '') {
            return response()->json(['has_pending' => false]);
        }

        $approver = User::query()
            ->where('username', $username)
            ->where('is_active', true)
            ->first();

        $hasPending = $approver && $approver->canApproveLeaveRequests() && $approver->canAccessMenu('approvals')
            && $workflows->actionableRequests($approver)->exists();

        return response()->json([
            'has_pending' => (bool) $hasPending,
        ]);
    }

    public function store(Request $request, LeaveApprovalWorkflowService $workflows): RedirectResponse
    {
        $request->merge(['username' => mb_strtolower(trim((string) $request->input('username', '')))]);
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['username' => 'Username atau kata sandi tidak sesuai.'])->onlyInput('username');
        }

        $user = Auth::user();
        $hasPendingApprovals = $user && $user->canApproveLeaveRequests() && $user->canAccessMenu('approvals')
            && $workflows->actionableRequests($user)->exists();

        $request->session()->regenerate();

        $redirect = redirect()->intended(route('dashboard'));
        if ($hasPendingApprovals) {
            $redirect->with('leave_notification', 'Ada pengajuan izin yang masih menunggu persetujuan Anda.');
        }

        return $redirect;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
