<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductivityScore;
use App\Repositories\Contracts\ProductivityScoreRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductivityScoreRepository implements ProductivityScoreRepositoryInterface
{
    public function all(): Collection
    {
        return ProductivityScore::with('employee')->get();
    }

    public function latestForAll(): Collection
    {
        $latestIds = ProductivityScore::selectRaw('MAX(id) as id')->groupBy('employee_id')->pluck('id');

        return ProductivityScore::with('employee')
            ->whereIn('id', $latestIds)
            ->get();
    }

    public function find(int $id): ?ProductivityScore
    {
        return ProductivityScore::find($id);
    }

    public function create(array $data): ProductivityScore
    {
        return ProductivityScore::create($data);
    }

    public function updateOrCreate(array $attributes, array $values): ProductivityScore
    {
        return ProductivityScore::updateOrCreate($attributes, $values);
    }
}
