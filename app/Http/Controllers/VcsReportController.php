<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\VcsAiReport;
use App\Services\VcsAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VcsReportController extends Controller
{
    protected VcsAnalysisService $analysisService;

    public function __construct(VcsAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Show detailed VCS Developer Analysis page.
     */
    public function show(Employee $employee): View
    {
        $metrics = $this->analysisService->getMetricsForEmployee($employee);
        
        $report = VcsAiReport::where('employee_id', $employee->id)
            ->latest('id')
            ->first();

        return view('vcs.show', compact('employee', 'metrics', 'report'));
    }

    /**
     * Generate or refresh VCS AI Analysis.
     */
    public function generate(Employee $employee): RedirectResponse
    {
        $metrics = $this->analysisService->getMetricsForEmployee($employee);

        $analysis = $this->analysisService->generateReport($employee, $metrics);

        VcsAiReport::create([
            'employee_id' => $employee->id,
            'summary' => $analysis['summary'] ?? '',
            'code_quality_score' => $analysis['code_quality_score'] ?? 70,
            'collaboration_score' => $analysis['collaboration_score'] ?? 70,
            'delivery_score' => $analysis['delivery_score'] ?? 70,
            'risk_analysis' => $analysis['risk_analysis'] ?? '',
            'recommendations' => $analysis['recommendations'] ?? '',
        ]);

        return redirect()
            ->route('vcs.analysis.show', $employee)
            ->with('success', 'VCS AI Analysis report generated successfully.');
    }
}
