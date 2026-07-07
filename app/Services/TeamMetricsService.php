<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Employee;
use App\Services\Contracts\TeamMetricsServiceInterface;
use App\Services\Contracts\MetricsServiceInterface;
use Illuminate\Support\Collection;

class TeamMetricsService implements TeamMetricsServiceInterface
{
    protected MetricsServiceInterface $metricsService;

    public function __construct(MetricsServiceInterface $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Calculate and return metrics for a single team.
     *
     * @param Team $team
     * @return array
     */
    public function getTeamMetrics(Team $team): array
    {
        $members = $team->members;
        $membersCount = $members->count();

        // 1. Average of team members' productivity scores
        if ($membersCount > 0) {
            $productivitySum = $members->map(function ($employee) {
                $latestScore = $employee->productivityScores()->latest()->first();
                return $latestScore ? $latestScore->productivity_score : 0.0;
            })->sum();
            $avgMembersProductivity = round($productivitySum / $membersCount, 2);
        } else {
            $avgMembersProductivity = 0.0;
        }

        // 2. Team task completion rate (scaled 0-10)
        $teamTasks = $team->tasks;
        $totalTeamTasks = $teamTasks->count();
        if ($totalTeamTasks > 0) {
            $completedTeamTasks = $teamTasks->where('status', 'Completed')->count();
            $teamCompletionRate = round(($completedTeamTasks / $totalTeamTasks) * 10, 2);
        } else {
            // Fall back to average of team members' completion rates (divided by 10 to be on 0-10 scale)
            if ($membersCount > 0) {
                $memberRatesSum = $members->map(function ($employee) {
                    $latestScore = $employee->productivityScores()->latest()->first();
                    return $latestScore ? ($latestScore->completion_rate / 10.0) : 0.0;
                })->sum();
                $teamCompletionRate = round($memberRatesSum / $membersCount, 2);
            } else {
                $teamCompletionRate = 0.0;
            }
        }

        // 3. Average task delivery speed (scaled 0-10)
        $completedTeamTasks = $teamTasks->where('status', 'Completed');
        $totalEstimated = $completedTeamTasks->sum('estimated_hours');
        $totalActual = $completedTeamTasks->sum('actual_hours');
        
        if ($totalEstimated > 0 && $totalActual > 0) {
            $avgDeliverySpeed = round(min(10.0, max(0.0, ($totalEstimated / $totalActual) * 5.0)), 2);
        } else {
            // Fallback to average of members' delivery speeds
            if ($membersCount > 0) {
                $memberSpeedsSum = $members->map(function ($employee) {
                    return $this->metricsService->calculateSpeedScore($employee);
                })->sum();
                $avgDeliverySpeed = round($memberSpeedsSum / $membersCount, 2);
            } else {
                $avgDeliverySpeed = 0.0;
            }
        }

        // 4. Team collaboration score (scaled 0-10)
        if ($membersCount > 0) {
            $collabSum = $members->map(function ($employee) {
                $individualCount = $employee->tasks->count();
                $teamCount = $employee->teams()->count();
                $teamTasksCount = $employee->taskMembers()->count();

                $collabPoints = ($individualCount * 0.5) + ($teamCount * 1.5) + ($teamTasksCount * 1.5);
                return min(10.0, max(0.0, round($collabPoints, 2)));
            })->sum();
            $teamCollaboration = round($collabSum / $membersCount, 2);
        } else {
            $teamCollaboration = 0.0;
        }

        // Team Productivity Score
        $teamProductivityScore = round(($avgMembersProductivity + $teamCompletionRate + $avgDeliverySpeed + $teamCollaboration) / 4, 2);

        // Team Consistency Score
        if ($membersCount > 0) {
            $consistencySum = $members->map(function ($employee) {
                $completedIndividual = $employee->tasks->where('status', 'Completed')->count();
                $completedTeam = $employee->taskMembers()->where('status', 'Completed')->count();
                $totalCompleted = $completedIndividual + $completedTeam;

                if ($totalCompleted === 0) {
                    return 0.0;
                }

                return min(10.0, max(0.0, round($totalCompleted / 2, 2)));
            })->sum();
            $teamConsistency = round($consistencySum / $membersCount, 2);
        } else {
            $teamConsistency = 0.0;
        }

        // Team Leadership Score
        $teamLeadershipScore = round(
            ($teamProductivityScore * 0.4)
            + ($teamCompletionRate * 0.3)
            + ($teamConsistency * 0.2)
            + ($teamCollaboration * 0.1),
            2
        );

        return [
            'team_id' => $team->id,
            'team_name' => $team->name,
            'description' => $team->description,
            'members_count' => $membersCount,
            'completed_tasks' => $teamTasks->where('status', 'Completed')->count(),
            'productivity_score' => min(10.0, max(0.0, $teamProductivityScore)),
            'leadership_score' => min(10.0, max(0.0, $teamLeadershipScore)),
            'team_completion_rate' => $teamCompletionRate,
            'avg_delivery_speed' => $avgDeliverySpeed,
            'team_consistency' => $teamConsistency,
            'team_collaboration' => $teamCollaboration,
        ];
    }

    /**
     * Get the ranked leaderboard of all teams.
     *
     * @return Collection
     */
    public function getTeamLeaderboard(): Collection
    {
        $teams = Team::with(['members', 'tasks'])->get();

        $leaderboard = $teams->map(function ($team) {
            return $this->getTeamMetrics($team);
        });

        return $leaderboard->sortByDesc('leadership_score')->values();
    }
}
