<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EvaluationDetail> $details
 * @property-read Subject|null $subject
 * @property-read Classroom|null $classroom
 * @property-read Schedule|null $schedule
 */
class Evaluation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'subject_id',
        'classroom_id',
        'teacher_id',
        'academic_year_id',
        'semester',
        'jenis',
        'nama_penilaian',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(EvaluationDetail::class, 'evaluation_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
