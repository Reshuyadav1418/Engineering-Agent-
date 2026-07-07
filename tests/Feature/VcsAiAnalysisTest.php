<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\VcsMetric;
use App\Models\VcsAiReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VcsAiAnalysisTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test VCS Developer Analysis show page loads successfully.
     */
    public function test_vcs_analysis_show_page_loads_successfully(): void
    {
        $employee = Employee::create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'department' => 'Engineering',
            'role' => 'Senior Developer',
            'github_username' => 'ada_love',
        ]);

        VcsMetric::create([
            'employee_id' => $employee->id,
            'provider' => 'github',
            'git_username' => 'ada_love',
            'commits' => 45,
            'pull_requests' => 12,
            'repositories' => 6,
            'reviews' => 8,
            'bugs_fixed' => 5,
            'deployments' => 4,
            'deployment_frequency' => 1.0,
            'last_synced_at' => now(),
        ]);

        $response = $this->get(route('vcs.analysis.show', $employee));

        $response->assertStatus(200);
        $response->assertSee('Ada Lovelace');
        $response->assertSee('ada_love');
        $response->assertSee('Github');
        $response->assertSee('VCS Developer Analysis');
    }

    /**
     * Test VCS AI Analysis report generation.
     */
    public function test_vcs_analysis_report_generation(): void
    {
        $employee = Employee::create([
            'name' => 'Grace Hopper',
            'email' => 'grace@example.com',
            'department' => 'Engineering',
            'role' => 'Principal Engineer',
            'github_username' => 'grace_hopper',
        ]);

        VcsMetric::create([
            'employee_id' => $employee->id,
            'provider' => 'github',
            'git_username' => 'grace_hopper',
            'commits' => 120,
            'pull_requests' => 30,
            'repositories' => 15,
            'reviews' => 25,
            'bugs_fixed' => 18,
            'deployments' => 12,
            'deployment_frequency' => 3.0,
            'last_synced_at' => now(),
        ]);

        // Fake the Ollama AI Response
        Http::fake([
            'http://localhost:11434/api/generate' => Http::response([
                'response' => json_encode([
                    'summary' => 'Outstanding lead developer with exceptional commits and PR handling.',
                    'code_quality_score' => 92,
                    'collaboration_score' => 95,
                    'delivery_score' => 90,
                    'risk_analysis' => 'No major risks detected.',
                    'recommendations' => 'Keep leading project architectures.'
                ])
            ], 200)
        ]);

        $response = $this->post(route('vcs.analysis.generate', $employee));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('vcs_ai_reports', [
            'employee_id' => $employee->id,
            'code_quality_score' => 92,
            'collaboration_score' => 95,
            'delivery_score' => 90,
            'summary' => 'Outstanding lead developer with exceptional commits and PR handling.',
            'risk_analysis' => 'No major risks detected.',
            'recommendations' => 'Keep leading project architectures.'
        ]);

        // Verify the details are rendered on the show page after redirect
        $showResponse = $this->followingRedirects()->post(route('vcs.analysis.generate', $employee));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Outstanding lead developer');
        $showResponse->assertSee('92');
        $showResponse->assertSee('95');
    }
}
