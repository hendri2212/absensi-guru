<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Teacher|null $teacher
 * @property-read Subject|null $subject
 * @property-read Classroom|null $classroom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Evaluation> $evaluations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attendance> $attendances
 * @property bool $sudah_absen
 * @property \Illuminate\Database\Eloquent\Collection<int, Evaluation> $all_evaluations
 */
class Schedule extends Model
{
    protected $fillable = [
        'academic_year_id',
        'semester',
        'teacher_id',
        'subject_id',
        'classroom_id',
        'hari',
        'jam_mulai',
        'jam_habis',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(\App\Models\Evaluation::class, 'subject_id', 'subject_id')
            ->where('classroom_id', $this->classroom_id)
            ->latest('tanggal');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'schedule_id');
    }
}
