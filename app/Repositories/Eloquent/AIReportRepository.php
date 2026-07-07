<?php

namespace App\Repositories\Eloquent;

use App\Models\AIReport;
use App\Repositories\Contracts\AIReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AIReportRepository implements AIReportRepositoryInterface
{
    public function all(): Collection
    {
        return AIReport::with(['employee', 'team'])->get();
    }

    public function latestForEmployee(int $employeeId): ?AIReport
    {
        return AIReport::where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function latestForTeam(int $teamId): ?AIReport
    {
        return AIReport::where('team_id', $teamId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function create(array $data): AIReport
    {
        return AIReport::create($data);
    }

    public function delete(int $id): bool
    {
        $report = AIReport::find($id);
        if ($report) {
            return $report->delete();
        }
        return false;
    }
}
