<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Services\Contracts\MetricsServiceInterface;
use Illuminate\Console\Command;

class EngineeringCommand extends Command
{
    protected $signature = 'engineering';
    protected $description = 'Generate productivity and leadership scores for all employees.';

    protected MetricsServiceInterface $metricsService;
    protected LeadershipScoreServiceInterface $leadershipScoreService;

    public function __construct(
        MetricsServiceInterface $metricsService,
        LeadershipScoreServiceInterface $leadershipScoreService
    ) {
        parent::__construct();

        $this->metricsService = $metricsService;
        $this->leadershipScoreService = $leadershipScoreService;
    }

    public function handle(): int
    {
        $employees = Employee::with('tasks')->get();
        if ($employees->isEmpty()) {
            $this->info('No employees found.');
            return Command::SUCCESS;
        }

        foreach ($employees as $employee) {
            $productivityScore = $this->metricsService->generateForEmployee($employee);
            $this->leadershipScoreService->generateForEmployee($employee, $productivityScore);
            $this->info("Generated scores for {$employee->name} (Employee ID: {$employee->id})");
        }

        $this->info('Engineering scores generation complete.');

        return Command::SUCCESS;
    }
}
