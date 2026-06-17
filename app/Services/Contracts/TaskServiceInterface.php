<?php

namespace App\Services\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskServiceInterface
{
    /** Get all tasks */
    public function all(): Collection;

    /** Find task by ID */
    public function find(int $id): ?Task;

    /** Create a new task */
    public function create(array $data): Task;

    /** Update existing task */
    public function update(int $id, array $data): Task;

    /** Delete a task */
    public function delete(int $id): bool;
}
