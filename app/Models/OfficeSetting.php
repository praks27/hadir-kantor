<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeSetting extends Model
{
    protected $fillable = [
        'name',
        'workplace_type',
        'latitude',
        'longitude',
        'radius_meters',
        'start_time',
        'end_time',
        'grace_minutes',
        'work_days',
    ];

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'work_days' => 'array'];
    }

    public static function current(): self
    {
        return self::query()->first() ?? self::defaults();
    }

    public static function defaults(): self
    {
        return new self([
            'name' => config('attendance.office.name'),
            'latitude' => config('attendance.office.latitude'),
            'longitude' => config('attendance.office.longitude'),
            'radius_meters' => config('attendance.office.radius_meters'),
            'start_time' => config('attendance.office.start_time'),
            'end_time' => config('attendance.office.end_time'),
            'grace_minutes' => config('attendance.office.grace_minutes'),
            'work_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
        ]);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'office_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'office_id');
    }
}
