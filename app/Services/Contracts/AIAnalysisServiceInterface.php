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
}
