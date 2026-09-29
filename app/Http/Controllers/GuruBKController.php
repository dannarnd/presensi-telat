<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Student;

class GuruBKController extends Controller
{
    public function index()
    {
        // Get all students with their active delay count
        $students = Student::with(['schoolClass', 'delayLogs' => function($q) {
            $q->orderBy('delay_time', 'desc');
        }])
            ->where('status', 'active')
            ->withCount('activeDelayLogs')
            ->has('activeDelayLogs', '>', 0)
            ->orderBy('active_delay_logs_count', 'desc')
            ->get();

        return Inertia::render('GuruBK/Dashboard', [
            'students' => $students
        ]);
    }

    public function history()
    {
        $logs = \App\Models\CounselingLog::with(['student.schoolClass', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('GuruBK/History', [
            'logs' => $logs
        ]);
    }

    public function storeCounseling(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'notes' => 'required|string',
        ]);

        \DB::transaction(function () use ($request) {
            \App\Models\CounselingLog::create([
                'student_id' => $request->student_id,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            \App\Models\DelayLog::where('student_id', $request->student_id)
                ->where('status', 'active')
                ->update(['status' => 'resolved']);
        });

        return redirect()->back()->with('success', 'Tindak lanjut BK berhasil disimpan, dan peringatan keterlambatan telah direset.');
    }
}
