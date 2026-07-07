<?php

namespace App\Services\Contracts;

use App\Models\Team;
use Illuminate\Support\Collection;

interface TeamMetricsServiceInterface
{
    /**
     * Calculate and return metrics for a single team.
     *
     * @param Team $team
     * @return array
     */
    public function getTeamMetrics(Team $team): array;

    /**
     * Get the ranked leaderboard of all teams.
     *
     * @return Collection
     */
    public function getTeamLeaderboard(): Collection;
}
