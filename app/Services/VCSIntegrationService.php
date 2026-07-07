<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\VcsMetric;
use App\Services\Contracts\MetricsServiceInterface;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Services\Contracts\AIAnalysisServiceInterface;
use App\Repositories\Contracts\AIReportRepositoryInterface;
use Illuminate\Support\Facades\Log;

class VCSIntegrationService
{
    protected GithubIntegrationService $githubService;
    protected GitlabIntegrationService $gitlabService;
    protected BitbucketIntegrationService $bitbucketService;
    protected MetricsServiceInterface $metricsService;
    protected LeadershipScoreServiceInterface $leadershipScoreService;
    protected AIAnalysisServiceInterface $analysisService;
    protected AIReportRepositoryInterface $reportRepository;

    public function __construct(
        GithubIntegrationService $githubService,
        GitlabIntegrationService $gitlabService,
        BitbucketIntegrationService $bitbucketService,
        MetricsServiceInterface $metricsService,
        LeadershipScoreServiceInterface $leadershipScoreService,
        AIAnalysisServiceInterface $analysisService,
        AIReportRepositoryInterface $reportRepository
    ) {
        $this->githubService = $githubService;
        $this->gitlabService = $gitlabService;
        $this->bitbucketService = $bitbucketService;
        $this->metricsService = $metricsService;
        $this->leadershipScoreService = $leadershipScoreService;
        $this->analysisService = $analysisService;
        $this->reportRepository = $reportRepository;
    }

    public function syncAll(bool $force = false): void
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $isFake = str_contains($employee->email ?? '', '@seeddata.local');

            if ($isFake) {
                // Seed employees: only sync once, never re-sync
                if (VcsMetric::where('employee_id', $employee->id)->exists()) {
                    continue;
                }
            } elseif (!$force) {
                // Real employees: skip if synced within the last hour to avoid timeout
                $recentSync = VcsMetric::where('employee_id', $employee->id)
                    ->where('last_synced_at', '>=', now()->subHour())
                    ->exists();

                if ($recentSync) {
                    continue;
                }
            }

            $this->syncForEmployee($employee);
        }
    }

    /**
     * Sync VCS metrics for a single employee and update their metrics/reports.
     */
    public function syncForEmployee(Employee $employee): void
    {
        // Each provider may use a different username
        $fallback       = strtolower(str_replace(' ', '', $employee->name));
        $githubUsername = $employee->github_username ?: $fallback;
        $gitlabUsername = $employee->gitlab_username  ?: $githubUsername; // fallback to github username
        $bitbucketUsername = $githubUsername; // Bitbucket typically mirrors GitHub username

        // Get latest productivity score to scale simulated metrics
        $productivityScore = optional($employee->productivityScores()->latest('id')->first())->productivity_score ?? 5.0;

        $isFake = str_contains($employee->email ?? '', '@seeddata.local');

        $providers = ['github', 'gitlab', 'bitbucket'];

        foreach ($providers as $provider) {
            $metrics  = [];
            $username = match ($provider) {
                'github'    => $githubUsername,
                'gitlab'    => $gitlabUsername,
                'bitbucket' => $bitbucketUsername,
                default     => $githubUsername,
            };

            if ($provider === 'github') {
                $metrics = $this->githubService->sync($username, $productivityScore, $isFake);
            } elseif ($provider === 'gitlab') {
                $metrics = $this->gitlabService->sync($username, $productivityScore, $isFake);
            } elseif ($provider === 'bitbucket') {
                $metrics = $this->bitbucketService->sync($username, $productivityScore, $isFake);
            }

            VcsMetric::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'provider' => $provider,
                ],
                [
                    'git_username' => $username,
                    'commits' => $metrics['commits'] ?? 0,
                    'pull_requests' => $metrics['pull_requests'] ?? 0,
                    'repositories' => $metrics['repositories'] ?? 0,
                    'reviews' => $metrics['reviews'] ?? 0,
                    'bugs_fixed' => $metrics['bugs_fixed'] ?? 0,
                    'deployments' => $metrics['deployments'] ?? 0,
                    'deployment_frequency' => $metrics['deployment_frequency'] ?? 0.0,
                    'last_synced_at' => now(),
                ]
            );
        }

        // After sync, update Productivity Score, Leadership Score, and AI Report
        $this->recalculateScoresAndReports($employee);
    }

    /**
     * Recalculate employee metrics and refresh AI analysis.
     */
    protected function recalculateScoresAndReports(Employee $employee): void
    {
        // 1. Recalculate Productivity Score
        $productivityScore = $this->metricsService->generateForEmployee($employee);

        // 2. Recalculate Leadership Score
        $leadershipScore = $this->leadershipScoreService->generateForEmployee($employee, $productivityScore);

        // 3. Generate/Refresh AI Report
        $individualTasksCount = $employee->tasks->count();
        $teamTasksCount = $employee->taskMembers->count();
        $teamContribution = $productivityScore->team_contribution ?? 0;
        $productivityVal = $productivityScore->productivity_score ?? 0;
        $leadershipVal = $leadershipScore->leadership_score ?? 0;

        try {
            $analysis = $this->analysisService->generateReport(
                $employee->name,
                $individualTasksCount,
                $teamTasksCount,
                $teamContribution,
                $productivityVal,
                $leadershipVal
            );
        } catch (\Throwable $e) {
            Log::warning('Ollama/AI offline during VCS sync recalculation: ' . $e->getMessage());
            $analysis = [
                'summary' => 'AI Report generator placeholder: Local Ollama AI service was offline or unreachable during VCS metrics sync.',
                'strengths' => ['VCS Metrics synchronized successfully.'],
                'weaknesses' => ['AI provider offline.'],
                'suggestions' => ['Verify local Ollama service is active.']
            ];
        }

        $this->reportRepository->create([
            'employee_id' => $employee->id,
            'summary' => $analysis['summary'] ?? '',
            'strengths' => $analysis['strengths'] ?? [],
            'weaknesses' => $analysis['weaknesses'] ?? [],
            'suggestions' => $analysis['suggestions'] ?? [],
            'created_at' => now(),
        ]);
    }
}
