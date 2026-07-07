<?php

namespace App\Services\Contracts;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

interface EmployeeServiceInterface
{
    public function all(): Collection;
    public function find(int $id): ?Employee;
    public function create(array $data): Employee;
    public function update(int $id, array $data): Employee;
    public function delete(int $id): void;

    /**
     * Search / filter employees by name, department, role, or github_username.
     */
    public function search(array $filters, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
