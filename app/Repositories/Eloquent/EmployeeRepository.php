<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function all(): Collection
    {
        return Employee::all();
    }

    public function find(int $id): ?Employee
    {
        return Employee::find($id);
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(int $id, array $data): Employee
    {
        $employee = $this->find($id);
        $employee->update($data);
        return $employee;
    }

    public function delete(int $id): bool
    {
        return Employee::destroy($id) > 0;
    }

    public function search(array $filters): Collection
    {
        $query = Employee::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['department'])) {
            $query->where('department', 'like', '%' . $filters['department'] . '%');
        }
        if (!empty($filters['role'])) {
            $query->where('role', 'like', '%' . $filters['role'] . '%');
        }
        if (!empty($filters['github_username'])) {
            $query->where('github_username', 'like', '%' . $filters['github_username'] . '%');
        }

        return $query->orderBy('name')->get();
    }
}
