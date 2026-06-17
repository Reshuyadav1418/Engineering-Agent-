<?php

namespace App\Services\Contracts;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

interface TeamServiceInterface
{
    public function all(): Collection;
    public function find(int $id): ?Team;
    public function create(array $data, array $members = []): Team;
    public function update(int $id, array $data, array $members = []): Team;
    public function delete(int $id): bool;
}
