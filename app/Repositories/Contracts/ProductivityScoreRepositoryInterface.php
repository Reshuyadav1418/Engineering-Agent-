<?php

namespace App\Repositories\Contracts;

use App\Models\ProductivityScore;
use Illuminate\Database\Eloquent\Collection;

interface ProductivityScoreRepositoryInterface
{
    public function all(): Collection;
    public function latestForAll(): Collection;
    public function find(int $id): ?ProductivityScore;
    public function create(array $data): ProductivityScore;
    public function updateOrCreate(array $attributes, array $values): ProductivityScore;
}
