<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student'); // Jika route menggunakan ID, cukup ambil langsung

        return [
            'nama' => 'required|string|max:100',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu',
            'jk' => 'required|in:L,P',
            'tgl_lahir' => 'required|date',
            'nis' => 'required|max:10|unique:students,nis,'.$studentId,
            'alamat' => 'required|string',
            'no_telp' => 'required|string|max:20',
            'no_telp_ortu' => 'required|string|max:20',
        ];
    }
}
