<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ProductivityScore;
use App\Repositories\Contracts\ProductivityScoreRepositoryInterface;
use App\Services\Contracts\MetricsServiceInterface;

class MetricsService implements MetricsServiceInterface
{
    protected ProductivityScoreRepositoryInterface $scoreRepository;

    public function __construct(ProductivityScoreRepositoryInterface $scoreRepository)
    {
        $this->scoreRepository = $scoreRepository;
    }

    public function generateForEmployee(Employee $employee): ProductivityScore
    {
        $individualTasks = $employee->tasks;
        $individualAssigned = $individualTasks->count();
        $individualCompleted = $individualTasks->where('status', 'Completed')->count();

        $teamTasksCount = $employee->taskMembers()->count();
        $teamCompletedCount = $employee->taskMembers()->where('status', 'Completed')->count();

        $tasksAssigned = $individualAssigned + $teamTasksCount;
        $tasksCompleted = $individualCompleted + $teamCompletedCount;

        $completionRate = $this->calculateCompletionRate($employee);
        $avgCompletionTime = $this->calculateAverageCompletionTime($employee);
        $speedScore = $this->calculateSpeedScore($employee);
        $consistencyScore = $this->calculateConsistencyScore($employee);
        
        $individualScore = $this->calculateProductivityScore($completionRate, $speedScore, $consistencyScore);
        $teamContribution = $this->calculateTeamContribution($employee);

        // Blend: 60% Individual, 40% Team Contribution
        $hasIndividual = $individualAssigned > 0;
        $hasTeam = $teamTasksCount > 0;

        if ($hasIndividual && $hasTeam) {
            $productivityScoreVal = round(($individualScore * 0.6) + ($teamContribution * 0.4), 2);
        } elseif ($hasIndividual) {
            $productivityScoreVal = $individualScore;
        } elseif ($hasTeam) {
            $productivityScoreVal = $teamContribution;
        } else {
            $productivityScoreVal = 0.0;
        }

        return $this->scoreRepository->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'tasks_assigned' => $tasksAssigned,
                'tasks_completed' => $tasksCompleted,
                'completion_rate' => $completionRate,
                'avg_completion_time' => $avgCompletionTime,
                'team_contribution' => $teamContribution,
                'productivity_score' => $productivityScoreVal,
            ]
        );
    }

    public function calculateCompletionRate(Employee $employee): float
    {
        $individualAssigned = $employee->tasks->count();
        $teamAssigned = $employee->taskMembers()->count();
        $totalAssigned = $individualAssigned + $teamAssigned;

        if ($totalAssigned === 0) {
            return 0.0;
        }

        $individualCompleted = $employee->tasks->where('status', 'Completed')->count();
        $teamCompleted = $employee->taskMembers()->where('status', 'Completed')->count();
        $totalCompleted = $individualCompleted + $teamCompleted;

        return round(($totalCompleted / $totalAssigned) * 100, 2);
    }

    public function calculateSpeedScore(Employee $employee): float
    {
        $completedTasks = $employee->tasks->where('status', 'Completed');
        $totalEstimated = $completedTasks->sum('estimated_hours');
        $totalActual = $completedTasks->sum('actual_hours');

        $completedTeamMembers = $employee->taskMembers()->where('status', 'Completed')->get();
        foreach ($completedTeamMembers as $tm) {
            $totalEstimated += $tm->assigned_hours;
            $totalActual += $tm->actual_hours;
        }

        if ($totalEstimated <= 0 || $totalActual <= 0) {
            return 0.0;
        }

        $ratio = $totalEstimated / $totalActual;
        $score = $ratio * 5;

        return round(min(10, max(0, $score)), 2);
    }

    public function calculateConsistencyScore(Employee $employee): float
    {
        $completedTasks = $employee->tasks->where('status', 'Completed');
        $completedDates = collect();

        foreach ($completedTasks as $task) {
            if ($task->completed_date) {
                $completedDates->push($task->completed_date);
            }
        }

        $completedTeamTasks = $employee->taskMembers()->where('status', 'Completed')->with('task')->get();
        foreach ($completedTeamTasks as $ctm) {
            if ($ctm->completed_at) {
                $completedDates->push($ctm->completed_at);
            } elseif ($ctm->task && $ctm->task->completed_date) {
                $completedDates->push($ctm->task->completed_date);
            }
        }

        if ($completedDates->isEmpty()) {
            return 0.0;
        }

        $weeklyCounts = $completedDates
            ->groupBy(fn ($date) => \Carbon\Carbon::parse($date)->weekOfYear)
            ->map->count()
            ->values();

        $mean = $weeklyCounts->avg();
        $variance = $weeklyCounts->reduce(fn ($carry, $count) => $carry + ($count - $mean) ** 2, 0) / max(1, $weeklyCounts->count());
        $stddev = sqrt($variance);

        if ($mean <= 0) {
            return 0.0;
        }

        $rawScore = (1 - ($stddev / $mean)) * 10;
        return round(min(10, max(0, $rawScore)), 2);
    }

    public function calculateTeamContribution(Employee $employee): float
    {
        $teamTaskMembers = $employee->taskMembers()->with('task')->get();
        if ($teamTaskMembers->isEmpty()) {
            return 0.0;
        }

        $totalContribution = 0.0;
        foreach ($teamTaskMembers as $member) {
            // Task Completion: 10 if completed, else 0
            $completionScore = ($member->status === 'Completed' || ($member->task && $member->task->status === 'Completed')) ? 10.0 : 0.0;

            // Time Efficiency: ratio of assigned to actual
            $efficiencyScore = 0.0;
            if ($member->assigned_hours > 0 && $member->actual_hours > 0) {
                $efficiencyScore = min(10.0, ($member->assigned_hours / $member->actual_hours) * 10.0);
            } elseif ($member->status === 'Completed' || ($member->task && $member->task->status === 'Completed')) {
                $efficiencyScore = 10.0;
            }

            // Role Contribution: Developer = 10, Lead = 10, Tester = 8, Reviewer = 7, other = 8
            $role = strtolower($member->role ?? '');
            if ($role === 'developer' || $role === 'lead') {
                $roleScore = 10.0;
            } elseif ($role === 'tester') {
                $roleScore = 8.0;
            } elseif ($role === 'reviewer') {
                $roleScore = 7.0;
            } else {
                $roleScore = 8.0;
            }

            // Contribution Score = (Task Completion × 50%) + (Time Efficiency × 30%) + (Role Contribution × 20%)
            $contribution = ($completionScore * 0.5) + ($efficiencyScore * 0.3) + ($roleScore * 0.2);
            $totalContribution += $contribution;
        }

        return round($totalContribution / $teamTaskMembers->count(), 2);
    }

    protected function calculateAverageCompletionTime(Employee $employee): float
    {
        $completedTasks = $employee->tasks->where('status', 'Completed')->filter(fn ($task) => $task->assigned_date && $task->completed_date);
        
        $completedTeamTasks = $employee->taskMembers()->where('status', 'Completed')->with('task')->get()
            ->filter(fn ($ctm) => $ctm->started_at && $ctm->completed_at);

        $totalCompleted = $completedTasks->count() + $completedTeamTasks->count();
        if ($totalCompleted === 0) {
            return 0.0;
        }

        $totalDays = $completedTasks->reduce(function ($carry, $task) {
            return $carry + max(0, $task->assigned_date->diffInDays($task->completed_date));
        }, 0);

        $totalDays += $completedTeamTasks->reduce(function ($carry, $ctm) {
            return $carry + max(0, $ctm->started_at->diffInDays($ctm->completed_at));
        }, 0);

        return round($totalDays / $totalCompleted, 2);
    }

    protected function calculateProductivityScore(float $completionRate, float $speedScore, float $consistencyScore): float
    {
        $completionScore = $completionRate / 10;
        $score = ($completionScore * 0.4) + ($speedScore * 0.3) + ($consistencyScore * 0.3);

        return round(min(10, max(0, $score)), 2);
    }
}
