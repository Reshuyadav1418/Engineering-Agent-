<?php

namespace App\Repositories\Contracts;

use App\Models\LeadershipScore;
use Illuminate\Database\Eloquent\Collection;

interface LeadershipScoreRepositoryInterface
{
    public function all(): Collection;
    public function latestForAll(?int $limit = null): Collection;
    public function find(int $id): ?LeadershipScore;
    public function create(array $data): LeadershipScore;
    public function updateOrCreate(array $attributes, array $values): LeadershipScore;
}
