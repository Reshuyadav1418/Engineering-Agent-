<?php

namespace App\Services;

use App\Services\Contracts\AIAnalysisServiceInterface;
use Illuminate\Support\Facades\Config;

class AIAnalysisService implements AIAnalysisServiceInterface
{
    protected OllamaProvider $ollamaProvider;

    public function __construct(OllamaProvider $ollamaProvider)
    {
        $this->ollamaProvider = $ollamaProvider;
    }

    public function generateReport(
        string $employeeName,
        int $individualTasks,
        int $teamTasks,
        float $teamContribution,
        float $productivityScore,
        float $leadershipScore
    ): array {
        $provider = Config::get('ai.provider', 'ollama');

        return $this->ollamaProvider->generateOllamaReport(
            $employeeName,
            $individualTasks,
            $teamTasks,
            $teamContribution,
            $productivityScore,
            $leadershipScore
        );
    }

    public function generateTeamReport(
        string $teamName,
        int $membersCount,
        int $completedTasks,
        float $productivityScore,
        float $leadershipScore,
        float $completionRate,
        float $consistencyScore,
        float $collaborationScore
    ): array {
        return $this->ollamaProvider->generateOllamaTeamReport(
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
}
