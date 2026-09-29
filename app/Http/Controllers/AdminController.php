<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalClasses = SchoolClass::count();
        $totalStudents = Student::count();

        // Get today's delay logs with count
        $todayLogs = \App\Models\DelayLog::with(['student' => function ($query) {
                $query->withCount('activeDelayLogs')->with('schoolClass');
            }])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'totalUsers' => $totalUsers,
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'todayLogs' => $todayLogs,
        ]);
    }

    public function backupDaily()
    {
        // Generate filename: Backup_Data_Hari_Ini_Selasa_29_Sep_2026_Jam_13_17.xlsx
        $hari = \Carbon\Carbon::now()->isoFormat('dddd');
        $tanggal = \Carbon\Carbon::now()->isoFormat('D_MMM_Y');
        $jam = \Carbon\Carbon::now()->format('H_i');
        
        $filename = "Backup_Data_Hari_Ini_{$hari}_{$tanggal}_Jam_{$jam}.xlsx";

        $logs = \App\Models\DelayLog::with(['student.schoolClass'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'Tanggal' => $log->created_at->format('d-m-Y'),
                    'Hari' => $log->created_at->isoFormat('dddd'),
                    'Jam' => $log->created_at->format('H:i:s'),
                    'NISN' => $log->student->nisn ?? '-',
                    'Nama Siswa' => $log->student->name ?? '-',
                    'Kelas' => $log->student->schoolClass->name ?? '-',
                    'Alasan' => $log->reason,
                    'Petugas (Guru Piket)' => $log->reporter_name ?? '-',
                ];
            });

        return (new \Rap2hpoutre\FastExcel\FastExcel($logs))->download($filename);
    }

    public function backupDatabase()
    {
        return back()->with('error', 'Fitur ini dinonaktifkan di versi Online (Vercel). Database Anda sekarang menggunakan Supabase (PostgreSQL). Silakan buka Dashboard Supabase Anda untuk melakukan backup database yang jauh lebih aman dan otomatis.');
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'nip' => 'nullable|string|max:50|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,guru_piket,wali_kelas,kepala_sekolah,guru_bk',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'nip' => $request->nip,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }
    public function updateUser(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'nip' => 'nullable|string|max:50|unique:users,nip,' . $user->id,
            'role' => 'required|in:admin,guru_piket,wali_kelas,kepala_sekolah,guru_bk',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8';
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->nip = $request->nip;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        // Don't allow self-deletion
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }
    public function classes()
    {
        $classes = SchoolClass::with('waliKelas')->withCount('students')->get();
        $waliKelasList = User::where('role', 'wali_kelas')->get();

        return Inertia::render('Admin/Classes', [
            'classes' => $classes,
            'waliKelasList' => $waliKelasList,
        ]);
    }

    public function storeClass(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        SchoolClass::create($request->only('name', 'wali_kelas_id'));

        return redirect()->back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function updateClass(Request $request, SchoolClass $schoolClass)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $schoolClass->update($request->only('name', 'wali_kelas_id'));

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroyClass(SchoolClass $schoolClass)
    {
        if ($schoolClass->students()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal: Kelas ini masih memiliki ' . $schoolClass->students()->count() . ' siswa. Pindahkan atau hapus siswa tersebut terlebih dahulu.');
        }

        $schoolClass->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function students(Request $request)
    {
        $query = Student::with('schoolClass')->orderBy('created_at', 'desc');

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        $students = $query->get();
        $classes = SchoolClass::all();

        return Inertia::render('Admin/Students', [
            'students' => $students,
            'classes' => $classes,
            'filters' => $request->only(['class_id']),
        ]);
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|unique:students,nisn|max:255',
            'name' => 'required|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'no_wa_ortu' => 'nullable|string|max:20',
        ]);

        Student::create($request->only('nisn', 'name', 'school_class_id', 'no_wa_ortu'));

        return redirect()->back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function updateStudent(Request $request, Student $student)
    {
        $request->validate([
            'nisn' => 'required|string|max:255|unique:students,nisn,' . $student->id,
            'name' => 'required|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'no_wa_ortu' => 'nullable|string|max:20',
        ]);

        $student->update($request->only('nisn', 'name', 'school_class_id', 'no_wa_ortu'));

        return redirect()->back()->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroyStudent(Student $student)
    {
        $student->delete();

        return redirect()->back()->with('success', 'Siswa berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return redirect()->back()->with('success', 'Password pengguna berhasil di-reset.');
    }

    public function bulkActionStudents(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'action' => 'required|in:pindah_kelas,jadikan_alumni',
            'target_class_id' => 'required_if:action,pindah_kelas|nullable|exists:school_classes,id',
        ]);

        if ($request->action === 'pindah_kelas') {
            Student::whereIn('id', $request->student_ids)->update([
                'school_class_id' => $request->target_class_id,
            ]);
            $msg = count($request->student_ids) . ' siswa berhasil dipindahkan kelasnya.';
        } else {
            Student::whereIn('id', $request->student_ids)->update([
                'status' => 'alumni',
            ]);
            $msg = count($request->student_ids) . ' siswa berhasil dijadikan alumni.';
        }

        return redirect()->back()->with('success', $msg);
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:10240',
        ]);

        try {
            $collection = (new \Rap2hpoutre\FastExcel\FastExcel)->import($request->file('file'));
            $successCount = 0;
            $failedRows = [];

            // Get all existing NISNs to check for duplicates quickly
            $existingNisns = Student::pluck('nisn')->toArray();

            foreach ($collection as $row) {
                $nisn = $row['NISN'] ?? $row['nisn'] ?? null;
                $nama = $row['Nama'] ?? $row['nama'] ?? null;
                $kelasName = $row['Kelas'] ?? $row['kelas'] ?? null;
                $noWa = $row['No WA'] ?? $row['no_wa'] ?? null;

                $namaLengkap = $nama ?: 'TIDAK ADA NAMA';
                $kelasLengkap = $kelasName ?: 'TIDAK ADA KELAS';

                if (!$nisn) {
                    $failedRows[] = [
                        'nama' => $namaLengkap,
                        'kelas' => $kelasLengkap,
                        'alasan' => 'NISN belum ada/kosong'
                    ];
                    continue;
                }

                if (in_array((string) $nisn, $existingNisns)) {
                    $failedRows[] = [
                        'nama' => $namaLengkap,
                        'kelas' => $kelasLengkap,
                        'alasan' => 'NISN bentrok (Sudah ada di database)'
                    ];
                    continue;
                }

                if (!$nama || !$kelasName) {
                    $failedRows[] = [
                        'nama' => $namaLengkap,
                        'kelas' => $kelasLengkap,
                        'alasan' => 'Data Nama atau Kelas kosong'
                    ];
                    continue;
                }

                // Find or create class
                $class = SchoolClass::firstOrCreate(['name' => $kelasName]);

                // Create student
                Student::create([
                    'nisn' => $nisn,
                    'name' => $nama,
                    'school_class_id' => $class->id,
                    'no_wa_ortu' => $noWa,
                    'status' => 'active',
                ]);

                $existingNisns[] = (string) $nisn; // Add to existing to prevent duplicates within the file itself
                $successCount++;
            }

            return redirect()->back()->with([
                'success' => $successCount . ' data siswa berhasil diimpor.',
                'import_anomalies' => $failedRows
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }
}
