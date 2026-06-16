<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read Evaluation|null $evaluation
 * @property-read Student|null $student
 */
class EvaluationDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluation_details';

    protected $fillable = [
        'evaluation_id',
        'student_id',
        'nilai',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
