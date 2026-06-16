<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcademicYearRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tahun' => 'required|string|max:10',
            'semester' => 'required|in:Ganjil,Genap',
        ];
    }

    public function messages(): array
    {
        return [
            'tahun.required' => 'Tahun akademik wajib diisi.',
            'semester.required' => 'Pilih salah satu semester.',
            'semester.in' => 'Format semester tidak valid.',
        ];
    }
}
