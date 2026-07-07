<?php

namespace App\Repositories\Eloquent;

use App\Models\LeadershipScore;
use App\Repositories\Contracts\LeadershipScoreRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LeadershipScoreRepository implements LeadershipScoreRepositoryInterface
{
    public function all(): Collection
    {
        return LeadershipScore::with('employee')->get();
    }

    public function latestForAll(?int $limit = null): Collection
    {
        $latestIds = LeadershipScore::selectRaw('MAX(id) as max_id')
            ->groupBy('employee_id')
            ->pluck('max_id');

        $query = LeadershipScore::with('employee')
            ->whereIn('id', $latestIds)
            ->orderByDesc('leadership_score');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function find(int $id): ?LeadershipScore
    {
        return LeadershipScore::find($id);
    }

    public function create(array $data): LeadershipScore
    {
        return LeadershipScore::create($data);
    }

    public function updateOrCreate(array $attributes, array $values): LeadershipScore
    {
        return LeadershipScore::updateOrCreate($attributes, $values);
    }
}
