<?php

namespace App\Http\Controllers;

use App\Models\DelayLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GuruPiketController extends Controller
{
    public function index()
    {
        // Get today's delay logs with count
        $todayLogs = DelayLog::with(['student' => function ($query) {
                $query->withCount('activeDelayLogs')->with('schoolClass');
            }])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get all classes for the dropdown
        $classes = \App\Models\SchoolClass::orderBy('name')->get();
        
        // Get all teachers (users with piket/wali/bk/admin roles) for reporter picker
        $teachers = \App\Models\User::whereNotIn('role', ['kepala_sekolah'])
            ->whereNotIn('name', ['Super Admin', 'Guru Piket', 'Kepala Sekolah', 'Guru BK', 'Admin Danil'])
            ->orderBy('name')
            ->pluck('name');

        return Inertia::render('GuruPiket/Dashboard', [
            'todayLogs' => $todayLogs,
            'classes' => $classes,
            'teachers' => $teachers,
        ]);
    }

    public function searchStudent(Request $request)
    {
        $query = $request->get('q');
        if (!$query) {
            return response()->json([]);
        }

        $students = Student::with('schoolClass')
            ->withCount('activeDelayLogs')
            ->where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('nisn', 'ilike', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($students);
    }
    
    public function studentsByClass(Request $request)
    {
        $classId = $request->get('class_id');
        if (!$classId) {
            return response()->json([]);
        }

        $students = Student::with('schoolClass')
            ->withCount('activeDelayLogs')
            ->where('status', 'active')
            ->where('school_class_id', $classId)
            ->orderBy('name')
            ->get();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'reason' => 'required|string|max:255',
            'reporter_name' => 'nullable|string|max:100',
        ]);

        // Mencegah double entry di hari yang sama
        $alreadyLoggedToday = DelayLog::where('student_id', $request->student_id)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyLoggedToday) {
            return redirect()->back()->withErrors(['student_id' => 'Siswa ini sudah dicatat terlambat pada hari ini!'])->withInput();
        }

        $log = DelayLog::create([
            'student_id' => $request->student_id,
            'delay_time' => now(),
            'reason' => $request->reason,
            'unique_code' => 'TRX-' . strtoupper(Str::random(5)),
            'reporter_name' => $request->reporter_name,
        ]);

        $log->load('student.schoolClass');

        return redirect()->back()->with('success', 'Data keterlambatan berhasil disimpan.');
    }

    public function update(Request $request, DelayLog $delayLog)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'reason' => 'required|string|max:255',
        ]);

        $delayLog->update([
            'student_id' => $request->student_id,
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Log keterlambatan berhasil diperbarui.');
    }

    public function destroy(DelayLog $delayLog)
    {
        $delayLog->delete();

        return redirect()->back()->with('success', 'Log keterlambatan berhasil dihapus.');
    }
}
