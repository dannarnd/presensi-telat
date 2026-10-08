<?php

namespace App\Http\Controllers;

use App\Models\DelayLog;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WaliKelasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $isAdmin = $user->role === 'admin';
        
        // Find the classes this wali kelas manages or all classes if admin
        $classQuery = SchoolClass::query();
        if (!$isAdmin) {
            $classQuery->where('wali_kelas_id', $user->id);
        }
        $availableClasses = $classQuery->get();
        $classIds = $availableClasses->pluck('id');
        
        $selectedClassId = $request->query('class_id');
        if ($isAdmin && $selectedClassId) {
            $classIds = collect([$selectedClassId]);
        }
        
        $students = Student::with(['schoolClass', 'delayLogs' => function($q) {
                $q->orderBy('delay_time', 'desc');
            }])
            ->withCount('delayLogs')
            ->whereIn('school_class_id', $classIds)
            ->whereHas('delayLogs') // Hanya tampilkan yang pernah telat
            ->orderBy('name')
            ->get();

        return Inertia::render('WaliKelas/Dashboard', [
            'students' => $students,
            'isAdmin' => $isAdmin,
            'availableClasses' => $availableClasses,
            'selectedClassId' => $selectedClassId
        ]);
    }
}
