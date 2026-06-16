<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $tingkat
 * @property string $paralel
 * @property-read string $nama_kelas
 * @property-read Teacher|null $teacher
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Schedule> $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Student> $students
 */
class Classroom extends Model
{
    protected $fillable = ['tingkat', 'paralel', 'walas_id'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'walas_id');
    }

    public function getNamaKelasAttribute(): string
    {
        return $this->tingkat . ' ' . $this->paralel;
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'classroom_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class)->where('status', 'aktif');
    }
}
