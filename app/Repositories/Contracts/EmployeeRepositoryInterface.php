<?php

namespace App\Repositories\Contracts;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

interface EmployeeRepositoryInterface
{    /**
     * Get all employees.
     */
    public function all(): Collection;

    /**
     * Find an employee by ID.
     */
    public function find(int $id): ?Employee;

    /**
     * Create a new employee.
     */
    public function create(array $data): Employee;

    /**
     * Update an existing employee.
     */
    public function update(int $id, array $data): Employee;

    /**
     * Delete an employee.
     */
    public function delete(int $id): bool;
}
