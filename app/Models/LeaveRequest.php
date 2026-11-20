<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    public const TYPES = ['Cuti', 'Izin', 'Izin sakit', 'Libur pengganti', 'Cuti khusus', 'Dinas luar'];

    public const SPECIAL_TYPES = ['Menikah', 'Melahirkan', 'Orang tua meninggal'];

    public const STATUSES = ['Menunggu', 'Disetujui', 'Ditolak', 'Dibatalkan'];

    protected $fillable = [
        'user_id',
        'office_id',
        'leave_type',
        'special_type',
        'start_date',
        'end_date',
        'reason',
        'attachment_path',
        'status',
        'current_approval_step',
        'reviewed_by',
        'review_note',
        'reviewed_at',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(OfficeSetting::class, 'office_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveRequestApproval::class)->orderBy('sequence');
    }
}
