<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\OfficeSetting;
use App\Models\User;
use App\Models\LeaveApprovalWorkflow;
use App\Services\LeaveApprovalWorkflowService;
use App\Services\LeaveBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveApprovalWorkflowService $workflows,
        private readonly LeaveBalanceService $leaveBalance,
    ) {}

    public function index(): View
    {
        $employee = Auth::user();
        $offices = OfficeSetting::query()->orderBy('name')->get();
        $supervisorChoiceOfficeIds = [];
        $choiceSupervisors = collect();
        if ($employee->approval_level === 'staff') {
            $supervisorChoiceOfficeIds = $offices->mapWithKeys(function ($office) use ($employee, &$choiceSupervisors) {
                $workflow = $this->workflowFor($office, $employee);
                if (! in_array('supervisor', $workflow?->approval_steps ?? [], true)) {
                    return [];
                }

                $request = new LeaveRequest(['office_id' => $office->id]);
                $request->setRelation('user', $employee);
                $eligible = $this->workflows->eligibleApprovers('supervisor', $request)->whereKeyNot($employee->id)->orderBy('name')->get(['id', 'name']);
                if ($eligible->count() < 2) {
                    return [];
                }
                $choiceSupervisors = $choiceSupervisors->merge($eligible)->unique('id');

                return [$office->id => $eligible->modelKeys()];
            })->all();
        }

        $requests = LeaveRequest::query()
            ->with(['office', 'approvals.reviewer', 'approvals.approver'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('leaves.index', [
            'requests' => $requests,
            'offices' => $offices,
            'supervisors' => $choiceSupervisors,
            'supervisorChoiceOfficeIds' => $supervisorChoiceOfficeIds,
            'types' => LeaveRequest::TYPES,
            'specialTypes' => LeaveRequest::SPECIAL_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'office_id' => ['required', 'integer', 'exists:office_settings,id'],
            'leave_type' => ['required', Rule::in(LeaveRequest::TYPES)],
            'special_type' => [Rule::requiredIf($request->input('leave_type') === 'Cuti khusus'), 'nullable', Rule::in(LeaveRequest::SPECIAL_TYPES)],
            'start_date' => ['required_unless:leave_type,Dinas luar', 'nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['required_unless:leave_type,Dinas luar', 'nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($data['leave_type'] === 'Dinas luar') {
            $data['start_date'] = today()->toDateString();
            $data['end_date'] = $data['start_date'];
        }

        $employee = Auth::user();
        $office = OfficeSetting::query()->findOrFail($data['office_id']);
        $workflow = $this->workflowFor($office, $employee);

        if (! $workflow || empty($workflow->approval_steps)) {
            return back()->withErrors(['approval' => 'Alur persetujuan untuk jabatan, departemen, dan lokasi kerja ini belum diatur. Hubungi superadmin.'])->withInput();
        }

        $leaveRequest = new LeaveRequest(['office_id' => $office->id]);
        $leaveRequest->setRelation('user', $employee);
        $selectedSupervisorId = $this->selectedSupervisorId($request, $leaveRequest, $workflow);
        $approverIds = [];
        foreach ($workflow->approval_steps as $level) {
            if ($level === 'supervisor' && $selectedSupervisorId !== null) {
                $approverIds[] = $selectedSupervisorId;
                continue;
            }
            $approver = $this->workflows->eligibleApprovers($level, $leaveRequest)
                ->whereKeyNot($employee->id)
                ->withCount(['assignedLeaveApprovals as pending_approvals_count' => fn ($query) => $query->where('status', 'Menunggu')])
                ->orderBy('pending_approvals_count')
                ->orderBy('id')
                ->first();
            if (! $approver) {
                $label = LeaveApprovalWorkflowService::label($level);

                return back()->withErrors(['approval' => "Belum ada approver aktif untuk tahap {$label} pada departemen atau lokasi kerja ini."])->withInput();
            }
            $approverIds[] = $approver->id;
        }

        $attachmentPath = $request->file('attachment')?->store('leave-attachments', 'local');

        $balanceError = DB::transaction(function () use ($data, $attachmentPath, $workflow, $approverIds): ?array {
            $employee = User::query()->lockForUpdate()->findOrFail(Auth::id());
            if ($data['leave_type'] === 'Cuti') {
                $insufficient = $this->leaveBalance->firstInsufficientYear($employee, $data['start_date'], $data['end_date']);
                if ($insufficient) {
                    return $insufficient;
                }
            }

            $leaveRequest = LeaveRequest::query()->create([
                'user_id' => Auth::id(),
                'office_id' => $data['office_id'],
                'leave_type' => $data['leave_type'],
                'special_type' => $data['leave_type'] === 'Cuti khusus' ? $data['special_type'] : null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reason' => $data['reason'],
                'attachment_path' => $attachmentPath,
                'status' => 'Menunggu',
                'current_approval_step' => 1,
            ]);

            foreach ($workflow->approval_steps as $index => $level) {
                $leaveRequest->approvals()->create([
                    'sequence' => $index + 1,
                    'approver_level' => $level,
                    'approver_user_id' => $approverIds[$index],
                    'status' => $index === 0 ? 'Menunggu' : 'Belum diproses',
                ]);
            }

            return null;
        });

        if ($balanceError) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }

            return back()->withErrors(['leave_balance' => "Hak cuti tahun {$balanceError['year']} tidak mencukupi. Sisa {$balanceError['remaining']} hari, sedangkan pengajuan ini menggunakan {$balanceError['requested']} hari."])->withInput();
        }

        return to_route('leaves.index')->with('status', 'Pengajuan berhasil dikirim dan menunggu persetujuan.');
    }

    public function update(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless((int) $leaveRequest->user_id === (int) Auth::id(), 404);
        abort_unless($this->canEdit($leaveRequest), 403);

        $data = $request->validateWithBag('edit', [
            'edit_request_id' => ['required', 'integer', 'in:'.$leaveRequest->id],
            'office_id' => ['required', 'integer', 'exists:office_settings,id'],
            'leave_type' => ['required', Rule::in(LeaveRequest::TYPES)],
            'special_type' => [Rule::requiredIf($request->input('leave_type') === 'Cuti khusus'), 'nullable', Rule::in(LeaveRequest::SPECIAL_TYPES)],
            'start_date' => ['required_unless:leave_type,Dinas luar', 'nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['required_unless:leave_type,Dinas luar', 'nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($data['leave_type'] === 'Dinas luar') {
            $data['start_date'] = today()->toDateString();
            $data['end_date'] = $data['start_date'];
        }

        $employee = Auth::user();
        $office = OfficeSetting::query()->findOrFail($data['office_id']);
        $workflow = $this->workflowFor($office, $employee);
        if (! $workflow || empty($workflow->approval_steps)) {
            return back()->withErrors(['approval' => 'Alur persetujuan untuk jabatan, departemen, dan lokasi kerja ini belum tersedia. Hubungi superadmin.'], 'edit')->withInput();
        }

        $editedRequest = new LeaveRequest(['office_id' => $office->id]);
        $editedRequest->setRelation('user', $employee);
        $selectedSupervisorId = $this->selectedSupervisorId($request, $editedRequest, $workflow, 'edit');
        $approverIds = [];
        foreach ($workflow->approval_steps as $level) {
            if ($level === 'supervisor' && $selectedSupervisorId !== null) {
                $approverIds[] = $selectedSupervisorId;
                continue;
            }
            $approver = $this->workflows->eligibleApprovers($level, $editedRequest)
                ->whereKeyNot($employee->id)
                ->withCount(['assignedLeaveApprovals as pending_approvals_count' => fn ($query) => $query->where('status', 'Menunggu')])
                ->orderBy('pending_approvals_count')
                ->orderBy('id')
                ->first();
            if (! $approver) {
                return back()->withErrors(['approval' => 'Approver aktif untuk alur lokasi kerja yang dipilih belum tersedia. Hubungi superadmin.'], 'edit')->withInput();
            }
            $approverIds[] = $approver->id;
        }

        $newAttachmentPath = $request->file('attachment')?->store('leave-attachments', 'local');
        $oldAttachmentPath = $leaveRequest->attachment_path;
        $updateResult = DB::transaction(function () use ($leaveRequest, $data, $workflow, $approverIds, $newAttachmentPath): bool|array {
            $lockedRequest = LeaveRequest::query()->lockForUpdate()->findOrFail($leaveRequest->id);
            if (! $this->canEdit($lockedRequest)) {
                return false;
            }

            $employee = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);
            if ($data['leave_type'] === 'Cuti') {
                $insufficient = $this->leaveBalance->firstInsufficientYear($employee, $data['start_date'], $data['end_date'], $lockedRequest->id);
                if ($insufficient) {
                    return $insufficient;
                }
            }

            $lockedRequest->update([
                'office_id' => $data['office_id'],
                'leave_type' => $data['leave_type'],
                'special_type' => $data['leave_type'] === 'Cuti khusus' ? $data['special_type'] : null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reason' => $data['reason'],
                'attachment_path' => $newAttachmentPath ?? $lockedRequest->attachment_path,
                'current_approval_step' => 1,
            ]);

            $lockedRequest->approvals()->delete();
            foreach ($workflow->approval_steps as $index => $level) {
                $lockedRequest->approvals()->create([
                    'sequence' => $index + 1,
                    'approver_level' => $level,
                    'approver_user_id' => $approverIds[$index],
                    'status' => $index === 0 ? 'Menunggu' : 'Belum diproses',
                ]);
            }

            return true;
        });

        if (! $updateResult) {
            if ($newAttachmentPath) {
                Storage::disk('local')->delete($newAttachmentPath);
            }

            return back()->withErrors(['edit' => 'Pengajuan sudah mulai diproses dan tidak dapat diedit lagi.'])->withInput();
        }

        if (is_array($updateResult)) {
            if ($newAttachmentPath) {
                Storage::disk('local')->delete($newAttachmentPath);
            }

            return back()->withErrors(['leave_balance' => "Hak cuti tahun {$updateResult['year']} tidak mencukupi. Sisa {$updateResult['remaining']} hari, sedangkan pengajuan ini menggunakan {$updateResult['requested']} hari."], 'edit')->withInput();
        }

        if ($newAttachmentPath && $oldAttachmentPath) {
            Storage::disk('local')->delete($oldAttachmentPath);
        }

        return to_route('leaves.index')->with('status', 'Pengajuan izin berhasil diperbarui.');
    }

    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless((int) $leaveRequest->user_id === (int) Auth::id(), 404);

        $cancelled = DB::transaction(function () use ($leaveRequest): bool {
            $lockedRequest = LeaveRequest::query()->lockForUpdate()->findOrFail($leaveRequest->id);
            if ($lockedRequest->status !== 'Menunggu') {
                return false;
            }

            $lockedRequest->approvals()
                ->whereIn('status', ['Menunggu', 'Belum diproses'])
                ->update(['status' => 'Dibatalkan']);
            $lockedRequest->update([
                'status' => 'Dibatalkan',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            return true;
        });

        if (! $cancelled) {
            return back()->withErrors(['cancel' => 'Pengajuan yang sudah diputuskan tidak dapat dibatalkan.']);
        }

        return to_route('leaves.index')->with('status', 'Pengajuan izin berhasil dibatalkan.');
    }

    private function canEdit(LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->status === 'Menunggu'
            && (int) $leaveRequest->current_approval_step === 1
            && ! $leaveRequest->approvals()->whereNotNull('reviewed_at')->exists();
    }

    public function downloadAttachment(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $canReview = $user->canApproveLeaveRequests()
            && $user->canAccessMenu('approvals')
            && ($this->workflows->actionableRequests($user)->whereKey($leaveRequest->id)->exists()
                || $leaveRequest->approvals()->where('reviewed_by', $user->id)->exists());
        $canReadAsEmployee = (int) Auth::id() === $leaveRequest->user_id && $user->canAccessMenu('leaves');
        $canReadAsAdmin = $user->canAccessMenu('report_leaves');
        abort_unless(
            $canReadAsEmployee || $canReadAsAdmin || $canReview,
            403
        );
        abort_unless($leaveRequest->attachment_path && Storage::disk('local')->exists($leaveRequest->attachment_path), 404);

        return Storage::disk('local')->download($leaveRequest->attachment_path);
    }

    private function workflowFor(OfficeSetting $office, User $employee): ?LeaveApprovalWorkflow
    {
        $query = LeaveApprovalWorkflow::query()
            ->where('workplace_type', $office->workplace_type)
            ->where('requester_level', $employee->approval_level);

        if ($office->workplace_type === 'kantor' && $employee->department_id) {
            $departmentWorkflow = (clone $query)->where('department_id', $employee->department_id)->first();
            if ($departmentWorkflow && ! empty($departmentWorkflow->approval_steps)) {
                return $departmentWorkflow;
            }
        }

        return $query->whereNull('department_id')->first();
    }

    private function selectedSupervisorId(Request $request, LeaveRequest $leaveRequest, LeaveApprovalWorkflow $workflow, ?string $errorBag = null): ?int
    {
        if ($leaveRequest->user->approval_level !== 'staff' || ! in_array('supervisor', $workflow->approval_steps ?? [], true)) {
            return null;
        }

        $supervisors = $this->workflows->eligibleApprovers('supervisor', $leaveRequest)
            ->whereKeyNot($leaveRequest->user_id ?? $leaveRequest->user->id)
            ->orderBy('name')
            ->get();

        if ($supervisors->count() === 1) {
            return (int) $supervisors->first()->id;
        }

        if ($supervisors->count() < 2) {
            return null;
        }

        $rules = ['supervisor_id' => ['required', 'integer', Rule::in($supervisors->modelKeys())]];
        if ($errorBag) {
            $request->validateWithBag($errorBag, $rules, ['supervisor_id.required' => 'Pilih atasan langsung yang akan menyetujui pengajuan.']);
        } else {
            $request->validate($rules, ['supervisor_id.required' => 'Pilih atasan langsung yang akan menyetujui pengajuan.']);
        }

        return (int) $request->input('supervisor_id');
    }
}
