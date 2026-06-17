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
}
