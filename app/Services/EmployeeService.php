<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Services\Contracts\EmployeeServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class EmployeeService implements EmployeeServiceInterface
{
    protected $employeeRepo;

    public function __construct(EmployeeRepositoryInterface $employeeRepo)
    {
        $this->employeeRepo = $employeeRepo;
    }

    public function all(): Collection
    {
        return $this->employeeRepo->all();
    }

    public function find(int $id): ?Employee
    {
        return $this->employeeRepo->find($id);
    }

    public function create(array $data): Employee
    {
        $employee = $this->employeeRepo->create($data);

        // Generate initial zero-state scores so the employee appears on the leaderboard instantly
        $metricsService = app(\App\Services\Contracts\MetricsServiceInterface::class);
        $leadershipService = app(\App\Services\Contracts\LeadershipScoreServiceInterface::class);

        $prodScore = $metricsService->generateForEmployee($employee);
        $leadershipService->generateForEmployee($employee, $prodScore);

        return $employee;
    }

    public function update(int $id, array $data): Employee
    {
        return $this->employeeRepo->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->employeeRepo->delete($id);
    }

    public function search(array $filters): Collection
    {
        return $this->employeeRepo->search($filters);
    }
}
