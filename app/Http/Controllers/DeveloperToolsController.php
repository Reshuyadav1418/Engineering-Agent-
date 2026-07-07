<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use App\Models\WorkingHour;
use App\Models\Attendance;
use App\Services\Contracts\MetricsServiceInterface;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Services\Contracts\AIAnalysisServiceInterface;
use App\Repositories\Contracts\AIReportRepositoryInterface;
use App\Services\WorkdeskIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DeveloperToolsController extends Controller
{
    protected MetricsServiceInterface $metricsService;
    protected LeadershipScoreServiceInterface $leadershipScoreService;
    protected AIAnalysisServiceInterface $analysisService;
    protected AIReportRepositoryInterface $reportRepository;
    protected WorkdeskIntegrationService $workdeskService;

    public function __construct(
        MetricsServiceInterface $metricsService,
        LeadershipScoreServiceInterface $leadershipScoreService,
        AIAnalysisServiceInterface $analysisService,
        AIReportRepositoryInterface $reportRepository,
        WorkdeskIntegrationService $workdeskService
    ) {
        $this->metricsService = $metricsService;
        $this->leadershipScoreService = $leadershipScoreService;
        $this->analysisService = $analysisService;
        $this->reportRepository = $reportRepository;
        $this->workdeskService = $workdeskService;
    }

    /**
     * Show Developer Tools UI.
     */
    public function index()
    {
        $employees = Employee::orderBy('name')->get();
        $tasks = Task::with('employee')->latest()->take(10)->get();
        $workingHours = WorkingHour::with('employee')->latest()->take(10)->get();
        $attendances = Attendance::with('employee')->latest()->take(10)->get();

        return view('developer-tools.index', compact(
            'employees',
            'tasks',
            'workingHours',
            'attendances'
        ));
    }

    /**
     * Helper to auto-update scores and AI report for a specific employee.
     */
    protected function autoUpdateEmployeeData($employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return;
        }

        // 1. Recalculate Productivity Score
        $productivityScore = $this->metricsService->generateForEmployee($employee);

        // 2. Recalculate Leadership Score
        $leadershipScore = $this->leadershipScoreService->generateForEmployee($employee, $productivityScore);

        // 3. Generate/Refresh AI Report
        $tasksAssigned = $employee->tasks->count();
        $tasksCompleted = $employee->tasks->where('status', 'Completed')->count();
        $completionRate = $tasksAssigned ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

        try {
            $analysis = $this->analysisService->generateReport(
                $employee->name,
                $tasksCompleted,
                $completionRate,
                $productivityScore->productivity_score,
                $leadershipScore->leadership_score
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Ollama/AI offline in Developer Sandbox: ' . $e->getMessage());
            $analysis = [
                'summary' => 'AI Report generator placeholder: Local Ollama AI service was offline or unreachable during Sandbox data submission.',
                'strengths' => ['Sandbox Employee data created successfully.'],
                'weaknesses' => ['AI provider offline.'],
                'suggestions' => ['Please check that the local Ollama daemon is active.']
            ];
        }

        $this->reportRepository->create([
            'employee_id' => $employee->id,
            'summary' => $analysis['summary'] ?? '',
            'strengths' => $analysis['strengths'] ?? [],
            'weaknesses' => $analysis['weaknesses'] ?? [],
            'suggestions' => $analysis['suggestions'] ?? [],
            'created_at' => now(),
        ]);
    }

    /**
     * Sandbox Form Submit: Employee
     */
    public function submitEmployee(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:employees,email',
            'department'      => 'required|string|max:255',
            'role'            => 'required|string|max:255',
            'github_username' => 'required|string|max:255',
            'gitlab_username' => 'nullable|string|max:255',
        ]);

        $employee = Employee::create($data);

        // Auto-update score and report for the new employee
        $this->autoUpdateEmployeeData($employee->id);

        return redirect()->back()->with('success', "Employee '{$employee->name}' added and metrics generated successfully.");
    }

    /**
     * Sandbox Form Submit: Task
     */
    public function submitTask(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Pending,In Progress,Completed',
            'assigned_date' => 'required|date',
            'completed_date' => 'nullable|date|required_if:status,Completed',
            'estimated_hours' => 'required|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0|required_if:status,Completed',
        ]);

        $task = Task::create($data);

        // Auto-update scores & report for the employee
        $this->autoUpdateEmployeeData($task->employee_id);

        return redirect()->back()->with('success', "Task '{$task->title}' added. Productivity, Leadership scores and AI Report auto-updated.");
    }

    /**
     * Sandbox Form Submit: Working Hours
     */
    public function submitWorkingHours(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0',
        ]);

        $workingHour = WorkingHour::create($data);

        // Auto-update scores & report for the employee
        $this->autoUpdateEmployeeData($workingHour->employee_id);

        return redirect()->back()->with('success', "Working Hours record added. Metrics auto-updated.");
    }

    /**
     * Sandbox Form Submit: Attendance
     */
    public function submitAttendance(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:Present,Absent,Late,Leave',
        ]);

        $attendance = Attendance::create($data);

        // Auto-update scores & report for the employee
        $this->autoUpdateEmployeeData($attendance->employee_id);

        return redirect()->back()->with('success', "Attendance record added. Metrics auto-updated.");
    }

    /**
     * API Tester HTTP request proxy.
     * Prevents deadlock issues on single-threaded dev servers (e.g. php artisan serve)
     * by executing local requests in-memory via app()->handle().
     */
    public function testApi(Request $request)
    {
        $baseUrl = $request->input('base_url');
        $endpoint = $request->input('endpoint');
        $method = strtoupper($request->input('method', 'GET'));
        
        $headersInput = $request->input('headers', '{}');
        $headers = json_decode($headersInput, true);
        if (!is_array($headers)) {
            $headers = [];
        }

        $bodyInput = $request->input('body', '{}');
        $body = json_decode($bodyInput, true);
        if (!is_array($body)) {
            $body = [];
        }

        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $startTime = microtime(true);

        // Parse URL target info
        $urlInfo = parse_url($url);
        $requestHost = request()->getHost();
        $requestPort = request()->getPort();
        
        $targetHost = $urlInfo['host'] ?? '';
        $targetPort = $urlInfo['port'] ?? null;
        
        $isLocal = false;
        if (empty($targetHost)) {
            $isLocal = true;
        } else {
            $isSameHost = strtolower($targetHost) === strtolower($requestHost);
            $isLocalhost = strtolower($targetHost) === 'localhost' || $targetHost === '127.0.0.1';
            
            if ($isSameHost || $isLocalhost) {
                // If same host, check if port is the same or not specified (which defaults to standard ports)
                $isSamePort = ($targetPort == $requestPort) || (empty($targetPort) && ($requestPort == 80 || $requestPort == 443 || $requestPort == 8000));
                if ($isSamePort) {
                    $isLocal = true;
                }
            }
        }

        // 1. Run local requests internally (In-Memory) to prevent thread locks
        if ($isLocal) {
            try {
                $path = $urlInfo['path'] ?? '/';
                
                // Strip the base URL subdirectory path (e.g. /Simpeltask/.../public) if present
                $baseUrlPrefix = request()->getBaseUrl();
                if (!empty($baseUrlPrefix) && str_starts_with($path, $baseUrlPrefix)) {
                    $path = substr($path, strlen($baseUrlPrefix));
                }
                
                $uri = $path;
                if (!empty($urlInfo['query'])) {
                    $uri .= '?' . $urlInfo['query'];
                }

                $content = ($method !== 'GET') ? json_encode($body) : null;
                
                // Create custom request clone
                $localRequest = Request::create(
                    $uri,
                    $method,
                    ($method === 'GET' ? $body : []),
                    [], // cookies
                    [], // files
                    [
                        'CONTENT_TYPE' => 'application/json',
                        'HTTP_ACCEPT' => 'application/json',
                    ],
                    $content
                );

                // Explicitly force JSON headers
                $localRequest->headers->set('Accept', 'application/json');
                $localRequest->headers->set('Content-Type', 'application/json');
                $localRequest->server->set('HTTP_ACCEPT', 'application/json');
                $localRequest->server->set('CONTENT_TYPE', 'application/json');

                // Copy input headers
                foreach ($headers as $key => $value) {
                    $lKey = strtolower($key);
                    if ($lKey !== 'host') {
                        $localRequest->headers->set($key, $value);
                        $localRequest->server->set('HTTP_' . strtoupper(str_replace('-', '_', $key)), $value);
                    }
                }

                // Dispatch to the application router internally
                $response = app()->handle($localRequest);

                $responseTime = round((microtime(true) - $startTime) * 1000, 2);

                $responseHeaders = [];
                foreach ($response->headers->all() as $name => $values) {
                    $responseHeaders[$name] = implode(', ', $values);
                }

                $responseBody = $response->getContent();
                $decodedBody = json_decode($responseBody, true);

                return response()->json([
                    'status' => $response->getStatusCode(),
                    'headers' => $responseHeaders,
                    'body' => is_array($decodedBody) ? $decodedBody : $responseBody,
                    'time_ms' => $responseTime
                ]);
            } catch (\Throwable $e) {
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                return response()->json([
                    'status' => 500,
                    'headers' => [],
                    'body' => [
                        'error' => 'Internal router execution failed.',
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ],
                    'time_ms' => $responseTime
                ], 500);
            }
        }

        // 2. Fall back to external HTTP request proxy for remote URLs
        try {
            if (!empty($body) && !isset($headers['Content-Type']) && !isset($headers['content-type'])) {
                $headers['Content-Type'] = 'application/json';
            }

            $response = Http::withHeaders($headers);

            if ($method === 'GET') {
                $res = $response->get($url, $body);
            } elseif ($method === 'POST') {
                $res = $response->post($url, $body);
            } elseif ($method === 'PUT') {
                $res = $response->put($url, $body);
            } elseif ($method === 'DELETE') {
                $res = $response->delete($url, $body);
            } else {
                $res = $response->send($method, $url, ['json' => $body]);
            }

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'status' => $res->status(),
                'headers' => $res->headers(),
                'body' => $res->json() ?? $res->body(),
                'time_ms' => $responseTime
            ]);
        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            return response()->json([
                'status' => 500,
                'headers' => [],
                'body' => [
                    'error' => 'Connection failed or endpoint returned an error.',
                    'message' => $e->getMessage()
                ],
                'time_ms' => $responseTime
            ], 500);
        }
    }

    /*
     |--------------------------------------------------------------------------
     | API Endpoints for API Tester / Sandbox simulation
     |--------------------------------------------------------------------------
     */

    public function apiGetEmployees()
    {
        return response()->json($this->workdeskService->getEmployees());
    }

    public function apiPostEmployee(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:employees,email',
            'department'      => 'required|string|max:255',
            'role'            => 'required|string|max:255',
            'github_username' => 'required|string|max:255',
            'gitlab_username' => 'nullable|string|max:255',
        ]);

        $employee = Employee::create($data);
        $this->autoUpdateEmployeeData($employee->id);

        return response()->json([
            'success' => true,
            'message' => 'Employee created and scores updated successfully via API.',
            'data' => $employee
        ], 211);
    }

    public function apiGetTasks()
    {
        return response()->json($this->workdeskService->getTasks());
    }

    public function apiPostTask(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Pending,In Progress,Completed',
            'assigned_date' => 'required|date',
            'completed_date' => 'nullable|date',
            'estimated_hours' => 'required|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $task = Task::create($data);
        $this->autoUpdateEmployeeData($task->employee_id);

        return response()->json([
            'success' => true,
            'message' => 'Task created and metrics updated via API.',
            'data' => $task
        ], 211);
    }

    public function apiGetWorkingHours()
    {
        return response()->json($this->workdeskService->getWorkingHours());
    }

    public function apiPostWorkingHours(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0',
        ]);

        $workingHour = WorkingHour::create($data);
        $this->autoUpdateEmployeeData($workingHour->employee_id);

        return response()->json([
            'success' => true,
            'message' => 'Working hours record created via API.',
            'data' => $workingHour
        ], 211);
    }

    public function apiGetAttendance()
    {
        return response()->json($this->workdeskService->getAttendance());
    }

    public function apiPostAttendance(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:Present,Absent,Late,Leave',
        ]);

        $attendance = Attendance::create($data);
        $this->autoUpdateEmployeeData($attendance->employee_id);

        return response()->json([
            'success' => true,
            'message' => 'Attendance record created via API.',
            'data' => $attendance
        ], 211);
    }

    public function apiGetMetrics()
    {
        return response()->json($this->workdeskService->getMetrics());
    }
}
