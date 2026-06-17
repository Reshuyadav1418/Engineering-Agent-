<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\Team;
use App\Models\Task;
use App\Models\ProductivityScore;
use App\Models\LeadershipScore;
use App\Services\MetricsService;
use App\Services\LeadershipScoreService;
use App\Repositories\Contracts\ProductivityScoreRepositoryInterface;
use App\Repositories\Contracts\LeadershipScoreRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected $metricsService;
    protected $leadershipScoreService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metricsService = app(MetricsService::class);
        $this->leadershipScoreService = app(LeadershipScoreService::class);
    }

    public function test_individual_task_productivity_calculation(): void
    {
        $employee = Employee::create([
            'name' => 'Rahul Dev',
            'email' => 'rahul@example.com',
            'department' => 'Engineering',
            'role' => 'Developer',
            'github_username' => 'rahuldev',
        ]);

        // Create completed individual task
        $task = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Build Payment API',
            'description' => 'Create routes and integration',
            'status' => 'Completed',
            'assigned_date' => now()->subDays(5)->format('Y-m-d'),
            'completed_date' => now()->format('Y-m-d'),
            'estimated_hours' => 20,
            'actual_hours' => 15,
        ]);

        $score = $this->metricsService->generateForEmployee($employee);

        $this->assertEquals(1, $score->tasks_assigned);
        $this->assertEquals(1, $score->tasks_completed);
        $this->assertEquals(100, $score->completion_rate);
        $this->assertGreaterThan(0, $score->productivity_score);
    }

    public function test_team_task_contribution_and_blend_calculation(): void
    {
        $employee1 = Employee::create([
            'name' => 'Aman Verma',
            'email' => 'aman@example.com',
            'department' => 'Engineering',
            'role' => 'Reviewer',
            'github_username' => 'amanv',
        ]);

        $team = Team::create([
            'name' => 'Backend Core',
            'description' => 'Core APIs',
            'team_lead_id' => $employee1->id,
        ]);

        // Add to team
        $team->members()->attach($employee1->id, ['role' => 'Reviewer']);

        // Create team task
        $task = Task::create([
            'team_id' => $team->id,
            'title' => 'Build Auth Module',
            'description' => 'Security auth',
            'status' => 'Completed',
            'assigned_date' => now()->subDays(5)->format('Y-m-d'),
            'completed_date' => now()->format('Y-m-d'),
            'estimated_hours' => 40,
            'actual_hours' => 30,
        ]);

        // Add task member
        $taskMember = $task->members()->create([
            'employee_id' => $employee1->id,
            'role' => 'Reviewer',
            'assigned_hours' => 10,
            'actual_hours' => 8,
            'status' => 'Completed',
            'started_at' => now()->subDays(2),
            'completed_at' => now(),
        ]);

        // 1. Task Completion: Completed = 10
        // 2. Time Efficiency: 10 / 8 = 1.25 -> capped at 10
        // 3. Role Contribution: Reviewer = 7
        // Contribution Score = (10 * 0.5) + (10 * 0.3) + (7 * 0.2) = 5.0 + 3.0 + 1.4 = 9.4
        $teamContribution = $this->metricsService->calculateTeamContribution($employee1);
        $this->assertEquals(9.4, $teamContribution);

        $score = $this->metricsService->generateForEmployee($employee1);
        $this->assertEquals(9.4, $score->team_contribution);
        $this->assertEquals(9.4, $score->productivity_score); // Since no individual tasks exist

        // Let's generate leadership score
        $leadScore = $this->leadershipScoreService->generateForEmployee($employee1, $score);
        $this->assertGreaterThan(0, $leadScore->leadership_score);
    }
}
