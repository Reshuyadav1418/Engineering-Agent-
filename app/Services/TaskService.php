<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\Contracts\TaskServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskService implements TaskServiceInterface
{
    protected $taskRepo;

    public function __construct(TaskRepositoryInterface $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function all(): Collection
    {
        return $this->taskRepo->all();
    }

    public function find(int $id): ?Task
    {
        return $this->taskRepo->find($id);
    }

    public function create(array $data): Task
    {
        return $this->taskRepo->create($data);
    }

    public function update(int $id, array $data): Task
    {
        return $this->taskRepo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $this->taskRepo->delete($id);
        return true;
    }
}
