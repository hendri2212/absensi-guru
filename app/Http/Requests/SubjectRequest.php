<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'nama_mapel' => 'required|string|unique:subjects,nama_mapel,'.$subjectId,
        ];
    }

    public function messages(): array
    {
        return [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'nama_mapel.string' => 'Nama mata pelajaran harus berupa teks.',
            'nama_mapel.unique' => 'Nama mata pelajaran sudah ada. Silakan gunakan nama lain.',
        ];
    }
}
