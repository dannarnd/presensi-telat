<?php

namespace App\Http\Controllers;

use App\Models\DelayLog;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WaliKelasController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Find the classes this wali kelas manages
        $classes = SchoolClass::where('wali_kelas_id', $user->id)->pluck('id');
        
        $students = Student::with(['schoolClass', 'delayLogs' => function($q) {
                $q->orderBy('delay_time', 'desc');
            }])
            ->withCount('delayLogs')
            ->whereIn('school_class_id', $classes)
            ->whereHas('delayLogs') // Hanya tampilkan yang pernah telat
            ->orderBy('name')
            ->get();

        return Inertia::render('WaliKelas/Dashboard', [
            'students' => $students
        ]);
    }
}
