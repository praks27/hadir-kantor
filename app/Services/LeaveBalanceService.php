<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\UserAnnualLeaveEntitlement;
use App\Models\User;
use Illuminate\Support\Carbon;

class LeaveBalanceService
{
    public function entitlement(User $user, int $year): int
    {
        return (int) (UserAnnualLeaveEntitlement::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->value('days') ?? 12);
    }

    public function usedDays(User $user, int $year, ?int $exceptRequestId = null): int
    {
        $yearStart = Carbon::create($year, 1, 1)->toDateString();
        $yearEnd = Carbon::create($year, 12, 31)->toDateString();

        return (int) LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('leave_type', 'Cuti')
            ->whereIn('status', ['Menunggu', 'Disetujui'])
            ->where('start_date', '<=', $yearEnd)
            ->where('end_date', '>=', $yearStart)
            ->when($exceptRequestId, fn ($query) => $query->whereKeyNot($exceptRequestId))
            ->get(['start_date', 'end_date'])
            ->sum(fn (LeaveRequest $request) => $this->daysInYear($request->start_date, $request->end_date, $year));
    }

    public function remainingDays(User $user, int $year): int
    {
        return max(0, $this->entitlement($user, $year) - $this->usedDays($user, $year));
    }

    public function summary(User $user, int $year): array
    {
        $entitlement = $this->entitlement($user, $year);
        $used = $this->usedDays($user, $year);

        return [
            'year' => $year,
            'entitlement' => $entitlement,
            'used' => $used,
            'remaining' => max(0, $entitlement - $used),
        ];
    }

    public function firstInsufficientYear(User $user, string $startDate, string $endDate, ?int $exceptRequestId = null): ?array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        for ($year = $start->year; $year <= $end->year; $year++) {
            $daysRequested = $this->daysInYear($start, $end, $year);
            if ($daysRequested === 0) {
                continue;
            }

            $remaining = max(0, $this->entitlement($user, $year) - $this->usedDays($user, $year, $exceptRequestId));
            if ($daysRequested > $remaining) {
                return ['year' => $year, 'requested' => $daysRequested, 'remaining' => $remaining];
            }
        }

        return null;
    }

    private function daysInYear(Carbon|string $startDate, Carbon|string $endDate, int $year): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->startOfDay();
        $from = $start->greaterThan($yearStart) ? $start : $yearStart;
        $through = $end->lessThan($yearEnd) ? $end : $yearEnd;

        return $from->greaterThan($through) ? 0 : (int) $from->diffInDays($through) + 1;
    }
}
