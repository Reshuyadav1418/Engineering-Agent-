<?php

namespace App\Http\Controllers;

use App\Services\Contracts\EmployeeServiceInterface;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Models\Employee;
use App\Models\ProductivityScore;
use App\Models\Attendance;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected $employeeService;
    protected $taskService;
    protected LeadershipScoreServiceInterface $leadershipScoreService;

    public function __construct(
        EmployeeServiceInterface $employeeService,
        TaskServiceInterface $taskService,
        LeadershipScoreServiceInterface $leadershipScoreService
    ) {
        $this->employeeService = $employeeService;
        $this->taskService = $taskService;
        $this->leadershipScoreService = $leadershipScoreService;
    }

    public function index(): View
    {
        $employeesCount  = \App\Models\Employee::count();
        $tasksCount      = \App\Models\Task::count();
        $completedTasks  = \App\Models\Task::where('status', 'Completed')->count();
        $inProgressTasks = \App\Models\Task::where('status', 'In Progress')->count();
        $pendingTasks    = \App\Models\Task::where('status', 'Pending')->count();

        // Recent tasks (for the "Recent Tasks" panel) – eager-loaded employee and team
        $recentTasks = \App\Models\Task::with(['employee', 'team'])->latest('id')->take(5)->get();

        // ── Average Productivity ──────────────────────────────────────────────
        // Pulls from the productivity_scores table; falls back to 0 if no data.
        $avgProductivity = round(ProductivityScore::avg('productivity_score') ?? 0, 1);

        // ── Top Performer ─────────────────────────────────────────────────────
        // Employee with the highest latest productivity_score.
        $topPerformerScore = ProductivityScore::with('employee')
            ->orderByDesc('productivity_score')
            ->first();
        $topPerformer = $topPerformerScore?->employee;

        // ── Productivity Trend (last 6 months) ────────────────────────────────
        // Groups average productivity_score by month of updated_at.
        $trendRecords = ProductivityScore::select('productivity_score', 'updated_at')
            ->where('updated_at', '>=', \Carbon\Carbon::now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(function ($score) {
                return $score->updated_at ? $score->updated_at->format('M Y') : '';
            })
            ->map(function ($group) {
                return (object)[
                    'month_label' => $group->first()->updated_at ? $group->first()->updated_at->format('M Y') : '',
                    'avg_score' => round($group->avg('productivity_score'), 2)
                ];
            });

        $productivityTrendLabels = [];
        $productivityTrendData = [];

        for ($i = 5; $i >= 0; $i--) {
            $label = \Carbon\Carbon::now()->subMonths($i)->format('M Y');
            $productivityTrendLabels[] = $label;
            // Pad with 0.0 if there is no data for the month
            $productivityTrendData[] = $trendRecords->has($label) ? $trendRecords->get($label)->avg_score : 0.0;
        }

        // ── Leadership Distribution (score brackets) ───────────────────────────
        // Groups employees into score brackets for a doughnut/pie chart.
        $latestScoreIds = \App\Models\LeadershipScore::selectRaw('MAX(id) as max_id')
            ->groupBy('employee_id')
            ->pluck('max_id');

        $leadershipBuckets = [
            'Elite (8-10)' => \App\Models\LeadershipScore::whereIn('id', $latestScoreIds)->where('leadership_score', '>=', 8)->count(),
            'Strong (6-8)' => \App\Models\LeadershipScore::whereIn('id', $latestScoreIds)->whereBetween('leadership_score', [6, 7.999])->count(),
            'Growing (4-6)' => \App\Models\LeadershipScore::whereIn('id', $latestScoreIds)->whereBetween('leadership_score', [4, 5.999])->count(),
            'Emerging (0-4)' => \App\Models\LeadershipScore::whereIn('id', $latestScoreIds)->where('leadership_score', '<', 4)->count(),
        ];

        // ── Task Completion Trend (last 6 months) ────────────────────────────
        // Counts completed tasks grouped by month of completed_date.
        $taskCompletionRaw = \App\Models\Task::where('status', 'Completed')
            ->whereNotNull('completed_date')
            ->where('completed_date', '>=', \Carbon\Carbon::now()->subMonths(5)->startOfMonth())
            ->select('completed_date')
            ->get()
            ->groupBy(fn ($t) => \Carbon\Carbon::parse($t->completed_date)->format('M Y'))
            ->map->count();

        $taskCompletionLabels = [];
        $taskCompletionData = [];

        for ($i = 5; $i >= 0; $i--) {
            $label = \Carbon\Carbon::now()->subMonths($i)->format('M Y');
            $taskCompletionLabels[] = $label;
            $taskCompletionData[] = $taskCompletionRaw->has($label) ? $taskCompletionRaw->get($label) : 0;
        }

        // ── Leaderboard (top 5 for dashboard widget) ─────────────────────────
        $topLeaderboard = $this->leadershipScoreService->getLatestLeaderboard(5);

        // ── Recent AI Reports ─────────────────────────────────────────────────
        $recentReports = \App\Models\AIReport::with('employee')
            ->latest()
            ->take(4)
            ->get();

        // ── Today's Attendance Overview ───────────────────────────────────────
        $today = \Carbon\Carbon::today();
        $todayAttendance = Attendance::where('attendance_date', $today)->get();
        $totalAttendanceToday = $todayAttendance->count();
        $presentToday        = $todayAttendance->where('status', 'Present')->count();
        $lateToday           = $todayAttendance->where('status', 'Late')->count();
        $absentToday         = $todayAttendance->where('status', 'Absent')->count();
        $presentPct  = $totalAttendanceToday > 0 ? round(($presentToday  / $totalAttendanceToday) * 100) : 0;
        $latePct     = $totalAttendanceToday > 0 ? round(($lateToday     / $totalAttendanceToday) * 100) : 0;
        $absentPct   = $totalAttendanceToday > 0 ? round(($absentToday   / $totalAttendanceToday) * 100) : 0;

        return view('dashboard', compact(
            'employeesCount',
            'tasksCount',
            'completedTasks',
            'inProgressTasks',
            'pendingTasks',
            'recentTasks',
            'avgProductivity',
            'topPerformer',
            'topPerformerScore',
            'productivityTrendLabels',
            'productivityTrendData',
            'leadershipBuckets',
            'taskCompletionLabels',
            'taskCompletionData',
            'topLeaderboard',
            'recentReports',
            'totalAttendanceToday',
            'presentToday',
            'lateToday',
            'absentToday',
            'presentPct',
            'latePct',
            'absentPct',
        ));
    }
}
