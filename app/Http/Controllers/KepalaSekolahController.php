<?php

namespace App\Http\Controllers;

use App\Models\DelayLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class KepalaSekolahController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $now = now();

        if ($period === 'this_month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $prevStartDate = $now->copy()->subMonth()->startOfMonth();
            $prevEndDate = $now->copy()->subMonth()->endOfMonth();
            $periodLabel = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
        } elseif ($period === 'last_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth();
            $endDate = $now->copy()->subMonth()->endOfMonth();
            $prevStartDate = $now->copy()->subMonths(2)->startOfMonth();
            $prevEndDate = $now->copy()->subMonths(2)->endOfMonth();
            $periodLabel = 'Bulan Lalu (' . $startDate->translatedFormat('F Y') . ')';
        } elseif ($period === 'this_semester') {
            $month = $now->month;
            if ($month >= 7) {
                $startDate = $now->copy()->month(7)->startOfMonth();
                $endDate = $now->copy()->month(12)->endOfMonth();
                $prevStartDate = $now->copy()->subYear()->month(1)->startOfMonth();
                $prevEndDate = $now->copy()->subYear()->month(6)->endOfMonth();
                $periodLabel = 'Semester Ganjil ' . $now->year;
            } else {
                $startDate = $now->copy()->month(1)->startOfMonth();
                $endDate = $now->copy()->month(6)->endOfMonth();
                $prevStartDate = $now->copy()->subYear()->month(7)->startOfMonth();
                $prevEndDate = $now->copy()->subYear()->month(12)->endOfMonth();
                $periodLabel = 'Semester Genap ' . $now->year;
            }
        } else {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $prevStartDate = $now->copy()->subMonth()->startOfMonth();
            $prevEndDate = $now->copy()->subMonth()->endOfMonth();
            $periodLabel = 'Bulan Ini';
        }

        $query = DelayLog::whereBetween('created_at', [$startDate, $endDate]);
        $prevQuery = DelayLog::whereBetween('created_at', [$prevStartDate, $prevEndDate]);

        // 1. Total Lates
        $totalLates = $query->count();
        $prevTotalLates = $prevQuery->count();

        // 2. MoM Trend
        $momPercentage = 0;
        $momTrend = 'neutral';
        if ($prevTotalLates > 0) {
            $momPercentage = round((($totalLates - $prevTotalLates) / $prevTotalLates) * 100);
            $momTrend = $momPercentage > 0 ? 'up' : ($momPercentage < 0 ? 'down' : 'neutral');
        } elseif ($totalLates > 0) {
            $momPercentage = 100;
            $momTrend = 'up';
        }

        // 3. BK Resolution Rate - only students with >= 3 delays need BK attention
        // Find student IDs with 3+ delay logs in this period
        $studentsNeedingBK = DB::table('delay_logs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('student_id')
            ->havingRaw('COUNT(id) >= 3')
            ->pluck('student_id');

        $totalNeedBK = $studentsNeedingBK->count();

        // Among those students, how many have been resolved by BK
        $resolvedLogs = DB::table('delay_logs')
            ->whereIn('student_id', $studentsNeedingBK)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'resolved')
            ->distinct('student_id')
            ->count('student_id');

        $activeLogs = $totalNeedBK - $resolvedLogs;
        $resolutionRate = $totalNeedBK > 0 ? round(($resolvedLogs / $totalNeedBK) * 100) : 0;

        // 4. Class Distribution (Doughnut) - count unique students
        $classDistribution = DB::table('delay_logs')
            ->join('students', 'delay_logs.student_id', '=', 'students.id')
            ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
            ->select('school_classes.name', DB::raw('COUNT(DISTINCT delay_logs.student_id) as total'))
            ->whereBetween('delay_logs.created_at', [$startDate, $endDate])
            ->groupBy('school_classes.name')
            ->orderByDesc('total')
            ->get();

        $doughnutLabels = $classDistribution->pluck('name')->toArray();
        $doughnutData = $classDistribution->pluck('total')->toArray();

        // 5. Trend (Bar Chart)
        $trendDataRaw = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(id) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $chartLabels = [];
        $chartData = [];
        foreach ($trendDataRaw as $row) {
            $chartLabels[] = Carbon::parse($row->date)->format('d M');
            $chartData[] = $row->count;
        }

        // 6. Wall of Shame
        $wallOfShame = DB::table('delay_logs')
            ->join('students', 'delay_logs.student_id', '=', 'students.id')
            ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
            ->select('students.name', 'students.nisn', 'school_classes.name as class_name', DB::raw('COUNT(delay_logs.id) as total_late'))
            ->whereBetween('delay_logs.created_at', [$startDate, $endDate])
            ->groupBy('students.id', 'students.name', 'students.nisn', 'school_classes.name')
            ->orderByDesc('total_late')
            ->limit(10)
            ->get();

        // 7. Most Problematic Class
        $mostProblematicClass = $classDistribution->first();
        if ($mostProblematicClass) {
            $mostProblematicClass->students = DB::table('delay_logs')
                ->join('students', 'delay_logs.student_id', '=', 'students.id')
                ->select('students.name', 'students.nisn', DB::raw('COUNT(delay_logs.id) as total_late'))
                ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
                ->where('school_classes.name', $mostProblematicClass->name)
                ->whereBetween('delay_logs.created_at', [$startDate, $endDate])
                ->groupBy('students.id', 'students.name', 'students.nisn')
                ->orderByDesc('total_late')
                ->limit(5)
                ->get();
        }

        return Inertia::render('KepalaSekolah/Dashboard', [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'totalLates' => $totalLates,
            'momPercentage' => abs($momPercentage),
            'momTrend' => $momTrend,
            'activeLogs' => $activeLogs,
            'resolvedLogs' => $resolvedLogs,
            'resolutionRate' => $resolutionRate,
            'doughnutLabels' => $doughnutLabels,
            'doughnutData' => $doughnutData,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'wallOfShame' => $wallOfShame,
            'mostProblematicClass' => $mostProblematicClass,
        ]);
    }

    public function exportPdf(Request $request)
    {
        // For simplicity, we reuse the same logic
        $period = $request->get('period', 'this_month');
        $now = now();

        if ($period === 'this_month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $periodLabel = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
        } elseif ($period === 'last_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth();
            $endDate = $now->copy()->subMonth()->endOfMonth();
            $periodLabel = 'Bulan Lalu (' . $startDate->translatedFormat('F Y') . ')';
        } elseif ($period === 'this_semester') {
            $month = $now->month;
            if ($month >= 7) {
                $startDate = $now->copy()->month(7)->startOfMonth();
                $endDate = $now->copy()->month(12)->endOfMonth();
                $periodLabel = 'Semester Ganjil ' . $now->year;
            } else {
                $startDate = $now->copy()->month(1)->startOfMonth();
                $endDate = $now->copy()->month(6)->endOfMonth();
                $periodLabel = 'Semester Genap ' . $now->year;
            }
        } else {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $periodLabel = 'Bulan Ini';
        }

        $query = DelayLog::whereBetween('created_at', [$startDate, $endDate]);

        $totalLate = $query->count();
        $activeLogs = (clone $query)->where('status', 'active')->count();
        $resolvedLogs = (clone $query)->where('status', 'resolved')->count();
        $resolutionRate = $totalLate > 0 ? round(($resolvedLogs / $totalLate) * 100) : 0;

        $wallOfShame = DB::table('delay_logs')
            ->join('students', 'delay_logs.student_id', '=', 'students.id')
            ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
            ->select('students.name', 'students.nisn', 'school_classes.name as class_name', DB::raw('COUNT(delay_logs.id) as total_late'))
            ->whereBetween('delay_logs.created_at', [$startDate, $endDate])
            ->groupBy('students.id', 'students.name', 'students.nisn', 'school_classes.name')
            ->orderByDesc('total_late')
            ->limit(20)
            ->get();

        return view('pdf.dashboard', [
            'totalLate' => $totalLate,
            'activeLogs' => $activeLogs,
            'resolvedLogs' => $resolvedLogs,
            'resolutionRate' => $resolutionRate,
            'wallOfShame' => $wallOfShame,
            'periodLabel' => $periodLabel,
            'date' => now()->translatedFormat('d F Y'),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $now = now();

        if ($period === 'this_month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($period === 'last_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth();
            $endDate = $now->copy()->subMonth()->endOfMonth();
        } elseif ($period === 'this_semester') {
            $month = $now->month;
            if ($month >= 7) {
                $startDate = $now->copy()->month(7)->startOfMonth();
                $endDate = $now->copy()->month(12)->endOfMonth();
            } else {
                $startDate = $now->copy()->month(1)->startOfMonth();
                $endDate = $now->copy()->month(6)->endOfMonth();
            }
        } else {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        }

        $logs = DB::table('delay_logs')
            ->join('students', 'delay_logs.student_id', '=', 'students.id')
            ->join('school_classes', 'students.school_class_id', '=', 'school_classes.id')
            ->select(
                'delay_logs.created_at as Tanggal',
                'students.nisn as NISN',
                'students.name as Nama Siswa',
                'school_classes.name as Kelas',
                'delay_logs.reason as Alasan',
                'delay_logs.status as Status',
                'delay_logs.reporter_name'
            )
            ->whereBetween('delay_logs.created_at', [$startDate, $endDate])
            ->orderBy('delay_logs.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $arr = (array) $item;
                $arr['Petugas Piket'] = $item->reporter_name ? $item->reporter_name : 'Guru Piket';
                unset($arr['reporter_name']);
                return $arr;
            });

        return (new \Rap2hpoutre\FastExcel\FastExcel($logs))->download('data_keterlambatan_mentah.xlsx');
    }
}
