<?php

namespace App\Http\Controllers;

use App\Exports\RekapNilaiExport;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    private function getTeacherId()
    {
        $user    = Auth::user();
        $teacher = $user->teacher ?? \App\Models\Teacher::where('user_id', $user->id)->first();

        return $teacher ? $teacher->id : abort(403, 'User tidak terhubung ke data Guru.');
    }

    public function rekapNilai($schedule_id)
    {
        $teacherId = $this->getTeacherId();

        // Pastikan schedule milik guru ini
        $schedule = Schedule::where('id', $schedule_id)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        $mapel = $schedule->subject->nama_mapel ?? 'mapel';
        $kelas = optional($schedule->classroom)->tingkat . '-' . optional($schedule->classroom)->paralel;
        $filename = "Rekap_{$mapel}_{$kelas}.xlsx";

        return Excel::download(new RekapNilaiExport($schedule_id, $teacherId), $filename);
    }
}
