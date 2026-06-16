<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $years = AcademicYear::orderBy('id', 'desc')->get();

        return view('admin.tahun-ajaran.index', compact('years'));
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
    public function store(AcademicYearRequest $request)
    {
        $validated = $request->validated();

        AcademicYear::create([
            'tahun' => $validated['tahun'],
            'semester' => $validated['semester'],
            'is_active' => false,
        ]);

        return back()->with('success', 'Tahun akademik berhasil ditambah!');
    }

    public function activate($id)
    {
        AcademicYear::where('is_active', true)->update(['is_active' => false]);

        $year = AcademicYear::findOrFail($id);
        $year->update(['is_active' => true]);

        return back()->with('success', 'Tahun akademik aktif berhasil diganti!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
