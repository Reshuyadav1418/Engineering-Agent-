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

    public function latestForAll(): Collection
    {
        $latestIds = LeadershipScore::selectRaw('MAX(id) as max_id')
            ->groupBy('employee_id')
            ->pluck('max_id');

        return LeadershipScore::with('employee')
            ->whereIn('id', $latestIds)
            ->get();
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
