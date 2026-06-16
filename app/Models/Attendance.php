<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AttendanceDetail> $details
 * @property-read Schedule|null $schedule
 */
class Attendance extends Model
{
    protected $fillable = [
        'schedule_id',
        'academic_year_id',
        'semester',
        'tanggal',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(AttendanceDetail::class, 'attendance_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
