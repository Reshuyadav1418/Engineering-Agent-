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

    /**
     * Sync VCS metrics for all employees across all active providers.
     */
    public function syncAll(): void
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $this->syncForEmployee($employee);
        }
    }

    /**
     * Sync VCS metrics for a single employee and update their metrics/reports.
     */
    public function syncForEmployee(Employee $employee): void
    {
        $username = $employee->github_username ?: strtolower(str_replace(' ', '', $employee->name));

        // Get latest productivity score to scale simulated metrics
        $productivityScore = optional($employee->productivityScores()->latest('id')->first())->productivity_score ?? 5.0;

        $providers = ['github', 'gitlab', 'bitbucket'];

        foreach ($providers as $provider) {
            $metrics = [];
            
            if ($provider === 'github') {
                $metrics = $this->githubService->sync($username, $productivityScore);
            } elseif ($provider === 'gitlab') {
                $metrics = $this->gitlabService->sync($username, $productivityScore);
            } elseif ($provider === 'bitbucket') {
                $metrics = $this->bitbucketService->sync($username, $productivityScore);
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
        $tasksAssigned = $employee->tasks->count();
        $tasksCompleted = $employee->tasks->where('status', 'Completed')->count();
        $completionRate = $tasksAssigned ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

        try {
            $analysis = $this->analysisService->generateReport(
                $employee->name,
                $tasksCompleted,
                $completionRate,
                $productivityScore->productivity_score,
                $leadershipScore->leadership_score
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
