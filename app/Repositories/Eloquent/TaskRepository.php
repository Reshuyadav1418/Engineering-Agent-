<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(): Collection
    {
        return Task::all();
    }

    public function find(int $id): ?Task
    {
        return Task::find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): Task
    {
        $task = $this->find($id);
        $task->update($data);
        return $task;
    }

    public function delete(int $id): void
    {
        $task = $this->find($id);
        if ($task) {
            $task->delete();
        }
    }

    public function paginated(int $perPage = 20, ?string $selectedMonth = null)
    {
        $query = Task::with(['employee', 'team', 'members.employee']);
        
        if ($selectedMonth) {
            $start = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth()->toDateString();
            $end = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth()->toDateString();
            $query->whereBetween('assigned_date', [$start, $end]);
        }
        
        return $query->latest('id')->paginate($perPage);
    }

    public function getAvailableMonths()
    {
        return Task::whereNotNull('assigned_date')
            ->select('assigned_date')
            ->distinct()
            ->orderBy('assigned_date', 'desc')
            ->pluck('assigned_date')
            ->map(fn ($date) => $date instanceof \Carbon\Carbon ? $date->format('Y-m') : \Carbon\Carbon::parse($date)->format('Y-m'))
            ->unique()
            ->values();
    }
}
