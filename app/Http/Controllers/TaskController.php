<?php

namespace App\Http\Controllers;

use App\Services\Contracts\TaskServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskServiceInterface $taskService)
    {
        $this->taskService = $taskService;
    }

    /** Display a listing of the resource. */
    public function index(Request $request): View
    {
        $allTasks = $this->taskService->all();

        // Extract distinct months from assigned_date for the dropdown
        $availableMonths = $allTasks
            ->filter(fn ($t) => $t->assigned_date !== null)
            ->map(fn ($t) => \Carbon\Carbon::parse($t->assigned_date)->format('Y-m'))
            ->unique()
            ->sort()
            ->values();

        // Apply month filter if selected
        $selectedMonth = $request->get('month');
        $tasks = $selectedMonth
            ? $allTasks->filter(fn ($t) =>
                $t->assigned_date &&
                \Carbon\Carbon::parse($t->assigned_date)->format('Y-m') === $selectedMonth
              )->values()
            : $allTasks;

        return view('tasks.index', compact('tasks', 'availableMonths', 'selectedMonth'));
    }

    /** Show the form for creating a new resource. */
    public function create(): View
    {
        $employees = \App\Models\Employee::all();
        $teams     = \App\Models\Team::with('members')->get();

        // Build a map: team_id => [ { id, name, role } ]  used by Alpine.js
        $teamMembersMap = $teams->keyBy('id')->map(fn ($team) =>
            $team->members->map(fn ($emp) => [
                'employee_id' => $emp->id,
                'name'        => $emp->name,
                'role'        => $emp->pivot->role ?? $emp->role ?? 'Developer',
            ])->values()
        );

        return view('tasks.create', compact('employees', 'teams', 'teamMembersMap'));
    }

    /** Store a newly created resource in storage. */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'assignment_type' => 'required|in:individual,team',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Pending,In Progress,Completed',
            'assigned_date' => 'required|date',
            'completed_date' => 'nullable|date',
            'estimated_hours' => 'required|numeric',
            'actual_hours' => 'nullable|numeric',
        ];

        if ($request->input('assignment_type') === 'individual') {
            $rules['employee_id'] = 'required|exists:employees,id';
        } else {
            $rules['team_id'] = 'required|exists:teams,id';
            $rules['members'] = 'nullable|array';
            $rules['members.*.employee_id'] = 'required|exists:employees,id';
            $rules['members.*.role'] = 'required|string|max:255';
            $rules['members.*.assigned_hours'] = 'required|numeric';
            $rules['members.*.actual_hours'] = 'nullable|numeric';
            $rules['members.*.status'] = 'required|in:Pending,In Progress,Completed';
            $rules['members.*.started_at'] = 'nullable|date';
            $rules['members.*.completed_at'] = 'nullable|date';
        }

        $validated = $request->validate($rules);

        $taskData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'assigned_date' => $validated['assigned_date'],
            'completed_date' => $validated['completed_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'],
            'actual_hours' => $validated['actual_hours'] ?? 0,
        ];

        $affectedEmployeeIds = [];

        if ($validated['assignment_type'] === 'individual') {
            $taskData['employee_id'] = $validated['employee_id'];
            $taskData['team_id'] = null;
            $affectedEmployeeIds[] = $validated['employee_id'];
            $task = $this->taskService->create($taskData);
        } else {
            $taskData['employee_id'] = null;
            $taskData['team_id'] = $validated['team_id'];
            $task = $this->taskService->create($taskData);

            $members = $request->input('members', []);
            foreach ($members as $m) {
                $affectedEmployeeIds[] = $m['employee_id'];
                $task->members()->create([
                    'employee_id' => $m['employee_id'],
                    'role' => $m['role'],
                    'assigned_hours' => $m['assigned_hours'],
                    'actual_hours' => $m['actual_hours'] ?? 0,
                    'status' => $m['status'],
                    'started_at' => $m['started_at'] ? \Carbon\Carbon::parse($m['started_at']) : null,
                    'completed_at' => $m['completed_at'] ? \Carbon\Carbon::parse($m['completed_at']) : null,
                ]);
            }
        }

        $this->recalculateMetricsForEmployees($affectedEmployeeIds);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }

    /** Display the specified resource. */
    public function show(int $id): View
    {
        $task = \App\Models\Task::with(['employee', 'team', 'members.employee'])->find($id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        return view('tasks.show', compact('task'));
    }

    /** Show the form for editing the specified resource. */
    public function edit(int $id): View
    {
        $task = \App\Models\Task::with(['members'])->find($id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        $employees = \App\Models\Employee::all();
        $teams = \App\Models\Team::all();
        return view('tasks.edit', compact('task', 'employees', 'teams'));
    }

    /** Update the specified resource in storage. */
    public function update(Request $request, int $id): RedirectResponse
    {
        $task = \App\Models\Task::with(['members'])->find($id);
        if (!$task) {
            abort(404, 'Task not found');
        }

        $rules = [
            'assignment_type' => 'required|in:individual,team',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Pending,In Progress,Completed',
            'assigned_date' => 'required|date',
            'completed_date' => 'nullable|date',
            'estimated_hours' => 'required|numeric',
            'actual_hours' => 'nullable|numeric',
        ];

        if ($request->input('assignment_type') === 'individual') {
            $rules['employee_id'] = 'required|exists:employees,id';
        } else {
            $rules['team_id'] = 'required|exists:teams,id';
            $rules['members'] = 'nullable|array';
            $rules['members.*.employee_id'] = 'required|exists:employees,id';
            $rules['members.*.role'] = 'required|string|max:255';
            $rules['members.*.assigned_hours'] = 'required|numeric';
            $rules['members.*.actual_hours'] = 'nullable|numeric';
            $rules['members.*.status'] = 'required|in:Pending,In Progress,Completed';
            $rules['members.*.started_at'] = 'nullable|date';
            $rules['members.*.completed_at'] = 'nullable|date';
        }

        $validated = $request->validate($rules);

        $taskData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'assigned_date' => $validated['assigned_date'],
            'completed_date' => $validated['completed_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'],
            'actual_hours' => $validated['actual_hours'] ?? 0,
        ];

        $affectedEmployeeIds = [];

        // Track previously affected employees
        if ($task->employee_id) {
            $affectedEmployeeIds[] = $task->employee_id;
        }
        foreach ($task->members as $m) {
            $affectedEmployeeIds[] = $m->employee_id;
        }

        if ($validated['assignment_type'] === 'individual') {
            $taskData['employee_id'] = $validated['employee_id'];
            $taskData['team_id'] = null;
            $affectedEmployeeIds[] = $validated['employee_id'];
            
            $task->update($taskData);
            $task->members()->delete();
        } else {
            $taskData['employee_id'] = null;
            $taskData['team_id'] = $validated['team_id'];
            
            $task->update($taskData);

            $memberIds = [];
            $members = $request->input('members', []);
            foreach ($members as $m) {
                $affectedEmployeeIds[] = $m['employee_id'];
                $memberIds[] = $m['employee_id'];

                $task->members()->updateOrCreate(
                    ['employee_id' => $m['employee_id']],
                    [
                        'role' => $m['role'],
                        'assigned_hours' => $m['assigned_hours'],
                        'actual_hours' => $m['actual_hours'] ?? 0,
                        'status' => $m['status'],
                        'started_at' => $m['started_at'] ? \Carbon\Carbon::parse($m['started_at']) : null,
                        'completed_at' => $m['completed_at'] ? \Carbon\Carbon::parse($m['completed_at']) : null,
                    ]
                );
            }

            // Remove members that are no longer part of this task
            $task->members()->whereNotIn('employee_id', $memberIds)->delete();
        }

        $this->recalculateMetricsForEmployees($affectedEmployeeIds);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully');
    }

    /** Remove the specified resource from storage. */
    public function destroy(int $id): RedirectResponse
    {
        $task = \App\Models\Task::with(['members'])->find($id);
        $affectedEmployeeIds = [];
        if ($task) {
            if ($task->employee_id) {
                $affectedEmployeeIds[] = $task->employee_id;
            }
            foreach ($task->members as $m) {
                $affectedEmployeeIds[] = $m->employee_id;
            }
            $task->delete();
        }

        $this->recalculateMetricsForEmployees($affectedEmployeeIds);

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully');
    }

    /** Helper to recalculate performance metrics for given employees */
    protected function recalculateMetricsForEmployees(array $employeeIds)
    {
        $metricsService = app(\App\Services\Contracts\MetricsServiceInterface::class);
        $leadershipService = app(\App\Services\Contracts\LeadershipScoreServiceInterface::class);

        foreach (array_unique($employeeIds) as $employeeId) {
            $employee = \App\Models\Employee::find($employeeId);
            if ($employee) {
                $prod = $metricsService->generateForEmployee($employee);
                $leadershipService->generateForEmployee($employee, $prod);
            }
        }
    }
}
