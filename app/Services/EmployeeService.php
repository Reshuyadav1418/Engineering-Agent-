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

        // Fetch VCS metrics (real or simulated) and calculate initial scores/reports instantly
        $vcsService = app(\App\Services\VCSIntegrationService::class);
        $vcsService->syncForEmployee($employee);

        return $employee;
    }

    public function update(int $id, array $data): Employee
    {
        // Capture old usernames BEFORE saving so we can detect changes
        $old = $this->employeeRepo->find($id);
        $oldGithub = $old?->github_username;
        $oldGitlab = $old?->gitlab_username;

        $employee = $this->employeeRepo->update($id, $data);

        // If either VCS username changed, re-sync metrics with the new username
        $githubChanged = ($data['github_username'] ?? $oldGithub) !== $oldGithub;
        $gitlabChanged = ($data['gitlab_username'] ?? $oldGitlab) !== $oldGitlab;

        if ($githubChanged || $gitlabChanged) {
            $vcsService = app(\App\Services\VCSIntegrationService::class);
            $vcsService->syncForEmployee($employee->fresh()); // fresh() ensures new usernames are loaded
        }

        return $employee;
    }

    public function delete(int $id): void
    {
        $this->employeeRepo->delete($id);
    }

    public function search(array $filters, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->employeeRepo->search($filters, $perPage);
    }
}
