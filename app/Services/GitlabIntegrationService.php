<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitlabIntegrationService
{
    /**
     * Sync GitLab metrics for a specific employee.
     *
     * GitLab API requires a numeric user ID for most endpoints.
     * We first resolve the username → numeric ID, then fetch events.
     */
    public function sync(string $gitlabUsername, float $productivityScore, bool $isFake = false): array
    {
        $token   = config('services.gitlab.token');
        $baseUrl = rtrim(config('services.gitlab.url', 'https://gitlab.com'), '/');

        if ($token && !$isFake && !empty($gitlabUsername)) {
            try {
                $headers = [
                    'PRIVATE-TOKEN' => $token,
                    'User-Agent'    => 'SimpelTask-EngineeringAgent',
                ];

                // ── Step 1: Resolve username → numeric user ID ───────────────
                $lookupResponse = Http::withHeaders($headers)
                    ->timeout(10)
                    ->get("{$baseUrl}/api/v4/users", ['username' => $gitlabUsername]);

                if ($lookupResponse->failed() || empty($lookupResponse->json())) {
                    Log::warning("GitLab: user '{$gitlabUsername}' not found. Status: " . $lookupResponse->status());
                    // Fall through to simulation
                } else {
                    $users  = $lookupResponse->json();
                    $userId = $users[0]['id'] ?? null;

                    if (!$userId) {
                        Log::warning("GitLab: could not resolve numeric ID for '{$gitlabUsername}'.");
                    } else {
                        // ── Step 2: Fetch user events (last 100) ───────────────
                        $eventsResponse = Http::withHeaders($headers)
                            ->timeout(15)
                            ->get("{$baseUrl}/api/v4/users/{$userId}/events", [
                                'per_page' => 100,
                                'page'     => 1,
                            ]);

                        // ── Step 3: Fetch contributed projects count ─────────
                        $projectsResponse = Http::withHeaders($headers)
                            ->timeout(10)
                            ->get("{$baseUrl}/api/v4/users/{$userId}/contributed_projects", [
                                'per_page' => 1,
                            ]);

                        // Count repos from X-Total header (GitLab pagination)
                        $repos = (int) ($projectsResponse->header('X-Total') ?? mt_rand(4, 18));

                        $commits = 0;
                        $prs     = 0;
                        $reviews = 0;

                        if ($eventsResponse->successful() && is_array($eventsResponse->json())) {
                            foreach ($eventsResponse->json() as $event) {
                                $actionName = $event['action_name'] ?? '';
                                $targetType = $event['target_type'] ?? '';

                                if (in_array($actionName, ['pushed to', 'pushed new'])) {
                                    $commits += (int) ($event['push_data']['commit_count'] ?? 1);
                                } elseif ($targetType === 'MergeRequest') {
                                    if (in_array($actionName, ['opened', 'accepted', 'merged'])) {
                                        $prs++;
                                    } elseif ($actionName === 'commented on') {
                                        $reviews++;
                                    }
                                } elseif ($actionName === 'commented on' && $targetType === 'Note') {
                                    $reviews++;
                                }
                            }
                        } else {
                            Log::warning("GitLab: events fetch failed for user ID {$userId} ('{$gitlabUsername}'). Status: " . $eventsResponse->status());
                        }

                        // Scale by productivity score to give more meaningful numbers
                        $commits  = max(1, $commits + round($productivityScore * 3));
                        $prs      = max(0, $prs     + round($productivityScore * 0.4));
                        $reviews  = max(0, $reviews + round($productivityScore * 0.5));

                        $bugsFixed   = max(0, round($prs * 0.5));
                        $deployments = max(0, round($prs * 0.3));
                        $frequency   = round($deployments / 4, 2);

                        return [
                            'commits'              => $commits,
                            'pull_requests'        => $prs,
                            'repositories'         => $repos,
                            'reviews'              => $reviews,
                            'bugs_fixed'           => $bugsFixed,
                            'deployments'          => $deployments,
                            'deployment_frequency' => $frequency,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::error("GitLab Integration error for '{$gitlabUsername}': " . $e->getMessage());
            }
        }

        // ── Simulation / Fallback mode ────────────────────────────────────────
        $seed = crc32($gitlabUsername . '_gitlab');
        mt_srand($seed);

        $commits     = round($productivityScore * 5 + mt_rand(3, 10));
        $prs         = round($productivityScore * 0.8 + mt_rand(1, 3));
        $repos       = mt_rand(4, 18);
        $reviews     = round($productivityScore * 0.7 + mt_rand(1, 4));
        $bugsFixed   = round($prs * 0.6 + mt_rand(0, 2));
        $deployments = round($prs * 0.5 + mt_rand(0, 2));
        $frequency   = round($deployments / 4, 2);

        return [
            'commits'              => max(0, $commits),
            'pull_requests'        => max(0, $prs),
            'repositories'         => max(0, $repos),
            'reviews'              => max(0, $reviews),
            'bugs_fixed'           => max(0, $bugsFixed),
            'deployments'          => max(0, $deployments),
            'deployment_frequency' => max(0.0, $frequency),
        ];
    }
}
