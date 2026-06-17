<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\VcsMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VCSTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test VCS Dashboard page loads successfully.
     */
    public function test_vcs_dashboard_loads_successfully(): void
    {
        $employee = Employee::create([
            'name' => 'Linus Torvalds',
            'email' => 'linus@example.com',
            'department' => 'Engineering',
            'role' => 'Principal Engineer',
            'github_username' => 'torvalds',
        ]);

        $response = $this->get(route('vcs.index'));

        $response->assertStatus(200);
        $response->assertSee('VCS Integration');
        $response->assertSee('Linus Torvalds');
        $response->assertSee('torvalds');
    }

    /**
     * Test VCS Sync route triggers synchronization and populates vcs_metrics.
     */
    public function test_vcs_sync_populates_metrics(): void
    {
        $employee = Employee::create([
            'name' => 'Guido van Rossum',
            'email' => 'guido@example.com',
            'department' => 'Engineering',
            'role' => 'Senior Developer',
            'github_username' => 'gvanrossum',
        ]);

        // Prior to sync, vcs_metrics should be empty (or populated automatically on dashboard load if checked there, but route call directly: )
        VcsMetric::truncate();

        $response = $this->post(route('vcs.sync'));

        $response->assertRedirect();
        
        // Assert metrics populated for github, gitlab, bitbucket
        $this->assertDatabaseHas('vcs_metrics', [
            'employee_id' => $employee->id,
            'provider' => 'github',
            'git_username' => 'gvanrossum'
        ]);

        $this->assertDatabaseHas('vcs_metrics', [
            'employee_id' => $employee->id,
            'provider' => 'gitlab',
            'git_username' => 'gvanrossum'
        ]);

        $this->assertDatabaseHas('vcs_metrics', [
            'employee_id' => $employee->id,
            'provider' => 'bitbucket',
            'git_username' => 'gvanrossum'
        ]);
    }

    /**
     * Test VCS Real API Mode for GitHub when GITHUB_TOKEN is set.
     */
    public function test_github_integration_service_hits_real_api_when_token_is_present(): void
    {
        // Set GITHUB_TOKEN temporarily in config or env
        putenv('GITHUB_TOKEN=fake_github_token');

        $employee = Employee::create([
            'name' => 'Yukihiro Matsumoto',
            'email' => 'matz@example.com',
            'department' => 'Engineering',
            'role' => 'Senior Developer',
            'github_username' => 'matz',
        ]);

        Http::fake([
            'https://api.github.com/users/matz/events' => Http::response([
                [
                    'type' => 'PushEvent',
                    'payload' => [
                        'commits' => [
                            ['sha' => '123'],
                            ['sha' => '456']
                        ]
                    ]
                ],
                [
                    'type' => 'PullRequestEvent'
                ]
            ], 200)
        ]);

        $response = $this->post(route('vcs.sync'));

        $response->assertRedirect();

        $this->assertDatabaseHas('vcs_metrics', [
            'employee_id' => $employee->id,
            'provider' => 'github',
            'git_username' => 'matz'
        ]);

        // Clean up environment variable
        putenv('GITHUB_TOKEN');
    }
}
