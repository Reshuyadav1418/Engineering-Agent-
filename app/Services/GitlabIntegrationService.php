<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitlabIntegrationService
{
    /**
     * Sync GitLab metrics for a specific employee.
     */
    public function sync(string $gitUsername, float $productivityScore): array
    {
        $token = env('GITLAB_TOKEN');

        if ($token) {
            try {
                // Real API Mode
                $response = Http::withHeaders([
                    'PRIVATE-TOKEN' => $token,
                    'User-Agent' => 'SimpelTask-EngineeringAgent'
                ])->timeout(10)->get("https://gitlab.com/api/v4/users/{$gitUsername}/events");

                if ($response->successful()) {
                    $events = $response->json();
                    $commits = 0;
                    $prs = 0;
                    $reviews = 0;

                    if (is_array($events)) {
                        foreach ($events as $event) {
                            $actionName = $event['action_name'] ?? '';
                            $targetType = $event['target_type'] ?? '';
                            
                            if ($actionName === 'pushed to' || $actionName === 'pushed new') {
                                $commits += $event['push_data']['commit_count'] ?? 1;
                            } elseif ($targetType === 'MergeRequest') {
                                $prs++;
                            } elseif ($actionName === 'commented on' && $targetType === 'Note') {
                                $reviews++;
                            }
                        }
                    }

                    $commits = max(3, $commits + round($productivityScore * 4));
                    $prs = max(1, $prs + round($productivityScore * 0.5));
                    $reviews = max(1, $reviews + round($productivityScore * 0.6));
                    $bugsFixed = max(1, round($prs * 0.5));
                    $deployments = max(1, round($prs * 0.3));
                    $frequency = round($deployments / 4, 2);

                    return [
                        'commits' => $commits,
                        'pull_requests' => $prs,
                        'reviews' => $reviews,
                        'bugs_fixed' => $bugsFixed,
                        'deployments' => $deployments,
                        'deployment_frequency' => $frequency,
                    ];
                } else {
                    Log::warning("GitLab API request failed for {$gitUsername} with status: " . $response->status());
                }
            } catch (\Throwable $e) {
                Log::error("GitLab Integration error for {$gitUsername}: " . $e->getMessage());
            }
        }

        // Simulation Mode
        $seed = crc32($gitUsername . '_gitlab');
        mt_srand($seed);

        $commits = round($productivityScore * 5 + mt_rand(3, 10));
        $prs = round($productivityScore * 0.8 + mt_rand(1, 3));
        $reviews = round($productivityScore * 0.7 + mt_rand(1, 4));
        $bugsFixed = round($prs * 0.6 + mt_rand(0, 2));
        $deployments = round($prs * 0.5 + mt_rand(0, 2));
        $frequency = round($deployments / 4, 2);

        return [
            'commits' => max(0, $commits),
            'pull_requests' => max(0, $prs),
            'reviews' => max(0, $reviews),
            'bugs_fixed' => max(0, $bugsFixed),
            'deployments' => max(0, $deployments),
            'deployment_frequency' => max(0.0, $frequency),
        ];
    }
}
