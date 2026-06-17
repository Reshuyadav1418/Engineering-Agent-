<?php

namespace Tests\Unit;

use App\Services\OllamaProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaProviderTest extends TestCase
{
    public function test_fallback_mock_report_when_ollama_server_is_unavailable(): void
    {
        // Fake HTTP request to return a failure status to trigger fallback
        Http::fake([
            'http://localhost:11434/*' => Http::response([], 500),
        ]);

        $provider = new OllamaProvider();
        $report = $provider->generateOllamaReport('John Doe', 10, 5, 8.5, 7.5, 9.0);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('strengths', $report);
        $this->assertArrayHasKey('weaknesses', $report);
        $this->assertArrayHasKey('suggestions', $report);
        
        $this->assertStringContainsString('John Doe', $report['summary']);
    }

    public function test_correct_response_when_ollama_server_is_available(): void
    {
        $mockJson = json_encode([
            'summary' => 'Excellent performance from John Doe.',
            'strengths' => ['Strong coding', 'Fast delivery'],
            'weaknesses' => ['Slightly quiet in meetings'],
            'suggestions' => ['Speak up more']
        ]);

        // Fake successful Ollama response
        Http::fake([
            'http://localhost:11434/*' => Http::response([
                'response' => $mockJson,
            ], 200),
        ]);

        $provider = new OllamaProvider();
        $report = $provider->generateOllamaReport('John Doe', 10, 5, 8.5, 7.5, 9.0);

        $this->assertIsArray($report);
        $this->assertEquals('Excellent performance from John Doe.', $report['summary']);
        $this->assertEquals(['Strong coding', 'Fast delivery'], $report['strengths']);
        $this->assertEquals(['Slightly quiet in meetings'], $report['weaknesses']);
        $this->assertEquals(['Speak up more'], $report['suggestions']);
    }
}
