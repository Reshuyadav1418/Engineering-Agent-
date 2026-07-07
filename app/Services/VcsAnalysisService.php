<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\VcsMetric;
use App\Models\VcsAiReport;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VcsAnalysisService
{
    /**
     * Compile both real and simulated metrics for an employee.
     */
    public function getMetricsForEmployee(Employee $employee): array
    {
        $vcsMetrics = VcsMetric::where('employee_id', $employee->id)->get();

        if ($vcsMetrics->isEmpty()) {
            // Auto sync to get baseline metrics
            try {
                app(VCSIntegrationService::class)->syncForEmployee($employee);
                $vcsMetrics = VcsMetric::where('employee_id', $employee->id)->get();
            } catch (\Throwable $e) {
                Log::error("Failed to sync VCS metrics for employee {$employee->id}: " . $e->getMessage());
            }
        }

        // Base metrics totals
        $totalCommits = $vcsMetrics->sum('commits');
        $totalPrs = $vcsMetrics->sum('pull_requests');
        $totalReviews = $vcsMetrics->sum('reviews');
        $totalBugsFixed = $vcsMetrics->sum('bugs_fixed');
        $totalDeployments = $vcsMetrics->sum('deployments');
        $avgDeploymentFrequency = $vcsMetrics->avg('deployment_frequency') ?: 0.0;
        $totalReposCount = $vcsMetrics->sum('repositories') ?: 5;

        // Connected providers and usernames
        $providers = $vcsMetrics->pluck('provider')->toArray();
        $usernames = $vcsMetrics->pluck('git_username', 'provider')->toArray();

        // Seed deterministic simulation of granular metrics
        $seed = crc32($employee->id . '_vcs_analysis_v1');
        mt_srand($seed);

        // 1. Code Activity Breakdown
        $weeklyCommits = max(0, round($totalCommits * (mt_rand(12, 18) / 100)));
        $monthlyCommits = max($weeklyCommits, round($totalCommits * (mt_rand(48, 62) / 100)));
        
        $filesChanged = max(0, round($totalCommits * (mt_rand(20, 35) / 10)));
        $linesAdded = max(0, round($filesChanged * mt_rand(40, 80)));
        $linesDeleted = max(0, round($linesAdded * (mt_rand(30, 55) / 100)));

        // 2. PR Analysis Breakdown
        $prCreated = $totalPrs;
        $prMerged = max(0, round($prCreated * (mt_rand(80, 95) / 100)));
        $prClosed = max(0, $prCreated - $prMerged - mt_rand(0, 1));
        $avgPrMergeTime = round(12.5 + mt_rand(2, 34), 1); // in hours

        // 3. Code Review Breakdown
        $reviewsGiven = $totalReviews;
        $reviewsReceived = max(0, round($reviewsGiven * (mt_rand(85, 125) / 100)));
        $approvalCount = max(0, round($reviewsGiven * (mt_rand(65, 85) / 100)));

        // 4. Bug Analysis (Simulated defaults)
        $bugsFixed = $totalBugsFixed;
        $bugsReported = max($bugsFixed, round($bugsFixed * (mt_rand(110, 140) / 100)));
        $openBugs = max(0, $bugsReported - $bugsFixed);

        // 5. Repository list (Simulated defaults)
        $repoList = $this->generateSimulatedRepoNames($employee, $totalReposCount);

        // Attempt Real API Queries to enrich data if tokens are present
        $githubUsername = $employee->github_username;
        $githubToken = config('services.github.token');

        if ($githubToken && $githubUsername) {
            try {
                $headers = [
                    'Authorization' => "token {$githubToken}",
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'SimpelTask-EngineeringAgent'
                ];

                // Fetch real public repository list (top 5)
                $reposResponse = Http::withHeaders($headers)->timeout(4)
                    ->get("https://api.github.com/users/{$githubUsername}/repos", [
                        'sort' => 'updated',
                        'per_page' => 5
                    ]);
                if ($reposResponse->successful() && is_array($reposResponse->json())) {
                    $realRepos = array_column($reposResponse->json(), 'name');
                    if (!empty($realRepos)) {
                        $repoList = $realRepos;
                    }
                }

                // Fetch bug issues authored by this user
                $bugQuery = "author:{$githubUsername} type:issue label:bug,fix,defect,hotfix";
                $issuesResponse = Http::withHeaders($headers)->timeout(4)
                    ->get("https://api.github.com/search/issues", ['q' => $bugQuery]);
                if ($issuesResponse->successful()) {
                    $bugsReported = $issuesResponse->json()['total_count'] ?? $bugsReported;
                }

                // Fetch resolved bug issues (closed)
                $fixedQuery = "author:{$githubUsername} type:issue label:bug,fix,defect,hotfix is:closed";
                $fixedResponse = Http::withHeaders($headers)->timeout(4)
                    ->get("https://api.github.com/search/issues", ['q' => $fixedQuery]);
                if ($fixedResponse->successful()) {
                    $bugsFixed = $fixedResponse->json()['total_count'] ?? $bugsFixed;
                }
                $openBugs = max(0, $bugsReported - $bugsFixed);

            } catch (\Throwable $e) {
                Log::warning("GitHub enrichment failed for user {$githubUsername}: " . $e->getMessage());
            }
        }

        // GitLab Real API Enrichment
        $gitlabUsername = $employee->gitlab_username;
        $gitlabToken = config('services.gitlab.token');
        $gitlabUrl = rtrim(config('services.gitlab.url', 'https://gitlab.com'), '/');

        if ($gitlabToken && $gitlabUsername) {
            try {
                $glHeaders = [
                    'PRIVATE-TOKEN' => $gitlabToken,
                    'User-Agent' => 'SimpelTask-EngineeringAgent'
                ];

                // Fetch GitLab User ID
                $userLookup = Http::withHeaders($glHeaders)->timeout(4)
                    ->get("{$gitlabUrl}/api/v4/users", ['username' => $gitlabUsername]);

                if ($userLookup->successful() && !empty($userLookup->json())) {
                    $glUserId = $userLookup->json()[0]['id'] ?? null;
                    if ($glUserId) {
                        // Fetch projects (repos)
                        $reposGl = Http::withHeaders($glHeaders)->timeout(4)
                            ->get("{$gitlabUrl}/api/v4/users/{$glUserId}/projects", ['per_page' => 5]);
                        if ($reposGl->successful() && is_array($reposGl->json())) {
                            $glRepos = array_column($reposGl->json(), 'name');
                            if (!empty($glRepos)) {
                                $repoList = array_unique(array_merge($repoList, $glRepos));
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("GitLab enrichment failed for user {$gitlabUsername}: " . $e->getMessage());
            }
        }

        return [
            'employee_name' => $employee->name,
            'git_usernames' => $usernames,
            'connected_providers' => $providers,
            'repository_list' => array_slice($repoList, 0, 8), // cap at 8 repos for display
            
            // Code Activity
            'total_commits' => $totalCommits,
            'weekly_commits' => $weeklyCommits,
            'monthly_commits' => $monthlyCommits,
            'files_changed' => $filesChanged,
            'lines_added' => $linesAdded,
            'lines_deleted' => $linesDeleted,

            // PR Analysis
            'prs_created' => $prCreated,
            'prs_merged' => $prMerged,
            'prs_closed' => $prClosed,
            'avg_pr_merge_time' => $avgPrMergeTime,

            // Code Review
            'reviews_given' => $reviewsGiven,
            'reviews_received' => $reviewsReceived,
            'approval_count' => $approvalCount,

            // Bug Analysis
            'bugs_reported' => $bugsReported,
            'bugs_fixed' => $bugsFixed,
            'open_bugs' => $openBugs,

            // Deployment
            'deployment_count' => $totalDeployments,
            'deployment_frequency' => round($avgDeploymentFrequency, 2),
        ];
    }

    /**
     * Generate deterministic simulated repo names based on employee name.
     */
    protected function generateSimulatedRepoNames(Employee $employee, int $count): array
    {
        $prefixes = ['core', 'shared', 'service', 'frontend', 'app', 'tool', 'lib', 'db'];
        $suffixes = ['api', 'dashboard', 'auth', 'pipeline', 'worker', 'cli', 'utils', 'docs'];
        
        $seed = crc32($employee->name . '_repos_sim');
        mt_srand($seed);
        
        $repos = [];
        for ($i = 0; $i < $count; $i++) {
            $p = $prefixes[mt_rand(0, count($prefixes) - 1)];
            $s = $suffixes[mt_rand(0, count($suffixes) - 1)];
            $repos[] = "{$p}-{$s}";
        }
        $repos = array_unique($repos);
        while (count($repos) < $count) {
            $repos[] = "module-" . (count($repos) + 1);
        }
        return array_values($repos);
    }

    /**
     * Request Ollama to analyze VCS metrics and return a structured JSON evaluation.
     */
    public function generateReport(Employee $employee, array $metrics): array
    {
        $provider = Config::get('ai.provider', 'ollama');

        if ($provider === 'ollama') {
            $endpoint = Config::get('ai.ollama.endpoint', 'http://localhost:11434');
            $model = Config::get('ai.ollama.model', 'llama3.2:1b');
            $url = rtrim($endpoint, '/') . '/api/generate';

            $prompt = $this->buildPrompt($employee->name, $metrics);

            try {
                Log::info('Ollama VCS analysis request', [
                    'url' => $url,
                    'model' => $model,
                ]);

                $response = Http::timeout(60)->post($url, [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json',
                    'options' => [
                        'temperature' => 0.2,
                    ],
                ]);

                if ($response->successful()) {
                    $content = $response->json('response');
                    $parsed = $this->extractJson($content);
                    if ($parsed !== null) {
                        return $parsed;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Ollama offline during VCS analysis: ' . $e->getMessage());
            }
        }

        // Fallback mock report generator if AI fails or config is simulation
        return $this->generateMockReport($employee->name, $metrics);
    }

    /**
     * Build standard prompt with exact instructions and metrics.
     */
    protected function buildPrompt(string $employeeName, array $metrics): string
    {
        $repoListStr = implode(', ', $metrics['repository_list']);
        
        return "You are a professional AI engineering lead. Evaluate developer {$employeeName} based on these VCS (Version Control System) metrics:\n\n" .
            "VCS Metrics:\n" .
            "- Total Commits: {$metrics['total_commits']}\n" .
            "- Weekly Commits: {$metrics['weekly_commits']}\n" .
            "- Monthly Commits: {$metrics['monthly_commits']}\n" .
            "- Files Changed: {$metrics['files_changed']}\n" .
            "- Lines Added: {$metrics['lines_added']}\n" .
            "- Lines Deleted: {$metrics['lines_deleted']}\n" .
            "- PRs Created: {$metrics['prs_created']}\n" .
            "- PRs Merged: {$metrics['prs_merged']}\n" .
            "- PRs Closed: {$metrics['prs_closed']}\n" .
            "- Average PR Merge Time: {$metrics['avg_pr_merge_time']} hours\n" .
            "- Reviews Given: {$metrics['reviews_given']}\n" .
            "- Reviews Received: {$metrics['reviews_received']}\n" .
            "- Approval Count: {$metrics['approval_count']}\n" .
            "- Bugs Reported: {$metrics['bugs_reported']}\n" .
            "- Bugs Fixed: {$metrics['bugs_fixed']}\n" .
            "- Open Bugs: {$metrics['open_bugs']}\n" .
            "- Deployments Count: {$metrics['deployment_count']}\n" .
            "- Deployment Frequency: {$metrics['deployment_frequency']} per week\n" .
            "- Repositories Contributed: {$repoListStr}\n\n" .
            "You MUST return ONLY a JSON object containing EXACTLY these keys. Do NOT include markdown code fences, backticks, or any trailing/leading text outside the JSON.\n\n" .
            "JSON structure required:\n" .
            "{\n" .
            "  \"summary\": \"A short 1-2 sentence developer coding summary.\",\n" .
            "  \"code_quality_score\": integer between 1 and 100,\n" .
            "  \"collaboration_score\": integer between 1 and 100,\n" .
            "  \"delivery_score\": integer between 1 and 100,\n" .
            "  \"risk_analysis\": \"A short paragraph describing any potential developer delivery risks or bottleneck concerns.\",\n" .
            "  \"recommendations\": \"Provide 2-3 specific recommendations for this developer.\"\n" .
            "}";
    }

    /**
     * Extract and parse JSON from Ollama response content.
     */
    protected function extractJson(?string $content): ?array
    {
        if (empty($content)) {
            return null;
        }

        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $this->normalizeResult($decoded);
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $snippet = substr($content, $start, $end - $start + 1);
        $decoded = json_decode($snippet, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $this->normalizeResult($decoded);
    }

    /**
     * Clean and normalize results to prevent schema mismatch.
     */
    protected function normalizeResult(array $result): array
    {
        return [
            'summary' => trim($result['summary'] ?? ''),
            'code_quality_score' => max(1, min(100, intval($result['code_quality_score'] ?? 70))),
            'collaboration_score' => max(1, min(100, intval($result['collaboration_score'] ?? 70))),
            'delivery_score' => max(1, min(100, intval($result['delivery_score'] ?? 70))),
            'risk_analysis' => trim($result['risk_analysis'] ?? ''),
            'recommendations' => trim($result['recommendations'] ?? ''),
        ];
    }

    /**
     * Fallback mock report generator using deterministic metrics.
     */
    public function generateMockReport(string $employeeName, array $metrics): array
    {
        $seed = crc32($employeeName . '_vcs_mock_ai');
        mt_srand($seed);

        $commits = $metrics['total_commits'];
        $prs = $metrics['prs_created'];
        $reviews = $metrics['reviews_given'];
        $bugsFixed = $metrics['bugs_fixed'];
        $openBugs = $metrics['open_bugs'];

        // Code quality score logic
        $codeQuality = 78 + mt_rand(-5, 12);
        if ($openBugs > 3) {
            $codeQuality -= 8;
        }
        if ($reviews > 12) {
            $codeQuality += 4;
        }
        $codeQuality = max(20, min(100, $codeQuality));

        // Collaboration score logic
        $collaboration = 75 + mt_rand(-7, 15);
        if ($reviews > 10) {
            $collaboration += 8;
        } elseif ($reviews < 3) {
            $collaboration -= 10;
        }
        $collaboration = max(20, min(100, $collaboration));

        // Delivery score logic
        $delivery = 72 + mt_rand(-6, 16);
        if ($metrics['deployment_frequency'] > 1.2) {
            $delivery += 8;
        }
        $delivery = max(20, min(100, $delivery));

        $summary = "{$employeeName} displays consistent contribution activity, registering {$commits} commits and {$prs} pull requests. They play a stable role in keeping deployments moving at {$metrics['deployment_frequency']} releases per week.";

        $risks = [];
        if ($reviews < 4) {
            $risks[] = "Review contribution is relatively low ({$reviews} reviews given), potentially isolating code knowledge.";
        }
        if ($openBugs > 2) {
            $risks[] = "Currently has {$openBugs} open bugs pending, representing a moderate delivery debt.";
        }
        if ($metrics['avg_pr_merge_time'] > 28) {
            $risks[] = "PR merge speed is slow, averaging {$metrics['avg_pr_merge_time']} hours per pull request.";
        }
        if (empty($risks)) {
            $risks[] = "No critical risks detected. Maintain current deployment and code review rates.";
        }
        $riskAnalysis = implode(" ", $risks);

        $recs = [];
        if ($reviews < 8) {
            $recs[] = "Dedicate time to peer review sessions to improve collaboration score (currently {$collaboration}).";
        }
        if ($openBugs > 0) {
            $recs[] = "Resolve the remaining {$openBugs} open bugs to maintain high repository health and code quality.";
        }
        if ($metrics['avg_pr_merge_time'] > 24) {
            $recs[] = "Deconstruct larger PRs into smaller, bite-sized tasks to shorten average merge time.";
        }
        $recs[] = "Continue checking in files and deploying on a predictable cycle.";
        $recommendations = implode("\n", array_map(fn($r) => "- " . $r, $recs));

        return [
            'summary' => $summary,
            'code_quality_score' => $codeQuality,
            'collaboration_score' => $collaboration,
            'delivery_score' => $delivery,
            'risk_analysis' => $riskAnalysis,
            'recommendations' => $recommendations
        ];
    }
}
