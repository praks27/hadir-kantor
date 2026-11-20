<?php

namespace App\Services;

use App\Models\MenuAccessSetting;
use App\Models\EmployeeRole;
use App\Models\User;

class MenuAccessService
{
    public const MENUS = [
        'attendance' => 'Absensi dan riwayat absensi',
        'leaves' => 'Pengajuan perizinan',
        'approvals' => 'Persetujuan izin',
        'report_overview' => 'Laporan: Ringkasan',
        'report_leaves' => 'Laporan: Pengajuan izin karyawan',
        'report_attendance' => 'Laporan: Absensi karyawan',
    ];

    public const MENU_DESCRIPTIONS = [
        'attendance' => 'Mencatat absensi masuk/pulang dan melihat riwayat absensi sendiri.',
        'leaves' => 'Membuat pengajuan izin, melihat riwayat, mengedit, dan membatalkan pengajuan sendiri.',
        'approvals' => 'Melihat dan memproses pengajuan yang ditugaskan kepada approver.',
        'report_overview' => 'Melihat ringkasan jumlah karyawan, absensi, dan pengajuan izin.',
        'report_leaves' => 'Melihat, mencetak, dan mengekspor laporan pengajuan izin karyawan.',
        'report_attendance' => 'Melihat, mencetak, dan mengekspor laporan absensi karyawan.',
    ];

    public function managedRoles(): array
    {
        return [
            'karyawan' => 'Karyawan',
            'admin' => 'Admin',
            ...EmployeeRole::query()->orderBy('name')->pluck('name', 'key')->all(),
        ];
    }

    private array $resolvedPermissions = [];

    public function allows(User $user, string $menu): bool
    {
        if (! array_key_exists($menu, self::MENUS)) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        if ($menu === 'leaves' && $user->approval_level === 'director') {
            return false;
        }

        $permissions = $this->permissionsFor($user);

        return (bool) ($permissions[$menu] ?? false);
    }

    public function permissionsFor(User $user): array
    {
        if (isset($this->resolvedPermissions[$user->id])) {
            return $this->resolvedPermissions[$user->id];
        }

        $defaults = $this->permissionsForRole($user->role);
        $userPermissions = MenuAccessSetting::query()
            ->where('scope_type', 'user')
            ->where('scope_key', (string) $user->id)
            ->first()?->permissions ?? [];

        foreach (self::MENUS as $key => $_label) {
            if (array_key_exists($key, $userPermissions) && $userPermissions[$key] !== null) {
                $defaults[$key] = (bool) $userPermissions[$key];
            } elseif (str_starts_with($key, 'report_') && array_key_exists('reports', $userPermissions) && $userPermissions['reports'] !== null) {
                $defaults[$key] = (bool) $userPermissions['reports'];
            }
        }

        return $this->resolvedPermissions[$user->id] = $defaults;
    }

    public function permissionsForRole(string $role): array
    {
        $permissions = $this->defaultPermissions($role);
        $overrides = MenuAccessSetting::query()
            ->where('scope_type', 'role')
            ->where('scope_key', $role)
            ->first()?->permissions ?? [];

        foreach (self::MENUS as $key => $_label) {
            $value = array_key_exists($key, $overrides)
                ? $overrides[$key]
                : (str_starts_with($key, 'report_') && array_key_exists('reports', $overrides)
                    ? $overrides['reports']
                    : $permissions[$key]);
            $permissions[$key] = (bool) $value;
        }

        return $permissions;
    }

    public function defaultPermissions(string $role): array
    {
        $defaults = array_fill_keys(array_keys(self::MENUS), false);
        if ($role !== 'superadmin') {
            $defaults['attendance'] = true;
            $defaults['leaves'] = true;
            $defaults['approvals'] = true;
        }
        if ($role === 'admin') {
            $defaults['report_overview'] = true;
            $defaults['report_leaves'] = true;
            $defaults['report_attendance'] = true;
        }

        return $defaults;
    }

    public function forget(?int $userId = null): void
    {
        if ($userId === null) {
            $this->resolvedPermissions = [];

            return;
        }

        unset($this->resolvedPermissions[$userId]);
    }
}
