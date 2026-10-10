<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruPiketController;
use App\Http\Controllers\KepalaSekolahController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WaliKelasController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/pantau', function () {
    $today = \Carbon\Carbon::today();
    
    $todayLogs = \App\Models\DelayLog::with(['student.schoolClass', 'student.delayLogs' => function($query) {
        $query->orderBy('delay_time', 'desc');
    }])
        ->whereDate('delay_time', $today)
        ->orderBy('delay_time', 'desc')
        ->get();
        
    $classes = \App\Models\SchoolClass::orderBy('name')->get();
        
    return Inertia::render('Pantau', [
        'todayLogs' => $todayLogs,
        'classes' => $classes,
    ]);
})->name('pantau');

Route::get('/cek-siswa', function () {
    return Inertia::render('CekSiswa');
})->name('cek_siswa');

Route::post('/api/cek-siswa', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'nis' => 'required|string',
    ]);
    
    $student = \App\Models\Student::with(['schoolClass', 'delayLogs' => function($query) {
        $query->orderBy('delay_time', 'desc');
    }])->where('nis', $request->nis)->first();
    
    if (!$student) {
        return response()->json(['message' => 'Siswa dengan NIS tersebut tidak ditemukan'], 404);
    }
    
    return response()->json(['student' => $student]);
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    return match($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'guru_piket' => redirect()->route('guru_piket.dashboard'),
        'wali_kelas' => redirect()->route('wali_kelas.dashboard'),
        'kepala_sekolah' => redirect()->route('kepala_sekolah.dashboard'),
        'guru_bk' => redirect()->route('guru_bk.dashboard'),
        default => abort(403)
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/backup-daily', [AdminController::class, 'backupDaily'])->name('backup_daily');
    Route::get('/backup-database', [AdminController::class, 'backupDatabase'])->name('backup_database');
    
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
    
    Route::get('/classes', [AdminController::class, 'classes'])->name('classes.index');
    Route::post('/classes', [AdminController::class, 'storeClass'])->name('classes.store');
    Route::put('/classes/{schoolClass}', [AdminController::class, 'updateClass'])->name('classes.update');
    Route::delete('/classes/{schoolClass}', [AdminController::class, 'destroyClass'])->name('classes.destroy');
    
    Route::get('/students', [AdminController::class, 'students'])->name('students.index');
    Route::post('/students', [AdminController::class, 'storeStudent'])->name('students.store');
    Route::put('/students/{student}', [AdminController::class, 'updateStudent'])->name('students.update');
    Route::delete('/students/{student}', [AdminController::class, 'destroyStudent'])->name('students.destroy');
    Route::post('/students/bulk-action', [AdminController::class, 'bulkActionStudents'])->name('students.bulk-action');
    Route::post('/students/import', [AdminController::class, 'importStudents'])->name('students.import');
});

Route::middleware(['auth', 'role:guru_piket,admin'])->prefix('guru-piket')->name('guru_piket.')->group(function () {
    Route::get('/dashboard', [GuruPiketController::class, 'index'])->name('dashboard');
    Route::post('/store-log', [GuruPiketController::class, 'store'])->name('store');
    Route::put('/logs/{delayLog}', [GuruPiketController::class, 'update'])->name('update_log');
    Route::delete('/logs/{delayLog}', [GuruPiketController::class, 'destroy'])->name('destroy_log');
    Route::get('/search-student', [GuruPiketController::class, 'searchStudent'])->name('search');
    Route::get('/students-by-class', [GuruPiketController::class, 'studentsByClass'])->name('students_by_class');
});

Route::middleware(['auth', 'role:wali_kelas,admin'])->prefix('wali-kelas')->name('wali_kelas.')->group(function () {
    Route::get('/dashboard', [WaliKelasController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:kepala_sekolah,admin'])->prefix('kepala-sekolah')->name('kepala_sekolah.')->group(function () {
    Route::get('/dashboard', [KepalaSekolahController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/pdf', [KepalaSekolahController::class, 'exportPdf'])->name('pdf');
    Route::get('/dashboard/excel', [KepalaSekolahController::class, 'exportExcel'])->name('excel');
});

Route::middleware(['auth', 'role:guru_bk,admin'])->prefix('guru-bk')->name('guru_bk.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\GuruBKController::class, 'index'])->name('dashboard');
    Route::get('/history', [\App\Http\Controllers\GuruBKController::class, 'history'])->name('history');
    Route::post('/counseling', [\App\Http\Controllers\GuruBKController::class, 'storeCounseling'])->name('counseling.store');
});

Route::middleware(['auth', 'role:admin,guru_piket,wali_kelas,kepala_sekolah,guru_bk'])->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('pdf');
    Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
    Route::get('/{student}', [\App\Http\Controllers\ReportController::class, 'show'])->name('show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
