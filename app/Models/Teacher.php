<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read User|null $user
 * @property-read School|null $school
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Schedule> $schedules
 */
class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'nama_guru',
        'agama',
        'nip',
        'jk',
        'tgl_lahir',
        'alamat',
        'no_telp',
        'school_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }
}
