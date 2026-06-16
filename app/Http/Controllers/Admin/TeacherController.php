<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $teachers = Teacher::with('user')
            ->when($search, function ($query, $search) {
                $query->where('nama_guru', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama_guru', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.guru.index', compact('teachers', 'search'));
    }

    public function create()
    {
        return view('admin.guru.create');
    }

    public function store(TeacherRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['nama_guru'],
                    'username' => $validated['username'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'Guru',
                ]);

                Teacher::create([
                    'user_id' => $user->id,
                    'nama_guru' => $validated['nama_guru'],
                    'nip' => $validated['nip'],
                    'jk' => $validated['jk'],
                    'agama' => $validated['agama'] ?? null,
                    'tgl_lahir' => $validated['tgl_lahir'] ?? null,
                    'alamat' => $validated['alamat'] ?? null,
                    'no_telp' => $validated['no_telp'] ?? null,
                    'school_id' => $validated['school_id'] ?? 1,
                ]);
            });

            return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil didaftarkan.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: '.$e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);

        return view('admin.guru.edit', compact('teacher'));
    }

    public function update(TeacherRequest $request, $id)
    {
        $validated = $request->validated();
        $teacher = Teacher::findOrFail($id);
        $user = User::findOrFail($teacher->user_id);

        try {
            DB::transaction(function () use ($validated, $teacher, $user) {
                $userData = ['username' => $validated['username']];

                if (! empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $user->update($userData);

                $teacher->update([
                    'nama_guru' => $validated['nama_guru'],
                    'nip' => $validated['nip'],
                    'jk' => $validated['jk'],
                    'agama' => $validated['agama'] ?? null,
                    'tgl_lahir' => $validated['tgl_lahir'] ?? null,
                    'alamat' => $validated['alamat'] ?? null,
                    'no_telp' => $validated['no_telp'] ?? null,
                ]);
            });

            return redirect()->route('admin.guru.index')->with('success', 'Data Guru berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal update: '.$e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $teacher = Teacher::findOrFail($id);
            $user = User::findOrFail($teacher->user_id);

            DB::transaction(function () use ($teacher, $user) {
                // hapus data guru
                $teacher->delete();
                // hapus akun
                $user->delete();
            });

            return redirect()->route('admin.guru.index')->with('success', 'Data Guru dan Akun berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
