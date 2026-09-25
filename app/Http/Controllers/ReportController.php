<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('schoolClass')->withCount(['delayLogs', 'activeDelayLogs']);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();

        return Inertia::render('Reports/Index', [
            'students' => $students,
            'classes' => $classes,
            'filters' => $request->only(['class_id', 'search']),
        ]);
    }

    public function show(Student $student)
    {
        $student->load(['schoolClass', 'delayLogs' => function($query) {
            $query->orderBy('delay_time', 'desc');
        }]);

        return response()->json($student);
    }

    public function exportPdf(Request $request)
    {
        $query = Student::with('schoolClass')->withCount(['delayLogs', 'activeDelayLogs']);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->get();
        
        return view('pdf.laporan', [
            'students' => $students,
            'filter_class' => $request->filled('class_id') ? SchoolClass::find($request->class_id)->name : 'Semua Kelas',
            'date' => now()->translatedFormat('d F Y'),
        ]);
    }
}
