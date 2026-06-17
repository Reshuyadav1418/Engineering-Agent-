<?php

namespace App\Http\Controllers;

use App\Services\Contracts\LeadershipScoreServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    protected LeadershipScoreServiceInterface $leadershipScoreService;

    public function __construct(LeadershipScoreServiceInterface $leadershipScoreService)
    {
        $this->leadershipScoreService = $leadershipScoreService;
    }

    public function index(): View
    {
        $leaderboard = $this->leadershipScoreService->getLatestLeaderboard();

        return view('leaderboard', compact('leaderboard'));
    }

    /**
     * Export the leaderboard as a downloadable CSV file.
     * Streams directly to the browser — no temporary file needed.
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

