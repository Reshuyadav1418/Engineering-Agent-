<?php

namespace App\Repositories\Contracts;

use App\Models\AIReport;
use Illuminate\Database\Eloquent\Collection;

interface AIReportRepositoryInterface
{
    public function all(): Collection;
    public function latestForEmployee(int $employeeId): ?AIReport;
    public function latestForTeam(int $teamId): ?AIReport;
    public function create(array $data): AIReport;
    public function delete(int $id): bool;
}
