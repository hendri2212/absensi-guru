<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sesuaikan jika ada logic permission
    }

    public function rules(): array
    {
        return [
            'schedule_id' => 'required|exists:schedules,id',
            'absensi' => 'required|array',
            'absensi.*.student_id' => 'required|exists:students,id',
            'absensi.*.status' => 'required|in:Sakit,Izin,Alpa,Hadir',
        ];
    }
}
