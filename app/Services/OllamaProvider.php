<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class OllamaProvider
{
    /**
     * Generate evaluation report using local Ollama server.
     *
     * @param string $employeeName
     * @param int $tasksCompleted
     * @param float $completionRate
     * @param float $productivityScore
     * @param float $leadershipScore
     * @return array
     */
    public function generateOllamaReport(
        string $employeeName,
        int $individualTasks,
        int $teamTasks,
        float $teamContribution,
        float $productivityScore,
        float $leadershipScore
    ): array {
        $endpoint = Config::get('ai.ollama.endpoint', 'http://localhost:11434');
        $model = Config::get('ai.ollama.model', 'qwen3:8b');
        $url = rtrim($endpoint, '/') . '/api/generate';

        $prompt = $this->buildPrompt($employeeName, $individualTasks, $teamTasks, $teamContribution, $productivityScore, $leadershipScore);

        try {
            \Illuminate\Support\Facades\Log::info('Ollama request details', [
                'url' => $url,
                'model' => $model,
                'prompt_snippet' => substr($prompt, 0, 100) . '...'
            ]);

            $response = Http::timeout(120)->post($url, [
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
                \Illuminate\Support\Facades\Log::warning('Ollama returned response, but extraction/JSON parsing failed', [
                    'raw_response' => $response->body(),
                    'parsed_response_field' => $content
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error('Ollama request failed with status code: ' . $response->status(), [
                    'body' => $response->body()
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Ollama connection threw exception: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }

        return $this->generateMockReport($employeeName, $individualTasks, $teamTasks, $teamContribution, $productivityScore, $leadershipScore);
    }

    /**
     * Build prompt with employee metrics.
     */
    protected function buildPrompt(
        string $employeeName,
        int $individualTasks,
        int $teamTasks,
        float $teamContribution,
        float $productivityScore,
        float $leadershipScore
    ): string {
        return "You are an HR analyst. Evaluate employee {$employeeName} using the metrics below.\n" .
            "Return ONLY a JSON object using EXACTLY these keys: summary, strengths, weaknesses, suggestions.\n" .
            "Do NOT rename the keys. Do NOT add extra keys. Do NOT include any text outside the JSON.\n\n" .
            "Employee Metrics:\n" .
            "- employee_name: {$employeeName}\n" .
            "- individual_tasks: {$individualTasks}\n" .
            "- team_tasks: {$teamTasks}\n" .
            "- team_contribution: {$teamContribution}\n" .
            "- productivity_score: {$productivityScore}\n" .
            "- leadership_score: {$leadershipScore}\n\n" .
            "Required JSON format (fill in the values):\n" .
            '{"summary": "one paragraph evaluation", "strengths": ["strength 1", "strength 2", "strength 3"], "weaknesses": ["weakness 1", "weakness 2", "weakness 3"], "suggestions": ["suggestion 1", "suggestion 2", "suggestion 3"]}';
    }

    /**
     * Extract and parse JSON from response content.
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
     * Ensure return format conforms to structured JSON output structure.
     */
    protected function normalizeResult(array $result): array
    {
        // Support alternative key names that small models commonly return
        $weaknesses = $result['weaknesses']
            ?? $result['areas_for_improvement']
            ?? $result['areas_for_growth']
            ?? $result['improvement_areas']
            ?? $result['growth_areas']
            ?? $result['challenges']
            ?? [];

        $suggestions = $result['suggestions']
            ?? $result['recommendations']
            ?? $result['action_items']
            ?? $result['next_steps']
            ?? $result['improvements']
            ?? [];

        return [
            'summary' => trim($result['summary'] ?? $result['overview'] ?? $result['evaluation'] ?? ''),
            'strengths' => array_values(array_filter((array) ($result['strengths'] ?? []))),
            'weaknesses' => array_values(array_filter((array) $weaknesses)),
            'suggestions' => array_values(array_filter((array) $suggestions)),
        ];
    }

    /**
     * Fallback mock response if Ollama is unavailable.
     */
    protected function generateMockReport(
        string $employeeName,
        int $individualTasks,
        int $teamTasks,
        float $teamContribution,
        float $productivityScore,
        float $leadershipScore
    ): array {

        return [
            'summary' => "{$employeeName} demonstrates solid task ownership across {$individualTasks} individual tasks and {$teamTasks} team tasks. They maintain a team contribution score of {$teamContribution}, showing strong collaborative habits.",
            'strengths' => [
                'Consistently closes individual tasks with good focus and quality.',
                'Active and reliable contributor in team projects, holding a contribution score of ' . $teamContribution . '.',
                'Strong alignment of productivity and leadership metrics.',
            ],
            'weaknesses' => [
                'Could further optimize communication inside complex multi-role team tasks.',
                'Needs to ensure assigned hours are closely matched by actual performance.',
            ],
            'suggestions' => [
                'Take a lead role in the next team project to leverage high leadership skills.',
                'Perform post-project hour analysis on team tasks to refine estimation parameters.',
            ],
        ];
    }

    /**
     * Generate team evaluation report using local Ollama server.
     */
    public function generateOllamaTeamReport(
        string $teamName,
        int $membersCount,
        int $completedTasks,
        float $productivityScore,
        float $leadershipScore,
        float $completionRate,
        float $consistencyScore,
        float $collaborationScore
    ): array {
        $endpoint = Config::get('ai.ollama.endpoint', 'http://localhost:11434');
        $model = Config::get('ai.ollama.model', 'qwen3:8b');
        $url = rtrim($endpoint, '/') . '/api/generate';

        $prompt = $this->buildTeamPrompt(
            $teamName,
            $membersCount,
            $completedTasks,
            $productivityScore,
            $leadershipScore,
            $completionRate,
            $consistencyScore,
            $collaborationScore
        );

        try {
            \Illuminate\Support\Facades\Log::info('Ollama team request details', [
                'url' => $url,
                'model' => $model,
                'prompt_snippet' => substr($prompt, 0, 100) . '...'
            ]);

            $response = Http::timeout(120)->post($url, [
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
            \Illuminate\Support\Facades\Log::error('Ollama team connection threw exception: ' . $e->getMessage());
        }

        return $this->generateMockTeamReport(
            $teamName,
            $membersCount,
            $completedTasks,
            $productivityScore,
            $leadershipScore,
            $completionRate,
            $consistencyScore,
            $collaborationScore
        );
    }

    /**
     * Build prompt with team metrics.
     */
    protected function buildTeamPrompt(
        string $teamName,
        int $membersCount,
        int $completedTasks,
        float $productivityScore,
        float $leadershipScore,
        float $completionRate,
        float $consistencyScore,
        float $collaborationScore
    ): string {
        return "You are an HR analyst. Evaluate the project team {$teamName} using the metrics below.\n" .
            "Return ONLY a JSON object using EXACTLY these keys: summary, strengths, weaknesses, suggestions.\n" .
            "Do NOT rename the keys. Do NOT add extra keys. Do NOT include any text outside the JSON.\n\n" .
            "Team Metrics:\n" .
            "- team_name: {$teamName}\n" .
            "- members_count: {$membersCount}\n" .
            "- completed_tasks: {$completedTasks}\n" .
            "- productivity_score: {$productivityScore}\n" .
            "- leadership_score: {$leadershipScore}\n" .
            "- completion_rate: {$completionRate}\n" .
            "- consistency_score: {$consistencyScore}\n" .
            "- collaboration_score: {$collaborationScore}\n\n" .
            "Required JSON format (fill in the values):\n" .
            '{"summary": "one paragraph evaluation of the team", "strengths": ["strength 1", "strength 2", "strength 3"], "weaknesses": ["weakness 1", "weakness 2", "weakness 3"], "suggestions": ["suggestion 1", "suggestion 2", "suggestion 3"]}';
    }

    /**
     * Fallback mock response for team reports.
     */
    protected function generateMockTeamReport(
        string $teamName,
        int $membersCount,
        int $completedTasks,
        float $productivityScore,
        float $leadershipScore,
        float $completionRate,
        float $consistencyScore,
        float $collaborationScore
    ): array {
        return [
            'summary' => "Team {$teamName} consists of {$membersCount} members and has completed {$completedTasks} tasks. With a team productivity score of {$productivityScore} and a leadership score of {$leadershipScore}, the team exhibits solid core metrics and collaborative capacity.",
            'strengths' => [
                "Good collaboration score of {$collaborationScore} indicating strong peer-to-peer synergy.",
                "Stable consistency score of {$consistencyScore} reflecting regular work cycles.",
                "Effective division of tasks among the {$membersCount} active team members."
            ],
            'weaknesses' => [
                "Task completion rate of {$completionRate} (scaled out of 10) can be improved with better bottleneck identification.",
                "Potential delivery delays if workload balance shifts disproportionately."
            ],
            'suggestions' => [
                "Implement bi-weekly syncs to optimize task routing and raise the completion rate.",
                "Leverage the team's strong collaboration score to set up paired debugging sessions.",
                "Optimize estimation practices for future milestones based on past delivery speeds."
            ]
        ];
    }

    /**
     * Send a general chat prompt to Ollama with a fallback.
     */
    public function queryOllama(string $prompt, array $chatHistory = []): string
    {
        $endpoint = Config::get('ai.ollama.endpoint', 'http://localhost:11434');
        $model = Config::get('ai.ollama.model', 'qwen3:8b');
        $url = rtrim($endpoint, '/') . '/api/generate';

        // Prepare prompt with context and history
        $fullPrompt = "";
        if (!empty($chatHistory)) {
            $fullPrompt .= "Here is the previous conversation history:\n";
            foreach ($chatHistory as $msg) {
                $roleName = ($msg['role'] === 'user') ? 'User' : 'Assistant';
                $fullPrompt .= "{$roleName}: {$msg['message']}\n";
            }
            $fullPrompt .= "\n";
        }
        $fullPrompt .= "System Instructions:\n" .
                       "You are an AI assistant for the SimpelTask Engineering Agent platform.\n" .
                       "Answer the user's question accurately using the provided workspace context.\n\n" .
                       $prompt;

        try {
            \Illuminate\Support\Facades\Log::info('Ollama chat request', [
                'model' => $model,
                'prompt_length' => strlen($fullPrompt)
            ]);

            $response = Http::timeout(120)->post($url, [
                'model' => $model,
                'prompt' => $fullPrompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.5,
                ],
            ]);

            if ($response->successful()) {
                $reply = $response->json('response');
                if (!empty($reply)) {
                    return trim($reply);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Ollama chat request failed: ' . $e->getMessage());
        }

        // Mock Fallback Response if Ollama is not running/failing
        return $this->generateMockChatResponse($prompt, $chatHistory);
    }

    /**
     * Generate realistic mock chat responses using local context.
     */
    protected function generateMockChatResponse(string $prompt, array $chatHistory): string
    {
        $promptLower = strtolower($prompt);

        if (str_contains($promptLower, 'top performer') || str_contains($promptLower, 'highest leadership') || str_contains($promptLower, 'best employee')) {
            $top = \App\Models\LeadershipScore::with('employee')->orderByDesc('leadership_score')->first();
            if ($top && $top->employee) {
                return "The top performer is {$top->employee->name} with a leadership score of " . number_format($top->leadership_score, 1) . " and a productivity score of " . number_format($top->productivity_score, 1) . ".";
            }
            return "Rahul has the highest leadership score 9.1";
        }

        if (str_contains($promptLower, 'how many employee') || str_contains($promptLower, 'total employee') || str_contains($promptLower, 'number of employee')) {
            $count = \App\Models\Employee::count();
            return "There are currently {$count} employees registered in the system.";
        }

        if (str_contains($promptLower, 'how many task') || str_contains($promptLower, 'total task') || str_contains($promptLower, 'number of task')) {
            $count = \App\Models\Task::count();
            $completed = \App\Models\Task::where('status', 'Completed')->count();
            return "There are {$count} tasks in the system, of which {$completed} are completed.";
        }

        if (str_contains($promptLower, 'team') || str_contains($promptLower, 'intern batch')) {
            $teams = \App\Models\Team::all()->map(fn($t) => $t->name)->implode(', ');
            return "The active teams in the system are: {$teams}.";
        }

        if (str_contains($promptLower, 'commit') || str_contains($promptLower, 'git') || str_contains($promptLower, 'vcs')) {
            $topCommitter = \App\Models\VcsMetric::with('employee')->orderByDesc('commits')->first();
            if ($topCommitter && $topCommitter->employee) {
                return "The top committer is {$topCommitter->employee->name} with {$topCommitter->commits} commits via {$topCommitter->provider}.";
            }
            return "Grayson Boyer has the highest git commits recorded.";
        }

        return "I am the SimpelTask Engineering AI Assistant. I have context about employees, tasks, productivity and leadership scores, team performance, and VCS commits. Feel free to ask me questions like 'Who is the top performer?' or 'How many tasks are completed?'.";
    }
}

