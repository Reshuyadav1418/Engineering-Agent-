<?php

namespace App\Http\Controllers;

use App\Models\VcsMetric;
use App\Services\VCSIntegrationService;
use Illuminate\Http\Request;

class VCSController extends Controller
{
    protected VCSIntegrationService $vcsService;

    public function __construct(VCSIntegrationService $vcsService)
    {
        $this->vcsService = $vcsService;
    }

    /**
     * Show VCS Dashboard.
     */
    public function index()
    {
        // Auto-seed if empty to populate the dashboard on first visit
        if (VcsMetric::count() === 0) {
            $this->vcsService->syncAll();
        }

        $metrics = VcsMetric::with('employee')->get();

        $tokens = [
            'github' => !empty(env('GITHUB_TOKEN')),
            'gitlab' => !empty(env('GITLAB_TOKEN')),
            'bitbucket' => !empty(env('BITBUCKET_TOKEN')),
        ];

        return view('vcs.index', compact('metrics', 'tokens'));
    }

    /**
     * Sync VCS metrics for all employees.
     */
    public function sync(Request $request)
    {
        $this->vcsService->syncAll();

        return redirect()->back()->with('success', 'VCS Metrics synchronized successfully and developer scores updated.');
    }
}
