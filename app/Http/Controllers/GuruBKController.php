<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Student;

class GuruBKController extends Controller
{
    public function index()
    {
        // Get all students who have ever been late, with delay logs and counseling logs
        $students = Student::with([
            'schoolClass', 
            'delayLogs' => function($q) {
                $q->orderBy('delay_time', 'desc');
            },
            'counselingLogs' => function($q) {
                $q->with('user')->orderBy('created_at', 'desc');
            }
        ])
            ->where('status', 'active')
            ->withCount(['delayLogs', 'activeDelayLogs', 'counselingLogs'])
            ->has('delayLogs', '>', 0)
            ->orderBy('delay_logs_count', 'desc')
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
            'counselor_name' => 'required|string|max:255',
            'notes' => 'required|string',
        ]);

        \DB::transaction(function () use ($request) {
            $delayCount = \App\Models\DelayLog::where('student_id', $request->student_id)->count();
            $stage = $delayCount >= 6 
                ? 'Tahap 3 (Konferensi Kasus / SP)' 
                : ($delayCount >= 5 
                    ? 'Tahap 2 (Panggilan Orang Tua)' 
                    : ($delayCount >= 3 
                        ? 'Tahap 1 (Bimbingan Siswa)' 
                        : 'Pembinaan Ringan'));

            \App\Models\CounselingLog::create([
                'student_id' => $request->student_id,
                'user_id' => auth()->id(),
                'counselor_name' => $request->counselor_name,
                'notes' => $request->notes,
                'delay_count' => $delayCount,
                'stage' => $stage,
            ]);
        });

        return redirect()->back()->with('success', 'Catatan tindak lanjut bimbingan konseling berhasil disimpan.');
    }
}
