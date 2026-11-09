<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Services\LeaveApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveApprovalWorkflowService $workflows) {}

    public function index(Request $request): View
    {
        $approver = $request->user();
        $actionable = $this->workflows->actionableRequests($approver);
        $history = $this->workflows->historyRequests($approver);
        $requests = LeaveRequest::query()
            ->with(['user.department', 'reviewer', 'office', 'approvals.reviewer', 'approvals.approver'])
            ->where(fn (Builder $query) => $query
                ->whereIn('id', (clone $actionable)->select('leave_requests.id'))
                ->orWhereIn('id', (clone $history)->select('leave_requests.id')))
            ->when(in_array($request->query('status'), LeaveRequest::STATUSES, true), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.leave-requests', [
            'requests' => $requests,
            'statuses' => LeaveRequest::STATUSES,
            'pendingCount' => $this->workflows->actionableCount($approver),
            'approvalLabels' => \App\Models\LeaveApprovalWorkflow::LEVELS,
        ]);
    }

    public function update(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Disetujui', 'Ditolak'])],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $updated = DB::transaction(function () use ($leaveRequest, $data, $request): bool {
            $lockedRequest = LeaveRequest::query()->lockForUpdate()->findOrFail($leaveRequest->id);
            if ($lockedRequest->status !== 'Menunggu' || ! $this->workflows->eligibleApprovers(
                (string) $request->user()->approval_level,
                $lockedRequest->loadMissing('user')
            )->whereKey($request->user()->id)->exists()) {
                return false;
            }

            $approval = $lockedRequest->approvals()
                ->where('sequence', $lockedRequest->current_approval_step)
                ->lockForUpdate()
                ->first();
            if (! $approval || $approval->status !== 'Menunggu'
                || $approval->approver_level !== $request->user()->approval_level
                || (int) $approval->approver_user_id !== (int) $request->user()->id) {
                return false;
            }

            $isFinalStep = ! $lockedRequest->approvals()->where('sequence', '>', $approval->sequence)->exists();

            $approval->update([
                'status' => $data['status'],
                'review_note' => $data['review_note'] ?? null,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            if ($data['status'] === 'Ditolak') {
                $lockedRequest->update([
                    'status' => 'Ditolak',
                    'review_note' => $data['review_note'] ?? null,
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);
            } elseif ($isFinalStep) {
                $lockedRequest->update([
                    'status' => 'Disetujui',
                    'review_note' => $data['review_note'] ?? null,
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);
            } else {
                $lockedRequest->approvals()->where('sequence', $approval->sequence + 1)->update(['status' => 'Menunggu']);
                $lockedRequest->increment('current_approval_step');
            }

            return true;
        });

        if (! $updated) {
            return back()->withErrors(['review' => 'Pengajuan sudah diproses atau tidak lagi dapat ditinjau.'])->withInput();
        }

        return back()->with('status', $data['status'] === 'Disetujui'
            ? 'Persetujuan tahap ini berhasil disimpan.'
            : 'Pengajuan berhasil ditolak.');
    }
}
