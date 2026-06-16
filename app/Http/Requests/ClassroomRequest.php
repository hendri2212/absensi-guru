<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Mendapatkan ID dari route untuk pengecekan unik saat update
        $classroomId = $this->route('classroom')?->id;

        return [
            'tingkat' => 'required|in:VII,VIII,IX',
            'paralel' => [
                'required',
                'in:A,B,C,D,E,F,G,H',
                // Validasi unik kombinasi tingkat dan paralel
                Rule::unique('classrooms')->where(function ($query) {
                    return $query->where('tingkat', $this->tingkat);
                })->ignore($classroomId),
            ],
            'walas_id' => 'required|exists:teachers,id',
        ];
    }
}
