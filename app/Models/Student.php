<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $nama
 * @property string $status
 * @property int $classroom_id
 * @property-read Classroom|null $classroom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EvaluationDetail> $evaluations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AttendanceDetail> $attendances
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudentClassHistory> $classHistories
 * @property mixed $nilai_saat_ini
 *
 * @method static Builder|Student aktif()
 * @method static Builder|Student lulus()
 */
class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'nama',
        'agama',
        'jk',
        'tgl_lahir',
        'nis',
        'alamat',
        'no_telp',
        'no_telp_ortu',
        'classroom_id',
        'status',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeLulus(Builder $query): Builder
    {
        return $query->where('status', 'lulus');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(EvaluationDetail::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(AttendanceDetail::class);
    }

    public function classHistories(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class)->latest();
    }
}
