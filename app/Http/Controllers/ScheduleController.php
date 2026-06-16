<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleRequest;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedule = Schedule::with(['teacher', 'subject', 'classroom'])->get();

        return response()->json([
            'status' => true,
            'message' => 'Jadwal mata pelajaran ditemukan',
            'data' => $schedule,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScheduleRequest $request)
    {
        $schedule = Schedule::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Jadwal berhasil ditambahkan',
            'data' => $schedule,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        return response()->json([
            'status' => true,
            'message' => 'Detail jadwal',
            'data' => $schedule->load(['teacher', 'subject', 'classroom']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Jadwal berhasil diperbarui',
            'data' => $schedule,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        if ($schedule->attendances()->exists() || $schedule->evaluations()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Jadwal tidak dapat dihapus karena masih memiliki data absensi atau penilaian.',
            ], 422);
        }

        $schedule->delete();

        return response()->json([
            'status' => true,
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }
}
