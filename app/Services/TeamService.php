<?php

namespace App\Services;

use App\Models\Team;
use App\Services\Contracts\TeamServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class TeamService implements TeamServiceInterface
{
    public function all(): Collection
    {
        return Team::with(['lead', 'members', 'tasks'])->get();
    }

    public function find(int $id): ?Team
    {
        return Team::with(['lead', 'members.productivityScores', 'members.leadershipScores', 'tasks.members.employee'])->find($id);
    }

    public function create(array $data, array $members = []): Team
    {
        $team = Team::create($data);
        $this->syncMembers($team, $members);
        return $team;
    }

    public function update(int $id, array $data, array $members = []): Team
    {
        $team = Team::findOrFail($id);
        $team->update($data);
        $this->syncMembers($team, $members);
        return $team;
    }

    public function delete(int $id): bool
    {
        $team = Team::find($id);
        if ($team) {
            $team->delete();
            return true;
        }
        return false;
    }

    protected function syncMembers(Team $team, array $members)
    {
        $syncData = [];
        foreach ($members as $member) {
            if (!empty($member['employee_id'])) {
                $syncData[$member['employee_id']] = [
                    'role' => $member['role'] ?? 'Member'
                ];
            }
        }
        $team->members()->sync($syncData);
    }
}
