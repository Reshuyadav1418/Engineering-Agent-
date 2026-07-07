<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Team;
use App\Repositories\Contracts\AIReportRepositoryInterface;
use App\Services\Contracts\AIAnalysisServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AIReportController extends Controller
{
    protected AIAnalysisServiceInterface $analysisService;
    protected AIReportRepositoryInterface $reportRepository;

    public function __construct(
        AIAnalysisServiceInterface $analysisService,
        AIReportRepositoryInterface $reportRepository
    ) {
        $this->analysisService = $analysisService;
        $this->reportRepository = $reportRepository;
    }

    public function index(): View
    {
        $reports = $this->reportRepository->all();

        return view('ai_reports.index', compact('reports'));
    }

    public function show(Employee $employee): View
    {
        $report = $this->reportRepository->latestForEmployee($employee->id);
        $individualTasksCount = $employee->tasks()->count();
        $teamTasksCount = $employee->taskMembers()->count();
        $tasksAssigned = $individualTasksCount + $teamTasksCount;
        
        $individualCompleted = $employee->tasks()->where('status', 'Completed')->count();
        $teamCompleted = $employee->taskMembers()->where('status', 'Completed')->count();
        $tasksCompleted = $individualCompleted + $teamCompleted;
        
        $completionRate = $tasksAssigned ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;
        
        $productivityModel = $employee->productivityScores()->latest('id')->first();
        $productivityScore = $productivityModel->productivity_score ?? 0;
        $teamContribution = $productivityModel->team_contribution ?? 0;
        
        $leadershipScore = optional($employee->leadershipScores()->latest('id')->first())->leadership_score ?? 0;

        return view('ai_reports.show', compact(
            'employee',
            'report',
            'tasksAssigned',
            'tasksCompleted',
            'completionRate',
            'productivityScore',
            'leadershipScore',
            'individualTasksCount',
            'teamTasksCount',
            'teamContribution'
        ));
    }

    public function generate(Employee $employee): RedirectResponse
    {
        $individualTasksCount = $employee->tasks()->count();
        $teamTasksCount = $employee->taskMembers()->count();
        
        $productivityModel = $employee->productivityScores()->latest('id')->first();
        $productivityScore = $productivityModel->productivity_score ?? 0;
        $teamContribution = $productivityModel->team_contribution ?? 0;
        
        $leadershipScore = optional($employee->leadershipScores()->latest('id')->first())->leadership_score ?? 0;

        $analysis = $this->analysisService->generateReport(
            $employee->name,
            $individualTasksCount,
            $teamTasksCount,
            $teamContribution,
            $productivityScore,
            $leadershipScore
        );

        $this->reportRepository->create([
            'employee_id' => $employee->id,
            'summary' => $analysis['summary'] ?? '',
            'strengths' => $analysis['strengths'] ?? [],
            'weaknesses' => $analysis['weaknesses'] ?? [],
            'suggestions' => $analysis['suggestions'] ?? [],
            'created_at' => now(),
        ]);

        return redirect()->route('ai.report.show', $employee)->with('success', 'AI report generated successfully.');
    }

    public function showTeam(Team $team): View
    {
        $report = $this->reportRepository->latestForTeam($team->id);
        
        $teamMetricsService = app(\App\Services\Contracts\TeamMetricsServiceInterface::class);
        $metrics = $teamMetricsService->getTeamMetrics($team);

        return view('ai_reports.show_team', compact('team', 'report', 'metrics'));
    }

    public function generateTeam(Team $team): RedirectResponse
    {
        $teamMetricsService = app(\App\Services\Contracts\TeamMetricsServiceInterface::class);
        $metrics = $teamMetricsService->getTeamMetrics($team);

        $analysis = $this->analysisService->generateTeamReport(
            $team->name,
            $metrics['members_count'],
            $metrics['completed_tasks'],
            $metrics['productivity_score'],
            $metrics['leadership_score'],
            $metrics['team_completion_rate'],
            $metrics['team_consistency'],
            $metrics['team_collaboration']
        );

        $this->reportRepository->create([
            'team_id' => $team->id,
            'summary' => $analysis['summary'] ?? '',
            'strengths' => $analysis['strengths'] ?? [],
            'weaknesses' => $analysis['weaknesses'] ?? [],
            'suggestions' => $analysis['suggestions'] ?? [],
            'created_at' => now(),
        ]);

        return redirect()->route('ai.report.show_team', $team)->with('success', 'Team AI report generated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->reportRepository->delete($id);
        return redirect()->route('ai.report.index')->with('success', 'AI report deleted successfully.');
    }
}
