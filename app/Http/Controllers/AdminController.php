<?php

namespace App\Http\Controllers;

use App\Models\CounselingLog;
use App\Models\DelayLog;
use App\Models\LoginLog;
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
        $totalCancelledLogs = DelayLog::onlyTrashed()->count();
        $todayCancelledLogs = DelayLog::onlyTrashed()->whereDate('deleted_at', today())->count();
        $todayLoginLogs = LoginLog::whereDate('created_at', today())->count();
        $failedLoginAttemptsToday = LoginLog::whereDate('created_at', today())->where('status', 'failed')->count();
        $totalCounselingLogs = CounselingLog::count();
        $todayCounselingLogs = CounselingLog::whereDate('created_at', today())->count();

        // Get today's delay logs with count
        $todayLogs = DelayLog::with(['student' => function ($query) {
                $query->withCount('activeDelayLogs')->with('schoolClass');
            }])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'totalUsers' => $totalUsers,
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'totalCancelledLogs' => $totalCancelledLogs,
            'todayCancelledLogs' => $todayCancelledLogs,
            'todayLoginLogs' => $todayLoginLogs,
            'failedLoginAttemptsToday' => $failedLoginAttemptsToday,
            'totalCounselingLogs' => $totalCounselingLogs,
            'todayCounselingLogs' => $todayCounselingLogs,
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

    public function cancellationLogs(Request $request)
    {
        $query = DelayLog::onlyTrashed()
            ->with(['student.schoolClass', 'deletedByUser'])
            ->orderBy('deleted_at', 'desc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                       ->orWhereRaw('LOWER(nisn) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereRaw('LOWER(deleter_name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(deletion_reason) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(deletion_device) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(deletion_ip) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('deleted_at', $request->date);
        }

        $logs = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/CancellationLogs', [
            'cancellationLogs' => $logs,
            'filters' => [
                'search' => $request->search ?? '',
                'date' => $request->date ?? '',
            ],
            'stats' => [
                'total' => DelayLog::onlyTrashed()->count(),
                'today' => DelayLog::onlyTrashed()->whereDate('deleted_at', today())->count(),
            ],
        ]);
    }

    public function restoreCancellationLog($id)
    {
        $log = DelayLog::onlyTrashed()->findOrFail($id);
        $studentName = $log->student?->name ?? 'Siswa';
        $log->restore();

        return redirect()->back()->with('success', "Log keterlambatan untuk {$studentName} berhasil dipulihkan!");
    }

    public function forceDeleteCancellationLog($id)
    {
        $log = DelayLog::onlyTrashed()->findOrFail($id);
        $studentName = $log->student?->name ?? 'Siswa';
        $log->forceDelete();

        return redirect()->back()->with('success', "Log keterlambatan untuk {$studentName} berhasil dimusnahkan secara permanen.");
    }

    public function loginLogs(Request $request)
    {
        $query = LoginLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(user_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(attempted_login) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(user_email) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(user_role) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(ip_address) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(device) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(failure_reason) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('user_role', $request->role);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/LoginLogs', [
            'loginLogs' => $logs,
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? 'all',
                'role' => $request->role ?? 'all',
                'date' => $request->date ?? '',
            ],
            'stats' => [
                'total' => LoginLog::count(),
                'today' => LoginLog::whereDate('created_at', today())->count(),
                'failed_today' => LoginLog::whereDate('created_at', today())->where('status', 'failed')->count(),
                'success_today' => LoginLog::whereDate('created_at', today())->where('status', 'success')->count(),
            ],
        ]);
    }

    public function destroyLoginLog($id)
    {
        $log = LoginLog::findOrFail($id);
        $log->delete();

        return redirect()->back()->with('success', 'Catatan log berhasil dihapus.');
    }

    public function clearLoginLogs(Request $request)
    {
        $scope = $request->input('scope', 'all');

        if ($scope === 'older_30_days') {
            $deletedCount = LoginLog::where('created_at', '<', now()->subDays(30))->delete();
            return redirect()->back()->with('success', "Sebanyak {$deletedCount} log yang lebih lama dari 30 hari berhasil dibersihkan.");
        }

        if ($scope === 'failed_only') {
            $deletedCount = LoginLog::where('status', 'failed')->delete();
            return redirect()->back()->with('success', "Sebanyak {$deletedCount} log gagal login berhasil dibersihkan.");
        }

        LoginLog::query()->delete();
        return redirect()->back()->with('success', 'Semua riwayat log aktivitas login berhasil dibersihkan.');
    }

    public function counselingLogs(Request $request)
    {
        $query = CounselingLog::with(['student.schoolClass', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                       ->orWhereRaw('LOWER(nisn) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('student.schoolClass', function ($cq) use ($search) {
                    $cq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereRaw('LOWER(counselor_name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(notes) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(stage) LIKE ?', ["%{$search}%"])
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        if ($request->filled('stage') && $request->stage !== 'all') {
            $stage = $request->stage;
            if ($stage === 'tahap_1') {
                $query->where('stage', 'like', '%Tahap 1%');
            } elseif ($stage === 'tahap_2') {
                $query->where('stage', 'like', '%Tahap 2%');
            } elseif ($stage === 'tahap_3') {
                $query->where('stage', 'like', '%Tahap 3%');
            }
        }

        if ($request->filled('counselor') && $request->counselor !== 'all') {
            $query->where('counselor_name', $request->counselor);
        }

        if ($request->filled('class_id') && $request->class_id !== 'all') {
            $classId = $request->class_id;
            $query->whereHas('student', function ($sq) use ($classId) {
                $sq->where('school_class_id', $classId);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20)->withQueryString();

        $classes = SchoolClass::orderBy('name')->get();
        $counselors = CounselingLog::select('counselor_name')
            ->whereNotNull('counselor_name')
            ->distinct()
            ->pluck('counselor_name');

        return Inertia::render('Admin/CounselingLogs', [
            'counselingLogs' => $logs,
            'filters' => [
                'search' => $request->search ?? '',
                'stage' => $request->stage ?? 'all',
                'counselor' => $request->counselor ?? 'all',
                'class_id' => $request->class_id ?? 'all',
                'date' => $request->date ?? '',
            ],
            'classes' => $classes,
            'counselors' => $counselors,
            'stats' => [
                'total' => CounselingLog::count(),
                'today' => CounselingLog::whereDate('created_at', today())->count(),
                'this_month' => CounselingLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'tahap1' => CounselingLog::where('stage', 'like', '%Tahap 1%')->count(),
                'tahap2' => CounselingLog::where('stage', 'like', '%Tahap 2%')->count(),
                'tahap3' => CounselingLog::where('stage', 'like', '%Tahap 3%')->count(),
            ],
        ]);
    }

    public function destroyCounselingLog($id)
    {
        $log = CounselingLog::with('student')->findOrFail($id);
        $studentName = $log->student?->name ?? 'Siswa';
        $log->delete();

        return redirect()->back()->with('success', "Catatan bimbingan BK untuk {$studentName} berhasil dihapus.");
    }

    public function clearCounselingLogs(Request $request)
    {
        $scope = $request->input('scope', 'all');

        if ($scope === 'older_30_days') {
            $deletedCount = CounselingLog::where('created_at', '<', now()->subDays(30))->delete();
            return redirect()->back()->with('success', "Sebanyak {$deletedCount} catatan bimbingan BK yang lebih lama dari 30 hari berhasil dibersihkan.");
        }

        CounselingLog::query()->delete();
        return redirect()->back()->with('success', 'Semua riwayat catatan bimbingan BK berhasil dibersihkan.');
    }
}
