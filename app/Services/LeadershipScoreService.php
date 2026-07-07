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

    public function getLatestLeaderboard(?int $limit = null): Collection
    {
        return $this->leadershipRepository->latestForAll($limit);
    }

    /**
     * Calculate leadership score in-memory for a specific date period (not persisted).
     *
     * @param  Employee            $employee
     * @param  float               $productivityScore  pre-computed for the same period
     * @param  \Carbon\Carbon|null $from
     * @param  \Carbon\Carbon|null $to
     * @return float  0–10
     */
    public function calculateOnDemand(Employee $employee, float $productivityScore, $from, $to): float
    {
        // Use pre-loaded collections — NO new SQL queries per employee
        $periodTasks = $employee->tasks->filter(function ($t) use ($from, $to) {
            $d = $t->assigned_date ? \Carbon\Carbon::parse($t->assigned_date) : null;
            return $d && (!$from || $d->gte($from)) && (!$to || $d->lte($to));
        });
        $periodTeamMembers = $employee->taskMembers->filter(function ($tm) use ($from, $to) {
            $d = $tm->started_at ?? $tm->created_at;
            $d = $d ? \Carbon\Carbon::parse($d) : null;
            return $d && (!$from || $d->gte($from)) && (!$to || $d->lte($to));
        });

        $totalAssigned  = $periodTasks->count() + $periodTeamMembers->count();
        $totalCompleted = $periodTasks->where('status', 'Completed')->count()
            + $periodTeamMembers->where('status', 'Completed')->count();

        $completionRate       = $totalAssigned > 0 ? ($totalCompleted / $totalAssigned) * 100 : 0;
        $completionRateScore  = $completionRate / 10;

        // Consistency: scale by number of completed tasks in period
        $totalCompletedAll = $totalCompleted;
        $consistencyScore  = min(10.0, max(0.0, round($totalCompletedAll / 2, 2)));

        // Collaboration: use pre-loaded teams collection — no extra query
        $collabPoints       = ($periodTasks->count() * 0.5) + ($employee->teams->count() * 1.5) + ($periodTeamMembers->count() * 1.5);
        $collaborationScore = min(10.0, max(0.0, round($collabPoints, 2)));

        $leadershipScore = round(
            ($productivityScore  * 0.4)
            + ($completionRateScore * 0.3)
            + ($consistencyScore    * 0.2)
            + ($collaborationScore  * 0.1),
            2
        );

        return min(10.0, max(0.0, $leadershipScore));
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
