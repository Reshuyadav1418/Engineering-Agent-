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

    /**
     * Calculate productivity score for a specific date period (in-memory, not persisted).
     *
     * @param  Employee              $employee
     * @param  \Carbon\Carbon|null   $from
     * @param  \Carbon\Carbon|null   $to
     * @return float  0–10
     */
    public function calculateForPeriod(Employee $employee, $from, $to): float
    {
        // Filter individual tasks to those assigned within the period
        $allIndividualTasks = $employee->tasks;
        $periodTasks = $allIndividualTasks->filter(function ($task) use ($from, $to) {
            $date = $task->assigned_date;
            if (!$date) return false;
            $d = \Carbon\Carbon::parse($date);
            return (!$from || $d->gte($from)) && (!$to || $d->lte($to));
        });

        // Use the already-eager-loaded collection (no parentheses = no new SQL query)
        $allTeamMembers    = $employee->taskMembers; // pre-loaded via Employee::with(['taskMembers','taskMembers.task'])
        $periodTeamMembers = $allTeamMembers->filter(function ($tm) use ($from, $to) {
            $date = $tm->started_at ?? $tm->created_at;
            if (!$date) return false;
            $d = \Carbon\Carbon::parse($date);
            return (!$from || $d->gte($from)) && (!$to || $d->lte($to));
        });

        $individualAssigned  = $periodTasks->count();
        $individualCompleted = $periodTasks->where('status', 'Completed')->count();
        $teamAssigned        = $periodTeamMembers->count();
        $teamCompleted       = $periodTeamMembers->where('status', 'Completed')->count();

        $totalAssigned  = $individualAssigned + $teamAssigned;
        if ($totalAssigned === 0) return 0.0;

        // Completion rate
        $completionRate = round((($individualCompleted + $teamCompleted) / $totalAssigned) * 100, 2);

        // Speed score (period tasks only)
        $totalEstimated = $periodTasks->where('status', 'Completed')->sum('estimated_hours');
        $totalActual    = $periodTasks->where('status', 'Completed')->sum('actual_hours');
        foreach ($periodTeamMembers->where('status', 'Completed') as $tm) {
            $totalEstimated += $tm->assigned_hours;
            $totalActual    += $tm->actual_hours;
        }
        $speedScore = ($totalEstimated > 0 && $totalActual > 0)
            ? round(min(10, max(0, ($totalEstimated / $totalActual) * 5)), 2)
            : 0.0;

        // Consistency score (period completed dates)
        $completedDates = collect();
        foreach ($periodTasks->where('status', 'Completed') as $t) {
            if ($t->completed_date) $completedDates->push($t->completed_date);
        }
        foreach ($periodTeamMembers->where('status', 'Completed') as $tm) {
            $completedDates->push($tm->completed_at ?? $tm->updated_at);
        }
        $consistencyScore = 0.0;
        if ($completedDates->isNotEmpty()) {
            $weeklyCounts = $completedDates
                ->groupBy(fn($d) => \Carbon\Carbon::parse($d)->weekOfYear)
                ->map->count()->values();
            $mean = $weeklyCounts->avg();
            if ($mean > 0) {
                $variance  = $weeklyCounts->reduce(fn($c, $v) => $c + ($v - $mean) ** 2, 0) / max(1, $weeklyCounts->count());
                $rawScore  = (1 - (sqrt($variance) / $mean)) * 10;
                $consistencyScore = round(min(10, max(0, $rawScore)), 2);
            }
        }

        // Team contribution (period team members only)
        $teamContribution = 0.0;
        if ($periodTeamMembers->isNotEmpty()) {
            $total = 0.0;
            foreach ($periodTeamMembers as $tm) {
                $comp = ($tm->status === 'Completed' || ($tm->task && $tm->task->status === 'Completed')) ? 10.0 : 0.0;
                $eff  = ($tm->assigned_hours > 0 && $tm->actual_hours > 0)
                    ? min(10.0, ($tm->assigned_hours / $tm->actual_hours) * 10.0)
                    : ($comp > 0 ? 10.0 : 0.0);
                $role = strtolower($tm->role ?? '');
                $roleScore = in_array($role, ['developer', 'lead']) ? 10.0
                    : ($role === 'tester' ? 8.0 : ($role === 'reviewer' ? 7.0 : 8.0));
                $total += ($comp * 0.5) + ($eff * 0.3) + ($roleScore * 0.2);
            }
            $teamContribution = round($total / $periodTeamMembers->count(), 2);
        }

        // Individual score
        $completionScore  = $completionRate / 10;
        $individualScore  = round(min(10, max(0, ($completionScore * 0.4) + ($speedScore * 0.3) + ($consistencyScore * 0.3))), 2);

        // Blend individual + team
        $hasIndividual = $individualAssigned > 0;
        $hasTeam       = $teamAssigned > 0;

        if ($hasIndividual && $hasTeam) {
            return round(($individualScore * 0.6) + ($teamContribution * 0.4), 2);
        } elseif ($hasIndividual) {
            return $individualScore;
        } elseif ($hasTeam) {
            return $teamContribution;
        }

        return 0.0;
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
