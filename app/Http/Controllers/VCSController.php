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
            'github'    => !empty(config('services.github.token')),
            'gitlab'    => !empty(config('services.gitlab.token')),
            'bitbucket' => !empty(config('services.bitbucket.token')),
        ];

        return view('vcs.index', compact('metrics', 'tokens'));
    }

    /**
     * Sync VCS metrics for all employees.
     *
     * By default only syncs employees not synced in the last hour to avoid
     * hammering external APIs and hitting the PHP execution time limit.
     * Pass ?force=1 in the request to force a full re-sync of everyone.
     */
    public function sync(Request $request)
    {
        // Lift the PHP time limit for this long-running operation
        set_time_limit(300); // 5 minutes max

        $force = (bool) $request->input('force', false);
        $this->vcsService->syncAll(force: $force);

        $msg = $force
            ? 'Force-sync complete. All employee VCS metrics and scores updated.'
            : 'VCS Metrics synchronized successfully and developer scores updated.';

        return redirect()->back()->with('success', $msg);
    }
}
