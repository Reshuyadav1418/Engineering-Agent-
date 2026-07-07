<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Services\Contracts\EmployeeServiceInterface;
use App\Services\Contracts\MetricsServiceInterface;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeServiceInterface $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['name', 'department', 'role', 'github_username', 'gitlab_username']);
        $employees = $this->employeeService->search($filters, 20);
        return view('employees.index', compact('employees', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:employees,email',
            'department'      => 'required|string|max:255',
            'role'            => 'required|string|max:255',
            'github_username' => 'nullable|string|max:255',
            'gitlab_username' => 'nullable|string|max:255',
        ]);
        $employee = $this->employeeService->create($data);

        // Auto-initialize scores so they appear on the dashboard immediately
        $metricsService  = app(MetricsServiceInterface::class);
        $leadershipService = app(LeadershipScoreServiceInterface::class);
        $productivityScore = $metricsService->generateForEmployee($employee);
        $leadershipService->generateForEmployee($employee, $productivityScore);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $employee = $this->employeeService->find($id);
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $employee = $this->employeeService->find($id);
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => "required|email|unique:employees,email,$id",
            'department'      => 'required|string|max:255',
            'role'            => 'required|string|max:255',
            'github_username' => 'nullable|string|max:255',
            'gitlab_username' => 'nullable|string|max:255',
        ]);
        $this->employeeService->update($id, $data);
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->employeeService->delete($id);
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }

    /**
     * Get attendance and working hours for a specific employee.
     */
    public function getAttendanceHours(int $id): \Illuminate\Http\JsonResponse
    {
        $employee = $this->employeeService->find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Fetch last 30 logs of attendance and working hours
        $attendances = $employee->attendances()->orderBy('attendance_date', 'desc')->take(30)->get();
        $workingHours = $employee->workingHours()->orderBy('work_date', 'desc')->take(30)->get();

        // Merge records by date
        $mergedData = [];

        foreach ($attendances as $att) {
            $dateStr = $att->attendance_date->format('Y-m-d');
            if (!isset($mergedData[$dateStr])) {
                $mergedData[$dateStr] = [
                    'date' => $dateStr,
                    'status' => $att->status,
                    'hours' => 0.0,
                ];
            } else {
                $mergedData[$dateStr]['status'] = $att->status;
            }
        }

        foreach ($workingHours as $wh) {
            $dateStr = $wh->work_date->format('Y-m-d');
            if (!isset($mergedData[$dateStr])) {
                $mergedData[$dateStr] = [
                    'date' => $dateStr,
                    'status' => 'N/A',
                    'hours' => (float) $wh->hours_worked,
                ];
            } else {
                $mergedData[$dateStr]['hours'] = (float) $wh->hours_worked;
            }
        }

        // Sort descending by date
        krsort($mergedData);
        $records = array_values($mergedData);

        // Summaries
        $totalHours = (float) $workingHours->sum('hours_worked');
        $presentCount = $attendances->where('status', 'Present')->count();
        $absentCount = $attendances->where('status', 'Absent')->count();
        $lateCount = $attendances->where('status', 'Late')->count();
        $leaveCount = $attendances->where('status', 'Leave')->count();
        $totalDays = $attendances->count();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
            ],
            'records' => $records,
            'summary' => [
                'total_hours' => round($totalHours, 2),
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'leave_count' => $leaveCount,
                'total_days' => $totalDays,
            ]
        ]);
    }
}
