<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Services\Contracts\MetricsServiceInterface;
use App\Services\Contracts\TeamMetricsServiceInterface;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    protected LeadershipScoreServiceInterface $leadershipScoreService;
    protected TeamMetricsServiceInterface $teamMetricsService;
    protected MetricsServiceInterface $metricsService;

    public function __construct(
        LeadershipScoreServiceInterface $leadershipScoreService,
        TeamMetricsServiceInterface $teamMetricsService,
        MetricsServiceInterface $metricsService
    ) {
        $this->leadershipScoreService = $leadershipScoreService;
        $this->teamMetricsService     = $teamMetricsService;
        $this->metricsService         = $metricsService;
    }

    public function index(): View
    {
        $leaderboard     = $this->leadershipScoreService->getLatestLeaderboard(20);
        $teamLeaderboard = $this->teamMetricsService->getTeamLeaderboard();
        $period          = 'all';
        $from            = null;
        $to              = null;
        $periodLabel     = 'All Time';

        return view('leaderboard', compact('leaderboard', 'teamLeaderboard', 'period', 'from', 'to', 'periodLabel'));
    }

    /**
     * Return leaderboard filtered to a specific time period.
     * Scores are calculated in-memory — the stored all-time scores are NOT overwritten.
     */
    public function filtered(Request $request): View
    {
        // Lift the time limit — calculating scores for all employees can take a while
        set_time_limit(300);

        $period = $request->input('period', 'monthly');
        [$from, $to, $periodLabel] = $this->resolveDateRange($period, $request);

        // Eager-load ALL nested relations in ONE query set — no N+1 queries in the map()
        $employees = Employee::with([
            'tasks',
            'taskMembers',
            'taskMembers.task',
            'teams',
        ])->get();

        $leaderboard = $employees->map(function (Employee $emp) use ($from, $to) {
            $prod = $this->metricsService->calculateForPeriod($emp, $from, $to);
            $lead = $this->leadershipScoreService->calculateOnDemand($emp, $prod, $from, $to);

            return (object) [
                'employee'           => $emp,
                'productivity_score' => $prod,
                'leadership_score'   => $lead,
            ];
        })
        ->sortByDesc('leadership_score')
        ->take(20)          // match the all-time limit of 20
        ->values();

        $teamLeaderboard = $this->teamMetricsService->getTeamLeaderboard();

        return view('leaderboard', compact('leaderboard', 'teamLeaderboard', 'period', 'from', 'to', 'periodLabel'));
    }

    /**
     * Resolve [Carbon $from, Carbon $to, string $label] from a period key.
     */
    protected function resolveDateRange(string $period, Request $request): array
    {
        $now = Carbon::now();

        return match ($period) {
            'daily'     => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Today — ' . $now->format('d M Y'),
            ],
            'weekly'    => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                'This Week (' . $now->copy()->startOfWeek()->format('d M') . ' – ' . $now->copy()->endOfWeek()->format('d M Y') . ')',
            ],
            'monthly'   => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'This Month — ' . $now->format('F Y'),
            ],
            'quarterly' => [
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter(),
                'This Quarter (Q' . $now->quarter . ' ' . $now->year . ')',
            ],
            'yearly'    => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'This Year — ' . $now->year,
            ],
            'custom'    => [
                $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : null,
                $request->filled('to')   ? Carbon::parse($request->input('to'))->endOfDay()   : null,
                'Custom: ' . ($request->input('from') ?? '∞') . ' → ' . ($request->input('to') ?? '∞'),
            ],
            default     => [null, null, 'All Time'],
        };
    }

    /**
     * Export the leaderboard as a downloadable CSV file.
     */
    public function export(): StreamedResponse
    {
        $leaderboard = $this->leadershipScoreService->getLatestLeaderboard();

        $filename = 'leaderboard_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($leaderboard) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens it correctly
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'Rank',
                'Name',
                'Department',
                'Role',
                'Productivity Score (/10)',
                'Leadership Score (/10)',
            ]);

            // Data rows
            foreach ($leaderboard->values() as $rank => $entry) {
                fputcsv($handle, [
                    $rank + 1,
                    $entry->employee->name        ?? '—',
                    $entry->employee->department  ?? '—',
                    $entry->employee->role        ?? '—',
                    number_format($entry->productivity_score, 2),
                    number_format($entry->leadership_score,   2),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
