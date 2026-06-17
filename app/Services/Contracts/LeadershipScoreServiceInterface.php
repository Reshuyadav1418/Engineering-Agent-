<?php

namespace App\Services\Contracts;

use App\Models\Employee;
use App\Models\LeadershipScore;
use App\Models\ProductivityScore;
use Illuminate\Database\Eloquent\Collection;

interface LeadershipScoreServiceInterface
{
    public function generateForEmployee(Employee $employee, ProductivityScore $productivityScore): LeadershipScore;
    public function getLatestLeaderboard(): Collection;
}
