<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => 'sometimes|required|exists:schedules,id',
            'subject_id' => 'sometimes|required|exists:subjects,id',
            'jenis' => 'required|in:Tugas,UH,UTS,UAS',
            'nama_penilaian' => 'required|string|max:30',
            'tanggal' => 'required|date',
            'penilaian' => 'required|array',
            'penilaian.*.student_id' => 'required|exists:students,id',
            'penilaian.*.nilai' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
