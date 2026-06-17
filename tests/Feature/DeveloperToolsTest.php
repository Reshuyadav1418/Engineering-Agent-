<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Task;
use App\Models\WorkingHour;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperToolsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the main Developer Tools view load.
     */
    public function test_developer_tools_page_loads_successfully(): void
    {
        $response = $this->get(route('developer.tools'));

        $response->assertStatus(200);
        $response->assertSee('Developer Tools');
    }

    /**
     * Test Employee sandbox submission.
     */
    public function test_sandbox_employee_submission_creates_employee_and_calculates_scores(): void
    {
        $employeeData = [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'department' => 'Engineering',
            'role' => 'Software Engineer',
            'github_username' => 'ada-lovelace',
        ];

        $response = $this->post(route('developer.tools.employee'), $employeeData);

        $response->assertRedirect();
        $this->assertDatabaseHas('employees', [
            'email' => 'ada@example.com',
        ]);

        $employee = Employee::where('email', 'ada@example.com')->first();

        // Verify productivity score exists
        $this->assertDatabaseHas('productivity_scores', [
            'employee_id' => $employee->id,
        ]);

        // Verify leadership score exists
        $this->assertDatabaseHas('leadership_scores', [
            'employee_id' => $employee->id,
        ]);

        // Verify AI report exists
        $this->assertDatabaseHas('ai_reports', [
            'employee_id' => $employee->id,
        ]);
    }

    /**
     * Test Task sandbox submission.
     */
    public function test_sandbox_task_submission_creates_task_and_updates_scores(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'department' => 'QA',
            'role' => 'QA Engineer',
            'github_username' => 'johndoe',
        ]);

        $taskData = [
            'employee_id' => $employee->id,
            'title' => 'Write Unit Tests',
            'description' => 'Test devtools implementation',
            'status' => 'Completed',
            'assigned_date' => '2026-06-10',
            'completed_date' => '2026-06-11',
            'estimated_hours' => 5.0,
            'actual_hours' => 4.5,
        ];

        $response = $this->post(route('developer.tools.task'), $taskData);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Write Unit Tests',
            'employee_id' => $employee->id,
        ]);
    }

    /**
     * Test Working Hours sandbox submission.
     */
    public function test_sandbox_working_hours_submission(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'department' => 'QA',
            'role' => 'QA Engineer',
            'github_username' => 'johndoe',
        ]);

        $hoursData = [
            'employee_id' => $employee->id,
            'work_date' => '2026-06-11',
            'hours_worked' => 8.0,
        ];

        $response = $this->post(route('developer.tools.working_hours'), $hoursData);

        $response->assertRedirect();
        $this->assertDatabaseHas('working_hours', [
            'employee_id' => $employee->id,
            'hours_worked' => 8.0,
        ]);
    }

    /**
     * Test Attendance sandbox submission.
     */
    public function test_sandbox_attendance_submission(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'department' => 'QA',
            'role' => 'QA Engineer',
            'github_username' => 'johndoe',
        ]);

        $attendanceData = [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-06-11',
            'status' => 'Present',
        ];

        $response = $this->post(route('developer.tools.attendance'), $attendanceData);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'status' => 'Present',
        ]);
    }

    /**
     * Test Developer API GET endpoints.
     */
    public function test_developer_api_get_endpoints(): void
    {
        $employee = Employee::create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'department' => 'Engineering',
            'role' => 'Software Engineer',
            'github_username' => 'ada-lovelace',
        ]);

        $this->get('/api/developer/employees')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Ada Lovelace']);

        $this->get('/api/developer/tasks')
            ->assertStatus(200);

        $this->get('/api/developer/working-hours')
            ->assertStatus(200);

        $this->get('/api/developer/attendance')
            ->assertStatus(200);

        $this->get('/api/developer/metrics')
            ->assertStatus(200);
    }

    /**
     * Test Developer API POST endpoint handles validation failure by returning JSON.
     */
    public function test_developer_api_post_validation_failure_returns_json_even_without_header(): void
    {
        // Missing fields to trigger validation error
        $response = $this->post('/api/developer/employees', []);

        // Assert that the response is JSON with status 422, even though we did not pass Accept: application/json
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'email',
                'department',
                'role',
                'github_username',
            ],
        ]);
    }
}
