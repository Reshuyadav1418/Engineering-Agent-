<?php

namespace App\Services;

use App\Models\User;
use App\Models\ChatMessage;
use App\Models\Employee;
use App\Models\Task;
use App\Models\Team;
use App\Models\VcsMetric;
use App\Services\Contracts\ChatServiceInterface;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Services\Contracts\TeamMetricsServiceInterface;
use Illuminate\Support\Collection;

class ChatService implements ChatServiceInterface
{
    protected OllamaProvider $ollamaProvider;
    protected LeadershipScoreServiceInterface $leadershipScoreService;
    protected TeamMetricsServiceInterface $teamMetricsService;

    public function __construct(
        OllamaProvider $ollamaProvider,
        LeadershipScoreServiceInterface $leadershipScoreService,
        TeamMetricsServiceInterface $teamMetricsService
    ) {
        $this->ollamaProvider = $ollamaProvider;
        $this->leadershipScoreService = $leadershipScoreService;
        $this->teamMetricsService = $teamMetricsService;
    }

    public function getChatHistory(User $user, int $limit = 50): Collection
    {
        return ChatMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function sendMessage(User $user, string $message): string
    {
        // 1. Save user message to database
        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'message' => $message,
        ]);

        // 2. Fetch recent conversation history for context (last 10 messages)
        $history = ChatMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'message' => $msg->message,
                ];
            })
            ->toArray();

        // 3. Build rich context
        $context = $this->buildContext();

        // 4. Combine prompt and context
        $prompt = "Workspace Context Data:\n" .
                  "=======================\n" .
                  $context . "\n" .
                  "=======================\n\n" .
                  "User Question: {$message}";

        // 5. Query Ollama
        $aiResponse = $this->ollamaProvider->queryOllama($prompt, $history);

        // 6. Save assistant response to database
        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'message' => $aiResponse,
        ]);

        return $aiResponse;
    }

    protected function buildContext(): string
    {
        // Employees and Scores (Top 15 Performers)
        $leaderboard = $this->leadershipScoreService->getLatestLeaderboard(15);
        $employeesStr = "Top 15 Employees (by Leadership Score):\n";
        foreach ($leaderboard as $rank => $entry) {
            $name = $entry->employee->name ?? 'Unknown';
            $role = $entry->employee->role ?? 'Engineer';
            $dept = $entry->employee->department ?? 'General';
            $prod = number_format($entry->productivity_score, 2);
            $lead = number_format($entry->leadership_score, 2);
            $employeesStr .= "- Rank " . ($rank + 1) . ": {$name} | Role: {$role} | Dept: {$dept} | Productivity Score: {$prod}/10 | Leadership Score: {$lead}/10\n";
        }

        // Total Employees count
        $totalEmployees = Employee::count();
        $employeesStr .= "\nTotal Registered Employees: {$totalEmployees}\n";

        // Task statistics (aggregated to prevent loading 180,000 tasks)
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'Completed')->count();
        $pendingTasks = Task::where('status', 'Pending')->count();
        $inProgressTasks = Task::where('status', 'In Progress')->count();
        $tasksStr = "Task Statistics:\n" .
                    "- Total Tasks in System: {$totalTasks}\n" .
                    "- Completed Tasks: {$completedTasks}\n" .
                    "- Pending Tasks: {$pendingTasks}\n" .
                    "- In Progress Tasks: {$inProgressTasks}\n";

        // Team Performance
        $teamLeaderboard = $this->teamMetricsService->getTeamLeaderboard();
        $teamsStr = "Team Performance Leaderboard:\n";
        foreach ($teamLeaderboard as $rank => $t) {
            $name = $t['team_name'];
            $members = $t['members_count'];
            $comp = $t['completed_tasks'];
            $prod = number_format($t['productivity_score'], 2);
            $lead = number_format($t['leadership_score'], 2);
            $teamsStr .= "- Rank " . ($rank + 1) . ": Team '{$name}' | Members: {$members} | Completed Tasks: {$comp} | Team Productivity: {$prod}/10 | Team Leadership: {$lead}/10\n";
        }

        // VCS Commits (Top 15 Committers)
        $vcsMetrics = VcsMetric::with('employee')->orderByDesc('commits')->limit(15)->get();
        $vcsStr = "Top 15 Git Committers:\n";
        foreach ($vcsMetrics as $rank => $vcs) {
            $name = $vcs->employee->name ?? 'Unknown';
            $username = $vcs->git_username ?? '—';
            $commits = $vcs->commits;
            $pr = $vcs->pull_requests;
            $provider = $vcs->provider;
            $vcsStr .= "- Rank " . ($rank + 1) . ": {$name} (Git: {$username}) | Provider: {$provider} | Commits: {$commits} | PRs: {$pr}\n";
        }

        return $employeesStr . "\n" . $tasksStr . "\n" . $teamsStr . "\n" . $vcsStr;
    }
}
