<?php

namespace App\Services\Contracts;

use App\Models\Employee;
use App\Models\ProductivityScore;

interface MetricsServiceInterface
{
    public function generateForEmployee(Employee $employee): ProductivityScore;
    public function calculateCompletionRate(Employee $employee): float;
    public function calculateSpeedScore(Employee $employee): float;
    public function calculateConsistencyScore(Employee $employee): float;
    public function calculateTeamContribution(Employee $employee): float;
}
