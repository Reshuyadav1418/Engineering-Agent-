<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GithubIntegrationService
{
    /**
     * Sync GitHub metrics for a specific employee.
     */
    public function sync(string $gitUsername, float $productivityScore, bool $isFake = false): array
    {
        $token = config('services.github.token');

        if ($token && !$isFake) {
            try {
                // Real API Mode using Search endpoints to get true totals
                $headers = [
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'SimpelTask-EngineeringAgent',
                    'Authorization' => "token {$token}"
                ];

                // Fetch public repos count from user profile
                $userResponse = Http::withHeaders($headers)->timeout(10)
                    ->get("https://api.github.com/users/{$gitUsername}");

                $repos = 0;
                if ($userResponse->successful()) {
                    $repos = $userResponse->json()['public_repos'] ?? 0;
                }

                // 1. Fetch total commits (requires cloak-preview header)
                $commitHeaders = $headers;
                $commitHeaders['Accept'] = 'application/vnd.github.cloak-preview+json';
                $commitResponse = Http::withHeaders($commitHeaders)->timeout(10)
                    ->get("https://api.github.com/search/commits", ['q' => "author:{$gitUsername}"]);

                $commits = 0;
                if ($commitResponse->successful()) {
                    $commits = $commitResponse->json()['total_count'] ?? 0;
                } else {
                    Log::warning("GitHub Commit Search failed for {$gitUsername} with status: " . $commitResponse->status());
                }

                // 2. Fetch total PRs
                $prResponse = Http::withHeaders($headers)->timeout(10)
                    ->get("https://api.github.com/search/issues", ['q' => "author:{$gitUsername} type:pr"]);

                $prs = 0;
                if ($prResponse->successful()) {
                    $prs = $prResponse->json()['total_count'] ?? 0;
                } else {
                    Log::warning("GitHub PR Search failed for {$gitUsername} with status: " . $prResponse->status());
                }

                // 3. Fetch total reviews (PRs reviewed by user)
                $reviewResponse = Http::withHeaders($headers)->timeout(10)
                    ->get("https://api.github.com/search/issues", ['q' => "reviewed-by:{$gitUsername} type:pr"]);

                $reviews = 0;
                if ($reviewResponse->successful()) {
                    $reviews = $reviewResponse->json()['total_count'] ?? 0;
                } else {
                    Log::warning("GitHub Review Search failed for {$gitUsername} with status: " . $reviewResponse->status());
                }

                // Calculate related metrics based on real PRs
                $bugsFixed = max(0, round($prs * 0.6));
                $deployments = max(0, round($prs * 0.4));
                $frequency = round($deployments / 4, 2);

                return [
                    'commits' => $commits,
                    'pull_requests' => $prs,
                    'repositories' => $repos,
                    'reviews' => $reviews,
                    'bugs_fixed' => $bugsFixed,
                    'deployments' => $deployments,
                    'deployment_frequency' => $frequency,
                ];
            } catch (\Throwable $e) {
                Log::error("GitHub Integration error for {$gitUsername}: " . $e->getMessage());
            }
        }

        // Simulation Mode (or Fallback if API fails)
        $seed = crc32($gitUsername . '_github');
        mt_srand($seed);

        $commits = round($productivityScore * 7 + mt_rand(4, 15));
        $prs = round($productivityScore * 1.0 + mt_rand(1, 4));
        $repos = mt_rand(5, 30);
        $reviews = round($productivityScore * 1.2 + mt_rand(1, 5));
        $bugsFixed = round($prs * 0.6 + mt_rand(0, 2));
        $deployments = round($prs * 0.4 + mt_rand(0, 2));
        $frequency = round($deployments / 4, 2);

        return [
            'commits' => max(0, $commits),
            'pull_requests' => max(0, $prs),
            'repositories' => max(0, $repos),
            'reviews' => max(0, $reviews),
            'bugs_fixed' => max(0, $bugsFixed),
            'deployments' => max(0, $deployments),
            'deployment_frequency' => max(0.0, $frequency),
        ];
    }
}
