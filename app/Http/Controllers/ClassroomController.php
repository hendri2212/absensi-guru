<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassroomRequest;
use App\Models\Classroom;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classroom = Classroom::with('teacher')
            // ->orderBy('tingkat', 'asc')
            // ->orderBy('paralel', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar kelas ditemukan',
            'data' => $classroom,
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
    public function store(ClassroomRequest $request)
    {
        $classroom = Classroom::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Kelas berhasil dibuat',
            'data' => $classroom,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $classroom)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data kelas ditemukan',
            'data' => $classroom,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classroom $classroom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClassroomRequest $request, Classroom $classroom)
    {
        $classroom->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Data kelas berhasil diupdate',
            'data' => $classroom,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom)
    {
        if ($classroom->students()->exists() || $classroom->schedules()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Kelas tidak dapat dihapus karena masih memiliki siswa atau jadwal aktif.',
            ], 422);
        }

        $classroom->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kelas '.$classroom->tingkat.'-'.$classroom->paralel.' berhasil dihapus.',
        ]);
    }
}
