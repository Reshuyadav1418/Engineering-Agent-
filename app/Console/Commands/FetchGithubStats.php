<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchGithubStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:stats {username=Reshuyadav1418}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and display real GitHub statistics (profile, commits, PRs, repos) for a given username.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->argument('username');
        $token = config('services.github.token');

        $this->info("Fetching GitHub statistics for user: {$username}...");

        if (empty($token)) {
            $this->warn("No GITHUB_TOKEN configured in your environment. API requests might be heavily rate-limited.");
        }

        $headers = [
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'SimpelTask-EngineeringAgent'
        ];

        if ($token) {
            $headers['Authorization'] = "token {$token}";
        }

        // 1. Fetch User Profile
        $this->comment("Contacting GitHub User API...");
        $userResponse = Http::withHeaders($headers)->get("https://api.github.com/users/{$username}");

        if ($userResponse->failed()) {
            $this->error("Failed to fetch user profile for {$username}. Status: " . $userResponse->status());
            if ($userResponse->status() == 404) {
                $this->error("User not found on GitHub.");
            } else {
                $this->error($userResponse->body());
            }
            return Command::FAILURE;
        }

        $userData = $userResponse->json();
        $name = $userData['name'] ?? $username;
        $publicRepos = $userData['public_repos'] ?? 0;
        $followers = $userData['followers'] ?? 0;
        $following = $userData['following'] ?? 0;
        $company = $userData['company'] ?? 'None';
        $location = $userData['location'] ?? 'None';
        $bio = $userData['bio'] ?? 'No bio provided.';

        $this->line("");
        $this->info("=== GITHUB PROFILE OVERVIEW ===");
        $this->line("Name:         <comment>{$name}</comment> (@{$username})");
        $this->line("Bio:          <comment>{$bio}</comment>");
        $this->line("Company:      <comment>{$company}</comment>");
        $this->line("Location:     <comment>{$location}</comment>");
        $this->line("Followers:    <comment>{$followers}</comment> | Following: <comment>{$following}</comment>");
        $this->line("Public Repos: <comment>{$publicRepos}</comment>");
        $this->line("--------------------------------");

        // 2. Fetch Commits Search count
        $this->comment("Searching total commits authored by {$username} across GitHub...");
        $commitHeaders = $headers;
        $commitHeaders['Accept'] = 'application/vnd.github.cloak-preview+json';
        
        $commitResponse = Http::withHeaders($commitHeaders)->get("https://api.github.com/search/commits", [
            'q' => "author:{$username}"
        ]);
        
        $totalCommits = 'N/A';
        if ($commitResponse->successful()) {
            $totalCommits = $commitResponse->json()['total_count'] ?? 0;
        } else {
            $this->warn("Failed to retrieve commits (Rate limit reached or restricted endpoint).");
        }

        // 3. Fetch PRs Search count
        $this->comment("Searching total Pull Requests authored by {$username} across GitHub...");
        $prResponse = Http::withHeaders($headers)->get("https://api.github.com/search/issues", [
            'q' => "author:{$username} type:pr"
        ]);
        
        $totalPRs = 'N/A';
        if ($prResponse->successful()) {
            $totalPRs = $prResponse->json()['total_count'] ?? 0;
        } else {
            $this->warn("Failed to retrieve pull requests (Rate limit reached or restricted endpoint).");
        }

        $this->line("Total Commits: <comment>{$totalCommits}</comment>");
        $this->line("Total PRs:     <comment>{$totalPRs}</comment>");
        $this->line("--------------------------------");

        // 4. Fetch repositories
        $this->comment("Fetching list of repositories...");
        $reposResponse = Http::withHeaders($headers)->get("https://api.github.com/users/{$username}/repos", [
            'per_page' => 10,
            'sort' => 'updated'
        ]);

        if ($reposResponse->successful()) {
            $repos = $reposResponse->json();
            $this->info("Top 10 Recently Updated Repositories:");
            
            $tableData = [];
            foreach ($repos as $repo) {
                $tableData[] = [
                    'Name' => $repo['name'],
                    'Primary Language' => $repo['language'] ?? 'N/A',
                    'Stars' => $repo['stargazers_count'] ?? 0,
                    'Forks' => $repo['forks_count'] ?? 0,
                    'Last Updated' => substr($repo['updated_at'], 0, 10)
                ];
            }
            
            $this->table(['Name', 'Primary Language', 'Stars', 'Forks', 'Last Updated'], $tableData);
        } else {
            $this->error("Failed to list repositories.");
        }

        return Command::SUCCESS;
    }
}
