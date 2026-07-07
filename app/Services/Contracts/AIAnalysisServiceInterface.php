<?php

namespace App\Services\Contracts;

interface AIAnalysisServiceInterface
{
    public function generateReport(
        string $employeeName,
        int $individualTasks,
        int $teamTasks,
        float $teamContribution,
        float $productivityScore,
        float $leadershipScore
    ): array;

    public function generateTeamReport(
        string $teamName,
        int $membersCount,
        int $completedTasks,
        float $productivityScore,
        float $leadershipScore,
        float $completionRate,
        float $consistencyScore,
        float $collaborationScore
    ): array;
}
