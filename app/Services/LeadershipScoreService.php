<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeadershipScore;
use App\Models\ProductivityScore;
use App\Repositories\Contracts\LeadershipScoreRepositoryInterface;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class LeadershipScoreService implements LeadershipScoreServiceInterface
{
    protected LeadershipScoreRepositoryInterface $leadershipRepository;

    public function __construct(LeadershipScoreRepositoryInterface $leadershipRepository)
    {
        $this->leadershipRepository = $leadershipRepository;
    }

    public function generateForEmployee(Employee $employee, ProductivityScore $productivityScore): LeadershipScore
    {
        $completionRateScore = $productivityScore->completion_rate / 10;
        $consistencyScore = $this->estimateConsistencyScore($employee);
        $collaborationScore = $this->estimateCollaborationScore($employee);

        $leadershipScore = round(
            ($productivityScore->productivity_score * 0.4)
            + ($completionRateScore * 0.3)
            + ($consistencyScore * 0.2)
            + ($collaborationScore * 0.1),
            2
        );

        return $this->leadershipRepository->create([
            'employee_id' => $employee->id,
            'productivity_score' => $productivityScore->productivity_score,
            'leadership_score' => min(10, max(0, $leadershipScore)),
        ]);
    }

    public function getLatestLeaderboard(): Collection
    {
        return $this->leadershipRepository->latestForAll()->sortByDesc('leadership_score');
    }

    protected function estimateConsistencyScore(Employee $employee): float
    {
        $completedIndividual = $employee->tasks->where('status', 'Completed')->count();
        $completedTeam = $employee->taskMembers()->where('status', 'Completed')->count();
        $totalCompleted = $completedIndividual + $completedTeam;

        if ($totalCompleted === 0) {
            return 0.0;
        }

        return min(10.0, max(0.0, round($totalCompleted / 2, 2)));
    }

    protected function estimateCollaborationScore(Employee $employee): float
    {
        $individualCount = $employee->tasks->count();
        $teamCount = $employee->teams()->count();
        $teamTasksCount = $employee->taskMembers()->count();

        $collabPoints = ($individualCount * 0.5) + ($teamCount * 1.5) + ($teamTasksCount * 1.5);

        return min(10.0, max(0.0, round($collabPoints, 2)));
    }
}
