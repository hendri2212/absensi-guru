<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherId = $this->route('guru');
        $userId = $teacherId ? \App\Models\Teacher::find($teacherId)?->user_id : null;

        return [
            'nip' => 'required|unique:teachers,nip,'.$teacherId,
            'username' => 'required|unique:users,username,'.$userId,
            'nama_guru' => 'required|string',
            'password' => $teacherId ? 'nullable|min:6' : 'required|min:6',
            'jk' => 'required|in:L,P',
            'agama' => 'nullable|string',
            'tgl_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'school_id' => 'nullable|exists:schools,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'nama_guru.required' => 'Nama guru wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'jk.required' => 'Jenis kelamin wajib dipilih.',
        ];
    }
}
