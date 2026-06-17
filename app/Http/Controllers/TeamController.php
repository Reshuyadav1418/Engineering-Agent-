<?php

namespace App\Http\Controllers;

use App\Services\Contracts\TeamServiceInterface;
use App\Services\Contracts\EmployeeServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeamController extends Controller
{
    protected $teamService;
    protected $employeeService;

    public function __construct(
        TeamServiceInterface $teamService,
        EmployeeServiceInterface $employeeService
    ) {
        $this->teamService = $teamService;
        $this->employeeService = $employeeService;
    }

    /**
     * Display a listing of the teams (Dashboard/Index).
     */
    public function index(): View
    {
        $teams = $this->teamService->all()->map(function ($team) {
            // Aggregate metrics
            $activeTasks = $team->tasks->where('status', '!=', 'Completed')->count();
            $completedTasks = $team->tasks->where('status', 'Completed')->count();

            $totalProductivity = 0;
            $totalScore = 0;
            $memberCount = $team->members->count();

            foreach ($team->members as $member) {
                $latestProd = optional($member->productivityScores()->latest('id')->first())->productivity_score ?? 0;
                $latestLead = optional($member->leadershipScores()->latest('id')->first())->leadership_score ?? 0;
                $totalProductivity += $latestProd;
                $totalScore += $latestLead;
            }

            $teamProductivity = $memberCount > 0 ? round($totalProductivity / $memberCount, 2) : 0;
            $teamScore = $memberCount > 0 ? round($totalScore / $memberCount, 2) : 0;

            $team->active_tasks_count = $activeTasks;
            $team->completed_tasks_count = $completedTasks;
            $team->productivity_score = $teamProductivity;
            $team->team_score = $teamScore;

            return $team;
        });

        return view('teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create(): View
    {
        $employees = $this->employeeService->all();
        return view('teams.create', compact('employees'));
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_lead_id' => 'nullable|exists:employees,id',
            'members' => 'nullable|array',
            'members.*.employee_id' => 'required|exists:employees,id',
            'members.*.role' => 'required|string|max:255',
        ]);

        $teamData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'team_lead_id' => $data['team_lead_id'] ?? null,
        ];

        $members = $data['members'] ?? [];

        $this->teamService->create($teamData, $members);

        return redirect()->route('teams.index')->with('success', 'Team created successfully');
    }

    /**
     * Display the specified team.
     */
    public function show(int $id): View
    {
        $team = $this->teamService->find($id);
        if (!$team) {
            abort(404, 'Team not found');
        }

        // Calculate metrics
        $activeTasks = $team->tasks->where('status', '!=', 'Completed');
        $completedTasks = $team->tasks->where('status', 'Completed');

        $totalProductivity = 0;
        $totalScore = 0;
        $memberCount = $team->members->count();

        foreach ($team->members as $member) {
            $latestProd = optional($member->productivityScores()->latest('id')->first())->productivity_score ?? 0;
            $latestLead = optional($member->leadershipScores()->latest('id')->first())->leadership_score ?? 0;
            $totalProductivity += $latestProd;
            $totalScore += $latestLead;
        }

        $teamProductivity = $memberCount > 0 ? round($totalProductivity / $memberCount, 2) : 0;
        $teamScore = $memberCount > 0 ? round($totalScore / $memberCount, 2) : 0;

        $team->active_tasks = $activeTasks;
        $team->completed_tasks = $completedTasks;
        $team->productivity_score = $teamProductivity;
        $team->team_score = $teamScore;

        return view('teams.show', compact('team'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(int $id): View
    {
        $team = $this->teamService->find($id);
        if (!$team) {
            abort(404, 'Team not found');
        }
        $employees = $this->employeeService->all();
        return view('teams.edit', compact('team', 'employees'));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_lead_id' => 'nullable|exists:employees,id',
            'members' => 'nullable|array',
            'members.*.employee_id' => 'required|exists:employees,id',
            'members.*.role' => 'required|string|max:255',
        ]);

        $teamData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'team_lead_id' => $data['team_lead_id'] ?? null,
        ];

        $members = $data['members'] ?? [];

        $this->teamService->update($id, $teamData, $members);

        return redirect()->route('teams.index')->with('success', 'Team updated successfully');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->teamService->delete($id);
        return redirect()->route('teams.index')->with('success', 'Team deleted successfully');
    }
}
