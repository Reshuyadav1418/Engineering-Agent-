<?php

namespace App\Http\Controllers;

use App\Services\Contracts\EmployeeServiceInterface;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Models\Employee;
use App\Models\ProductivityScore;
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
        $allTasks     = $this->taskService->all();
        $allEmployees = $this->employeeService->all();

        $employeesCount  = $allEmployees->count();
        $tasksCount      = $allTasks->count();
        $completedTasks  = $allTasks->where('status', 'Completed')->count();
        $inProgressTasks = $allTasks->where('status', 'In Progress')->count();
        $pendingTasks    = $allTasks->where('status', 'Pending')->count();

        // Recent tasks (for the "Recent Tasks" panel) – eager-loaded employee
        $recentTasks = $allTasks->sortByDesc('id')->take(5);

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
        $trendRecords = ProductivityScore::all()
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

        // ── Leadership Distribution (score buckets) ───────────────────────────
        // Groups employees into score brackets for a doughnut/pie chart.
        $leaderboard = $this->leadershipScoreService->getLatestLeaderboard();

        $leadershipBuckets = ['Elite (8-10)' => 0, 'Strong (6-8)' => 0, 'Growing (4-6)' => 0, 'Emerging (0-4)' => 0];
        foreach ($leaderboard as $entry) {
            $s = $entry->leadership_score;
            if ($s >= 8)      $leadershipBuckets['Elite (8-10)']++;
            elseif ($s >= 6)  $leadershipBuckets['Strong (6-8)']++;
            elseif ($s >= 4)  $leadershipBuckets['Growing (4-6)']++;
            else              $leadershipBuckets['Emerging (0-4)']++;
        }

        // ── Task Completion Trend (last 6 months) ────────────────────────────
        // Counts completed tasks grouped by month of completed_date.
        $taskCompletionRaw = $allTasks
            ->where('status', 'Completed')
            ->filter(fn ($t) => $t->completed_date !== null)
            ->groupBy(fn ($t) => $t->completed_date->format('M Y'))
            ->map->count();

        $taskCompletionLabels = [];
        $taskCompletionData = [];

        for ($i = 5; $i >= 0; $i--) {
            $label = \Carbon\Carbon::now()->subMonths($i)->format('M Y');
            $taskCompletionLabels[] = $label;
            $taskCompletionData[] = $taskCompletionRaw->has($label) ? $taskCompletionRaw->get($label) : 0;
        }

        // ── Leaderboard (top 5 for dashboard widget) ─────────────────────────
        $topLeaderboard = $leaderboard->take(5);

        // ── Recent AI Reports ─────────────────────────────────────────────────
        $recentReports = \App\Models\AIReport::with('employee')
            ->latest()
            ->take(4)
            ->get();

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
        ));
    }
}
