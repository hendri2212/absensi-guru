<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.backup.index');
    }

    public function download()
    {
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $filename  = 'backup_' . $database . '_' . now()->format('Ymd_His') . '.sql';
        $dumpPath  = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

        $command = "\"{$dumpPath}\" --host={$host} --port={$port} --user={$username} "
            . ($password ? "--password={$password} " : '')
            . "{$database}";

        $output = shell_exec($command);

        if (! $output) {
            return redirect()->back()->with('error', 'Gagal membuat backup. Pastikan mysqldump tersedia.');
        }

        return response($output, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt',
        ]);

        $sql = file_get_contents($request->file('backup_file')->getRealPath());

        if (! $sql) {
            return redirect()->back()->with('error', 'File backup kosong atau tidak valid.');
        }

        try {
            // Pecah per statement dan eksekusi satu-satu
            $statements = array_filter(
                array_map('trim', explode(";\n", $sql)),
                fn($s) => $s !== ''
            );

            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Database berhasil direstore. Silakan login kembali.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal restore: ' . $e->getMessage());
        }
    }
}
